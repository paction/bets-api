<?php

class Config 
{
    public static function loadEnv($path) 
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // skippig comments
            if (strpos(trim($line), '#') === 0) {
                continue; 
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Removing quotes around, if there are any
            if (preg_match('/^("|\')(.*)(\1)$/', $value, $matches)) {
                $value = $matches[2];
            }

            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

Config::loadEnv(__DIR__ . '/../.env');