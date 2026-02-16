# Gemini payment file generator for PHP

PHP library for generating Gemini (GPC) payment files, following the [Raiffeisenbank eKomunikátor specification](https://www.rb.cz/attachments/direct-banking/ekomunikator-datova-struktura.pdf).

## Installation

```bash
composer require ciki/gemini-payment
```

## Usage

```php
use Ciki\GeminiPayment\Gemini;
use Ciki\GeminiPayment\Item;

// Initialize Gemini with payment type and optional due date
$gemini = new Gemini(Gemini::TYPE_UHRADA, new DateTimeImmutable('2026-02-20'));

// Set sender account details
$gemini->setSender('5500', '1234567890', '123'); // bank code, account number, prefix
$gemini->setSenderAccountName('My Company Ltd.');

// Create payment item
$item = new Item('123-1234567890/0100', 1500.50, '20260001'); // account, amount, varSym
$item->setAccountName('Partner Company')
    ->setConstSym('0308')
    ->setSpecSym('12345')
    ->setMessage('Invoice 20260001')
    ->setSecondaryVarSym('99999') // VS for sender
    ->setSecondaryMessage('External Ref') // Message for sender
    ->setBankInfo('Urgent payment');

$gemini->addItem($item);

// Generate file content
$output = $gemini->generate();

file_put_contents('payment.gpc', $output);
```

## Development

Run tests:
```bash
vendor/bin/tester tests
```

## License

MIT
