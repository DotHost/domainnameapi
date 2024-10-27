<?php

/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name php-dna
 * 4.03.2023 14:32
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
require_once __DIR__ . '/../DomainNameApi/DomainNameAPI_PHPLibrary.php';

$username = 'dothost';
$password = '@DotEightPlus2019';

$dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

/**
 * Enable Theft Protection Lock for domain
 * @param string $DomainName
 * @return array
 */
$lock = $dna->EnableTheftProtectionLock('domainhakkinda.com');
print_r($lock);