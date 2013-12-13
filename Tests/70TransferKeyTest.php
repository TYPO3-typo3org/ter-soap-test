<?php

namespace Xopn\TerFunctionalTests\Tests;

class TransferKeyTest extends AbstractTestCase {

	/**
	 * check that extension key can be transfered
	 */
	public function testTransferKey() {
		$extensionKey = $this->getSomeExtensionKey();

		$this->registerKeyOrFail($extensionKey, 'alice');

		$this->assertTrue(
			$this->transferKey($extensionKey, 'alice', 'bob'),
			'alice can transfer ' . $extensionKey . ' to bob'
		);

		$this->assertTrue(
			$this->deleteKey($extensionKey, 'bob'),
			'bob can delete the key assigned to him'
		);
	}

	/**
	 * test that no unauthorized user can transfer a key
	 */
	public function testTransferKeyCantBeTransferedByNonOwner() {
		$extensionKey = $this->getSomeExtensionKey();

		$this->registerKeyOrFail($extensionKey, 'alice');

		$this->setExpectedException('\\etobi\\extensionUtils\\T3oSoap\\Exception\\AccessDeniedException');
		$this->transferKey($extensionKey, 'eve', 'bob');
	}

	public function testTransferKeyCanBeTransferedByAdmin() {
		$extensionKey = $this->getSomeExtensionKey();

		$this->registerKeyOrFail($extensionKey, 'alice');

		$this->assertTrue(
			$this->transferKey($extensionKey, 'admin', 'bob'),
			'admin can transfer ' . $extensionKey . ' to bob'
		);

		$this->assertTrue(
			$this->deleteKey($extensionKey, 'bob'),
			'bob can delete the key assigned to him'
		);
	}
}