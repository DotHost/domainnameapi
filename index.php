<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/DomainNameApi/DomainNameAPI_PHPLibrary.php';

$username = 'dothost';
$password = '@DotEightPlus2019';

$dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

// Set the content type to JSON for all responses
header('Content-Type: application/json');

// Get the 'action' parameter from the query string
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Route handling
switch ($action) {
    case 'reseller':
        $reseller = $dna->GetResellerDetails();
        echo json_encode($reseller);
        break;

    case 'tldlist':
        // Get the 'count' parameter from the query string, default to 2 if not set
        $count = isset($_GET['count']) ? (int)$_GET['count'] : 2;
        $tldlist = $dna->GetTldList($count);
        echo json_encode($tldlist);
        break;

    default:
        echo json_encode(['success' => 'Domain Registrar found']);
        break;
}