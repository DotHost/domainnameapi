<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust origin as needed

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);