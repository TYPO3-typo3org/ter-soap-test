<?php

namespace Xopn\TerFunctionalTests\Tests;

class LoginTest extends AbstractTestCase {

	public function testPing() {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\PingCommand');
		$returnCode = $command->execute();
		$this->assertSame(0, $returnCode, 'TER is running');
	}

	public function testLoginWithValidCredentials() {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\LoginCommand');
		$returnCode = $command->execute($this->getCredentials('alice'));
		$this->assertSame(0, $returnCode, 'Alice can login with valid credentials');
	}

	public function testLoginWithInvalidCredentials() {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\LoginCommand');
		$aliceCredentials = $this->getCredentials('alice');
		$aliceCredentials['--password'] = 'not-really-alices-password';

		$returnCode = $command->execute($aliceCredentials);
		$this->assertNotSame(0, $returnCode, 'Alice can not login with a bad password');
	}
}