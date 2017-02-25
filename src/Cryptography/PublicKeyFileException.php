<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

class PublicKeyFileException extends \Exception
{

	/**
	 * @var string
	 */
	private $publicKeyFile;

	public function __construct(string $publicKeyFile, \Throwable $previous = null)
	{
		parent::__construct(sprintf(
			'Public key could not be loaded from file \'%s\'. Please make sure that the file contains valid public key in PEM format.',
			$publicKeyFile
		), 0, $previous);

		$this->publicKeyFile = $publicKeyFile;
	}

	public function getPublicKeyFile(): string
	{
		return $this->publicKeyFile;
	}

}
