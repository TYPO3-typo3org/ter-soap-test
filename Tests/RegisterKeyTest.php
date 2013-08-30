<?php

namespace Xopn\TerFunctionalTests\Tests;

class RegisterKeyTest extends AbstractTestCase {

	/**
	 * run the whole registration process for extension keys
	 */
	public function testRegisterKey() {
		$extensionKey = $this->getSomeExtensionKey();

		// step 1: make sure key is not registered
		$commandTester = $this->checkKey($extensionKey, 'alice');
		$this->assertSame(0, $commandTester->getReturnCode(), 'Key can be registered (' . $commandTester->getDisplay() . ')');

		// step 2: register the key on alice
		$commandTester = $this->registerKey($extensionKey, 'alice');
		$this->assertSame(0, $commandTester->getReturnCode(), 'Key has been successfully registered (' . $commandTester->getDisplay() . ')');

		// step 3: check that the key is registered
		$commandTester = $this->checkKey($extensionKey, 'alice');
		$this->assertSame(1, $commandTester->getReturnCode(), 'Key is no longer available for registration (' . $commandTester->getDisplay() . ')');

		// step 4: try to register the key on eve
		$commandTester = $this->registerKey($extensionKey, 'eve');
		$this->assertNotSame(0, $commandTester->getReturnCode(), 'Cannot register a key that is already registered (' . $commandTester->getDisplay() . ')');

		// step 5: check that eve can not unregister alice's key
		$commandTester = $this->deleteKey($extensionKey, 'eve');
		$this->assertNotSame(0, $commandTester->getReturnCode(), 'Cannot unregister a key that you do not own (' . $commandTester->getDisplay() . ')');

		// step 6: unregister key without uploads
		$commandTester = $this->deleteKey($extensionKey, 'alice');
		$this->assertSame(0, $commandTester->getReturnCode(), 'Keys without uploaded versions can be unregistered by the owner (' . $commandTester->getDisplay() . ')');

		// step 7: make sure key is not registered
		$commandTester = $this->checkKey($extensionKey, 'bob');
		$this->assertSame(0, $commandTester->getReturnCode(), 'Someone else can register an unregistered key (' . $commandTester->getDisplay() . ')');
	}
}