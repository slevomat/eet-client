<?php declare(strict_types = 1);

namespace SlevomatEET;

use SlevomatEET\Cryptography\CryptographyService;

class CryptographyServiceTest extends \PHPUnit\Framework\TestCase
{

	const EXPECTED_PKP = 'a0asEiJhFCBlVtptSspKvEZhcrvnzF7SQ55C4DhnStnSu1b37GUI2+Dlme9P94UCPZ1oCUPJdsYOBZ3IX6aEgEe0FJKXYX0kXraYCJKIo3g64wRchE7iblIOBCK1uHh8qqHA66Isnhb6hqBOOdlt2aWO/0jCzlfeQr0axpPF1mohMnP3h3ICaxZh0dnMdju5OmMrq+91PL5T9KkR7bfGHqAoWJ0kmxY/mZumtRfGil2/xf7I5pdVeYXPgDO/Tojzm6J95n68fPDOXTDrTzKYmqDjpg3kmWepLNQKFXRmkQrkBLToJWG1LDUDm3UTTmPWzq4c0XnGcXJDZglxfolGpA==';
	const EXPECTED_BKP = '9356D566-A3E48838-FB403790-D201244E-95DCBD92';

	public function testGetCodes()
	{
		$data = $this->getMockData();
		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub');

		$expectedPkp = base64_decode(self::EXPECTED_PKP);
		$pkpCode = $crypto->getPkpCode($data);
		self::assertSame($expectedPkp, $pkpCode);
		self::assertSame(self::EXPECTED_BKP, $crypto->getBkpCode($pkpCode));
	}

	public function testWSESignature()
	{
		$data = $this->getMockData();
		$request = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns1=\"http://fs.mfcr.cz/eet/schema/v3\"><SOAP-ENV:Body><ns1:Trzba><ns1:Hlavicka uuid_zpravy=\"1a10c633-8c0b-4003-93cd-9987836b6d57\" dat_odesl=\"{$data['celk_trzba']}\" prvni_zaslani=\"true\" overeni=\"false\"/><ns1:Data dic_popl=\"{$data['dic_popl']}\" id_provoz=\"{$data['id_provoz']}\" id_pokl=\"{$data['id_pokl']}\" porad_cis=\"{$data['porad_cis']}\" dat_trzby=\"{$data['dat_trzby']}\" celk_trzba=\"{$data['celk_trzba']}\" rezim=\"0\"/><ns1:KontrolniKody><ns1:pkp digest=\"SHA256\" cipher=\"RSA2048\" encoding=\"base64\">" . self::EXPECTED_PKP . "</ns1:pkp><ns1:bkp digest=\"SHA1\" encoding=\"base16\">" . self::EXPECTED_BKP . "</ns1:bkp></ns1:KontrolniKody></ns1:Trzba></SOAP-ENV:Body></SOAP-ENV:Envelope>";

		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground_With_Password-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub', 'eet');
		$this->assertNotEmpty($crypto->addWSESignature($request));

		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground_With_Password-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub');
		$this->expectException(\PHPUnit_Framework_Error::class);
		$crypto->addWSESignature($request);
	}

	protected function getMockData()
	{
		$data = [
			'dic_popl' => 'CZ00000019',
			'id_provoz' => '273',
			'id_pokl' => '/5546/RO24',
			'porad_cis' => '0/6460/ZQ42',
			'dat_trzby' => Formatter::formatDateTime(new \DateTimeImmutable('2016-08-05 00:30:12', new \DateTimeZone('Europe/Prague'))),
			'celk_trzba' => Formatter::formatAmount(3411300),
		];
		return $data;
	}

}
