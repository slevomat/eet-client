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
			[new EvidenceEnvironment(EvidenceEnvironment::PLAYGROUND), 'EETServiceSOAP_playground.wsdl'],
			[new EvidenceEnvironment(EvidenceEnvironment::PRODUCTION), 'EETServiceSOAP_production.wsdl'],
		];
	}

}
