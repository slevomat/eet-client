<?php declare(strict_types = 1);

namespace SlevomatEET;

use Consistence\Enum\Enum;

class EvidenceEnvironment extends Enum
{

	public const PLAYGROUND = 'playground';
	public const PRODUCTION = 'production';

	public function getWsdlPath(): string
	{
		if ($this->equalsValue(self::PRODUCTION)) {
			return __DIR__ . '/../wsdl/EETServiceSOAP_production.wsdl';
		}
		return __DIR__ . '/../wsdl/EETServiceSOAP_playground.wsdl';
	}

}
