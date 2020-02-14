<?php declare(strict_types = 1);

namespace SlevomatEET\Cryptography;

use const OPENSSL_ALGO_SHA1;

function openssl_sign($data, &$signature, $priv_key_id, $signature_alg = OPENSSL_ALGO_SHA1)
{
	return false;
}

function openssl_verify($data, $signature, $pub_key_id, $signature_alg = OPENSSL_ALGO_SHA1)
{
	return -1;
}

function openssl_error_string()
{
	return 'error_message';
}
