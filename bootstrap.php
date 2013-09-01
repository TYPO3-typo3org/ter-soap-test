<?php

define('EXTENSION_UTILS_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Typo3ExtensionUtils'));

// autoloader for Typo3ExtensionUtils
require_once(EXTENSION_UTILS_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php');

// poor men's autoloader
$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR ;
require_once($basePath . 'Utility' . DIRECTORY_SEPARATOR . 'Config.php');
require_once($basePath . 'Tests' . DIRECTORY_SEPARATOR . 'AbstractTestCase.php');

// load configuration
$config = include('config.php');
if(empty($config)) {
	throw new \InvalidArgumentException('config.php is empty. That\'s probably not what you want.');
}
$GLOBALS['Config'] = new \Xopn\TerFunctionalTests\Tester\Config($config);