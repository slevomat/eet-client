<?php declare(strict_types = 1);

namespace SlevomatEET;

class EvidenceResponse
{

	/** @var array */
	private $rawData;

	/** @var string|null */
	private $uuid;

	/** @var string|null */
	private $bkp;

	/** @var bool */
	private $test;

	/** @var string|null */
	private $fik;

	/** @var \DateTimeImmutable */
	private $responseTime;

	public function __construct(\stdClass $rawData)
	{
		$this->rawData = $rawData;
		$this->uuid = $rawData->Hlavicka->uuid_zpravy ?? null;
		if (isset($rawData->Potvrzeni)) {
			$this->fik = $rawData->Potvrzeni->fik;
		}
		$this->bkp = $rawData->Hlavicka->bkp ?? null;
		$this->test = $rawData->Potvrzeni->test ?? $rawData->Chyba->test ?? false;
		$this->responseTime = \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $rawData->Hlavicka->dat_prij ?? $rawData->Hlavicka->dat_odmit);
	}

	public function getFik(): string
	{
		if (!$this->isValid()) {
			throw new InvalidResponseWithoutFikException($this);
		}

		return $this->fik;
	}

	public function getRawData(): array
	{
		return $this->rawData;
	}

	/**
	 * @return string|null
	 */
	public function getUuid()
	{
		return $this->uuid;
	}

	/**
	 * @return string|null
	 */
	public function getBkp()
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

	public function getResponseTime(): \DateTimeImmutable
	{
		return $this->responseTime;
	}

}
