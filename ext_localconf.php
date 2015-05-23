<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PITS.' . $_EXTKEY,
	'Pitsdownloadcenter',
	array(
		'Download' => 'list, show , forceDownload '
		
	),
	// non-cacheable actions
	array(
		'Download' => 'forceDownload',
		
	)
);
