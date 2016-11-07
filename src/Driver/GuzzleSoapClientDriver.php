<?php declare(strict_types = 1);

namespace SlevomatEET\Driver;

class GuzzleSoapClientDriver implements SoapClientDriver
{

	const DEFAULT_TIMEOUT = 2.5;

	/** @var \GuzzleHttp\Client */
	private $httpClient;

	/** @var float */
	private $connectionTimeout;

	/** @var float */
	private $requestTimeout;

	public function __construct(\GuzzleHttp\Client $httpClient, float $connectionTimeout = self::DEFAULT_TIMEOUT, float $requestTimeout = self::DEFAULT_TIMEOUT)
	{
		$this->httpClient = $httpClient;
		$this->connectionTimeout = $connectionTimeout;
		$this->requestTimeout = $requestTimeout;
	}

	public function send(string $request, string $location, string $action, int $soapVersion): string
	{
		$headers = [
			'User-Agent: PHP',
			sprintf('Content-Type: %s; charset=utf-8', $soapVersion === 2 ? 'application/soap+xml' : 'text/xml'),
			sprintf('SOAPAction: %s', $action),
			sprintf('Content-Length: %s', strlen($request)),
		];

		$request = new \GuzzleHttp\Psr7\Request('POST', $location, $headers, $request);
		try {
			$httpResponse = $this->httpClient->send($request, [
				\GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
				\GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => false,
				\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => $this->connectionTimeout,
				\GuzzleHttp\RequestOptions::TIMEOUT => $this->requestTimeout,
			]);

			return (string) $httpResponse->getBody();
		} catch (\GuzzleHttp\Exception\RequestException $e) {
			throw new DriverRequestFailedException($e);
		}
	}

}
