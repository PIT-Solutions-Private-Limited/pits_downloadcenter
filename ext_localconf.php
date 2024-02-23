<?php

declare(strict_types=1);

use PITS\PitsDownloadcenter\Controller\DownloadController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
	'PitsDownloadcenter',
	'Pitsdownloadcenter',
	[DownloadController::class => 'list, show , forceDownload'],
	[]
);
$queryparams = ['category','keyword_search','file_types','cPage'];
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = array_merge_recursive($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'],$queryparams);
