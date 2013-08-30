<?php

namespace Xopn\TerFunctionalTests\Tests;

use etobi\extensionUtils\Service\EmConf;
use etobi\extensionUtils\Service\Extension;
use Symfony\Component\Console\Application;
use Xopn\TerFunctionalTests\Tester\CommandTester;

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
	 * create a mock a command by class name
	 *
	 * @param string $commandClassName
	 * @param array $arguments
	 * @param array $options
	 * @return CommandTester
	 */
	protected function getCommand($commandClassName, $arguments = array(), $options = array()) {

		$command = $this->instanciateCommandClass($commandClassName);

		$application = new Application();
		$application->add($command);

		$commandTester = new CommandTester($command);

		// set mandatory arguments and options
		$defaultInput = array(
			'command' => $command->getName(),
		);
		$defaultOptions = array(
			'interactive' => FALSE,
		);
		if($command instanceof \etobi\extensionUtils\Command\Ter\AbstractTerCommand) {
			$defaultInput['--wsdl'] = $this->getConfiguration('wsdlUrl');
		}
		$commandTester->setDefaultInput($defaultInput);
		$commandTester->setDefaultOptions($defaultOptions);

		return $commandTester;
	}

	/**
	 * create and validate a command instance
	 *
	 * @param $className
	 * @return \Symfony\Component\Console\Command\Command
	 * @throws \InvalidArgumentException
	 */
	protected function instanciateCommandClass($className) {
		$command = new $className;
		if(!($command instanceof \Symfony\Component\Console\Command\Command)) {
			throw new \InvalidArgumentException(sprintf(
				'class "%s" is no Symfony Command',
				$className
			));
		}
		return $command;
	}

	/**
	 * get credentials for a user.
	 * The return can be used as argument for a command execution
	 *
	 * @param $username
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function getCredentials($username) {
		try {
			$password = $this->getConfiguration('users.' . $username);
		}
		catch(\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(sprintf(
				'The user "%s" is not configured.',
				$username
			));
		}

		return array(
			'--username' => $username,
			'--password' => $password
		);
	}

	/**
	 * execute the command to register an extension key
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @return CommandTester
	 */
	protected function registerKey($extensionKey, $username = 'alice') {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\RegisterExtensionKeyCommand');

		$arguments = $this->getCredentials($username);
		$arguments = array_merge($arguments, array(
			'extensionKey' => $extensionKey,
			'--title' => 'Test extension',
			'--description' => 'Created by an automated test running at ' . gmdate('Y-m-d H:i:s'),
		));

		$command->execute($arguments);
		return $command;
	}

	/**
	 * execute the command to unregister an extension key
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @return CommandTester
	 */
	protected function deleteKey($extensionKey, $username = 'alice') {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\DeleteExtensionKeyCommand');

		$arguments = $this->getCredentials($username);
		$arguments = array_merge($arguments, array(
			'extensionKey' => $extensionKey,
		));

		$command->execute($arguments);

		return $command;
	}

	/**
	 * execute the command to check if an extension key can be registered
	 *
	 * @param $extensionKey
	 * @param string $username
	 * @return CommandTester
	 */
	protected function checkKey($extensionKey, $username = 'alice') {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\CheckExtensionKeyCommand');

		$arguments = $this->getCredentials($username);
		$arguments = array_merge($arguments, array(
			'extensionKey' => $extensionKey,
		));

		$command->execute($arguments);
		return $command;
	}

	/**
	 * execute the command to check if an extension key can be registered
	 *
	 * @param $extensionKey
	 * @param string $path
	 * @param string $username
	 * @return CommandTester
	 */
	protected function uploadExtension($extensionKey, $path, $username = 'alice') {
		$command = $this->getCommand('etobi\\extensionUtils\\Command\\Ter\\UploadCommand');

		$arguments = $this->getCredentials($username);
		$arguments = array_merge($arguments, array(
			'extensionKey' => $extensionKey,
			'pathToExtension' => $path,
			'--comment' => 'Test upload on ' . gmdate('Y-m-d H:i:s')
		));

		$command->execute($arguments);
		return $command;
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



}