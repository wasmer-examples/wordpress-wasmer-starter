<?php

$path = '/all_headers.json';

// check if file exists, and if so, respond with the file contents
if (file_exists($path)) {
    echo file_get_contents($path);
    exit;
}

$headers = getallheaders();
$json = json_encode($headers, JSON_PRETTY_PRINT);
file_put_contents($path, $json);

echo "CACHED!";
