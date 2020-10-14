<?php declare(strict_types = 1);

namespace App\Forms\UserForms;

final class SignUpFormFactory
{
	use \Nette\SmartObject;

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

	/**
	 * @var UserFormFactory
	 */
	private $userFormFactory;


	public function __construct(
		\App\Forms\FormFactory $factory,
		\App\Model\UserManager $userManager,
		\Nette\Security\User $user,
		UserFormFactory $userFormFactory
	)
	{
		$this->factory = $factory;
		$this->userManager = $userManager;
		$this->user = $user;
		$this->userFormFactory = $userFormFactory;
	}


	public function create(callable $onSuccess, $caption): \Nette\Application\UI\Form
	{
		$form = $this->userFormFactory->create($onSuccess, $caption);
		$form->onSuccess[] = function (\Nette\Application\UI\Form $form, \stdClass $values) use ($onSuccess): void {
			try {
				$this->userManager->add($values);
			} catch (\App\Model\DuplicateNameException $e) {
				$form['login']->addError('Přihlašovací jméno už použito');

				return;
			}
			$onSuccess();
		};

		return $form;
	}
}
