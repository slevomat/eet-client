<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;
use SlevomatEET\Driver\DriverRequestFailedException;
use SlevomatEET\Driver\SoapClientDriver;
use SoapFault;

class Client
{

	/** @var CryptographyService */
	private $cryptographyService;

	/** @var Configuration */
	private $configuration;

	/** @var SoapClient|null */
	private $soapClient;

	/** @var SoapClientDriver */
	private $soapClientDriver;

	public function __construct(CryptographyService $cryptographyService, Configuration $configuration, SoapClientDriver $soapClientDriver)
	{
		$this->cryptographyService = $cryptographyService;
		$this->configuration = $configuration;
		$this->soapClientDriver = $soapClientDriver;
	}

	public function send(Receipt $receipt): EvidenceResponse
	{
		$request = new EvidenceRequest($receipt, $this->configuration, $this->cryptographyService);

		try {
			$response = $this->getSoapClient()->OdeslaniTrzby($request->getRequestData());
		} catch (DriverRequestFailedException $e) {
			throw new FailedRequestException($request, $e);
		} catch (SoapFault $e) {
			throw new FailedRequestException($request, $e);
		}

		$response = new EvidenceResponse($response, $request);
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

}
