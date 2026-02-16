<?php

declare(strict_types=1);

namespace Ciki\GeminiPayment;

final class Utils
{
	/**
	 * @param string $fullAccountNumber in format (xxxxxx-)xxxxxxxx/xxxx
	 */
	public static function getAccountNumberParts(string $fullAccountNumber): array
	{
		$accountParts = explode('/', $fullAccountNumber);
		$bankCode = $accountParts[1];
		if (strpos($accountParts[0], '-') !== false) {
			$numberParts = explode('-', $accountParts[0]);
			$accountPrefix = $numberParts[0];
			$accountNumber = $numberParts[1];
		} else {
			$accountPrefix = '';
			$accountNumber = $accountParts[0];
		}

		return [$accountPrefix, $accountNumber, $bankCode];
	}
}
