<?php declare(strict_types = 1);

namespace SlevomatEET;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use SlevomatEET\Cryptography\CryptographyService;

class EvidenceRequestTest extends TestCase
{

	/** @var Configuration */
	private $configuration;

	protected function setUp(): void
	{
		$this->configuration = new Configuration('CZ00000019', '273', '/5546/RO24', EvidenceEnvironment::get(EvidenceEnvironment::PLAYGROUND), true);
	}

	public function testRequestFormatting(): void
	{
		$receipt = new Receipt(
			true,
			'CZ683555118',
			'0/6460/ZQ42',
			new DateTimeImmutable('2016-11-01 00:30:12', new DateTimeZone('Europe/Prague')),
			3411300
		);

		$crypto = $this->createMock(CryptographyService::class);
		$crypto->method('getPkpCode')
			->willReturn('123');

		$crypto->method('getBkpCode')
			->willReturn('456');

		$request = new EvidenceRequest($receipt, $this->configuration, $crypto);

		$requestData = $request->getRequestData();

		$this->assertArrayHasKey('Hlavicka', $requestData);
		$this->assertArrayHasKey('Data', $requestData);
		$this->assertArrayHasKey('KontrolniKody', $requestData);
		$headerData = $requestData['Hlavicka'];
		$this->assertArrayHasKey('uuid_zpravy', $headerData);
		$this->assertArrayHasKey('dat_odesl', $headerData);

		$this->assertSame($headerData, $request->getHeader());
		unset($headerData['uuid_zpravy']);
		unset($headerData['dat_odesl']);

		$this->assertSame([
			'prvni_zaslani' => true,
			'overeni' => true,
		], $headerData);

		$this->assertSame([
			'dic_popl' => 'CZ00000019',
			'dic_poverujiciho' => 'CZ683555118',
			'id_provoz' => '273',
			'id_pokl' => '/5546/RO24',
			'porad_cis' => '0/6460/ZQ42',
			'dat_trzby' => '2016-11-01T00:30:12+01:00',
			'celk_trzba' => '34113.00',
			'rezim' => 0,
		], $requestData['Data']);

		$this->assertSame($requestData['Data'], $request->getBody());

		$this->assertSame([
			'pkp' => [
				'_' => '123',
				'digest' => 'SHA256',
				'cipher' => 'RSA2048',
				'encoding' => 'base64',
			],
			'bkp' => [
				'_' => '456',
				'digest' => 'SHA1',
				'encoding' => 'base16',
			],
		], $requestData['KontrolniKody']);

		$this->assertSame('123', $request->getPkpCode());
		$this->assertSame('456', $request->getBkpCode());

		$this->assertInstanceOf(DateTimeImmutable::class, $request->getSendDate());
	}

}
