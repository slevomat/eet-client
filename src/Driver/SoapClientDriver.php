<?php declare(strict_types = 1);

namespace SlevomatEET\Driver;

interface SoapClientDriver
{

	public function send(string $request, string $location, string $action, int $soapVersion): string;

}
