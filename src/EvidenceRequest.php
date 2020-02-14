<?php declare(strict_types = 1);

namespace SlevomatEET;

use DateTimeImmutable;
use SlevomatEET\Cryptography\CryptographyService;

class EvidenceRequest
{

	/** @var DateTimeImmutable */
	private $sendDate;

	/** @var mixed[] */
	private $header;

	/** @var mixed[] */
	private $body;

	/** @var string */
	private $pkpCode;

	/** @var string */
	private $bkpCode;

	public function __construct(Receipt $receipt, Configuration $configuration, CryptographyService $cryptographyService)
	{
		$this->sendDate = new DateTimeImmutable();
		$this->header = [
			'uuid_zpravy' => $receipt->getUuid()->toString(),
			'dat_odesl' => Formatter::formatDateTime($this->sendDate),
			'prvni_zaslani' => $receipt->isFirstSend(),
			'overeni' => $configuration->isVerificationMode(),
		];

		$body = [
			'dic_popl' => $configuration->getVatId(),
			'dic_poverujiciho' => $receipt->getDelegatedVatId(),
			'id_provoz' => $configuration->getPremiseId(),
			'id_pokl' => $configuration->getCashRegisterId(),
			'porad_cis' => $receipt->getReceiptNumber(),
			'dat_trzby' => Formatter::formatDateTime($receipt->getReceiptTime()),
			'celk_trzba' => Formatter::formatAmount($receipt->getTotalPrice()),
			'zakl_nepodl_dph' => Formatter::formatAmount($receipt->getPriceZeroVat()),
			'zakl_dan1' => Formatter::formatAmount($receipt->getPriceStandardVat()),
			'dan1' => Formatter::formatAmount($receipt->getVatStandard()),
			'zakl_dan2' => Formatter::formatAmount($receipt->getPriceFirstReducedVat()),
			'dan2' => Formatter::formatAmount($receipt->getVatFirstReduced()),
			'zakl_dan3' => Formatter::formatAmount($receipt->getPriceSecondReducedVat()),
			'dan3' => Formatter::formatAmount($receipt->getVatSecondReduced()),
			'cest_sluz' => Formatter::formatAmount($receipt->getPriceTravelService()),
			'pouzit_zboz1' => Formatter::formatAmount($receipt->getPriceUsedGoodsStandardVat()),
			'pouzit_zboz2' => Formatter::formatAmount($receipt->getPriceUsedGoodsFirstReducedVat()),
			'pouzit_zboz3' => Formatter::formatAmount($receipt->getPriceUsedGoodsSecondReducedVat()),
			'urceno_cerp_zuct' => Formatter::formatAmount($receipt->getPriceForSubsequentSettlement()),
			'cerp_zuct' => Formatter::formatAmount($receipt->getPriceUsedSubsequentSettlement()),
			'rezim' => $configuration->getEvidenceMode()->getValue(),
		];
		$this->body = array_filter($body, static function ($value): bool {
			return $value !== null;
		});

		$this->pkpCode = $cryptographyService->getPkpCode($this->body);
		$this->bkpCode = $cryptographyService->getBkpCode($this->pkpCode);
	}

	/**
	 * @return mixed[]
	 */
	public function getRequestData(): array
	{
		return [
			'Hlavicka' => $this->header,
			'Data' => $this->body,
			'KontrolniKody' => [
				'pkp' => [
					'_' => $this->pkpCode,
					'digest' => 'SHA256',
					'cipher' => 'RSA2048',
					'encoding' => 'base64',
				],
				'bkp' => [
					'_' => $this->bkpCode,
					'digest' => 'SHA1',
					'encoding' => 'base16',
				],
			],
		];
	}

	public function getSendDate(): DateTimeImmutable
	{
		return $this->sendDate;
	}

	/**
	 * @return mixed[]
	 */
	public function getHeader(): array
	{
		return $this->header;
	}

	/**
	 * @return mixed[]
	 */
	public function getBody(): array
	{
		return $this->body;
	}

	public function getPkpCode(): string
	{
		return $this->pkpCode;
	}

	public function getBkpCode(): string
	{
		return $this->bkpCode;
	}

}
