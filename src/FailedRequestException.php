<?php declare(strict_types = 1);

namespace SlevomatEET;

class FailedRequestException extends \Exception
{

	/** @var \SlevomatEET\EvidenceRequest */
	private $requestData;

	public function __construct(EvidenceRequest $requestData, \Throwable $previous)
	{
		parent::__construct('Request error: ' . $previous->getMessage(), $previous->getCode(), $previous);
		$this->requestData = $requestData;
	}

	public function getRequestData(): EvidenceRequest
	{
		return $this->requestData;
	}

}
