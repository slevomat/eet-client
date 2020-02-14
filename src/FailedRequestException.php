<?php declare(strict_types = 1);

namespace SlevomatEET;

use Exception;
use Throwable;

class FailedRequestException extends Exception
{

	/** @var EvidenceRequest */
	private $request;

	public function __construct(EvidenceRequest $request, Throwable $previous)
	{
		parent::__construct('Request error: ' . $previous->getMessage(), $previous->getCode(), $previous);
		$this->request = $request;
	}

	public function getRequest(): EvidenceRequest
	{
		return $this->request;
	}

}
