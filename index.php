<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utilities.php';

// Define rate limiting settings
$rateLimit = 1000; // Maximum number of requests allowed
$timeWindow = 600; // Time window in seconds (e.g., 10 Minutes)

// Get the client's IP address
$clientIP = $_SERVER['REMOTE_ADDR'];

// Initialize session storage for rate-limiting data
if (!isset($_SESSION['rate_limit'])) {
    $_SESSION['rate_limit'] = [];
}

// Initialize or update the client's rate limit data
if (!isset($_SESSION['rate_limit'][$clientIP])) {
    $_SESSION['rate_limit'][$clientIP] = [
        'requests' => 1,
        'start_time' => time()
    ];
} else {
    $rateData = &$_SESSION['rate_limit'][$clientIP];
    $elapsedTime = time() - $rateData['start_time'];

    // Check if the time window has elapsed
    if ($elapsedTime < $timeWindow) {
        // If within the time window, increment the request count
        $rateData['requests']++;

        // Check if request count exceeds the rate limit
        if ($rateData['requests'] > $rateLimit) {
            sendErrorResponse(429, "API_RATE_LIMIT_EXCEEDED", "Too many requests. Please try again later.");
        }
    } else {
        // Reset the rate limit data if the time window has passed
        $rateData['requests'] = 1;
        $rateData['start_time'] = time();
    }
}

try {
    require_once __DIR__ . '/api.php';
} catch (Exception $e) {
    sendErrorResponse(500, "API_PROCESS_ERROR", "Failed to process request", $e->getMessage());
}
