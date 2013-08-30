<?php

return array(
	'wsdlUrl' => 'http://t3org.dev/wsdl/tx_ter_wsdl.php',
	'extensionDownloadUrl' => 'http://t3org.dev/fileadmin/ter',

	'users' => array(
		'alice' => 'alice-password',
		'bob' => 'bob-password',
		'eve' => 'eve-password',
	),
	'typo3Version' => array(
		// should be dynamic
		'min' => '4.5.0',
		'max' => '6.1.99'
	),
);