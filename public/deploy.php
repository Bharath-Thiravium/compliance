<?php

$secret = 'compliance2026';

if (!isset($_GET['token']) || $_GET['token'] !== $secret) {
    http_response_code(403);
    die('Unauthorized');
}

$output = shell_exec('cd ' . base_path() . ' && bash deploy.sh 2>&1');

echo '<pre>' . htmlspecialchars($output) . '</pre>';
