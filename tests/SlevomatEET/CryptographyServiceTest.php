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

	public function testWSESignatureWithoutPrivateKeyPassword()
	{
		$request = $this->getMockRequest();
		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub');

		$this->assertNotEmpty($crypto->addWSESignature($request));
	}

	public function testWSESignatureWithPrivateKeyPassword()
	{
		$request = $this->getMockRequest();
		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground_With_Password-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub', 'eet');

		$this->assertNotEmpty($crypto->addWSESignature($request));
	}

	public function testWSESignatureWithInvalidPrivateKeyPassword()
	{
		$request = $this->getMockRequest();
		$crypto = new CryptographyService(__DIR__ . '/../../cert/EET_CA1_Playground_With_Password-CZ00000019.key', __DIR__ . '/../../cert/EET_CA1_Playground-CZ00000019.pub', 'invalid');

		$this->expectException(\PHPUnit_Framework_Error::class);
		$this->expectExceptionMessage('openssl_sign(): supplied key param cannot be coerced into a private key');
		$crypto->addWSESignature($request);
	}

	protected function getMockData(): array
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

	protected function getMockRequest(): string
	{
		$requestMock = file_get_contents(__DIR__ . '/Fixture/CZ00000019.fixture.3.1.xml');

		$data = $this->getMockData();
		$data += [
			'pkp' => self::EXPECTED_PKP,
			'bkp' => self::EXPECTED_BKP
		];

		$patterns = array_map(function ($dataKey) {
			return "~{{$dataKey}}~";
		}, array_keys($data));
		$replacements = array_values($data);

		$preparedRequestMock = preg_replace($patterns, $replacements, $requestMock);

		return $preparedRequestMock;
	}

}
