<?php

/***************************************************************************
 * Extension Manager/Repository config file for ext "pits_downloadcenter".
 *********** ***************************************************************/
$EM_CONF[$_EXTKEY] = [
	'title' => 'Download Center',
	'description' => 'Frontend download center that lists FAL file collections in a fast Angular-based interface with keyword search, hierarchical category and file type filters, sorting and pagination. Editors manage downloadable documents with categories and thumbnails; visitors filter, sort and share deep-linked file lists.',
	'category' => 'plugin',
	'version' => '7.0.1',
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

