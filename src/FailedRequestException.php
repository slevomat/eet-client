<?php declare(strict_types = 1);

namespace SlevomatEET;

class FailedRequestException extends \Exception
{

	/** @var array */
	private $requestData;

	public function __construct(array $requestData, \Throwable $previous)
	{
		parent::__construct('Request error: ' . $previous->getMessage(), $previous->getCode(), $previous);
		$this->requestData = $requestData;
	}

	public function getRequestData(): array
	{
		return $this->requestData;
	}

}
