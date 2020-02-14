<?php declare(strict_types = 1);

namespace SlevomatEET;

use DateTimeImmutable;

class Formatter
{

	public static function formatDateTime(DateTimeImmutable $value): string
	{
		return $value->format('c');
	}

	public static function formatAmount(?int $price = null): ?string
	{
		return $price === null ? null : number_format($price / 100, 2, '.', '');
	}

}
