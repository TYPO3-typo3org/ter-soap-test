<?php

namespace Xopn\TerFunctionalTests\Tests;

use etobi\extensionUtils\T3oSoap\Exception\ExtensionKeyNotValidException;

class DeleteKeyTest extends AbstractTestCase {

	public function testDeleteKey() {
		$extensionKey = $this->getSomeExtensionKey();
		$username = 'alice';

		$this->registerKeyOrFail($extensionKey, $username);

		$this->assertTrue(
			$this->deleteKey($extensionKey, $username),
			'Alice can delete her own unused key'
		);
	}

	public function testKeyCanNotBeDeletedByUnauthorizedUsers() {
		$this->setExpectedException('\\etobi\\extensionUtils\\T3oSoap\\Exception\\AccessDeniedException');

		$extensionKey = $this->getSomeExtensionKey();

		$this->registerKeyOrFail($extensionKey, 'alice');

		$this->deleteKey($extensionKey, 'eve');

		// clean up
		$this->tryToDeleteExtensionKey($extensionKey, 'alice');
	}

	public function testDeleteOfUnregisteredKeyFails() {
		$this->setExpectedException('\\etobi\\extensionUtils\\T3oSoap\\Exception\\ExtensionKeyNotExistsException');

		$extensionKey = $this->getSomeExtensionKey();

		$this->deleteKey($extensionKey, 'alice');
	}

	public function testExtensionKeyWithVersionsCanNotBeDeleted() {
		$extensionKey = $this->getSomeExtensionKey();
		$username = 'alice';
		$this->registerKeyOrFail($extensionKey, $username);
		$this->uploadExtensionForKeyOrFail($extensionKey, $username);

		$this->setExpectedException('\\etobi\\extensionUtils\\T3oSoap\\Exception\\ExtensionKeyHasUploadsException');
		$this->deleteKey($extensionKey, $username);
	}
}