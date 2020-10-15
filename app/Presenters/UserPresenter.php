<?php declare(strict_types = 1);

namespace App\Presenters;

class UserPresenter extends BasePresenter
{
	/**
	 * @var \App\Forms\UserForms\UserFormFactory
	 */
	private $userFormFactory;


	public function __construct(
		\App\Forms\UserForms\UserFormFactory $userFormFactory
	)
	{
		$this->userFormFactory = $userFormFactory;
		parent::__construct();
	}


	protected function createComponentSelfEditForm(): \Nette\Application\UI\Form
	{
		return $this->userFormFactory->createSelfEdit(
			function (): void {
				$this->redirect('Homepage:');
			},
			$this->getUser());
	}

}