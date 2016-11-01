<?php declare(strict_types = 1);

namespace SlevomatEET\Type;

class InvalidEnumValueException extends \InvalidArgumentException
{

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var mixed[]
	 */
	private $availableValues;

	/**
	 * @param mixed $value
	 * @param mixed[] $availableValues
	 */
	public function __construct($value, array $availableValues)
	{
		parent::__construct(sprintf(
			'Invalid enum value \'%s\' (%s). Available values: %s',
			$value,
			is_object($value) ? get_class($value) : gettype($value),
			implode(', ', $availableValues)
		));

		$this->value = $value;
		$this->availableValues = $availableValues;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return mixed[]
	 */
	public function getAvailableValues(): array
	{
		return $this->availableValues;
	}

}
