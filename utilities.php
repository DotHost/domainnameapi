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
function getRequiredParameter($name, $input)
{
    if (!isset($input[$name]) || (is_array($input[$name]) && empty($input[$name]))) {
        sendErrorResponse(400, "API_400_ERROR", "$name is required and must be a non-empty array.");
    }
    return $input[$name];
}
