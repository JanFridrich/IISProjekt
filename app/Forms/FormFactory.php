<?php declare(strict_types = 1);

namespace App\Forms;

final class FormFactory
{
	use \Nette\SmartObject;

	public function create(): \Nette\Application\UI\Form
	{
		$form = new \Nette\Application\UI\Form;

		return $form;
	}
}
