<?php

namespace Xopn\TerFunctionalTests\Tests;

class LoginTest extends AbstractTestCase {

	/**
	 * test that the SOAP-API can be pinged
	 *
	 * @test
	 */
	public function testPing() {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\PingCommand');
		$returnCode = $command->execute();
		$this->assertSame(0, $returnCode, 'TER is running');
	}
}