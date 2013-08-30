<?php

namespace Xopn\TerFunctionalTests\Tester;

/**
 * Get configuration through a class
 *
 * @package Xopn\TerFunctionalTests\Tester
 */
class Config {

	protected $data;

	public function __construct($data = array()) {
		$this->data = $data;
	}

	public function get($identifier) {
		$segments = explode('.', $identifier);

		$data = $this->data;
		while($segment = array_shift($segments)) {
			if(!array_key_exists($segment, $data)) {
				throw new \InvalidArgumentException(sprintf(
					'Segment "%s" does not exist in "%s"',
					$segment,
					$identifier
				));
			}
			$data = $data[$segment];
		}

		return $data;
	}

}