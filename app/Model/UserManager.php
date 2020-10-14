<?php

declare(strict_types = 1);

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

final class UserManager implements Nette\Security\IAuthenticator
{
	use Nette\SmartObject;

	public const
		TABLE_NAME = 'uzivatel',
		COLUMN_ID = 'id',
		COLUMN_LOGIN = 'login',
		COLUMN_PASSWORD_HASH = 'heslo',
		COLUMN_EMAIL = 'email',
		COLUMN_NAME = 'jmeno',
		COLUMN_SURNAME = 'prijmeni',
		COLUMN_ROLE = 'role',
		COLUMN_BIRTH_NUMBER = 'rodne_cislo',
		COLUMN_PHONE = 'telefonni_cislo',
		DEFAULT_ROLE_VALUE = 'pacient';

	/** @var Nette\Database\Context */
	private $database;

	/** @var Passwords */
	private $passwords;


	public function __construct(Nette\Database\Context $database, Passwords $passwords)
	{
		$this->database = $database;
		$this->passwords = $passwords;
	}


	/**
	 * Performs an authentication.
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials): Nette\Security\IIdentity
	{
		[$username, $password] = $credentials;

		$row = $this->database->table(self::TABLE_NAME)
			->where(self::COLUMN_LOGIN, $username)
			->fetch();

		if ( ! $row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif ( ! $this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update([
				self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
			]);
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);

		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}


	/**
	 * Adds new user.
	 * @throws DuplicateNameException
	 */
	public function add(\stdClass $values): void
	{
		\Tracy\Debugger::barDump($values);
		Nette\Utils\Validators::assert($values->email, 'email');
		try {
			$birthNumber = \str_replace('/', '', $values->rodne_cislo);
			$this->database->table(self::TABLE_NAME)->insert([
				self::COLUMN_LOGIN => $values->login,
				self::COLUMN_PASSWORD_HASH => $this->passwords->hash($values->heslo),
				self::COLUMN_EMAIL => $values->email,
				self::COLUMN_NAME => $values->jmeno,
				self::COLUMN_SURNAME => $values->prijmeni,
				self::COLUMN_ROLE => self::DEFAULT_ROLE_VALUE,
				self::COLUMN_BIRTH_NUMBER => $birthNumber,
				self::COLUMN_PHONE => \preg_replace('/\s+/', '', $values->telefonni_cislo),
			]);
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}
}
