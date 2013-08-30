<?php

namespace Xopn\TerFunctionalTests\Tester;

class CommandTester extends \Symfony\Component\Console\Tester\CommandTester {

	/**
	 * @var array
	 */
	protected $defaultOptions;
	/**
	 * @var array
	 */
	protected $defaultArguments;

	/**
	 * @param array $defaultArguments
	 */
	public function setDefaultArguments($defaultArguments = array()) {
		$this->defaultArguments = $defaultArguments;
	}

	/**
	 * @param array $defaultOptions
	 */
	public function setDefaultOptions($defaultOptions = array()) {
		$this->defaultOptions = $defaultOptions;
	}

	public function setDefault($arguments = array(), $options = array()) {
		$this->setDefaultArguments($arguments);
		$this->setDefaultOptions($options);
	}

	/**
	 * @param array $input
	 * @param array $options
	 * @return int
	 */
	public function execute(array $input = array(), array $options = array()) {
		$input = array_merge($this->defaultArguments, $input);
		$options = array_merge($this->defaultOptions, $options);
		return parent::execute($input, $options);
	}


}