<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use DOMDocument;
use RobRichards\WsePhp\WSSESoap;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use const OPENSSL_ALGO_SHA256;
use const PHP_VERSION_ID;
use function is_file;

class CryptographyService
{

	/** @var string */
	private $privateKeyFile;

	/** @var string */
	private $privateKeyPassword;

	/** @var string */
	private $publicKeyFile;

	/** @var bool */
	private $publicKeyVerified;

	public function __construct(string $privateKeyFile, string $publicKeyFile, string $privateKeyPassword = '')
	{
		if (!is_file($privateKeyFile)) {
			throw new PrivateKeyFileNotFoundException($privateKeyFile);
		}
		if (!is_file($publicKeyFile)) {
			throw new PublicKeyFileNotFoundException($publicKeyFile);
		}
		$this->privateKeyFile = $privateKeyFile;
		$this->publicKeyFile = $publicKeyFile;
		$this->privateKeyPassword = $privateKeyPassword;
	}

	/**
	 * @param mixed[] $body
	 * @return string
	 */
	public function getPkpCode(array $body): string
	{
		$values = [
			$body['dic_popl'],
			$body['id_provoz'],
			$body['id_pokl'],
			$body['porad_cis'],
			$body['dat_trzby'],
			$body['celk_trzba'],
		];

		$plaintext = implode('|', $values);

		$privateKey = (string) file_get_contents($this->privateKeyFile);
		$privateKeyId = openssl_pkey_get_private($privateKey, $this->privateKeyPassword);
		if ($privateKeyId === false) {
			throw new PrivateKeyFileException($this->privateKeyFile);
		}

		$ok = openssl_sign($plaintext, $signature, $privateKeyId, OPENSSL_ALGO_SHA256);
		if (!$ok) {
			throw new SigningFailedException($values);
		}
		if (PHP_VERSION_ID < 80000) {
			// phpcs:ignore Generic.PHP.DeprecatedFunctions
			openssl_free_key($privateKeyId);
		}

		return $signature;
	}

	public function getBkpCode(string $pkpCode): string
	{
		$bkp = strtoupper(sha1($pkpCode));

		return implode('-', str_split($bkp, 8));
	}

	public function addWSESignature(string $request): string
	{
		$publicKey = (string) file_get_contents($this->publicKeyFile);
		$this->verifyPublicKey($publicKey);
		$securityKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
		$document = new DOMDocument('1.0');
		$document->loadXML($request);
		$wse = new WSSESoap($document);
		$securityKey->passphrase = $this->privateKeyPassword;
		$securityKey->loadKey($this->privateKeyFile, true);
		$wse->addTimestamp();
		$wse->signSoapDoc($securityKey, ['algorithm' => XMLSecurityDSig::SHA256]);
		$binaryToken = $wse->addBinaryToken($publicKey);
		$wse->attachTokentoSig($binaryToken);

		return $wse->saveXML();
	}

	private function verifyPublicKey(string $fileContents): void
	{
		if ($this->publicKeyVerified) {
			return;
		}
		$publicKeyResource = openssl_get_publickey($fileContents);
		if ($publicKeyResource === false) {
			throw new InvalidPublicKeyException($this->publicKeyFile, (string) openssl_error_string());
		}
		if (PHP_VERSION_ID < 80000) {
			// phpcs:ignore Generic.PHP.DeprecatedFunctions
			openssl_free_key($publicKeyResource);
		}
		$this->publicKeyVerified = true;
	}

}
