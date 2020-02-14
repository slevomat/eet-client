<?php declare(strict_types = 1);

namespace SlevomatEET;

class Configuration
{

	/** @var string */
	private $vatId;

	/** @var string */
	private $premiseId;

	/** @var string */
	private $cashRegisterId;

	/** @var bool */
	private $verificationMode;

	/** @var EvidenceMode */
	private $evidenceMode;

	/** @var EvidenceEnvironment */
	private $evidenceEnvironment;

	public function __construct(string $vatId, string $premiseId, string $cashRegisterId, EvidenceEnvironment $evidenceEnvironment, bool $verificationMode = false)
	{
		$this->vatId = $vatId;
		$this->premiseId = $premiseId;
		$this->cashRegisterId = $cashRegisterId;
		$this->evidenceMode = EvidenceMode::get(EvidenceMode::REGULAR);
		$this->verificationMode = $verificationMode;
		$this->evidenceEnvironment = $evidenceEnvironment;
	}

	public function getVatId(): string
	{
		return $this->vatId;
	}

	public function getPremiseId(): string
	{
		return $this->premiseId;
	}

	public function getCashRegisterId(): string
	{
		return $this->cashRegisterId;
	}

	public function isVerificationMode(): bool
	{
		return $this->verificationMode;
	}

	public function getEvidenceMode(): EvidenceMode
	{
		return $this->evidenceMode;
	}

	public function getEvidenceEnvironment(): EvidenceEnvironment
	{
		return $this->evidenceEnvironment;
	}

}
