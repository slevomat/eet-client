<?php declare(strict_types = 1);

namespace SlevomatEET\Driver;

use Exception;
use Throwable;

class DriverRequestFailedException extends Exception
{

	public function __construct(Throwable $e)
	{
		parent::__construct($e->getMessage(), $e->getCode(), $e);
	}

}
