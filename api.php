<?php
require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/DomainNameApi/DomainNameAPI_PHPLibrary.php';

$action = $_GET['action'] ?? 'status';

$dna = null;

if ($action === 'status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendErrorResponse(405, "API_405_ERROR", "Only GET requests are allowed for the status action");
    }
    $response = ['status' => 'success', 'message' => 'Domain Name API is active.'];
} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendErrorResponse(405, "API_405_ERROR", "Only POST requests are allowed for this action");
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $username = getRequiredParameter('username', $input);
    $password = getRequiredParameter('password', $input);
    $dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

    if ($action === 'tldlist') {
        $count = is_numeric($input['count'] ?? null) ? (int)$input['count'] : 2;
        $response = $dna->GetTldList($count);
    } elseif ($action === 'singlecheckavailability') {
        $domain = getRequiredParameter('domain', $input);
        if (!preg_match('/^([a-zA-Z0-9-]+)\.([a-zA-Z.]{2,})$/', $domain, $matches)) {
            sendErrorResponse(400, "API_400_ERROR", "Invalid domain format.");
        }
        $sld = $matches[1];
        $tld = $matches[2];
        $response = $dna->CheckAvailability([$sld], [$tld], 1, 'create');
    } elseif ($action === 'bulkcheckavailability') {
        $domains = getRequiredParameter('domains', $input);
        if (!is_array($domains) || empty($domains)) {
            sendErrorResponse(400, "API_400_ERROR", "Domains must be provided as a non-empty array.");
        }

        $availabilityResults = [];
        foreach ($domains as $domain) {
            if (!preg_match('/^([a-zA-Z0-9-]+)\.([a-zA-Z.]{2,})$/', $domain, $matches)) {
                $availabilityResults[$domain] = [
                    'status' => 'error',
                    'message' => "Invalid domain format."
                ];
                continue;
            }
            $sld = $matches[1];
            $tld = $matches[2];
            $result = $dna->CheckAvailability([$sld], [$tld], 1, 'create');
            $availabilityResults[$domain] = $result;
        }

        $response = [
            'status' => 'success',
            'availability' => $availabilityResults
        ];
    } elseif ($action === 'resellerdetails') {
        $response = $dna->GetResellerDetails();
    } elseif ($action === 'registerdomain') {
        $domainName = getRequiredParameter('domain', $input);
        $period = getRequiredParameter('period', $input);
        $contact = getRequiredParameter('contact', $input);
        $registrant = getRequiredParameter('registrant', $input);
        $nameservers = getRequiredParameter('nameservers', $input);
        $privacyProtection = isset($input['privacyProtection']) ? (bool)$input['privacyProtection'] : false;
        $eppLock = isset($input['eppLock']) ? (bool)$input['eppLock'] : true;

        // Register domain with dynamic nameservers, privacy option, and EPP lock
        $response = $dna->RegisterWithContactInfo(
            $domainName,
            $period,
            [
                'Administrative' => $contact,
                'Billing'        => $contact,
                'Technical'      => $contact,
                'Registrant'     => $registrant
            ],
            $nameservers,
            $eppLock,
            $privacyProtection
        );
    } elseif ($action === 'modifydomainprivacy') {
        $domainName = getRequiredParameter('domain', $input);
        $privacyStatus = getRequiredParameter('status', $input);
        $privacyStatus = filter_var($privacyStatus, FILTER_VALIDATE_BOOLEAN);

        // Modify privacy protection status of the domain
        $response = $dna->ModifyPrivacyProtectionStatus($domainName, $privacyStatus);
    } else {
        sendErrorResponse(400, "API_400_ERROR", "Invalid action requested.");
    }
}

if (isset($response['result']) && $response['result'] === "ERROR") {
    $errorCode = $response['error']['Code'] ?? "UNKNOWN_ERROR";
    $errorMessage = $response['error']['Message'] ?? "An error occurred";
    $errorDetails = $response['error']['Details'] ?? "No additional details";
    sendErrorResponse(400, $errorCode, $errorMessage, $errorDetails);
}

echo json_encode($response);
