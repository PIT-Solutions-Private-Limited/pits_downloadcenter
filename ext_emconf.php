<?php

declare(strict_types=1);

/***************************************************************************
 * Extension Manager/Repository config file for ext "pits_downloadcenter".
 *********** ***************************************************************/
$EM_CONF[$_EXTKEY] = [
	'title' => 'Download Center',
	'description' => 'Download Center Sponsored by TNT-Graphics AG',
	'category' => 'plugin',
	'version' => '7.0.0',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'author' => 'PIT Solutions Ltd',
	'author_email' => 'contact@pitsolutions.com',
	'author_company' => NULL,
	'constraints' => [
		'depends' =>
		[
			'typo3' => '13.0.0-13.4.99',
		],
		'conflicts' => [],
		'suggests' => [],
	],
];

