<?php

declare(strict_types=1);

use Ciki\GeminiPayment\Item;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

// Test Item creation
$item = new Item('123456-1234567890/0100', 100.50, '1234567890');
Assert::same('123456', $item->getAccountPrefix());
Assert::same('1234567890', $item->getAccountNumber());
Assert::same('0100', $item->getBankCode());
Assert::same(10050, $item->getAmount());
Assert::same('1234567890', $item->getVarSym());

// Test setting invalid amount
Assert::exception(function () {
	$item = new Item('123456-1234567890/0100', 100.555, '1234567890');
}, InvalidArgumentException::class, '~Parameter \$amount must be either whole number representing amount in cents or decimal number with 2 decimal places~');

// Test setAmount with no conversion
$item->setAmount(10000, false);
Assert::same(10000, $item->getAmount());

// Test setVarSym validation
Assert::exception(function () use ($item) {
	$item->setVarSym('12345678901');
}, InvalidArgumentException::class, 'Parameter $number must be numeric string of max length 10!');

// Test setConstSym validation
Assert::exception(function () use ($item) {
	$item->setConstSym('12345678901');
}, InvalidArgumentException::class, 'Parameter $number must be numeric string of max length 10!');

// Test setSpecSym validation
Assert::exception(function () use ($item) {
	$item->setSpecSym('12345678901');
}, InvalidArgumentException::class, 'Parameter $number must be numeric string of max length 10!');

// Test setMessage truncation
$msg = str_repeat('a', 150);
$item->setMessage($msg);
Assert::same(str_repeat('a', 140), $item->getMessage());
