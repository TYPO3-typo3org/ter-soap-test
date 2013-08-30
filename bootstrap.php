<?php

define('EXTENSION_UTILS_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Typo3ExtensionUtils'));

// autoloader
require_once(EXTENSION_UTILS_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php');

// poor men's autoloader
$classPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Tester' . DIRECTORY_SEPARATOR;
require_once($classPath . 'CommandTester.php');