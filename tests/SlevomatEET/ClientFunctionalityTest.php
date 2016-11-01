<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;

class ClientFunctionalityTest extends \PHPUnit\Framework\TestCase
{

	public function testFunctionality()
	{
		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub');
		$configuration = new Configuration('CZ00000019', '273', '/5546/RO24', new EvidenceEnvironment(EvidenceEnvironment::PLAYGROUND), false);
		$client = new Client($crypto, $configuration);

		$receipt = new Receipt(
			true,
			'CZ683555118',
			'0/6460/ZQ42',
			new \DateTimeImmutable('2016-11-01 00:30:12'),
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
