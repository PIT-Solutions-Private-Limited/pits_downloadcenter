<?php

declare(strict_types=1);

/***************************************************************************
 * Extension Manager/Repository config file for ext "pits_downloadcenter".
 *********** ***************************************************************/
$EM_CONF[$_EXTKEY] = array (
	'title' => 'Download Center',
	'description' => 'Download Center Sponsored by TNT-Graphics AG',
	'category' => 'plugin',
	'version' => '6.0.0',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'author' => 'PITS Team',
	'author_email' => 'sruthi.kg@pitsolutions.com',
	'author_company' => NULL,
	'constraints' => array (
		'depends' =>
		array (
			'typo3' => '13.0.0-13.4.99',
		),
		'conflicts' => array (),
		'suggests' => array (),
	),
);

