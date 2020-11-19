<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;
use SlevomatEET\Driver\SoapClientDriver;
use const SOAP_1_1;
use const WSDL_CACHE_DISK;

class SoapClient extends \SoapClient
{

	/** @var CryptographyService */
	private $cryptoService;

	/** @var SoapClientDriver */
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
	 * @param mixed[] $parameters
	 * @return mixed
	 */
	public function OdeslaniTrzby(array $parameters)
	{
		return $this->__soapCall(__FUNCTION__, ['Trzba' => $parameters]);
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 *
	 * @param string $request
	 * @param string $location
	 * @param string $action
	 * @param int $version
	 * @param bool|int $oneWay
	 * @return string|null
	 */
	public function __doRequest($request, $location, $action, $version, $oneWay = 0): ?string
	{
		$signedRequest = $this->cryptoService->addWSESignature($request);
		$response = $this->clientDriver->send($signedRequest, $location, $action, $version);

		if ($oneWay) {
			return null;
		}
		return $response;
	}

}
