<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use SlevomatEET\Formatter;
use Throwable;
use function base64_encode;

class CryptographyServiceTest extends TestCase
{

	private const EXPECTED_PKP = 'hdBqjqCTaEfJ6JI06H+c4OLvRGtntcwLlG0fucEkla++g9RLxP55jYlPLFf6Sdpm5jPC+hpBHry98zsPBlbwkcFiWdmgT2VBCtXxrwfRmJQOHNRdWhItDsHC4p45G+KmtC4uJCFAqFNL+E999wevPaS6Q02WktmvWI5+XUZnN75hR+G94oznpJS8T140850/FsYDlvPw0ZVWJwDMBzVrOWWxPSN3SBwa40TjD3dVIMlMC1Bo0NccnFp0y7GxNMSfIzDhF5R4S2Rmawe85znZ0PiHXMkPDhXLLpPx1pNiMsTwfeoEnhEMSU/PjjmLpbUzaRfLwZzgf+7Bl0ZX+/lsqA==';
	private const EXPECTED_BKP = 'F049C3F1-165CDCDA-2E35BC3A-FCB5C660-4B84D0B7';
	private const PRIVATE_KEY_WITHOUT_PASSWORD_PATH = __DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.key';
	private const PRIVATE_KEY_WITH_PASSWORD_PATH = __DIR__ . '/../../../cert/EET_CA1_Playground_With_Password-CZ00000019.key';
	private const PUBLIC_KEY_PATH = __DIR__ . '/../../../cert/EET_CA1_Playground-CZ00000019.pub';
	private const INVALID_KEY_PATH = __DIR__ . '/invalid-certificate.pem';

	public function testGetCodes(): void
	{
		$data = $this->getReceiptData();
		$crypto = $this->createCryptographyServiceWithoutPassword();

		$expectedPkp = self::EXPECTED_PKP;
		$pkpCode = $crypto->getPkpCode($data);
		self::assertSame($expectedPkp, base64_encode($pkpCode));
		self::assertSame(self::EXPECTED_BKP, $crypto->getBkpCode($pkpCode));
	}

	/**
	 * @dataProvider provideInvalidKeyPaths
	 *
	 * @param string $privateKeyPath
	 * @param string $publicKeyPath
	 * @param string $expectedExceptionType
	 *
	 * @phpstan-param class-string<Throwable> $expectedExceptionType
	 */
	public function testInvalidKeyPaths(string $privateKeyPath, string $publicKeyPath, string $expectedExceptionType): void
	{
		$this->expectException($expectedExceptionType);
		new CryptographyService($privateKeyPath, $publicKeyPath);
	}

	/**
	 * @return mixed[][]
	 */
	public function provideInvalidKeyPaths(): array
	{
		return [
			[self::PRIVATE_KEY_WITHOUT_PASSWORD_PATH, './foo/path', PublicKeyFileNotFoundException::class],
			['./foo/path', self::PUBLIC_KEY_PATH, PrivateKeyFileNotFoundException::class],
		];
	}

	public function testInvalidPrivateKeyInPkpCalculation(): void
	{
		$cryptoService = new CryptographyService(
			self::INVALID_KEY_PATH,
			self::PUBLIC_KEY_PATH
		);

		try {
			$cryptoService->getPkpCode($this->getReceiptData());
			$this->fail();

		} catch (PrivateKeyFileException $e) {
			$this->assertSame(self::INVALID_KEY_PATH, $e->getPrivateKeyFile());
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testExceptions2(): void
	{
		include __DIR__ . '/OpenSslFunctionsMock.php';

		$cryptoService = $this->createCryptographyServiceWithoutPassword();

		try {
			$cryptoService->getPkpCode($this->getReceiptData());
			$this->fail();

		} catch (SigningFailedException $e) {
			$this->assertSame(array_values($this->getReceiptData()), $e->getData());
		}
	}

	public function testWSESignatureWithoutPrivateKeyPassword(): void
	{
		$request = $this->getRequestData();
		$crypto = $this->createCryptographyServiceWithoutPassword();

		$this->assertNotEmpty($crypto->addWSESignature($request));
	}

	public function testWSESignatureWithPrivateKeyPassword(): void
	{
		$request = $this->getRequestData();
		$crypto = $this->createCryptographyServiceWithPassword('eet');

		$this->assertNotEmpty($crypto->addWSESignature($request));
	}

	public function testWSESignatureWithInvalidPrivateKeyPassword(): void
	{
		$request = $this->getRequestData();
		$crypto = $this->createCryptographyServiceWithPassword('invalid');

		$this->expectError();
		$this->expectDeprecationMessageMatches('~openssl_sign\(\): supplied key param cannot be coerced into a private key~i');
		$crypto->addWSESignature($request);
	}

	public function testWSESignatureWithInvalidPublicKey(): void
	{
		$request = $this->getRequestData();
		$crypto = new CryptographyService(
			self::PRIVATE_KEY_WITHOUT_PASSWORD_PATH,
			self::INVALID_KEY_PATH
		);

		$this->expectException(InvalidPublicKeyException::class);
		$crypto->addWSESignature($request);
	}

	/**
	 * @return mixed[]
	 */
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

		return (string) preg_replace($patterns, $replacements, $requestTemplate);
	}

	private function createCryptographyServiceWithoutPassword(): CryptographyService
	{
		return new CryptographyService(self::PRIVATE_KEY_WITHOUT_PASSWORD_PATH, self::PUBLIC_KEY_PATH);
	}

	private function createCryptographyServiceWithPassword(string $password): CryptographyService
	{
		return new CryptographyService(
			self::PRIVATE_KEY_WITH_PASSWORD_PATH,
			self::PUBLIC_KEY_PATH,
			$password
		);
	}

}
