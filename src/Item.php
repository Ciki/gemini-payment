<?php

declare(strict_types=1);

namespace Ciki\GeminiPayment;

use InvalidArgumentException;
use Nette\Utils\Strings;

final class Item
{
	/** @var string recipient account prefix max 6 numbers */
	private string $accountPrefix = '';

	/** @var string recipient account number max 10 numbers */
	private string $accountNumber = '';

	/** @var string recipient bank code 4 numbers */
	private string $bankCode = '';

	/** @var int in cents/halere */
	private int $amount = 0;

	/** @var string max 10 numbers */
	private string $varSym = '';

	/** @var string max 10 numbers */
	private string $specSym = '';

	/** @var string max 10 numbers */
	private string $constSym = '';

	/** @var string max 140 chars */
	private string $message = '';


	public function __construct(string $fullAccountNumber, float $amount, string $varSym)
	{
		$this->setAccount($fullAccountNumber)
			->setAmount($amount)
			->setVarSym($varSym);
	}


	public function setAmount(float $amount, bool $convert2cents = true): self
	{
		if ($convert2cents) {
			$amount *= 100;
		}
		// due to float arithmetics the $amount may get nasty, ex. 1052.1 * 100 = 105209.99999999999
		if (round($amount) - $amount >= 0.1) {
			throw new InvalidArgumentException('Parameter $amount must be either whole number representing amount in cents or decimal number with 2 decimal places, given ' . $amount);
		}
		$this->amount = (int) round($amount);
		return $this;
	}


	public function getAmount(): int
	{
		return $this->amount;
	}


	/**
	 * @param string $fullAccountNumber in format (xxxxxx-)xxxxxxxxxx/xxxx
	 */
	public function setAccount(string $fullAccountNumber): self
	{
		$parts = Utils::getAccountNumberParts($fullAccountNumber);
		$this->bankCode = $parts[2];
		$this->accountPrefix = $parts[0];
		$this->accountNumber = $parts[1];

		return $this;
	}


	public function setVarSym(string $number): self
	{
		$len = 10;
		if (!empty($number) && !is_numeric($number) || strlen($number) > $len) {
			throw new InvalidArgumentException("Parameter \$number must be numeric string of max length {$len}!");
		}
		$this->varSym = $number;
		return $this;
	}


	public function setConstSym(string $number): self
	{
		$len = 10;
		if (!empty($number) && !is_numeric($number) || strlen($number) > $len) {
			throw new InvalidArgumentException("Parameter \$number must be numeric string of max length {$len}!");
		}
		$this->constSym = $number;
		return $this;
	}


	public function setSpecSym(string $number): self
	{
		$len = 10;
		if (!empty($number) && !is_numeric($number) || strlen($number) > $len) {
			throw new InvalidArgumentException("Parameter \$number must be numeric string of max length {$len}!");
		}
		$this->specSym = $number;
		return $this;
	}


	public function setMessage(string $msg): self
	{
		$this->message = Strings::truncate(Strings::toAscii($msg), 140);
		return $this;
	}


	public function getAccountPrefix(): string
	{
		return $this->accountPrefix;
	}


	public function getAccountNumber(): string
	{
		return $this->accountNumber;
	}


	public function getBankCode(): string
	{
		return $this->bankCode;
	}


	public function getVarSym(): string
	{
		return $this->varSym;
	}


	public function getSpecSym(): string
	{
		return $this->specSym;
	}


	public function getConstSym(): string
	{
		return $this->constSym;
	}


	public function getMessage(): string
	{
		return $this->message;
	}
}
