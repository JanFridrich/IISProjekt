<?php declare(strict_types = 1);

namespace App\Forms\UserForms;

class UserFormFactory
{
	use \Nette\SmartObject;

	private const PASSWORD_MIN_LENGTH = 7;
	private const BIRTH_NUMBER_MIN_LENGTH = 9;
	private const BIRTH_NUMBER_MAX_LENGTH = 11;
	private const PHONE_NUMBER_LENGTH = 9;

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
			->setOption('description', sprintf('alspoň %d znaků', self::PASSWORD_MIN_LENGTH))
			->setRequired('Prosím vytvořte si heslo.')
			->addRule($form::MIN_LENGTH, sprintf('alspoň %d znaků', self::PASSWORD_MIN_LENGTH), self::PASSWORD_MIN_LENGTH);

		$form->addPassword(\App\Model\UserManager::COLUMN_PASSWORD_HASH . '2', 'Heslo znovu:')
			->setRequired('Napište heslo znovu.')
			->addRule($form::EQUAL, 'Hesla se musí shodovat', $form[\App\Model\UserManager::COLUMN_PASSWORD_HASH]);

		// nastavíme defaultní hodnoty pro testování
		$form->addText(\App\Model\UserManager::COLUMN_PHONE, 'Telefonní číslo: +420')
			->setRequired()
			->setDefaultValue('999333444')
			->addRule(\App\Model\PhoneNumberValidator::class.'::numberValidator', 'Pouze mezery a čísla mohou být součástí telefonního čísla');

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
}