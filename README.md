# Phishing Script

PHP script for credential collection via a backdoored HTML form.

### Disclaimer:
For educational purposes only. Unauthorized use is illegal. Ensure proper authorization.

### Description:
Collects credentials from manipulated HTML forms. Features include logging, redirection, and handling of multiple password retries.

### Configuration:
1. Open `Phishing Post.php`.
2. Modify variables:
    - `$harvest_filename`: Data filename.
    - `$post_url`: Target post URL.
    - `$resend_post_data`: Resend data to redirect address?
    - `$redirect`: Redirection after data collection.
    - `$redirect_type`: Redirection type.
    - `$wrong_password_url`: "Wrong password" redirection URL.
    - `$password_retry`: Login attempts before redirection.
    - `$log_everyone`: Log all visitors?
    - `$log_format`: Logging format.
    - `$csv_separator`: CSV log separator.
    - `$show_meta_data`: Include metadata in logs?
    - `$exclude_visitors`: Exclude based on VISITOR_ID.

### Usage:
1. Configure as per above.
2. Upload `Phishing Post.php` to web server.
3. Create backdoored HTML form pointing to script.
4. Deploy or send form for phishing.

### Legal Notice:
Malicious or unauthorized use is prohibited. Author & OpenAI not liable for misuse. Adhere to all applicable laws & regulations.

### Support:
For questions, contact the script author or refer to the OpenAI community.

### License:
MIT
