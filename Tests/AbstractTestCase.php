<?php

namespace Xopn\TerFunctionalTests\Tests;

use etobi\extensionUtils\Service\EmConf;
use etobi\extensionUtils\Service\Extension;
use etobi\extensionUtils\ter\TerUpload;
use Symfony\Component\Console\Application;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase {

	protected function tearDown() {
		$this->removeCreatedFolders();
	}

	/**
	 * @var int
	 */
	protected $bugfixVersion = 1;

	/**
	 * get a configuration from config.php
	 *
	 * @param string $identifier
	 * @return mixed
	 */
	protected function getConfiguration($identifier) {
		return $GLOBALS['Config']->get($identifier);
	}

	/**
	 * poor man's Dependency Injection
	 *
	 * @param $className
	 * @throws \InvalidArgumentException
	 * @return \etobi\extensionUtils\T3oSoap\AbstractRequest
	 */
	public function getRequestObject($className) {
		if(!class_exists($className)) {
			throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $className));
		}
		$object = new $className();
		if(!($object instanceof \etobi\extensionUtils\T3oSoap\AbstractRequest)) {
			throw new \InvalidArgumentException(sprintf('expected class %s to be a \\etobi\\extensionUtils\\T3oSoap\\AbstractRequest, but it is not', $className));
		}

		if($this->getConfiguration('wsdlUrl')) {
			$object->setWsdlURL($this->getConfiguration('wsdlUrl'));
		}

		return $object;
	}

	protected function getRealUsername($username) {
		return 'autotest-' . $username;
	}

	/**
	 * @param $username
	 * @return string
	 */
	public function getUserPassword($username) {
		return $this->getConfiguration('users.' . $username);
	}

	/**
	 * execute the command to register an extension key
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @return boolean
	 */
	protected function registerKey($extensionKey, $username = 'alice') {
		/** @var \etobi\extensionUtils\T3oSoap\RegisterExtensionKeyRequest $requestObject */
		$requestObject = $this->getRequestObject('\\etobi\\extensionUtils\\T3oSoap\\RegisterExtensionKeyRequest');

		$requestObject->setCredentials(
			$this->getRealUsername($username),
			$this->getUserPassword($username)
		);

		return $requestObject->registerExtensionKey(
			$extensionKey,
			'Test extension',
			'Created by an automated test running at ' . gmdate('Y-m-d H:i:s')
		);
	}

	/**
	 * try to register an extension key and make the testcase fail if registration fails
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @return bool
	 */
	protected function registerKeyOrFail($extensionKey, $username = 'alice') {
		$returnValue = $this->registerKey($extensionKey, $username);
		if(!$returnValue) {
			$this->fail(sprintf(
				'Extension %s could not be registered for %s',
				$extensionKey,
				$username
			));
		}
		return $returnValue;
	}

	/**
	 * execute the command to transfer an extension key
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @param string $targetUsername
	 * @return boolean
	 */
	protected function transferKey($extensionKey, $username = 'alice', $targetUsername = 'bob') {
		/** @var \etobi\extensionUtils\T3oSoap\TransferExtensionKeyRequest $requestObject */
		$requestObject = $this->getRequestObject('\\etobi\\extensionUtils\\T3oSoap\\TransferExtensionKeyRequest');

		$requestObject->setCredentials(
			$this->getRealUsername($username),
			$this->getUserPassword($username)
		);

		return $requestObject->transferExtensionKey(
			$extensionKey,
			$this->getRealUsername($targetUsername)
		);
	}

	/**
	 * execute the command to unregister an extension key
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @return boolean
	 */
	protected function deleteKey($extensionKey, $username = 'alice') {
		/** @var \etobi\extensionUtils\T3oSoap\DeleteExtensionKeyRequest $requestObject */
		$requestObject = $this->getRequestObject('\\etobi\\extensionUtils\\T3oSoap\\DeleteExtensionKeyRequest');

		$requestObject->setCredentials(
			$this->getRealUsername($username),
			$this->getUserPassword($username)
		);

		return $requestObject->deleteExtensionKey(
			$extensionKey
		);
	}

	/**
	 * execute the command to check if an extension key can be registered
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @return boolean
	 */
	protected function checkKey($extensionKey, $username = 'alice') {
		/** @var \etobi\extensionUtils\T3oSoap\CheckExtensionKeyRequest $requestObject */
		$requestObject = $this->getRequestObject('\\etobi\\extensionUtils\\T3oSoap\\CheckExtensionKeyRequest');

		$requestObject->setCredentials(
			$this->getRealUsername($username),
			$this->getUserPassword($username)
		);

		return $requestObject->checkExtensionKey($extensionKey);
	}

	/**
	 * execute the command to check if an extension key can be registered
	 *
	 * @param $extensionKey
	 * @param string $path
	 * @param string $username
	 * @return array
	 */
	protected function uploadExtension($extensionKey, $path, $username = 'alice') {

		$upload = new TerUpload();

		$upload->setExtensionKey($extensionKey)
			->setWsdlURL($this->getConfiguration('wsdlUrl'))
			->setUsername($this->getRealUsername($username))
			->setPassword($this->getUserPassword($username))
			->setUploadComment('Test upload on ' . gmdate('Y-m-d H:i:s'))
			->setPath($path)
		;

		$response = $upload->execute();

		// we assume success when result messages are set
		return array_key_exists('resultMessages', $response) && !empty($response['resultMessages']);
	}

	protected function uploadExtensionForKeyOrFail($extensionKey, $username = 'alice') {

		$extensionFolder = $this->createExtension();
		$extensionVersion = $this->getNextVersion();

		// set TYPO3 version in ext_emconf.php
		$this->setDependingTypo3Version(
			$extensionFolder,
			$this->getConfiguration('typo3Version.min') . '-' . $this->getConfiguration('typo3Version.max')
		);

		// set version of the extension
		$emConf = new EmConf($extensionFolder . DIRECTORY_SEPARATOR . 'ext_emconf.php');
		$emConf->setVersion($extensionVersion);
		$emConf->writeFile();

		$return = $this->uploadExtension($extensionKey, $extensionFolder, $username);
		if(!$return) {
			$this->fail('Extension ' . $extensionKey . ' could not be uploaded.');
		}
	}


	protected $extensionKey = NULL;

	protected function getSomeExtensionKey() {
		return 'test_' . gmdate('YmdHis') . '_' . rand(100000,999999);
	}

	/**
	 * @var Extension
	 */
	protected $extensionService = NULL;

	/**
	 * @return Extension
	 */
	protected function getExtensionService() {
		if(is_null($this->extensionService)) {
			$this->extensionService = new Extension($this->getConfiguration('extensionDownloadUrl'));
		}
		return $this->extensionService;
	}

	/**
	 * get status code for querying an uri
	 *
	 * @param $uri
	 * @return null
	 */
	protected function queryStatusCode($uri) {
		$headers = get_headers($uri, TRUE);
		$statusCode = NULL;

		preg_match('/\d{3}/', $headers[0], $statusCode);

		return empty($statusCode) ? NULL : $statusCode[0];
	}


	/**
	 * an array of folder paths that have been created as temporary folders for testing
	 *
	 * this will be removed in tearDown()
	 *
	 * @var array
	 */
	protected $createdFolders = array();

	/**
	 * copy the default extension to a temporary folder so that modifications can be made.
	 *
	 * The temporary folder will be automatically deleted at the end of the test case
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function createExtension() {
		$source = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'test_extension';
		$target = tempnam(sys_get_temp_dir(), 'terUploadTest');
		unlink($target);

		// copy extension to temp folder
		$cmd = sprintf(
			'%s -r %s %s',
			'cp',
			escapeshellarg($source),
			escapeshellarg($target)
		);
		system($cmd);
		if(!is_dir($target)) {
			throw new \RuntimeException(sprintf(
				'Whoopsi. Could not create folder %s',
				$target
			));
		}
		$this->createdFolders[] = $target;
		return $target;
	}

	/**
	 * remove all folders that have been created for testing purposes
	 */
	protected function removeCreatedFolders() {

		// remove all created folders
		foreach($this->createdFolders as $folder) {
			$cmd = sprintf(
				'%s -rf %s',
				'rm',
				escapeshellarg($folder)
			);
			shell_exec($cmd);
		}
		$this->createdFolders = array();
	}

	protected function getNextVersion() {
		$version = '0.0.' . $this->bugfixVersion;
		$this->bugfixVersion++;
		return $version;
	}

	/**
	 * set a dependency on the TYPO3 version in ext_emconf.php
	 *
	 * @param $path
	 * @param $versionString
	 */
	protected function setDependingTypo3Version($path, $versionString) {
		$emConf = new EmConf($path . DIRECTORY_SEPARATOR . 'ext_emconf.php');
		$emConf['constraints'] = array(
			'depends' => array(
				'typo3' => $versionString,
			),
		);
		$emConf->writeFile();
	}

	public function getInvalidExtensionKeys() {
		$data = array(
			// disallowed patterns
			array('ab', 'two-letter extension keys'),
			array('extension_key_that_is_too_long_to_be_registered_in_ter', 'there is an upper limit for key length'),
			array('_foobar', 'underscore as first char is disallowed'),
			array('42foobar', 'number as first char is disallowed'),
			array('foobar_', 'underscore as last char is disallowed'),
			array('foobar42', 'number as last char is disallowed', TRUE),

			// allowed chars
			array('foo bar', 'spaces are disallowed'),
			array('FOOBAR', 'uppercase disallowed'),
			array('føøbar', 'non-ascii is disallowed'),
			array('f##bar', 'symbols are disallowed'),
			array('foo__bar', 'double underscore is disallowed', TRUE),

			// disallowed prefixes
			array('txfoobar', 'prefix "tx" disallowed'),
			array('user_foobar', 'prefix "user_" disallowed'),
			array('pagesfoobar', 'prefix "pages" disallowed'),
			array('tt_foobar', 'prefix "tt_" disallowed'),
			array('sys_foobar', 'prefix "sys_" disallowed'),
			array('ts_language_foobar', 'prefix "ts_language_" disallowed'),
			array('csh_foobar', 'prefix "csh_" disallowed'),
		);

		$return = array();
		foreach($data as $dat) {
			$return[$dat[1]] = $dat;
		}

		return $return;
	}

	public function getCoreExtensionKeys() {
		return array(
			array('typo3'),
			array('about'),
			array('aboutmodules'),
			array('adodb'),
			array('backend'),
			array('belog'),
			array('beuser'),
			array('cms'),
			array('context_help'),
			array('core'),
			array('cshmanual'),
			array('css_styled_content'),
			array('dbal'),
			array('documentation'),
			array('em'),
			array('extbase'),
			array('extensionmanager'),
			array('extra_page_cm_options'),
			array('feedit'),
			array('felogin'),
			array('filelist'),
			array('fluid'),
			array('form'),
			array('frontend'),
			array('func'),
			array('func_wizards'),
			array('impexp'),
			array('indexed_search'),
			array('indexed_search_mysql'),
			array('info'),
			array('info_pagetsconfig'),
			array('install'),
			array('lang'),
			array('linkvalidator'),
			array('lowlevel'),
			array('opendocs'),
			array('openid'),
			array('perm'),
			array('recordlist'),
			array('recycler'),
			array('reports'),
			array('rsaauth'),
			array('rtehtmlarea'),
			array('saltedpasswords'),
			array('scheduler'),
			array('setup'),
			array('simulatestatic'),
			array('statictemplates'),
			array('sv'),
			array('sys_action'),
			array('sys_note'),
			array('t3editor'),
			array('t3skin'),
			array('taskcenter'),
			array('tsconfig_help'),
			array('tstemplate'),
			array('tstemplate_analyzer'),
			array('tstemplate_ceditor'),
			array('tstemplate_info'),
			array('tstemplate_objbrowser'),
			array('version'),
			array('viewpage'),
			array('wizard_crpages'),
			array('wizard_sortpages'),
			array('workspaces'),
		);
	}


	/**
	 * remove extension key, but fail silently if this fails
	 *
	 * After these tests we should try to remove the registered keys, but
	 * if it fails then this is not considered a failing test in *this* Test Case
	 *
	 * @param $username
	 * @param $extensionKey
	 */
	protected function tryToDeleteExtensionKey($extensionKey, $username) {
		try {
			$this->deleteKey($extensionKey, $username);
		} catch (\Exception $e) {
			// fail silently
		}
	}



}