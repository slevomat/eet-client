<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;

class SoapClient extends \SoapClient
{

	/** @var \SlevomatEET\Cryptography\CryptographyService */
	private $cryptoService;

	public function __construct(string $wsdl, CryptographyService $cryptoService)
	{
		$options = [
			'soap_version' => SOAP_1_1,
			'encoding' => 'UTF-8',
			'trace' => true,
			'exceptions' => true,
			'cache_wsdl' => WSDL_CACHE_DISK,
			'user_agent' => 'Slevomat EET client',
			'keep_alive' => true,
			'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
			'connection_timeout' => 2,
		];
		parent::__construct($wsdl, $options);
		$this->cryptoService = $cryptoService;
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
	 * @return string
	 */
	public function __doRequest($request, $location, $action, $version, $oneWay = 0)
	{
		try {
			return parent::__doRequest($this->cryptoService->addWSESignature($request), $location, $action, $version, $oneWay);
		} catch (\SoapFault $e) {
			throw $e;
		}
	}

}
