<?php

// Send a standardized error response
function sendErrorResponse($code, $errorCode, $message, $details = "No additional details")
{
    http_response_code($code);
    echo json_encode([
        "result" => "ERROR",
        "error" => [
            "Code" => $errorCode,
            "Message" => $message,
            "Details" => $details
        ]
    ]);
    exit;
}

// Validate and fetch a required parameter from input
function getRequiredParameter($paramName, $input)
{
    if (!isset($input[$paramName]) || !is_string($input[$paramName]) || empty(trim($input[$paramName]))) {
        sendErrorResponse(400, "API_400_ERROR", "$paramName is required and must be a non-empty string");
    }
    return trim($input[$paramName]);
}
