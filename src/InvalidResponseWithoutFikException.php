<?php declare(strict_types = 1);

namespace SlevomatEET;

use Exception;
use Throwable;

class InvalidResponseWithoutFikException extends Exception
{

	/** @var EvidenceResponse */
	private $response;

	public function __construct(EvidenceResponse $response, ?Throwable $previous = null)
	{
		parent::__construct('Missing FIK in response', 0, $previous);
		$this->response = $response;
	}

	public function getResponse(): EvidenceResponse
	{
		return $this->response;
	}

}
