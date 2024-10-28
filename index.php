<?php
header("Content-Type: application/json");

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Utility function for sending error responses
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

// Get JSON input from the request body
$input = json_decode(file_get_contents("php://input"), true);

// Check for required parameters
function getRequiredParameter($paramName)
{
    global $input;
    if (!isset($input[$paramName])) {
        sendErrorResponse(400, "API_400_ERROR", "$paramName is required");
    }
    return $input[$paramName];
}

$username = getRequiredParameter('username');
$password = getRequiredParameter('password');

// Initialize the DomainNameAPI class
require_once __DIR__ . '/DomainNameApi/DomainNameAPI_PHPLibrary.php';
try {
    $dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);
} catch (Exception $e) {
    sendErrorResponse(500, "API_INIT_ERROR", "Failed to initialize API", $e->getMessage());
}

// Determine the action based on the query parameter
$action = $_GET['action'] ?? 'account';

// Perform action and handle response
try {
    if ($action === 'tldlist') {
        // Dynamically get the pricing matrix version from input, default to 2 if not provided
        $count = $input['count'] ?? 2;
        $response = $dna->GetTldList($count);
    } else {
        // Default action: Fetch reseller account details
        $response = $dna->GetResellerDetails();
    }

    // Check for errors in the response
    if (isset($response['result']) && $response['result'] === "ERROR") {
        $errorCode = $response['error']['Code'] ?? "UNKNOWN_ERROR";
        $errorMessage = $response['error']['Message'] ?? "An error occurred";
        $errorDetails = $response['error']['Details'] ?? "No additional details";
        sendErrorResponse(400, $errorCode, $errorMessage, $errorDetails);
    }

    // Respond with success (200 OK)
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    sendErrorResponse(500, "API_PROCESS_ERROR", "Failed to process request", $e->getMessage());
}
