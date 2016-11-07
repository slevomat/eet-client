<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;
use SlevomatEET\Driver\DriverRequestFailedException;
use SlevomatEET\Driver\SoapClientDriver;

class Client
{

	/** @var \SlevomatEET\Cryptography\CryptographyService */
	private $cryptographyService;

	/** @var \SlevomatEET\Configuration */
	private $configuration;

	/** @var \SlevomatEET\SoapClient|null */
	private $soapClient;

	/** @var \SlevomatEET\Driver\SoapClientDriver */
	private $soapClientDriver;

	public function __construct(CryptographyService $cryptographyService, Configuration $configuration, SoapClientDriver $soapClientDriver)
	{
		$this->cryptographyService = $cryptographyService;
		$this->configuration = $configuration;
		$this->soapClientDriver = $soapClientDriver;
	}

	public function send(Receipt $receipt): EvidenceResponse
	{
		$header = [
			'uuid_zpravy' => $receipt->getUuid()->toString(),
			'dat_odesl' => Formatter::formatDateTime(new \DateTimeImmutable()),
			'prvni_zaslani' => $receipt->isFirstSend(),
			'overeni' => $this->configuration->isVerificationMode(),
		];

		$body = [
			'dic_popl' => $this->configuration->getVatId(),
			'dic_poverujiciho' => $receipt->getDelegatedVatId(),
			'id_provoz' => $this->configuration->getPremiseId(),
			'id_pokl' => $this->configuration->getCashRegisterId(),
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
			'pouzit_zboz2' => Formatter::formatAmount($receipt->getPriceUsedGoodsFirstReduced()),
			'pouzit_zboz3' => Formatter::formatAmount($receipt->getPriceUsedGoodsSecondReduced()),
			'urceno_cerp_zuct' => Formatter::formatAmount($receipt->getPriceForSubsequentSettlement()),
			'cerp_zuct' => Formatter::formatAmount($receipt->getPriceUsedSubsequentSettlement()),
			'rezim' => $this->configuration->getEvidenceMode()->getValue(),
		];

		$body = array_filter($body, function ($value): bool {
			return $value !== null;
		});

		$pkpCode = $this->cryptographyService->getPkpCode($body);
		$bkpCode = $this->cryptographyService->getBkpCode($pkpCode);

		$request = [
			'Hlavicka' => $header,
			'Data' => $body,
			'KontrolniKody' => [
				'pkp' => [
					'_' => $pkpCode,
					'digest' => 'SHA256',
					'cipher' => 'RSA2048',
					'encoding' => 'base64',
				],
				'bkp' => [
					'_' => $bkpCode,
					'digest' => 'SHA1',
					'encoding' => 'base16',
				],
			],
		];
		try {
			$response = $this->getSoapClient()->OdeslaniTrzby($request);
		} catch (DriverRequestFailedException $e) {
			throw new FailedRequestException($request, $e);
		} catch (\SoapFault $e) {
			throw new FailedRequestException($request, $e);
		}

		$response = new EvidenceResponse($response);
		if (!$response->isValid()) {
			throw new InvalidResponseReceivedException($response);
		}

		return $response;
	}

	private function getSoapClient(): SoapClient
	{
		if ($this->soapClient === null) {
			$this->soapClient = new SoapClient($this->configuration->getEvidenceEnvironment()->getWsdlPath(), $this->cryptographyService, $this->soapClientDriver);
		}

		return $this->soapClient;
	}

	public function setSoapClient(SoapClient $soapClient)
	{
		$this->soapClient = $soapClient;
	}

}
