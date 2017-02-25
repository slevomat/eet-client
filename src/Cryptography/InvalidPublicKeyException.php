<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use Throwable;

class InvalidPublicKeyException extends PublicKeyFileException
{

	public function __construct(string $publicKeyFile, string $openSslError, ?Throwable $previous = null)
	{
		parent::__construct(sprintf(
			'Public key could not be loaded from file \'%s\'. Please make sure that the file contains valid public key in PEM format. (OpenSSL error: %s)',
			$publicKeyFile,
			$openSslError
		), $publicKeyFile, $previous);
	}

}
