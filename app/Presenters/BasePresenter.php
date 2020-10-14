<?php

declare(strict_types=1);

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
		if ( ! $this->isAllowed() ) {
			$this->redirect('Sign:in');
		}
	}

	protected function beforeRender(): void
	{
		$this->template->user = $this->getUser();
	}


	protected function isAllowed(): bool
	{
		\Tracy\Debugger::barDump($this->getUser()->isInRole('pacient'));

		return $this->getUser()->isLoggedIn();
	}
}
