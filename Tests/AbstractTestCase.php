<?php

namespace Xopn\TerFunctionalTests\Tests;

use Symfony\Component\Console\Application;
use Xopn\TerFunctionalTests\Tester\CommandTester;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase {

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
		$commandTester->setDefaultArguments(array('command' => $command->getName()));
		if($command instanceof \etobi\extensionUtils\Command\Ter\AbstractTerCommand) {
			$commandTester->setDefaultOptions(array('wsdl' => $this->getConfiguration('wsdlUrl')));
		}

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

}