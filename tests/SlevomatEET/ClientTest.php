<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;
use SlevomatEET\Driver\DriverRequestFailedException;
use SlevomatEET\Driver\SoapClientDriver;

class ClientTest extends \PHPUnit\Framework\TestCase
{

	/** @var \SlevomatEET\Cryptography\CryptographyService|\PHPUnit_Framework_MockObject_MockObject */
	private $cryptographyService;

	/** @var \SlevomatEET\Configuration|\PHPUnit_Framework_MockObject_MockObject */
	private $configuration;

	/** @var \SlevomatEET\Driver\SoapClientDriver|\PHPUnit_Framework_MockObject_MockObject */
	private $soapClientDriver;

	public function setUp()
	{
		$this->cryptographyService = $this->createMock(CryptographyService::class);
		$this->configuration = $configuration = new Configuration('CZ00000019', '273', '/5546/RO24', EvidenceEnvironment::get(EvidenceEnvironment::PLAYGROUND), false);
		$this->soapClientDriver = $this->createMock(SoapClientDriver::class);
	}

	public function testSendSuccess()
	{
		$client = new Client(
			$this->cryptographyService,
			$this->configuration,
			$this->soapClientDriver
		);

		$soapClient = $this->createMock(SoapClient::class);
		(function () use ($soapClient) {
			$this->soapClient = $soapClient;
		})->call($client);

		$receipt = $this->getTestReceipt();

		$responseData = (object) [
			'Hlavicka' => (object) [
				'uuid_zpravy' => '12345',
				'dat_prij' => '2016-11-21T08:00:00Z',
			],
			'Potvrzeni' => (object) [
				'fik' => '888-88-88-8888-ff',
			],
		];

		$soapClient->expects($this->once())
			->method('OdeslaniTrzby')
			->with($this->callback(function (array $request) {
				$this->assertArrayHasKey('Hlavicka', $request);
				$this->assertArrayHasKey('Data', $request);
				$this->assertArrayHasKey('KontrolniKody', $request);

				return true;
			}))
			->willReturn($responseData);

		$response = $client->send($receipt);

		$this->assertTrue($response->isValid());
		$this->assertFalse($response->isTest());
		$this->assertSame($response->getFik(), '888-88-88-8888-ff');
	}

	public function testSendInvalidResponse()
	{
		$client = new Client(
			$this->cryptographyService,
			$this->configuration,
			$this->soapClientDriver
		);

		$soapClient = $this->createMock(SoapClient::class);
		(function () use ($soapClient) {
			$this->soapClient = $soapClient;
		})->call($client);

		$receipt = $this->getTestReceipt();

		$responseData = (object) [
			'Hlavicka' => (object) [
				'uuid_zpravy' => '12345',
				'dat_odmit' => '2016-11-21T08:00:00Z',
			],
			'Chyba' => (object) [],
		];

		$soapClient->expects($this->once())
			->method('OdeslaniTrzby')
			->with($this->callback(function (array $request) {
				$this->assertArrayHasKey('Hlavicka', $request);
				$this->assertArrayHasKey('Data', $request);
				$this->assertArrayHasKey('KontrolniKody', $request);

				return true;
			}))
			->willReturn($responseData);

		try {
			$client->send($receipt);
			$this->fail('Expected exception was not thrown');
		} catch (InvalidResponseReceivedException $e) {
			$response = $e->getResponse();
			$this->assertFalse($response->isValid());
			$this->assertFalse($response->isTest());
			$this->expectException(InvalidResponseWithoutFikException::class);
			$response->getFik();
		}
	}

	public function testSendFailedRequest()
	{
		$this->soapClientDriver->expects($this->once())
			->method('send')
			->with(
				$this->stringStartsWith('<?xml'),
				$this->identicalTo('https://pg.eet.cz:443/eet/services/EETServiceSOAP/v3'),
				$this->identicalTo('http://fs.mfcr.cz/eet/OdeslaniTrzby')
			)
			->willThrowException(new DriverRequestFailedException(new \Exception('Fail')));
		$this->cryptographyService->method('addWSESignature')
			->willReturnArgument(0);

		$client = new Client(
			$this->cryptographyService,
			$this->configuration,
			$this->soapClientDriver
		);

		$receipt = $this->getTestReceipt();

		try {
			$client->send($receipt);
			$this->fail('Expected exception was not thrown');
		} catch (FailedRequestException $e) {
			$request = $e->getRequest();
			$this->assertSame('', $request->getBkpCode());
		}
	}

	private function getTestReceipt(): Receipt
	{
		$receipt = new Receipt(
			true,
			'CZ683555118',
			'0/6460/ZQ42',
			new \DateTimeImmutable('2016-11-01 00:30:12'),
			3411300
		);

		return $receipt;
	}

}
