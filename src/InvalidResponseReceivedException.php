<?php declare(strict_types = 1);

namespace SlevomatEET;

use Exception;
use Throwable;

class InvalidResponseReceivedException extends Exception
{

	/** @var EvidenceResponse */
	private $response;

	public function __construct(EvidenceResponse $response, ?Throwable $previous = null)
	{
		parent::__construct(sprintf('Invalid response received. Check response data for errors and warnings.'), 0, $previous);
		$this->response = $response;
	}

	public function getResponse(): EvidenceResponse
	{
		return $this->response;
	}

}
