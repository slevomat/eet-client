<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use Exception;

class SigningFailedException extends Exception
{

	/** @var mixed[] */
	private $data;

	/**
	 * @param mixed[] $data
	 */
	public function __construct(array $data)
	{
		parent::__construct('Signing failed');

		$this->data = $data;
	}

	/**
	 * @return mixed[]
	 */
	public function getData(): array
	{
		return $this->data;
	}

}
