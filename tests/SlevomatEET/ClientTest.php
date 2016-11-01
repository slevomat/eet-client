<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;

class ClientTest extends \PHPUnit\Framework\TestCase
{

	/** @var \PHPUnit_Framework_MockObject_MockObject|\SlevomatEET\Cryptography\CryptographyService */
	private $crypto;

	/** @var \SlevomatEET\Configuration */
	private $configuration;

	public function setUp()
	{
		$this->crypto = $this->createMock(CryptographyService::class);

		$this->configuration = new Configuration('CZ00000019', '273', '/5546/RO24', new EvidenceEnvironment(EvidenceEnvironment::PLAYGROUND), true);
	}

	public function testRequestFormatting()
	{
		$client = new Client($this->crypto, $this->configuration);

		$receipt = new Receipt(
			true,
			'CZ683555118',
			'0/6460/ZQ42',
			new \DateTimeImmutable('2016-11-01 00:30:12'),
			3411300
		);

		$this->crypto->method('getPkpCode')
			->willReturn('123');

		$this->crypto->method('getBkpCode')
			->willReturn('456');

		$soapClient = $this->createMock(SoapClient::class);
		$soapClient->expects(self::once())
			->method('OdeslaniTrzby')
			->with(self::callback(function (array $parameters) {
				$this->assertArrayHasKey('Hlavicka', $parameters);
				$this->assertArrayHasKey('Data', $parameters);
				$this->assertArrayHasKey('KontrolniKody', $parameters);
				$this->assertArrayHasKey('uuid_zpravy', $parameters['Hlavicka']);
				$this->assertArrayHasKey('dat_odesl', $parameters['Hlavicka']);

				unset($parameters['Hlavicka']['uuid_zpravy']);
				unset($parameters['Hlavicka']['dat_odesl']);

				$this->assertSame([
					'prvni_zaslani' => true,
					'overeni' => true,
				], $parameters['Hlavicka']);

				$this->assertSame([
					'dic_popl' => 'CZ00000019',
					'dic_poverujiciho' => 'CZ683555118',
					'id_provoz' => '273',
					'id_pokl' => '/5546/RO24',
					'porad_cis' => '0/6460/ZQ42',
					'dat_trzby' => '2016-11-01T00:30:12+01:00',
					'celk_trzba' => '34113.00',
					'rezim' => 0,
				], $parameters['Data']);

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
				], $parameters['KontrolniKody']);

				return true;
			}))
			->willReturn((object) ['Potvrzeni' => (object) ['fik' => '123456'], 'Hlavicka' => (object) ['dat_prij' => '2016-11-03T11:00:00+01:00']]);
		$client->setSoapClient($soapClient);
		$client->send($receipt);
	}

}
