<?php

function send_verification_email($to, $code) {
    $host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
    $port = $_ENV['SMTP_PORT'] ?? 587;
    $user = $_ENV['SMTP_USER'] ?? '';
    $pass = $_ENV['SMTP_PASS'] ?? '';

    if (empty($user) || empty($pass)) {
        throw new Exception("SMTP credentials missing in .env file.");
    }

    $subject = "Your SkillBridge Verification Code";
    $message = "Your verification code is: " . $code . "\r\n\r\nPlease enter this code to activate your account.";

    // Headers
    $headers = "From: SkillBridge <$user>\r\n";
    $headers .= "Reply-To: $user\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "\r\n"; // End of headers

    // Connect to SMTP Server (Using SSL wrapper on port 465 if Gmail, or 587 for TLS)
    // To keep it extremely simple without complex STARTTLS negotiation, we recommend using port 465 with SSL.
    // However, if port 587 is provided, we can try fsockopen without SSL prefix first, but raw STARTTLS is hard.
    // For simplicity, we'll prefix 'ssl://' if port is 465.
    
    $protocol = ($port == 465) ? 'ssl://' : '';
    
    $socket = fsockopen($protocol . $host, $port, $errno, $errstr, 10);
    if (!$socket) {
        throw new Exception("Failed to connect to SMTP server: $errstr");
    }

    // A helper to read response
    function smtp_read($socket) {
        $data = '';
        while($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") { break; }
        }
        return $data;
    }

    // A helper to send command
    function smtp_write($socket, $cmd) {
        fputs($socket, $cmd . "\r\n");
    }

    // Wait for greeting
    smtp_read($socket);

    // EHLO
    smtp_write($socket, "EHLO " . $host);
    $res = smtp_read($socket);
    
    // STARTTLS if port 587
    if ($port == 587 && strpos($res, 'STARTTLS') !== false) {
        smtp_write($socket, "STARTTLS");
        smtp_read($socket);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        // EHLO again after TLS
        smtp_write($socket, "EHLO " . $host);
        smtp_read($socket);
    }

    // AUTH LOGIN
    smtp_write($socket, "AUTH LOGIN");
    smtp_read($socket);
    
    smtp_write($socket, base64_encode($user));
    smtp_read($socket);
    
    smtp_write($socket, base64_encode($pass));
    $res = smtp_read($socket);
    
    if (substr($res, 0, 3) != '235') {
        throw new Exception("SMTP Authentication Failed.");
    }

    // MAIL FROM
    smtp_write($socket, "MAIL FROM: <$user>");
    smtp_read($socket);

    // RCPT TO
    smtp_write($socket, "RCPT TO: <$to>");
    smtp_read($socket);

    // DATA
    smtp_write($socket, "DATA");
    smtp_read($socket);

    // SEND MESSAGE
    smtp_write($socket, $headers . $message . "\r\n.");
    $res = smtp_read($socket);

    if (substr($res, 0, 3) != '250') {
        throw new Exception("Failed to send email data.");
    }

    // QUIT
    smtp_write($socket, "QUIT");
    fclose($socket);

    return true;
}
?>
