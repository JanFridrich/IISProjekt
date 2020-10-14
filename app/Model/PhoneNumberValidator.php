<?php declare(strict_types = 1);

namespace App\Model;

class PhoneNumberValidator
{
	public static function numberValidator($item): bool
	{
		$number = \preg_replace('/\s+/', '', $item->value);

		return \is_numeric($number);
	}
}