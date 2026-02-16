<?php

declare(strict_types=1);

use Ciki\GeminiPayment\Utils;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

// Test simple account number
$fullAccountNumber = '12345678/0100';
Assert::same(['', '12345678', '0100'], Utils::getAccountNumberParts($fullAccountNumber));

// Test account number with prefix
$fullAccountNumber = '123-12345678/0100';
Assert::same(['123', '12345678', '0100'], Utils::getAccountNumberParts($fullAccountNumber));

// Test with zero prefix
$fullAccountNumber = '0-12345678/0100';
Assert::same(['0', '12345678', '0100'], Utils::getAccountNumberParts($fullAccountNumber));

// Test with long prefix
$fullAccountNumber = '123456-12345678/0100';
Assert::same(['123456', '12345678', '0100'], Utils::getAccountNumberParts($fullAccountNumber));
