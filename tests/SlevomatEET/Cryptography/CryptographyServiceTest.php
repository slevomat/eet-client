<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use SlevomatEET\Formatter;
use function base64_encode;

class CryptographyServiceTest extends TestCase
{

	const EXPECTED_PKP = 'hdBqjqCTaEfJ6JI06H+c4OLvRGtntcwLlG0fucEkla++g9RLxP55jYlPLFf6Sdpm5jPC+hpBHry98zsPBlbwkcFiWdmgT2VBCtXxrwfRmJQOHNRdWhItDsHC4p45G+KmtC4uJCFAqFNL+E999wevPaS6Q02WktmvWI5+XUZnN75hR+G94oznpJS8T140850/FsYDlvPw0ZVWJwDMBzVrOWWxPSN3SBwa40TjD3dVIMlMC1Bo0NccnFp0y7GxNMSfIzDhF5R4S2Rmawe85znZ0PiHXMkPDhXLLpPx1pNiMsTwfeoEnhEMSU/PjjmLpbUzaRfLwZzgf+7Bl0ZX+/lsqA==';
	const EXPECTED_BKP = 'F049C3F1-165CDCDA-2E35BC3A-FCB5C660-4B84D0B7';

	public function testGetCodes()
	{
		$data = $this->getReceiptData();
		$crypto = new CryptographyService(__DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.key', __DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.pub');

		$expectedPkp = self::EXPECTED_PKP;
		$pkpCode = $crypto->getPkpCode($data);
		self::assertSame($expectedPkp, base64_encode($pkpCode));
		self::assertSame(self::EXPECTED_BKP, $crypto->getBkpCode($pkpCode));
	}

	public function testExceptions()
	{
		$cryptoService = new CryptographyService(
			__DIR__ . '/invalid-certificate.pem',
			__DIR__ . '/invalid-certificate.pem'
		);

		try {
			$cryptoService->getPkpCode($this->getReceiptData());
			$this->fail();

		} catch (PrivateKeyFileException $e) {
			$this->assertSame(__DIR__ . '/invalid-certificate.pem', $e->getPrivateKeyFile());
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testExceptions2()
	{
		include __DIR__ . '/OpenSslFunctionsMock.php';

		$cryptoService = new CryptographyService(
			__DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.key',
			__DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.pub'
		);

		try {
			$cryptoService->getPkpCode($this->getReceiptData());
			$this->fail();

		} catch (SigningFailedException $e) {
			$this->assertSame(array_values($this->getReceiptData()), $e->getData());
		}
	}

	public function testWSESignatureWithoutPrivateKeyPassword()
	{
		$request = $this->getRequestData();
		$crypto = new CryptographyService(
			__DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.key',
			__DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.pub'
		);

		$this->assertNotEmpty($crypto->addWSESignature($request));
	}

	public function testWSESignatureWithPrivateKeyPassword()
	{
		$request = $this->getRequestData();
		$crypto = new CryptographyService(
			__DIR__ . '/../../../cert/EET_CA1_Playground_With_Password-CZ00000019.key',
			__DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.pub',
			'eet'
		);

		$this->assertNotEmpty($crypto->addWSESignature($request));
	}

	public function testWSESignatureWithInvalidPrivateKeyPassword()
	{
		$request = $this->getRequestData();
		$crypto = new CryptographyService(
			__DIR__ . '/../../../cert/EET_CA1_Playground_With_Password-CZ00000019.key',
			__DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.pub',
			'invalid'
		);

		$this->expectException(Error::class);
		$this->expectExceptionMessage('openssl_sign(): supplied key param cannot be coerced into a private key');
		$crypto->addWSESignature($request);
	}

	private function getReceiptData(): array
	{
		return [
			'dic_popl' => 'CZ00000019',
			'id_provoz' => '273',
			'id_pokl' => '/5546/RO24',
			'porad_cis' => '0/6460/ZQ42',
			'dat_trzby' => Formatter::formatDateTime(new DateTimeImmutable('2016-08-05 00:30:12', new DateTimeZone('Europe/Prague'))),
			'celk_trzba' => Formatter::formatAmount(3411300),
		];
	}

	private function getRequestData(): string
	{
		$requestTemplate = (string) file_get_contents(__DIR__ . '/CZ00000019.fixture.3.1.xml');

		$data = $this->getReceiptData();
		$data += [
			'pkp' => self::EXPECTED_PKP,
			'bkp' => self::EXPECTED_BKP,
		];

		$patterns = array_map(static function (string $dataKey): string {
			return sprintf('~{%s}~', $dataKey);
		}, array_keys($data));
		$replacements = array_values($data);

		return preg_replace($patterns, $replacements, $requestTemplate);
	}

}
