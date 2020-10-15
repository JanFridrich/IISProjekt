<?php declare(strict_types = 1);

namespace App\Presenters;

final class HomepagePresenter extends BasePresenter
{
	public function renderAdmin(): void
	{
		$this->template->anyVariable = 'blabolovina';
	}


	public function renderPatient(): void
	{

	}


	public function renderHIWorker(): void
	{

	}


	public function renderDoctor(): void
	{

	}
}
