<?php

/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name php-dna
 * 4.03.2023 14:41
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
require_once __DIR__ . '/../DomainNameApi/DomainNameAPI_PHPLibrary.php';

$username = 'dothost';
$password = '@DotEightPlus2019';

$dna = new \DomainNameApi\DomainNameAPI_PHPLibrary($username, $password);

/**
 * Delete Child Name Server for domain
 * @param string $DomainName
 * @param string $NameServer
 * @return array
 */
$ns_del = $dna->DeleteChildNameServer('domainhakkinda.com', 'test5.domainhakkinda.com');
print_r($ns_del);


/**
 * Array
(
    [result] => OK
)
 */
