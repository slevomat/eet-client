<?php declare(strict_types = 1);

namespace SlevomatEET\Driver;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;

class GuzzleSoapClientDriver implements SoapClientDriver
{

	public const DEFAULT_TIMEOUT = 2.5;
	public const HEADER_USER_AGENT = 'PHP';

	/** @var Client */
	private $httpClient;

	/** @var float */
	private $connectionTimeout;

	/** @var float */
	private $requestTimeout;

	public function __construct(Client $httpClient, float $connectionTimeout = self::DEFAULT_TIMEOUT, float $requestTimeout = self::DEFAULT_TIMEOUT)
	{
		$this->httpClient = $httpClient;
		$this->connectionTimeout = $connectionTimeout;
		$this->requestTimeout = $requestTimeout;
	}

	public function send(string $request, string $location, string $action, int $soapVersion): string
	{
		$headers = [
			'User-Agent' => self::HEADER_USER_AGENT,
			'Content-Type' => sprintf('%s; charset=utf-8', $soapVersion === 2 ? 'application/soap+xml' : 'text/xml'),
			'SOAPAction' => $action,
			'Content-Length' => (string) strlen($request),
		];

		$request = new Request('POST', $location, $headers, $request);
		try {
			$httpResponse = $this->httpClient->send($request, [
				RequestOptions::HTTP_ERRORS => false,
				RequestOptions::ALLOW_REDIRECTS => false,
				RequestOptions::CONNECT_TIMEOUT => $this->connectionTimeout,
				RequestOptions::TIMEOUT => $this->requestTimeout,
			]);

			return (string) $httpResponse->getBody();
		} catch (RequestException $e) {
			throw new DriverRequestFailedException($e);
		}
	}

}
