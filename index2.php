<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/DomainNameApi/DomainNameAPI_PHPLibrary.php';

$username = 'test1.dna@apiname.com';
$password = 'FsUvpJMzQ69scpqE';

$dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

// Parse the path
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Set the content type to JSON for all responses
header('Content-Type: application/json');

// Route handling
switch ($path) {
    case 'reseller':
        $reseller = $dna->GetResellerDetails();
        echo json_encode($reseller);
        break;

    case 'tldlist':
        $tldlist = $dna->GetTldList(2);
        echo json_encode($tldlist);
        break;

    default:
        echo json_encode(['success' => 'Domain Registrar found']);
        break;
}
