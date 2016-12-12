<?php declare(strict_types = 1);

namespace SlevomatEET\Driver;

class GuzzleSoapClientDriverTest extends \PHPUnit\Framework\TestCase
{

	public function testSend()
	{
		$requestData = 'fooData';
		$responseData = 'responseData';
		$location = 'https://pg.eet.cz';
		$soapAction = 'fooAction';

		$guzzleHttpClient = $this->createMock(\GuzzleHttp\Client::class);
		$guzzleHttpClient
			->expects(self::once())
			->method('send')
			->with(self::callback(function (\GuzzleHttp\Psr7\Request $request) use ($requestData, $location, $soapAction) {
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
			->willReturn(new \GuzzleHttp\Psr7\Response(200, [], $responseData));

		$guzzleSoapClientDriver = new GuzzleSoapClientDriver($guzzleHttpClient);
		$response = $guzzleSoapClientDriver->send($requestData, $location, $soapAction, SOAP_1_1);

		$this->assertSame($responseData, $response);
	}

}
