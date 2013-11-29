<?php

$baseUrl = getenv('TERTEST_BASEURL') ?: 't3org.dev';

return array(
	'wsdlUrl' => sprintf('http://%s/wsdl/tx_ter_wsdl.php', $baseUrl),
	'extensionDownloadUrl' => sprintf('http://%s/fileadmin/ter', $baseUrl),

	'users' => array(
		'alice' => 'alice-password',
		'bob' => 'bob-password',
		'eve' => 'eve-password',
	),
	'typo3Version' => array(
		// should be dynamic
		'min' => getenv('TERTEST_TYPO3VERSION_MIN') ?: '4.5.0',
		'max' => getenv('TERTEST_TYPO3VERSION_MAX') ?: '6.1.99'
	),
);