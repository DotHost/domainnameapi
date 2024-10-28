<?php
require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/DomainNameApi/DomainNameAPI_PHPLibrary.php';

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse(405, "API_405_ERROR", "Only POST requests are allowed");
}

// Get JSON input from the request body
$input = json_decode(file_get_contents("php://input"), true);

// Fetch required parameters
$username = getRequiredParameter('username', $input);
$password = getRequiredParameter('password', $input);

// Initialize the DomainNameAPI class
$dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

// Determine the action based on the query parameter
$action = $_GET['action'] ?? 'account';

// Execute the requested action and return the response
if ($action === 'tldlist') {
    // Get TLD list
    $count = is_numeric($input['count'] ?? null) ? (int)$input['count'] : 2;
    $response = $dna->GetTldList($count);
} elseif ($action === 'singlecheckavailability') {
    // Check domain availability

    // Ensure domain parameter is provided
    $domain = getRequiredParameter('domain', $input);

    // Validate domain format, allowing for multi-part TLDs (e.g., .com.ng, .co.uk)
    if (!preg_match('/^([a-zA-Z0-9-]+)\.([a-zA-Z.]{2,})$/', $domain, $matches)) {
        sendErrorResponse(400, "API_400_ERROR", "Invalid domain format. Use format: example.com or example.com.ng");
    }

    // Extract SLD and TLD
    $sld = $matches[1];
    $tld = $matches[2];

    // Split TLD by dots for each part if needed
    $tldParts = explode('.', $tld);

    // Call CheckAvailability API method
    $response = $dna->CheckAvailability([$sld], [$tld], 1, 'create');
} else {
    // Default action: Fetch reseller account details
    $response = $dna->GetResellerDetails();
}

// Check for errors in the response and send an error response if necessary
if (isset($response['result']) && $response['result'] === "ERROR") {
    $errorCode = $response['error']['Code'] ?? "UNKNOWN_ERROR";
    $errorMessage = $response['error']['Message'] ?? "An error occurred";
    $errorDetails = $response['error']['Details'] ?? "No additional details";
    sendErrorResponse(400, $errorCode, $errorMessage, $errorDetails);
}

// Return the successful response
echo json_encode($response);
