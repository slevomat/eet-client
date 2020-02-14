<?php declare(strict_types = 1);

namespace SlevomatEET\Driver;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use const SOAP_1_1;

class GuzzleSoapClientDriverTest extends TestCase
{

	public function testSend(): void
	{
		$requestData = 'fooData';
		$responseData = 'responseData';
		$location = 'https://pg.eet.cz';
		$soapAction = 'fooAction';

		$guzzleHttpClient = $this->createMock(Client::class);
		$guzzleHttpClient
			->expects(self::once())
			->method('send')
			->with(self::callback(function (Request $request) use ($requestData, $location, $soapAction) {
				$this->assertEquals([
					'Host' => [
						'pg.eet.cz',
					],
					'User-Agent' => [
						GuzzleSoapClientDriver::HEADER_USER_AGENT,
					],
					'Content-Type' => [
						'text/xml; charset=utf-8',
					],
					'SOAPAction' => [
						$soapAction,
					],
					'Content-Length' => [
						strlen($requestData),
					],
				], $request->getHeaders());
				$this->assertEquals($location, (string) $request->getUri());

				return true;
			}))
			->willReturn(new Response(200, [], $responseData));

		$guzzleSoapClientDriver = new GuzzleSoapClientDriver($guzzleHttpClient);
		$response = $guzzleSoapClientDriver->send($requestData, $location, $soapAction, SOAP_1_1);

		$this->assertSame($responseData, $response);
	}

}
