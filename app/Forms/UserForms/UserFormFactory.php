<?php declare(strict_types = 1);

namespace App\Forms\UserForms;

class UserFormFactory
{
	use \Nette\SmartObject;

	public const PASSWORD_MIN_LENGTH = 7;
	public const BIRTH_NUMBER_MIN_LENGTH = 9;
	public const BIRTH_NUMBER_MAX_LENGTH = 11;

	/**
	 * @var \App\Forms\FormFactory
	 */
	private $factory;

	/**
	 * @var \App\Model\UserManager
	 */
	private $userManager;

	/**
	 * @var \Nette\Security\User
	 */
	private $user;


	public function __construct(\App\Forms\FormFactory $factory, \App\Model\UserManager $userManager, \Nette\Security\User $user)
	{
		$this->factory = $factory;
		$this->userManager = $userManager;
		$this->user = $user;
	}


	public function create(callable $onSuccess, $caption): \Nette\Application\UI\Form
	{
		$form = $this->factory->create();
		// Login a heslo musí mít každý svoje
		$form->addText(\App\Model\UserManager::COLUMN_LOGIN, 'Login:')
			->setRequired('Prosím vytvořte si přihlašovací login.');

		$form->addPassword(\App\Model\UserManager::COLUMN_PASSWORD_HASH, 'Heslo:')
			->setOption('description', sprintf('alspoň %d znaků', self::PASSWORD_MIN_LENGTH));

		$form->addPassword(\App\Model\UserManager::COLUMN_PASSWORD_HASH . '2', 'Heslo znovu:')
			->addRule($form::EQUAL, 'Hesla se musí shodovat', $form[\App\Model\UserManager::COLUMN_PASSWORD_HASH]);

		// nastavíme defaultní hodnoty pro testování
		$form->addText(\App\Model\UserManager::COLUMN_PHONE, 'Telefonní číslo: +420')
			->setRequired()
			->setDefaultValue('999333444')
			->addRule(\App\Model\FormValidators::class . '::phoneNumberValidator', 'Pouze mezery a čísla mohou být součástí telefonního čísla');

		$form->addEmail(\App\Model\UserManager::COLUMN_EMAIL, 'Email:')
			->setRequired()
			->setDefaultValue('email@email.cz');

		$form->addText(\App\Model\UserManager::COLUMN_NAME, 'Jméno:')
			->setRequired()
			->setDefaultValue('Jméno');

		$form->addText(\App\Model\UserManager::COLUMN_SURNAME, 'Příjmení:')
			->setRequired()
			->setDefaultValue('Příjmení');

		$form->addText(\App\Model\UserManager::COLUMN_BIRTH_NUMBER, 'Rodné číslo:')
			->setOption('description', 'rodné číslo ve formátu RRMMDD/XXXX, možno zadat i bez lomítka')
			->addRule($form::MIN_LENGTH, 'Zadejte prosím rodné číslo ve správném formátu.', self::BIRTH_NUMBER_MIN_LENGTH)
			->addRule($form::MAX_LENGTH, 'Zadejte prosím rodné číslo ve správném formátu.', self::BIRTH_NUMBER_MAX_LENGTH)
			->setRequired()
			->setDefaultValue('121212/2025');

		$form->addSubmit('send', $caption);

		return $form;
	}


	public function createSelfEdit(callable $onSuccess, \Nette\Security\User $user): \Nette\Application\UI\Form
	{
		$form = $this->create($onSuccess, 'Uložit');
		$form->setDefaults($user->getIdentity()->data);

		/** @var \Nette\Forms\Controls\TextInput $newPassword */
		$newPassword = $form->getComponent(\App\Model\UserManager::COLUMN_PASSWORD_HASH);
		$newPassword->setCaption('Nové heslo')
			->setOption('description', 'Pouze pro změnu hesla');

		$form->addPassword('oldPassword', 'Potvrzovací heslo')
			->setOption('description', 'Uveďte aktuální heslo');
		$form->onSuccess[] = function (\Nette\Application\UI\Form $form, \stdClass $values) use ($onSuccess, $user): void {
			try {
				$this->userManager->update($values, $user);
				$user->logout();
				$password = $values->oldPassword;
				if (trim($values->heslo) !== '') {
					$password = $values->heslo;
				}
				$user->login($values->login, $password);
			} catch (\Nette\Security\AuthenticationException $e) {
				$form['oldPassword']->addError('Zadejte správné heslo prosím');

				return;
			} catch (\App\Model\ChangingLoginException $e) {
				$form[\App\Model\UserManager::COLUMN_LOGIN]->addError('Nemůžete si měnit login !');

				return;
			}
			$onSuccess();
		};

		return $form;
	}
}