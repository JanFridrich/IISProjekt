<?php

declare(strict_types = 1);

namespace App\Presenters;

final class SignPresenter extends \Nette\Application\UI\Presenter
{
	/** @persistent */
	public $backlink = '';

	/**
	 * @var \App\Forms\UserForms\SignInFormFactory
	 */
	private $signInFactory;

	/**
	 * @var \App\Forms\UserForms\SignUpFormFactory
	 */
	private $signUpFactory;


	public function __construct(\App\Forms\UserForms\SignInFormFactory $signInFactory, \App\Forms\UserForms\SignUpFormFactory $signUpFactory)
	{

		parent::__construct();
		$this->signInFactory = $signInFactory;
		$this->signUpFactory = $signUpFactory;
	}


	protected function beforeRender(): void
	{
		$this->template->user = $this->getUser();
	}


	/**
	 * Sign-in form factory.
	 */
	protected function createComponentSignInForm(): \Nette\Application\UI\Form
	{
		return $this->signInFactory->create(function (): void {
			$this->restoreRequest($this->backlink);
			$this->redirect('Homepage:');
		});
	}


	/**
	 * Sign-up form factory.
	 */
	protected function createComponentSignUpForm(): \Nette\Application\UI\Form
	{
		return $this->signUpFactory->create(
			function (): void {
				$this->redirect('Homepage:');
			},
			'Registrovat');
	}


	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->redirect('Homepage:');
	}
}
