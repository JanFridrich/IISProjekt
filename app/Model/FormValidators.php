<?php declare(strict_types = 1);

namespace App\Model;

class FormValidators
{
	public static function phoneNumberValidator($item): bool
	{
		$number = \preg_replace('/\s+/', '', $item->value);

		return \is_numeric($number);
	}

}