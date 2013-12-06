<?php

namespace Xopn\TerFunctionalTests\Tests;

class PingTest extends AbstractTestCase {

	/**
	 * @group production
	 */
	public function testPing() {
		/** @var \etobi\extensionUtils\T3oSoap\PingRequest $requestObject */
		$requestObject = $this->getRequestObject('\\etobi\\extensionUtils\\T3oSoap\\PingRequest');

		$this->assertTrue(
			$requestObject->isApiWorking(),
			'TER is running'
		);
	}

}