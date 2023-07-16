# Phishing Script README

This PHP script is intended to be used for phishing attempts as a credentials collector linked to a backdoored HTML form. It collects user data and can transmit it to a specified login backend.

## Disclaimer

**Important:** This script is for educational purposes only and should never be used for illegal activities. Unauthorized access to personal information is a violation of privacy laws. Use this script responsibly and with proper authorization.

## Description

The phishing script is designed to collect user credentials from a manipulated HTML form. It includes features for logging collected data, redirecting users, and handling multiple password retries.

## Configuration

Before using the script, you need to configure certain parameters to fit your specific phishing campaign. Open the script file (`Phishing Post.php`) and modify the following variables in the configuration section:

- `$harvest_filename`: Filename for harvested data (keep it non-guessable).
- `$post_url`: Target URL used for posting the form.
- `$resend_post_data`: Resend post data to the redirect address?
- `$redirect`: Target to redirect to after collecting input data.
- `$redirect_type`: Type of page redirection.
- `$wrong_password_url`: URL for the "wrong password" message redirection.
- `$password_retry`: Specifies how many login attempts the user has before redirection.
- `$log_everyone`: Set to true to log everyone, regardless of their user agent.
- `$log_format`: Set the format for logging results.
- `$csv_separator`: Separator used in the CSV log file.
- `$show_meta_data`: Specifies whether to include metadata in the harvesting log.
- `$exclude_visitors`: Exclude specific clients based on their VISITOR_ID value.

## Usage

1. Configure the script by following the instructions in the "Configuration" section.
2. Upload the script (`Phishing Post.php`) to your web server or phishing environment.
3. Create a backdoored HTML form with the action attribute pointing to the script URL.
4. Deploy the HTML form on a webpage or send it to targets as part of a phishing campaign.

## Disclaimer and Legal Notice

Use of this script for any malicious or unauthorized activity is strictly prohibited. The author and OpenAI are not responsible for any illegal or unethical use of this script. Please use it responsibly and in compliance with applicable laws and regulations.

## Support

For any questions or assistance, please contact the script author or refer to the OpenAI community for support.

## License

This project is licensed under the [MIT License](LICENSE).
