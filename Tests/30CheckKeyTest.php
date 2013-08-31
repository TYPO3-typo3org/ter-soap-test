<?php

namespace Xopn\TerFunctionalTests\Tests;

use etobi\extensionUtils\T3oSoap\Exception\ExtensionKeyNotValidException;

class CheckKeyTest extends AbstractTestCase {

	public function testKeyRegistration() {
		$extensionKey = $this->getSomeExtensionKey();

		$this->assertTrue(
			$this->checkKey($extensionKey),
			'Key ' . $extensionKey . 'can be registered'
		);
	}

	public function testKeyWithAndWithoutUnderscoresAreConsideredIdentical() {
		$extensionKey = $this->getSomeExtensionKey();
		$username = 'alice';

		$this->registerKeyOrFail($extensionKey, $username);

		$this->assertFalse(
			$this->checkKey(str_replace('_', '', $extensionKey)),
			'Extension key can not be configured if there is a key matching with all chars except underscores'
		);

		$this->tryToDeleteExtensionKey($extensionKey, $username);
	}

	/**
	 * @dataProvider getInvalidExtensionKeys
	 */
	public function testInvalidExtensionKeys($extensionKey, $comment, $skipped = FALSE) {
		if($skipped) {
			$this->markTestSkipped('skip test for ' . $skipped);
		}

		$comment = sprintf(
			'Key "%s" is formally invalid, because %s',
			$extensionKey,
			$comment
		);

		try {
			$this->checkKey($extensionKey);
			$this->fail($comment);
		} catch (ExtensionKeyNotValidException $e) {
			$this->assertTrue(TRUE, $comment);
		}
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
			$this->assertFalse(
				$this->checkKey($extensionKey),
				$comment
			);
		} catch (ExtensionKeyNotValidException $e) {
			$this->assertTrue(TRUE, $comment);
		}
	}

	public function testRegisteredExtensionKeys() {
		$extensionKey = 'news';

		$this->assertFalse(
			$this->checkKey($extensionKey),
			sprintf(
				'Key "%s" is recognized as registered',
				$extensionKey
			)
		);
	}
}