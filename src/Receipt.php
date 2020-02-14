<?php declare(strict_types = 1);

namespace SlevomatEET;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Receipt
{

	/**
	 * XML uuid_zpravy
	 *
	 * @var UuidInterface
	 */
	private $uuid;

	/**
	 * XML prvni_zaslani
	 *
	 * @var bool
	 */
	private $firstSend = true;

	/**
	 * XML dic_poverujiciho
	 *
	 * @var string|null
	 */
	private $delegatedVatId;

	/**
	 * XML porad_cis
	 *
	 * @var string
	 */
	private $receiptNumber;

	/**
	 * XML dat_trzby
	 *
	 * @var DateTimeImmutable
	 */
	private $receiptTime;

	/**
	 * XML celk_trzba
	 *
	 * @var int
	 */
	private $totalPrice;

	/**
	 * XML zakl_nepodl_dph
	 *
	 * @var int|null
	 */
	private $priceZeroVat;

	/**
	 * XML zakl_dan1
	 *
	 * @var int|null
	 */
	private $priceStandardVat;

	/**
	 * XML dan1
	 *
	 * @var int|null
	 */
	private $vatStandard;

	/**
	 * XML zakl_dan2
	 *
	 * @var int|null
	 */
	private $priceFirstReducedVat;

	/**
	 * XML dan2
	 *
	 * @var int|null
	 */
	private $vatFirstReduced;

	/**
	 * XML zakl_dan3
	 *
	 * @var int|null
	 */
	private $priceSecondReducedVat;

	/**
	 * XML dan3
	 *
	 * @var int|null
	 */
	private $vatSecondReduced;

	/**
	 * XML cest_sluz
	 *
	 * @var int|null
	 */
	private $priceTravelService;

	/**
	 * XML pouzit_zboz1
	 *
	 * @var int|null
	 */
	private $priceUsedGoodsStandardVat;

	/**
	 * XML pouzit_zboz2
	 *
	 * @var int|null
	 */
	private $priceUsedGoodsFirstReducedVat;

	/**
	 * XML pouzit_zboz3
	 *
	 * @var int|null
	 */
	private $priceUsedGoodsSecondReducedVat;

	/**
	 * XML urceno_cerp_zuct
	 *
	 * @var int|null
	 */
	private $priceForSubsequentSettlement;

	/**
	 * XML cerp_zuct
	 *
	 * @var int|null
	 */
	private $priceUsedSubsequentSettlement;

	public function __construct(
		bool $firstSend,
		?string $delegatedVatId,
		string $receiptNumber,
		DateTimeImmutable $receiptTime,
		int $totalPrice = 0,
		?int $priceZeroVat = null,
		?int $priceStandardVat = null,
		?int $vatStandard = null,
		?int $priceFirstReducedVat = null,
		?int $vatFirstReduced = null,
		?int $priceSecondReducedVat = null,
		?int $vatSecondReduced = null,
		?int $priceTravelService = null,
		?int $priceUsedGoodsStandardVat = null,
		?int $priceUsedGoodsFirstReducedVat = null,
		?int $priceUsedGoodsSecondReducedVat = null,
		?int $priceSubsequentSettlement = null,
		?int $priceUsedSubsequentSettlement = null
	)
	{
		$this->uuid = Uuid::uuid4();
		$this->firstSend = $firstSend;
		$this->delegatedVatId = $delegatedVatId;
		$this->receiptNumber = $receiptNumber;
		$this->receiptTime = $receiptTime;
		$this->totalPrice = $totalPrice;
		$this->priceZeroVat = $priceZeroVat;
		$this->priceStandardVat = $priceStandardVat;
		$this->vatStandard = $vatStandard;
		$this->priceFirstReducedVat = $priceFirstReducedVat;
		$this->vatFirstReduced = $vatFirstReduced;
		$this->priceSecondReducedVat = $priceSecondReducedVat;
		$this->vatSecondReduced = $vatSecondReduced;
		$this->priceTravelService = $priceTravelService;
		$this->priceUsedGoodsStandardVat = $priceUsedGoodsStandardVat;
		$this->priceUsedGoodsFirstReducedVat = $priceUsedGoodsFirstReducedVat;
		$this->priceUsedGoodsSecondReducedVat = $priceUsedGoodsSecondReducedVat;
		$this->priceForSubsequentSettlement = $priceSubsequentSettlement;
		$this->priceUsedSubsequentSettlement = $priceUsedSubsequentSettlement;
	}

	public function getUuid(): UuidInterface
	{
		return $this->uuid;
	}

	public function isFirstSend(): bool
	{
		return $this->firstSend;
	}

	public function getDelegatedVatId(): ?string
	{
		return $this->delegatedVatId;
	}

	public function getReceiptNumber(): string
	{
		return $this->receiptNumber;
	}

	public function getReceiptTime(): DateTimeImmutable
	{
		return $this->receiptTime;
	}

	public function getTotalPrice(): int
	{
		return $this->totalPrice;
	}

	public function getPriceZeroVat(): ?int
	{
		return $this->priceZeroVat;
	}

	public function getPriceStandardVat(): ?int
	{
		return $this->priceStandardVat;
	}

	public function getVatStandard(): ?int
	{
		return $this->vatStandard;
	}

	public function getPriceFirstReducedVat(): ?int
	{
		return $this->priceFirstReducedVat;
	}

	public function getVatFirstReduced(): ?int
	{
		return $this->vatFirstReduced;
	}

	public function getPriceSecondReducedVat(): ?int
	{
		return $this->priceSecondReducedVat;
	}

	public function getVatSecondReduced(): ?int
	{
		return $this->vatSecondReduced;
	}

	public function getPriceTravelService(): ?int
	{
		return $this->priceTravelService;
	}

	public function getPriceUsedGoodsStandardVat(): ?int
	{
		return $this->priceUsedGoodsStandardVat;
	}

	public function getPriceUsedGoodsFirstReducedVat(): ?int
	{
		return $this->priceUsedGoodsFirstReducedVat;
	}

	public function getPriceUsedGoodsSecondReducedVat(): ?int
	{
		return $this->priceUsedGoodsSecondReducedVat;
	}

	public function getPriceForSubsequentSettlement(): ?int
	{
		return $this->priceForSubsequentSettlement;
	}

	public function getPriceUsedSubsequentSettlement(): ?int
	{
		return $this->priceUsedSubsequentSettlement;
	}

}
