<?php

namespace Xopn\TerFunctionalTests\Tests;

use etobi\extensionUtils\Service\EmConf;
use etobi\extensionUtils\Service\Extension;

class TerUploadTest extends AbstractTestCase {

	/**
	 * test what several set Typo3 versions do to the extension upload
	 *
	 * @dataProvider getTypo3VersionTestCases
	 *
	 * @param string   $typo3Versions  version string that should be set in ext_emconf.php
	 * @param boolean  $assertSuccess  if the test case should assume success of the upload
	 * @param string   $comment        comment for testcase
	 */
	public function testUploadWithTypo3Version($typo3Versions = NULL, $assertSuccess = TRUE, $comment = '') {
		$extensionFolder = $this->createExtension();
		$extensionKey = $this->getSomeExtensionKey();
		$extensionVersion = $this->getNextVersion();

		$this->registerKeyOrFail($extensionKey, 'alice');

		// set TYPO3 version in ext_emconf.php
		if($typo3Versions) {
			$this->setDependingTypo3Version(
				$extensionFolder,
				$typo3Versions
			);
		}

		// set version of the extension
		$emConf = new EmConf($extensionFolder . DIRECTORY_SEPARATOR . 'ext_emconf.php');
		$emConf->setVersion($extensionVersion);
		$emConf->writeFile();

		if($assertSuccess === TRUE) {
			$this->assertTrue(
				$this->uploadExtension($extensionKey, $extensionFolder, 'alice'),
				'upload accepted'
			);

			// check the extension can be downloaded
			$downloadUri = $this->getExtensionService()->getDownloadUri($extensionKey, $extensionVersion);
			$statusCode = $this->queryStatusCode($downloadUri);
			$this->assertEquals(200, $statusCode, 'extension can be downloaded from ' . $downloadUri);
		} else {
			// @TODO there is no nice interface yet for Typo3ExtensionUtils
			$this->setExpectedException('\\SoapFault');
			$this->uploadExtension($extensionKey, $extensionFolder, 'alice');
		}
	}

	public function getTypo3VersionTestCases() {
		$array = array(
			array(
				$this->getConfiguration('typo3Version.min') . '-' . $this->getConfiguration('typo3Version.max'),
				TRUE,
				'upload with allowed versions only'
			),
			array(
				'3.8.0-4.2.99',
				TRUE,
				'upload of outdated versions only is possible'
			),
			array(
				'3.8.0-' . $this->getConfiguration('typo3Version.max'),
				TRUE,
				'upload of range from outdated to actual version is possible'
			),
			array(
				'9.0.0-9.9.99',
				FALSE,
				'upload with not yet released TYPO3 versions only'
			),
			array(
				$this->getConfiguration('typo3Version.min') . '-6.3.99',
				FALSE,
				'upload with allowed and not yet released TYPO3 versions'
			),
			array(
				NULL,
				FALSE,
				'upload without TYPO3 version fails'
			),

		);

		$return = array();
		foreach($array as $value) {
			$return[$value[2]] = $value;
		}

		return $return;
	}



}