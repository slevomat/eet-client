<?php declare(strict_types = 1);

namespace SlevomatEET;

use Composer\CaBundle\CaBundle;
use DateTimeImmutable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use SlevomatEET\Cryptography\CryptographyService;
use SlevomatEET\Driver\GuzzleSoapClientDriver;

class ClientFunctionalityTest extends TestCase
{

	public function testFunctionality(): void
	{
		if (getenv('TRAVIS') !== false) {
			$this->markTestSkipped('EET is blocking Travis CI :(');
		}
		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub');
		$configuration = new Configuration('CZ00000019', '273', '/5546/RO24', EvidenceEnvironment::get(EvidenceEnvironment::PLAYGROUND), false);
		$client = new Client(
			$crypto,
			$configuration,
			new GuzzleSoapClientDriver(
				new GuzzleClient(
					[RequestOptions::VERIFY => CaBundle::getBundledCaBundlePath()]
				)
			)
		);

		$receipt = new Receipt(
			true,
			'CZ683555118',
			'0/6460/ZQ42',
			new DateTimeImmutable('2016-11-01 00:30:12'),
			3411300
		);

		$response = $client->send($receipt);

		$this->assertInstanceOf(EvidenceResponse::class, $response);
		$this->assertTrue($response->isValid());
		$this->assertNotNull($response->getFik());
		$this->assertNotNull($response->getUuid());
		$this->assertTrue($response->isTest());
	}

}
