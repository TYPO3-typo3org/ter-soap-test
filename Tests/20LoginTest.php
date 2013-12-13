<?php

namespace Xopn\TerFunctionalTests\Tests;

class LoginTest extends AbstractTestCase {

	/**
	 * @group production
	 */
	public function testLoginWithInvalidCredentials() {
		/** @var \etobi\extensionUtils\T3oSoap\LoginRequest $requestObject */
		$requestObject = $this->getRequestObject('\\etobi\\extensionUtils\\T3oSoap\\LoginRequest');

		$requestObject->setCredentials(
			$this->getRealUsername('alice'),
			'not-really-alices-password'
		);

		$this->assertFalse(
			$requestObject->checkCredentials(),
			'Alice can not login with a bad password'
		);
	}

	/**
	 * test that login with valid credentials
	 *
	 * also do a selfcheck, to make sure all our testusers are allowed to login with their credentials
	 *
	 * @dataProvider getTestUsers
	 * @param $username
	 */
	public function testLoginWithValidCredentials($username) {
		/** @var \etobi\extensionUtils\T3oSoap\LoginRequest $requestObject */
		$requestObject = $this->getRequestObject('\\etobi\\extensionUtils\\T3oSoap\\LoginRequest');

		$requestObject->setCredentials(
			$this->getRealUsername($username),
			$this->getUserPassword($username)
		);

		$this->assertTrue(
			$requestObject->checkCredentials(),
			sprintf(
				'%s can login with password "%s"',
				$username,
				$this->getUserPassword($username)
			)
		);
	}

	public function getTestUsers() {
		return array(
			array('alice'),
			array('bob'),
			array('eve'),
			array('reviewer'),
			array('admin'),
		);
	}
}