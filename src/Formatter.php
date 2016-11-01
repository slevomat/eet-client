<?php declare(strict_types = 1);

namespace SlevomatEET;

class Formatter
{

	public static function formatDateTime(\DateTimeImmutable $value): string
	{
		return $value->format('c');
	}

	/**
	 * @param int|null $price
	 * @return string|null
	 */
	public static function formatAmount(int $price = null)
	{
		return $price === null ? null : number_format($price / 100, 2, '.', '');
	}

}
