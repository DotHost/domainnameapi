<?php
require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/DomainNameApi/DomainNameAPI_PHPLibrary.php';

$action = $_GET['action'] ?? 'status';
$dna = null;

function validateRequestMethod($expectedMethod)
{
    if ($_SERVER['REQUEST_METHOD'] !== $expectedMethod) {
        sendErrorResponse(405, "API_405_ERROR", "Only {$expectedMethod} requests are allowed.");
    }
}

function handleErrorResponse($response)
{
    if (isset($response['result']) && $response['result'] === "ERROR") {
        $errorCode = $response['error']['Code'] ?? "UNKNOWN_ERROR";
        $errorMessage = $response['error']['Message'] ?? "An error occurred";
        sendErrorResponse(400, $errorCode, $errorMessage);
    }
}

function getDomainFromInput($input)
{
    return getRequiredParameter('domain', $input);
}

if ($action === 'status') {
    validateRequestMethod('GET');
    $response = ['status' => 'success', 'message' => 'Domain Name API is active.'];
} else {
    validateRequestMethod('POST');

    $input = json_decode(file_get_contents("php://input"), true);
    $username = getRequiredParameter('username', $input);
    $password = getRequiredParameter('password', $input);
    $dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

    switch ($action) {
        case 'tldlist':
            $count = is_numeric($input['count'] ?? null) ? (int)$input['count'] : 2;
            $response = $dna->GetTldList($count);
            break;

        case 'singlecheckavailability':
            $domain = getRequiredParameter('domain', $input);
            if (!preg_match('/^([a-zA-Z0-9-]+)\.([a-zA-Z.]{2,})$/', $domain, $matches)) {
                sendErrorResponse(400, "API_400_ERROR", "Invalid domain format.");
            }
            $response = $dna->CheckAvailability([$matches[1]], [$matches[2]], 1, 'create');
            break;

        case 'bulkcheckavailability':
            $domains = getRequiredParameter('domains', $input);
            if (!is_array($domains) || empty($domains)) {
                sendErrorResponse(400, "API_400_ERROR", "Domains must be provided as a non-empty array.");
            }

            $availabilityResults = [];
            foreach ($domains as $domain) {
                if (!preg_match('/^([a-zA-Z0-9-]+)\.([a-zA-Z.]{2,})$/', $domain, $matches)) {
                    $availabilityResults[$domain] = ['status' => 'error', 'message' => "Invalid domain format."];
                    continue;
                }
                $result = $dna->CheckAvailability([$matches[1]], [$matches[2]], 1, 'create');
                $availabilityResults[$domain] = $result;
            }

            $response = ['status' => 'success', 'availability' => $availabilityResults];
            break;

        case 'resellerdetails':
            $response = $dna->GetResellerDetails();
            break;

        case 'registerdomain':
            $response = $dna->RegisterWithContactInfo(
                getRequiredParameter('domain', $input),
                getRequiredParameter('period', $input),
                [
                    'Administrative' => getRequiredParameter('contact', $input),
                    'Billing'        => getRequiredParameter('contact', $input),
                    'Technical'      => getRequiredParameter('contact', $input),
                    'Registrant'     => getRequiredParameter('registrant', $input)
                ],
                getRequiredParameter('nameservers', $input),
                (bool)($input['eppLock'] ?? true),
                (bool)($input['privacyProtection'] ?? false)
            );
            break;

        case 'getdomainlist':
            $response = $dna->GetList();
            break;

        case 'getdetails':
            $response = $dna->GetDetails(getDomainFromInput($input));
            break;

        case 'checkbalance':
            $response = $dna->GetCurrentBalance();
            break;

        case 'getcontacts':
            $response = $dna->GetContacts(getDomainFromInput($input));
            break;

        case 'enabletheftlock':
            $response = $dna->EnableTheftProtectionLock(getDomainFromInput($input));
            break;

        case 'disabletheftlock':
            $response = $dna->DisableTheftProtectionLock(getDomainFromInput($input));
            break;

        case 'modifynameserver':
            $response = $dna->ModifyNameServer(getDomainFromInput($input), getRequiredParameter('nameservers', $input));
            break;

        case 'addchildnameserver':
            $response = $dna->AddChildNameServer(
                getDomainFromInput($input),
                getRequiredParameter('nameServer', $input),
                getRequiredParameter('ipAddress', $input)
            );
            break;

        case 'modifychildnameserver':
            $response = $dna->ModifyChildNameServer(
                getDomainFromInput($input),
                getRequiredParameter('nameServer', $input),
                getRequiredParameter('ipAddress', $input)
            );
            break;

        case 'deletechildnameserver':
            $response = $dna->DeleteChildNameServer(getDomainFromInput($input), getRequiredParameter('nameServer', $input));
            break;

        case 'syncfromregistry':
            $response = $dna->SyncFromRegistry(getDomainFromInput($input));
            break;

        case 'transferdomain':
            $domainName = getDomainFromInput($input);
            $authCode = getRequiredParameter('authCode', $input);
            $period = isset($input['period']) && is_numeric($input['period']) ? (int)$input['period'] : 1;
            $result = $dna->Transfer($domainName, $authCode, $period);
            handleErrorResponse($result);
            $response = $result;
            break;

        case 'canceltransfer':
            $domainName = getDomainFromInput($input);
            $cancel = $dna->CancelTransfer($domainName);
            handleErrorResponse($cancel);
            $response = $cancel;
            break;

        case 'modifyprivacystatus':
            $domainName = getDomainFromInput($input);
            $status = getRequiredParameter('status', $input);
            $reason = $input['reason'] ?? '';
            $privacy = $dna->ModifyPrivacyProtectionStatus($domainName, $status, $reason);
            handleErrorResponse($privacy);
            $response = $privacy;
            break;

        default:
            sendErrorResponse(400, "API_400_ERROR", "Invalid action requested.");
    }
}

handleErrorResponse($response);
echo json_encode($response);
