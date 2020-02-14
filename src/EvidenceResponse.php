<?php declare(strict_types = 1);

namespace SlevomatEET;

use DateTime;
use DateTimeImmutable;
use stdClass;

class EvidenceResponse
{

	/** @var stdClass */
	private $rawData;

	/** @var string|null */
	private $uuid;

	/** @var string|null */
	private $bkp;

	/** @var bool */
	private $test;

	/** @var string|null */
	private $fik;

	/** @var DateTimeImmutable */
	private $responseTime;

	/** @var EvidenceRequest */
	private $evidenceRequest;

	public function __construct(stdClass $rawData, EvidenceRequest $evidenceRequest)
	{
		$this->rawData = $rawData;
		$this->uuid = $rawData->Hlavicka->uuid_zpravy ?? null;
		if (isset($rawData->Potvrzeni)) {
			$this->fik = $rawData->Potvrzeni->fik;
		}
		$this->bkp = $rawData->Hlavicka->bkp ?? null;
		$this->test = $rawData->Potvrzeni->test ?? $rawData->Chyba->test ?? false;
		$this->responseTime = DateTimeImmutable::createFromFormat(DateTime::ISO8601, $rawData->Hlavicka->dat_prij ?? $rawData->Hlavicka->dat_odmit);
		$this->evidenceRequest = $evidenceRequest;
	}

	public function getFik(): string
	{
		if ($this->fik === null) {
			throw new InvalidResponseWithoutFikException($this);
		}

		return $this->fik;
	}

	public function getRawData(): stdClass
	{
		return $this->rawData;
	}

	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function getBkp(): ?string
	{
		return $this->bkp;
	}

	public function isTest(): bool
	{
		return $this->test;
	}

	public function isValid(): bool
	{
		return $this->fik !== null;
	}

	public function getResponseTime(): DateTimeImmutable
	{
		return $this->responseTime;
	}

	public function getRequest(): EvidenceRequest
	{
		return $this->evidenceRequest;
	}

}
