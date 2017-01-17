<?php declare(strict_types = 1);

namespace SlevomatEET;

class EvidenceEnvironmentTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @dataProvider dataTestGetWsdlPath
	 */
	public function testGetWsdlPath(EvidenceEnvironment $environment, string $expectedFileName)
	{
		$this->assertSame($expectedFileName, basename($environment->getWsdlPath()));
	}

	public function dataTestGetWsdlPath(): array
	{
		return [
			[EvidenceEnvironment::get(EvidenceEnvironment::PLAYGROUND), 'EETServiceSOAP_playground.wsdl'],
			[EvidenceEnvironment::get(EvidenceEnvironment::PRODUCTION), 'EETServiceSOAP_production.wsdl'],
		];
	}

}
