<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use Throwable;

class PublicKeyFileNotFoundException extends PublicKeyFileException
{

	public function __construct(string $publicKeyFile, ?Throwable $previous = null)
	{
		parent::__construct(sprintf(
			'Public key could not be loaded from file \'%s\'.',
			$publicKeyFile
		), $publicKeyFile, $previous);
	}

}
