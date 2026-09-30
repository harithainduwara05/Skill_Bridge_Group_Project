<?php
/**
 * SkillBridge - Admin User Credentials Email Dispatcher
 * Pure PHP SMTP socket-based email sender (No external libraries/APIs used)
 */

require_once __DIR__ . '/../../Config/env_loader.php';

if (!function_exists('admin_smtp_read')) {
    function admin_smtp_read($socket) {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") { 
                break; 
            }
        }
        return $data;
    }
}

if (!function_exists('admin_smtp_write')) {
    function admin_smtp_write($socket, $cmd) {
        fputs($socket, $cmd . "\r\n");
    }
}

if (!function_exists('send_raw_smtp_email')) {
    function send_raw_smtp_email($toEmail, $subject, $messageBody) {
        $host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $port = intval($_ENV['SMTP_PORT'] ?? 465);
        $user = $_ENV['SMTP_USER'] ?? '';
        $pass = $_ENV['SMTP_PASS'] ?? '';

        if (empty($user) || empty($pass)) {
            throw new Exception("SMTP credentials missing in .env file.");
        }

        // Standard Email Headers
        $headers  = "From: SkillBridge <$user>\r\n";
        $headers .= "Reply-To: $user\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $headers .= "\r\n";

        // Protocol prefix: port 465 requires SSL wrapper
        $protocol = ($port === 465) ? 'ssl://' : '';

        $socket = fsockopen($protocol . $host, $port, $errno, $errstr, 15);
        if (!$socket) {
            throw new Exception("Failed to connect to SMTP server: $errstr ($errno)");
        }

        // 1. Initial Greeting
        admin_smtp_read($socket);

        // 2. EHLO
        admin_smtp_write($socket, "EHLO " . $host);
        $res = admin_smtp_read($socket);

        // 3. STARTTLS if port 587
        if ($port === 587 && strpos($res, 'STARTTLS') !== false) {
            admin_smtp_write($socket, "STARTTLS");
            admin_smtp_read($socket);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            admin_smtp_write($socket, "EHLO " . $host);
            admin_smtp_read($socket);
        }

        // 4. AUTH LOGIN
        admin_smtp_write($socket, "AUTH LOGIN");
        admin_smtp_read($socket);

        admin_smtp_write($socket, base64_encode($user));
        admin_smtp_read($socket);

        admin_smtp_write($socket, base64_encode($pass));
        $res = admin_smtp_read($socket);

        if (substr($res, 0, 3) !== '235') {
            throw new Exception("SMTP Authentication Failed: " . trim($res));
        }

        // 5. MAIL FROM
        admin_smtp_write($socket, "MAIL FROM: <$user>");
        admin_smtp_read($socket);

        // 6. RCPT TO
        admin_smtp_write($socket, "RCPT TO: <$toEmail>");
        admin_smtp_read($socket);

        // 7. DATA
        admin_smtp_write($socket, "DATA");
        admin_smtp_read($socket);

        // 8. SEND MESSAGE CONTENT
        admin_smtp_write($socket, $headers . $messageBody . "\r\n.");
        $res = admin_smtp_read($socket);

        if (substr($res, 0, 3) !== '250') {
            throw new Exception("Failed to send email data: " . trim($res));
        }

        // 9. QUIT
        admin_smtp_write($socket, "QUIT");
        fclose($socket);

        return true;
    }
}

/**
 * Sends newly created account credentials to a user using SkillBridge email template.
 */
function send_user_credentials_email($toEmail, $userName, $plainPassword, $username = null, $subject = null) {
    if (empty($username)) {
        $username = $toEmail;
    }
    if (empty($subject)) {
        $subject = "Welcome to SkillBridge - Your Account Credentials";
    }

    $message  = "Dear " . $userName . ",\r\n\r\n";
    $message .= "Welcome to SkillBridge!\r\n\r\n";
    $message .= "An account has been created for you on the SkillBridge platform by the administrator. Please find your login details below:\r\n\r\n";
    $message .= "Username: " . $username . "\r\n";
    $message .= "Password: " . $plainPassword . "\r\n\r\n";
    $message .= "You can use these credentials to log in to the SkillBridge platform and access your account.\r\n\r\n";
    $message .= "For security purposes, please do not share your login credentials with anyone and consider changing your password after your first login.\r\n\r\n";
    $message .= "If you experience any issues while logging in or have any questions, please contact the administrator.\r\n\r\n";
    $message .= "Best regards,\r\n";
    $message .= "SkillBridge Administration Team\r\n";

    return send_raw_smtp_email($toEmail, $subject, $message);
}

/**
 * Sends an activation invitation email instructing the user to set their password via OTP.
 * Zero-knowledge password security (no passwords sent in plaintext).
 */
function send_user_activation_email($toEmail, $userName, $subject = null) {
    if (empty($subject)) {
        $subject = "Welcome to SkillBridge - Activate Your Account";
    }

    $message  = "Dear " . $userName . ",\r\n\r\n";
    $message .= "Welcome to SkillBridge!\r\n\r\n";
    $message .= "An account has been created for you on the SkillBridge platform by the administrator.\r\n\r\n";
    $message .= "For your security, administrators do not set or view your password. To activate your account and choose your personal password, please follow these steps:\r\n\r\n";
    $message .= "1. Visit the SkillBridge Login page.\r\n";
    $message .= "2. Click on 'Forgot Password / First-Time Setup'.\r\n";
    $message .= "3. Enter your registered email address (" . $toEmail . ") to receive a 6-digit verification code.\r\n";
    $message .= "4. Enter the verification code and set your new private password.\r\n\r\n";
    $message .= "Once completed, you can log in immediately and access your SkillBridge dashboard.\r\n\r\n";
    $message .= "If you did not request this account or have any questions, please contact our administration team.\r\n\r\n";
    $message .= "Best regards,\r\n";
    $message .= "SkillBridge Administration Team\r\n";

    return send_raw_smtp_email($toEmail, $subject, $message);
}
