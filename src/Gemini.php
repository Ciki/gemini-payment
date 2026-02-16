<?php

declare(strict_types=1);

namespace Ciki\GeminiPayment;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * @link https://www.rb.cz/attachments/direct-banking/ekomunikator-datova-struktura.pdf
 */
final class Gemini
{
	/** @var int 2 numbers */
	const int TYPE_UHRADA = 11;
	const int TYPE_INKASO = 32;


	private DateTimeInterface $dueDate;

	/** @var int one of self::TYPE_* consts */
	private int $paymentType = self::TYPE_UHRADA;

	/** @var string 4 numbers */
	private string $senderBankCode;

	/** @var string max 10 numbers */
	private string $senderAccountNumber;

	/** @var string max 6 numbers */
	private string $senderAccountPrefix;

	/** @var Item[] */
	private array $items = [];


	public function __construct(int $paymentType = self::TYPE_UHRADA, ?DateTimeInterface $date = null)
	{
		$this->setPaymentType($paymentType);
		$this->setDueDate($date);
	}


	public function setPaymentType(int $type): self
	{
		$allowed = [self::TYPE_UHRADA, self::TYPE_INKASO];
		if (!in_array($type, $allowed, true)) {
			throw new InvalidArgumentException('Parameter $type has invalid value, given: ' . $type);
		}
		$this->paymentType = $type;
		return $this;
	}


	public function setDueDate(?DateTimeInterface $date = null): self
	{
		if ($date === null) {
			$date = new DateTimeImmutable();
		}
		$this->dueDate = $date;
		return $this;
	}


	public function setSender(string $bankCode, string $accountNumber, string $accountPrefix = ''): void
	{
		$len = 4;
		if (!is_numeric($bankCode) || strlen($bankCode) !== $len) {
			throw new InvalidArgumentException("Parameter \$bankCode must be numeric string of length $len!");
		}

		$this->senderBankCode = $bankCode;
		$this->senderAccountNumber = $accountNumber;
		$this->senderAccountPrefix = $accountPrefix;
	}


	/**
	 * @param string $fullAccountNumber in format (xxxxxx-)xxxxxxxx/xxxx
	 */
	public function setSenderFromFullAccountNumber(string $fullAccountNumber): self
	{
		$parts = Utils::getAccountNumberParts($fullAccountNumber);
		$this->setSender($parts[2], $parts[1], $parts[0]);
		return $this;
	}


	public function addItem(Item $item): Item
	{
		$this->items[] = $item;
		return $item;
	}


	public function generate(): string
	{
		$today = new DateTimeImmutable();

		// $senderAccountName = str_pad($this->senderAccountName ?? '', 20, '0', STR_PAD_LEFT);
		$senderAccountPrefix = str_pad($this->senderAccountPrefix, 6, '0', STR_PAD_LEFT);
		$senderAccountNumber = str_pad($this->senderAccountNumber, 10, '0', STR_PAD_LEFT);
		$senderBankCode = $this->senderBankCode;

		$rows = [];
		$rowNo = 1;
		foreach ($this->items as $item) {
			$rowNoPadded = str_pad((string) $rowNo++, 6, '0', STR_PAD_LEFT);
			$paymentType = $this->paymentType;
			$recipientBankCode = $item->getBankCode();
			$space3 = '   ';
			$space6 = '      ';
			$amountInCents = str_pad(strval($item->getAmount()), 15);
			$dueDate = $this->dueDate->format('ymd') ?? $space6; // space6 => 'today' will be used
			$constSym = str_pad($item->getConstSym(), 10, '0', STR_PAD_LEFT);
			$varSym = str_pad($item->getVarSym(), 10, '0', STR_PAD_LEFT);
			$specSym = str_pad($item->getSpecSym(), 10, '0', STR_PAD_LEFT);
			$recipientAccountPrefix = str_pad($item->getAccountPrefix(), 6, '0', STR_PAD_LEFT);
			$recipientAccountNumber = str_pad($item->getAccountNumber(), 10, '0', STR_PAD_LEFT);
			// $recipientAccountName = str_pad('', 20, '0', STR_PAD_LEFT);// empty
			$msg = $item->getMessage();

			$row = $rowNoPadded . $paymentType . $today->format('ymd') . $senderBankCode . $space3 . $recipientBankCode . $space3 . $amountInCents
				. $dueDate . $constSym . $varSym . $specSym . $senderAccountPrefix . $senderAccountNumber
				. $recipientAccountPrefix . $recipientAccountNumber
				. $msg
				// below not used fields
				// . $senderAccountName
				// . $recipientAccountName
				// . $varSymDebet
				// . $specSymDebet
				// . $msgDebet
				// . $bankInfo
			;

			$rows[] = $row;
		}
		$rowsStr = join("\r\n", $rows);
		return $rowsStr;
	}
}
