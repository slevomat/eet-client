<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;
use SlevomatEET\Driver\SoapClientDriver;

class SoapClient extends \SoapClient
{

	/** @var \SlevomatEET\Cryptography\CryptographyService */
	private $cryptoService;

	/** @var \SlevomatEET\Driver\SoapClientDriver */
	private $clientDriver;

	public function __construct(string $wsdl, CryptographyService $cryptoService, SoapClientDriver $clientDriver)
	{
		$options = [
			'soap_version' => SOAP_1_1,
			'encoding' => 'UTF-8',
			'trace' => true,
			'exceptions' => true,
			'cache_wsdl' => WSDL_CACHE_DISK,
		];
		parent::__construct($wsdl, $options);
		$this->cryptoService = $cryptoService;
		$this->clientDriver = $clientDriver;
	}

	/**
	 * @param array $parameters
	 * @return mixed
	 */
	public function OdeslaniTrzby(array $parameters)
	{
		return $this->__soapCall(__FUNCTION__, ['Trzba' => $parameters]);
	}

	/**
	 * @param string $request
	 * @param string $location
	 * @param string $action
	 * @param int $version
	 * @param int $oneWay
	 * @return string|null
	 */
	public function __doRequest($request, $location, $action, $version, $oneWay = 0)
	{
		$signedRequest = $this->cryptoService->addWSESignature($request);
		$response = $this->clientDriver->send($signedRequest, $location, $action, $version);

		if ($oneWay === 1) {
			return null;
		}
		return $response;
	}

}
