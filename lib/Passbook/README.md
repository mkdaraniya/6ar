# PHP PASSBOOK LIBRARY

[![Build Status](https://travis-ci.org/eymengunay/php-passbook.png?branch=master)](https://travis-ci.org/eymengunay/php-passbook)
[![Code Coverage](https://scrutinizer-ci.com/g/eymengunay/php-passbook/badges/coverage.png?s=46b9519154148d7c525fd706d8b37999c40e9312)](https://scrutinizer-ci.com/g/eymengunay/php-passbook/)
[![Total Downloads](https://poser.pugx.org/eo/passbook/downloads.png)](https://packagist.org/packages/eo/passbook)
[![Latest Stable Version](https://poser.pugx.org/eo/passbook/v/stable.png)](https://packagist.org/packages/eo/passbook)

## What is Passbook?

> Passbook is an application in iOS that allows users to store coupons, boarding passes, event tickets,
> store cards, 'generic' cards and other forms of mobile payment.

## What does this library do?

PHP-Passbook is a library for creating and packaging passes inside your application. Distribution of generated pass files can be done by attaching the file in an e-mail or serving it from your web server.

## Breaking changes

### Version 1.1.0

* Pass setRelevantDate now accepts a DateTime object instead of string
* `PassFactory:override` property renamed to `PassFactory:overwrite`
* JMS Serializer dependency removed

## Installing

### Using Composer

To add PHP-Passbook as a local, per-project dependency to your project, simply add a dependency on eo/passbook to your project's composer.json file. Here is a minimal example of a composer.json file that just defines a development-time dependency on the latest version of the library:

```
{
    "require": {
        "eo/passbook": "dev-master"
    }
}
```

## API Doc
Search by class, method name, or package: http://eymengunay.github.io/php-passbook/api

## Usage Example

```
<?php

use Passbook\Pass\Field;
use Passbook\Pass\Image;
use Passbook\PassFactory;
use Passbook\Pass\Barcode;
use Passbook\Pass\Structure;
use Passbook\Type\EventTicket;

// Create an event ticket
$pass = new EventTicket("1234567890", "The Beat Goes On");
$pass->setBackgroundColor('rgb(60, 65, 76)');
$pass->setLogoText('Apple Inc.');

// Create pass structure
$structure = new Structure();

// Add primary field
$primary = new Field('event', 'The Beat Goes On');
$primary->setLabel('Event');
$structure->addPrimaryField($primary);

// Add secondary field
$secondary = new Field('location', 'Moscone West');
$secondary->setLabel('Location');
$structure->addSecondaryField($secondary);

// Add auxiliary field
$auxiliary = new Field('datetime', '2013-04-15 @10:25');
$auxiliary->setLabel('Date & Time');
$structure->addAuxiliaryField($auxiliary);

// Add icon image
$icon = new Image('/path/to/icon.png', 'icon');
$pass->addImage($icon);

// Set pass structure
$pass->setStructure($structure);

// Add barcode
$barcode = new Barcode(Barcode::TYPE_QR, 'barcodeMessage');
$pass->setBarcode($barcode);

// Create pass factory instance
$factory = new PassFactory('PASS-TYPE-IDENTIFIER', 'TEAM-IDENTIFIER', 'ORGANIZATION-NAME', '/path/to/p12/certificate', 'P12-PASSWORD', '/path/to/wwdr/certificate');
$factory->setOutputPath('/path/to/output/path');
$factory->package($pass);
```

## Requirements
* PHP 5.3+
* [zip](http://php.net/manual/en/book.zip.php)
* [OpenSSL](http://www.php.net/manual/en/book.openssl.php)

## Obtaining the Pass Type Identifier and Team ID

You can find more information on [http://developer.apple.com/library/ios/#documentation/userexperience/conceptual/PassKit_PG/Chapters/YourFirst.html](http://developer.apple.com/library/ios/#documentation/userexperience/conceptual/PassKit_PG/Chapters/YourFirst.html)

## Requesting Certificates

### P12 Certificate
Once you have downloaded the Apple iPhone certificate from Apple, export it to the P12 certificate format.

**To do this on Mac OS:**

1. Open the Keychain Access application (in the Applications/Utilities folder).
2. If you have not already added the certificate to Keychain, select File > Import. Then navigate to the certificate file (the .cer file) you obtained from Apple.
3. Select the Keys category in Keychain Access.
4. Select the private key associated with your iPhone Development Certificate. The private key is identified by the iPhone Developer: <First Name> <Last Name> public certificate that is paired with it.
5. Select File > Export Items.
6. Save your key in the Personal Information Exchange (.p12) file format.
7. You will be prompted to create a password that is used when you attempt to import this key on another computer.

**on Windows:**

1. Convert the developer certificate file you receive from Apple into a PEM certificate file. Run the following command-line statement from the OpenSSL bin directory:

```
openssl x509 -in developer_identity.cer -inform DER -out developer_identity.pem -outform PEM
```

2. If you are using the private key from the keychain on a Mac computer, convert it into a PEM key:

```
openssl pkcs12 -nocerts -in mykey.p12 -out mykey.pem
```

3. You can now generate a valid P12 file, based on the key and the PEM version of the iPhone developer certificate:

```
openssl pkcs12 -export -inkey mykey.key -in developer_identity.pem -out iphone_dev.p12
```

If you are using a key from the Mac OS keychain, use the PEM version you generated in the previous step. Otherwise, use the OpenSSL key you generated earlier (on Windows).

### WWDR Certificate
Apple’s World Wide Developer Relations (WWDR) certificate is available from Apple at <http://developer.apple.com/certificationauthority/AppleWWDRCA.cer>. You will have to add this to your Keychain Access and export it in .pem format to use it with the library. The WWDR certificate links your development certificate to Apple, completing the trust chain for your application.

## Running Tests
Before submitting a patch for inclusion, you need to run the test suite to check that you have not broken anything.

To run the test suite, install PHPUnit 3.7 (or later) first.

### Dependencies
To run the entire test suite, including tests that depend on external dependencies, php-passbook needs to be able to autoload them. By default, they are autoloaded from vendor/ under the main root directory (see vendor/autoload.php).

To install them all, use [Composer](http://getcomposer.org):

Step 1: Get [Composer](http://getcomposer.org)
```
curl -s http://getcomposer.org/installer | php
```
Make sure you download composer.phar in the same folder where the composer.json file is located.

Step 2: Install vendors
```
php composer.phar --dev install
```

> Note that the script takes some time to finish.

### Running
First, install the vendors (see above).

Then, run the test suite from the package root directory with the following command:
```
phpunit
```

The output should display OK. If not, you need to figure out what's going on and if the tests are broken because of your modifications.

## Reporting an issue or a feature request
Issues and feature requests related to this library are tracked in the Github issue tracker: https://github.com/eymengunay/php-passbook/issues

## See also
[PassbookBundle](https://github.com/eymengunay/PassbookBundle): PHP-Passbook library integration for Symfony2
