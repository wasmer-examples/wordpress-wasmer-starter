<?php

// Set content type header
header('Content-Type: application/json');

// Get the opcache statistics
$opcache_stats = opcache_get_status();

// Output the statistics
echo json_encode($opcache_stats, JSON_PRETTY_PRINT);
