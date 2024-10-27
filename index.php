<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/DomainNameApi/DomainNameAPI_PHPLibrary.php';

$username = 'dothost';
$password = '@DotEightPlus2019';

$dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

// Parse the path and remove the directory name from the start
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Remove 'php-dna' if it’s present at the beginning of the path
$path = preg_replace('/^php-dna\//', '', $path);

// Set the content type to JSON for all responses
header('Content-Type: application/json');

// Route handling
switch ($path) {
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
