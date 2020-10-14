<?php declare(strict_types = 1);

namespace App\Forms\UserForms;

final class SignInFormFactory
{
	use \Nette\SmartObject;

	/** @var \App\Forms\FormFactory */
	private $factory;

	/** @var \Nette\Security\User */
	private $user;


	public function __construct(\App\Forms\FormFactory $factory, \Nette\Security\User $user)
	{
		$this->factory = $factory;
		$this->user = $user;
	}


	public function create(callable $onSuccess): \Nette\Application\UI\Form
	{
		$form = $this->factory->create();
		$form->addText('login', 'Login :')
			->setRequired('Chybějící login');

		$form->addPassword('heslo', 'Heslo:')
			->setRequired('Chybějící heslo');

		$form->addCheckbox('remember', 'Trvale přihlásit');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = function (\Nette\Application\UI\Form $form, \stdClass $values) use ($onSuccess): void {
			try {
				$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
				$this->user->login($values->login, $values->heslo);
			} catch (\Nette\Security\AuthenticationException $e) {
				$form->addError('Nesprávné heslo nebo přihlašovací jméno');

				return;
			}
			$onSuccess();
		};

		return $form;
	}
}
