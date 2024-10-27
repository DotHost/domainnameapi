## Installation and Integration Guide

### Minimum Requirements

- PHP7.4 or higher (Recommended 8.1)
- PHP SOAPClient extension must be active.

## Usage

Download the files and review the examples inside the [examples](examples) folder.

```php
require_once __DIR__.'/src/DomainNameAPI_PHPLibrary.php';

$dna = new \DomainNameApi\DomainNameAPI_PHPLibrary('username','password');
```

For the domain list:

```php
$list = $dna->GetList(['OrderColumn'=>'Id', 'OrderDirection'=>'ASC', 'PageNumber'=>0,'PageSize'=>1000]);
```

To get the TLD list:

```php
$list = $dna->GetTldList(100);
```

To check domain availability:

```php
$check = $dna->CheckAvailability('domainname.com',1,'create');
```

For domain details:

```php
$detail = $dna->GetDetails('domainname.com');
```

To modify nameservers:

```php
$ns = $dna->SetNameservers(ModifyNameServer('domain.com',['ns1'=>'ns1.domain.com','ns2'=>'ns2.domain.com']);
```

To activate domain lock:

```php
$lock = $dna->EnableTheftProtectionLock('domainname.com');
```

To remove domain lock:

```php
$lock = $dna->DisableTheftProtectionLock('domainname.com');
```

To add ChildNS to a domain:

```php
$childns = $dna->AddChildNameServer('domainname.com','ns1.domainname.com','1.2.3.4');
```

To save Contact for a domain:

```php
$contact = [
    "FirstName"        => 'Bunyamin',
    "LastName"         => 'Mutlu',
    "Company"          => '',
    "EMail"            => 'bun.mutlu@gmail.com',
    "AddressLine1"     => 'address 1',
    "AddressLine2"     => 'test',
    "City"             => 'Kocaeli',
    "Country"          => 'TR',
    "Fax"              => '2626060026',
    "FaxCountryCode"   => '90',
    "Phone"            => '5555555555',
    "PhoneCountryCode" => 90,
    "Type"             => 'Contact',
    "ZipCode"          => '41829',
    "State"            => 'GEBZE'
];

$childns = $dna->SaveContacts('domainname.com','ns1','1.2.3.4');
```

To get domain Contacts:

```php
$contact = $dna->GetContacts('domainname.com');
```

To renew a domain:

```php
$lock=$dna->Renew('domainname.com',1);
```

To sync with the Registry:

```php
$lock=$dna->SyncFromRegistry('domainname.com');
```

To check balance (Parameters: 1=TL, 2=USD, or use USD TRY TL labels directly):

```php
$balance_usd = $dna->GetCurrentBalance(); // Default USD
$balance_usd = $dna->GetCurrentBalance('USD');
$balance_try = $dna->GetCurrentBalance('TRY');
$balance_usd = $dna->GetCurrentBalance(1); // 1=TRY/TL
$balance_try = $dna->GetCurrentBalance(2); // 2=USD
```

To get Reseller details:

```php
$reseller = $dna->GetResellerDetails();
```

To register a domain:

```php
$contact = [
    "FirstName"        => 'Bunyamin',
    "LastName"         => 'Mutlu',
    "Company"          => '',
    "EMail"            => 'bun.mutlu@gmail.com',
    "AddressLine1"     => 'address 1',
    "AddressLine2"     => 'test',
    "City"             => 'Kocaeli',
    "Country"          => 'TR',
    "Fax"              => '2626060026',
    "FaxCountryCode"   => '90',
    "Phone"            => '5555555555',
    "PhoneCountryCode" => 90,
    "Type"             => 'Contact',
    "ZipCode"          => '41829',
    "State"            => 'GEBZE'
];

$info = $a->RegisterWithContactInfo(
    'domainname.com.tr',
    1,
    [
        'Administrative' => $contact,
        'Billing'        => $contact,
        'Technical'      => $contact,
        'Registrant'     => $contact
    ],
    ["tr.atakdomain.com", "eu.atakdomain.com"],true,false,
    [
        'TRABISDOMAINCATEGORY' => 1,
        'TRABISCITIZIENID'     => '1112221111111',
        'TRABISNAMESURNAME'    => 'Bunyamin Mutlu',
        'TRABISCOUNTRYID'      => '215',
        'TRABISCITYID'        => '41'
    ]);
```

## Response and Error Codes with Explanations

| Code | Description                                     | Detail                                                                                  |
| ---- | ----------------------------------------------- | --------------------------------------------------------------------------------------- |
| 1000 | Command completed successfully                  | Operation successful.                                                                   |
| 1001 | Command completed successfully; action pending. | Operation successful, but it's queued for completion.                                   |
| 2003 | Required parameter missing                      | Parameter missing error. For example, missing phone entry in contact info.              |
| 2105 | Object is not eligible for renewal              | Domain status not eligible for renewal; it's locked for updates.                        |
| 2200 | Authentication error                            | Authorization error, security code incorrect, or domain with another registrar.         |
| 2302 | Object exists                                   | Domain or nameserver info already exists in the database.                               |
| 2303 | Object does not exist                           | Domain or nameserver info does not exist in the database. A new record must be created. |
| 2304 | Object status prohibits operation               | Domain status not eligible for updates, it's locked for updates.                        |
