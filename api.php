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
    } elseif ($action === 'getdomainlist') {
        $response = $dna->GetList(); // Fetch domain list
    } elseif ($action === 'getdetails') {
        $domainName = getRequiredParameter('domain', $input);
        $response = $dna->GetDetails($domainName); // Fetch domain details
    } elseif ($action === 'checkbalance') {
        $response = $dna->GetCurrentBalance(); // Check account balance
    } elseif ($action === 'getcontacts') {
        $domainName = getRequiredParameter('domain', $input);
        $response = $dna->GetContacts($domainName); // Get contacts for a domain
    } elseif ($action === 'enabletheftlock') {
        $domainName = getRequiredParameter('domain', $input);
        $response = $dna->EnableTheftProtectionLock($domainName); // Enable theft protection
    } elseif ($action === 'disabletheftlock') {
        $domainName = getRequiredParameter('domain', $input);
        $response = $dna->DisableTheftProtectionLock($domainName); // Disable theft protection
    } elseif ($action === 'modifynameserver') {
        $domainName = getRequiredParameter('domain', $input);
        $nameServers = getRequiredParameter('nameservers', $input);
        $response = $dna->ModifyNameServer($domainName, $nameServers); // Modify name server
    } elseif ($action === 'addchildnameserver') {
        $domainName = getRequiredParameter('domain', $input);
        $nameServer = getRequiredParameter('nameServer', $input);
        $ipAddress = getRequiredParameter('ipAddress', $input);
        $response = $dna->AddChildNameServer($domainName, $nameServer, $ipAddress); // Add child name server
    } elseif ($action === 'modifychildnameserver') {
        $domainName = getRequiredParameter('domain', $input);
        $nameServer = getRequiredParameter('nameServer', $input);
        $ipAddress = getRequiredParameter('ipAddress', $input);
        $response = $dna->ModifyChildNameServer($domainName, $nameServer, $ipAddress); // Modify child name server
    } elseif ($action === 'deletechildnameserver') {
        $domainName = getRequiredParameter('domain', $input);
        $nameServer = getRequiredParameter('nameServer', $input);
        $response = $dna->DeleteChildNameServer($domainName, $nameServer); // Delete child name server
    } elseif ($action === 'syncfromregistry') {
        $domainName = getRequiredParameter('domain', $input);
        $response = $dna->SyncFromRegistry($domainName); // Sync domain information from the registry
    } elseif ($action === 'transferdomain') {
        $domainName = getRequiredParameter('domain', $input);
        $authCode = getRequiredParameter('authCode', $input);
        $period = isset($input['period']) && is_numeric($input['period']) ? (int)$input['period'] : 1;

        $result = $dna->Transfer($domainName, $authCode, $period);

        if (isset($result['result']) && $result['result'] === "ERROR") {
            $errorCode = $result['error']['Code'] ?? "UNKNOWN_ERROR";
            $errorMessage = $result['error']['Message'] ?? "An error occurred";
            sendErrorResponse(400, $errorCode, $errorMessage);
        }

        $response = $result;
    } elseif ($action === 'canceltransfer') {
        $domainName = getRequiredParameter('domain', $input);

        $cancel = $dna->CancelTransfer($domainName);

        if (isset($cancel['result']) && $cancel['result'] === "ERROR") {
            $errorCode = $cancel['error']['Code'] ?? "UNKNOWN_ERROR";
            $errorMessage = $cancel['error']['Message'] ?? "An error occurred";
            sendErrorResponse(400, $errorCode, $errorMessage);
        }

        $response = $cancel;
    } elseif ($action === 'modifyprivacystatus') {
        $domainName = getRequiredParameter('domain', $input);
        $status = getRequiredParameter('status', $input);
        $reason = $input['reason'] ?? ''; // Optional comment

        $privacy = $dna->ModifyPrivacyProtectionStatus($domainName, $status, $reason);

        if (isset($privacy['result']) && $privacy['result'] === "ERROR") {
            $errorCode = $privacy['error']['Code'] ?? "UNKNOWN_ERROR";
            $errorMessage = $privacy['error']['Message'] ?? "An error occurred";
            sendErrorResponse(400, $errorCode, $errorMessage);
        }

        $response = $privacy;
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
