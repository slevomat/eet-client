<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use Exception;
use Throwable;

abstract class PublicKeyFileException extends Exception
{

	/** @var string */
	private $publicKeyFile;

	public function __construct(string $message, string $publicKeyFile, ?Throwable $previous = null)
	{
		parent::__construct($message, 0, $previous);

		$this->publicKeyFile = $publicKeyFile;
	}

	public function getPublicKeyFile(): string
	{
		return $this->publicKeyFile;
	}

}
