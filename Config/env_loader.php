<?php
function load_env($path) {
    if(!file_exists($path)) {
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach($lines as $line) {
        $trimmed = trim($line);
        if(strpos($trimmed, '#') === 0 || empty($trimmed)) continue;
        if(strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (strpos($value, '#') !== false && !preg_match('/^["\'].*["\']$/', $value)) {
                $parts = explode('#', $value, 2);
                $value = trim($parts[0]);
            }
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if(!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    return true;
}

// Load the .env file from the project root
load_env(__DIR__ . '/../.env');
?>
