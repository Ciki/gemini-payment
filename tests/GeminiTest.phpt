<?php

declare(strict_types=1);

use Ciki\GeminiPayment\Gemini;
use Ciki\GeminiPayment\Item;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

// Test Gemini creation
$dueDate = new DateTimeImmutable('2023-01-01');
$gemini = new Gemini(Gemini::TYPE_UHRADA, $dueDate);
$gemini->setSender('0100', '1234567890', '123456');

$item1 = new Item('111111-2222222222/0300', 100.50, '5555555555');
$item1->setConstSym('0308');
$item1->setMessage('Test payment 1');
$gemini->addItem($item1);

$item2 = new Item('3333333333/0800', 50.00, '6666666666');
$item2->setSpecSym('7777777777');
$item2->setMessage('Test payment 2');
$gemini->addItem($item2);

$generated = $gemini->generate();

// Verify output format
$lines = explode("
", $generated);
Assert::count(2, $lines);

// Line 1 check
$line1 = $lines[0];
// 123456...
// rowNoPadded (6) + type (2) + today (6) + senderBank (4) + space (3) + recipientBank (4) + space (3) + amount (15) ...
Assert::same('000001', substr($line1, 0, 6)); // Row number
Assert::same('11', substr($line1, 6, 2)); // Type
// Today date is dynamic, skipping check for index 8-13 (6 chars)
Assert::same('0100', substr($line1, 14, 4)); // Sender Bank Code
Assert::same('   ', substr($line1, 18, 3)); // Space
Assert::same('0300', substr($line1, 21, 4)); // Recipient Bank Code (Item 1)
Assert::same('   ', substr($line1, 25, 3)); // Space
Assert::same('10050          ', substr($line1, 28, 15)); // Amount (100.50 * 100 = 10050), padded to 15 chars left aligned?

// Let's check str_pad default
// str_pad(string $string, int $length, string $pad_string = " ", int $pad_type = STR_PAD_RIGHT): string
// So yes, right padded with spaces.
// Code: $amountInCents = str_pad(strval($item->getAmount()), 15);

// Due date
Assert::same('230101', substr($line1, 43, 6)); // Due date 2023-01-01

// Line 2 check
$line2 = $lines[1];
Assert::same('000002', substr($line2, 0, 6)); // Row number
Assert::same('0800', substr($line2, 21, 4)); // Recipient Bank Code (Item 2)
Assert::same('5000           ', substr($line2, 28, 15)); // Amount (50.00 * 100 = 5000)

// Test invalid bank code
Assert::exception(function () {
	$gemini = new Gemini();
	$gemini->setSender('010', '1234567890');
}, InvalidArgumentException::class, 'Parameter $bankCode must be numeric string of length 4!');

// Test invalid payment type
Assert::exception(function () {
	new Gemini(99);
}, InvalidArgumentException::class, 'Parameter $type has invalid value, given: 99');

// Test setSenderFromFullAccountNumber
$gemini = new Gemini();
$gemini->setSenderFromFullAccountNumber('123456-1234567890/0100');
// We cannot easily check private properties, but generate() should work.
$item = new Item('111111-2222222222/0300', 100.50, '5555555555');
$gemini->addItem($item);
$output = $gemini->generate();
// Check if sender bank code (0100) is present at correct position
$lines = explode("
", $output);
$line1 = $lines[0];
Assert::same('0100', substr($line1, 14, 4));
