<?php

declare(strict_types = 1);

namespace App\Presenters;

use Nette;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected function startup()
	{
		parent::startup();
		if ( ! $this->isAllowed()) {
			$this->redirect('Sign:in');
		}
		switch ($this->getUser()->getRoles()) {
			case 'admin':
				$this->redirect('Homepage:admin');
			case 'lekar':
				$this->redirect('Homepage:doctor');
			case 'pracovnikZP':
				$this->redirect('Homepage:HIWorker');
			default:
				$this->redirect('Homepage:patient');
		}
	}


	protected function beforeRender(): void
	{
		$this->template->user = $this->getUser();
	}


	protected function isAllowed(): bool
	{
		return $this->getUser()->isLoggedIn();
	}
}
