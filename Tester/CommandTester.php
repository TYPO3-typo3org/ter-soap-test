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
	protected $defaultInput;

	/**
	 * @var int
	 */
	protected $returnCode;

	/**
	 * @param array $defaultInput
	 */
	public function setDefaultInput($defaultInput = array()) {
		$this->defaultInput = $defaultInput;
	}

	/**
	 * @param array $defaultOptions
	 */
	public function setDefaultOptions($defaultOptions = array()) {
		$this->defaultOptions = $defaultOptions;
	}

	public function setDefault($arguments = array(), $options = array()) {
		$this->setDefaultInput($arguments);
		$this->setDefaultOptions($options);
	}

	/**
	 * @param array $input
	 * @param array $options
	 * @return int
	 */
	public function execute(array $input = array(), array $options = array()) {
		$input = array_merge($this->defaultInput, $input);
		$options = array_merge($this->defaultOptions, $options);

		$this->returnCode = parent::execute($input, $options);

		return $this->returnCode;
	}

	/**
	 * @return int
	 */
	public function getReturnCode() {
		return $this->returnCode;
	}




}