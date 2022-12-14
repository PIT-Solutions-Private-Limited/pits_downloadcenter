<?php
use PITS\PitsDownloadcenter\Controller\DownloadController;

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PITS.pitsDownloadcenter',
	'Pitsdownloadcenter',
	array(
		DownloadController::class => 'list, show , forceDownload '
		
	),
	// non-cacheable actions
	array(
		
	)
);
