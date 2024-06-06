<?php

$path = '/phpinfo.html';

// check if file exists, and if so, respond with the file contents
if (file_exists($path)) {
    echo file_get_contents($path);
    exit;
}

ob_start();
phpinfo();
$phpinfo = ob_get_contents();
ob_get_clean();

// Write the phpinfo() output to a file
file_put_contents($path, $phpinfo);

echo "CACHED!";

