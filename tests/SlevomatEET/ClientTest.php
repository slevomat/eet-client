<?php declare(strict_types = 1);

namespace SlevomatEET;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use SlevomatEET\Cryptography\CryptographyService;
use SlevomatEET\Driver\DriverRequestFailedException;
use SlevomatEET\Driver\SoapClientDriver;

class ClientTest extends TestCase
{

	/** @var Configuration */
	private $configuration;

	protected function setUp(): void
	{
		$this->configuration = new Configuration('CZ00000019', '273', '/5546/RO24', EvidenceEnvironment::get(EvidenceEnvironment::PLAYGROUND), false);
	}

	public function testSendSuccess(): void
	{
		$client = new Client(
			$this->createMock(CryptographyService::class),
			$this->configuration,
			$this->createMock(SoapClientDriver::class)
		);

		$soapClient = $this->createMock(SoapClient::class);
		(function () use ($soapClient): void {
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

	public function testSendInvalidResponse(): void
	{
		$client = new Client(
			$this->createMock(CryptographyService::class),
			$this->configuration,
			$this->createMock(SoapClientDriver::class)
		);

		$soapClient = $this->createMock(SoapClient::class);
		(function () use ($soapClient): void {
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

	public function testSendFailedRequest(): void
	{
		$soapClientDriver = $this->createMock(SoapClientDriver::class);
		$soapClientDriver->expects($this->once())
			->method('send')
			->with(
				$this->stringStartsWith('<?xml'),
				$this->identicalTo('https://pg.eet.cz:443/eet/services/EETServiceSOAP/v3'),
				$this->identicalTo('http://fs.mfcr.cz/eet/OdeslaniTrzby')
			)
			->willThrowException(new DriverRequestFailedException(new Exception('Fail')));
		$cryptographyService = $this->createMock(CryptographyService::class);
		$cryptographyService->method('addWSESignature')
			->willReturnArgument(0);

		$client = new Client(
			$cryptographyService,
			$this->configuration,
			$soapClientDriver
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
		return new Receipt(
			true,
			'CZ683555118',
			'0/6460/ZQ42',
			new DateTimeImmutable('2016-11-01 00:30:12'),
			3411300
		);
	}

}
