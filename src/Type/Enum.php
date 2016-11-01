<?php declare(strict_types = 1);

namespace SlevomatEET\Type;

abstract class Enum
{

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var mixed[]
	 */
	private static $availableValues;

	/**
	 * @param mixed $value
	 */
	public function __construct($value)
	{
		self::checkValue($value);
		$this->value = $value;
	}

	/**
	 * @param mixed $value
	 */
	private static function checkValue($value)
	{
		if (!self::isValidValue($value)) {
			throw new InvalidEnumValueException(
				$value,
				self::getAvailableValues()
			);
		}
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	public function equals(self $enum): bool
	{
		if (get_class($this) !== get_class($enum)) {
			throw new InvalidEnumTypeException($enum, get_class($this));
		}

		return $this->equalsValue($enum->getValue());
	}

	public function equalsValue($value): bool
	{
		self::checkValue($value);

		return $this->getValue() === $value;
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	private static function isValidValue($value): bool
	{
		return in_array($value, self::getAvailableValues(), true);
	}

	private static function getAvailableValues(): array
	{
		$index = get_called_class();
		if (!isset(self::$availableValues[$index])) {
			$classReflection = new \ReflectionClass(get_called_class());
			self::$availableValues[$index] = $classReflection->getConstants();
		}

		return self::$availableValues[$index];
	}

}
