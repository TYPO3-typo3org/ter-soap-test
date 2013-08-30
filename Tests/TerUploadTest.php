<?php

namespace Xopn\TerFunctionalTests\Tests;

use etobi\extensionUtils\Service\EmConf;
use etobi\extensionUtils\Service\Extension;

class TerUploadTest extends AbstractTestCase {

	/**
	 * @var array
	 */
	protected $createdFolders = array();

	protected function setUp() {
		// register an extension key

		$this->extensionKey = $this->getSomeExtensionKey();
		$commandTester = $this->registerKey($this->extensionKey, 'alice');
		$this->assertSame(0, $commandTester->getReturnCode(), 'extension key could be registered');
	}

	public function testBasic() {
		$extensionFolder = $this->createExtension();
		$extensionKey = $this->getSomeExtensionKey();
		$extensionVersion = $this->getNextVersion();

		$commandTester = $this->registerKey($extensionKey, 'alice');
		if($commandTester->getReturnCode() != 0) {
			throw new \RuntimeException('could not register extension key: ' . $commandTester->getDisplay());
		}

		// set TYPO3 version in the file
		$this->setDependingTypo3Version(
			$extensionFolder,
			$this->getConfiguration('typo3Version.min') . '-' . $this->getConfiguration('typo3Version.max')
		);

		// set version of the extension
		$emConf = new EmConf($extensionFolder . DIRECTORY_SEPARATOR . 'ext_emconf.php');
		$emConf->setVersion($extensionVersion);
		$emConf->writeFile();

		// upload the extension
		$commandTester = $this->uploadExtension($extensionKey, $extensionFolder, 'alice');
		$this->assertSame(0, $commandTester->getReturnCode(), 'upload of extension does not fail (' . $commandTester->getDisplay() . ')');

		// check the extension can be downloaded
		$downloadUri = $this->getExtensionService()->getDownloadUri($extensionKey, $extensionVersion);
		$statusCode = $this->queryStatusCode($downloadUri);
		$this->assertEquals(200, $statusCode, 'extension can be downloaded from ' . $downloadUri);
	}



}