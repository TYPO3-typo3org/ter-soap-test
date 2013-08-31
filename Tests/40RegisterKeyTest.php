<?php

namespace Xopn\TerFunctionalTests\Tests;

use etobi\extensionUtils\T3oSoap\Exception\ExtensionKeyNotValidException;

class RegisterKeyTest extends AbstractTestCase {

	public function testRegisterKey() {
		$extensionKey = $this->getSomeExtensionKey();
		$username = 'alice';

		// should not throw exception
		$this->registerKey($extensionKey, $username);

		$this->tryToDeleteExtensionKey($extensionKey, $username);
	}


	public function testDuplicateRegistrationFails() {
		$extensionKey = $this->getSomeExtensionKey();
		$username = 'alice';

		// should not throw exception
		$this->registerKey($extensionKey, $username);

		$this->setExpectedException('\\etobi\\extensionUtils\\T3oSoap\\Exception\\ExtensionKeyAlreadyExistsException');
		$this->registerKey($extensionKey, $username);
	}

	/**
	 * @dataProvider getInvalidExtensionKeys
	 */
	public function testRegisterInvalidKeys($extensionKey, $comment, $skipped = FALSE) {
		if($skipped) {
			$this->markTestSkipped('skip test for ' . $skipped);
		}

		$username = 'alice';
		$comment = sprintf(
			'invalid key "%s" can not be registered (because %s)',
			$extensionKey,
			$comment
		);

		try {
			$this->registerKey($extensionKey, $username);
			$this->fail($comment);
		} catch (ExtensionKeyNotValidException $e) {
			$this->assertTrue(TRUE, $comment);
		}

		$this->tryToDeleteExtensionKey($extensionKey, $username);
	}

	/**
	 * @dataProvider getCoreExtensionKeys
	 */
	public function testCoreExtensionKeys($extensionKey, $skipped = FALSE) {
		if($skipped) {
			$this->markTestSkipped('skip test for ' . $skipped);
		}

		$comment = sprintf(
			'Core Extension Key "%s" is not available for registration',
			$extensionKey
		);

		try {
			$this->setExpectedException('\\etobi\\extensionUtils\\T3oSoap\\Exception\\ExtensionKeyAlreadyExistsException');

			$this->registerKey($extensionKey, 'alice');
		} catch (ExtensionKeyNotValidException $e) {
			$this->assertTrue(TRUE, $comment);
		}
	}

	/**
	 * only test a few extensions to check that the logic is implemented
	 *
	 * @return array
	 */
	public function getInvalidExtensionKeys()
	{
		return array_slice(parent::getInvalidExtensionKeys(), 0, 3);
	}

	/**
	 * only test a few extensions to check that the logic is implemented
	 *
	 * @return array
	 */
	public function getCoreExtensionKeys()
	{
		return array_slice(parent::getCoreExtensionKeys(), 0, 3);
	}


}