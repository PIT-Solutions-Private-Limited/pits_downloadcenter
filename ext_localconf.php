<?php

declare(strict_types=1);

use PITS\PitsDownloadcenter\Controller\DownloadController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

// 3rd param: all actions (cached + uncached); 4th param: non-cacheable actions.
// 'show' is the AJAX JSON endpoint and 'forceDownload' streams a file — both must be uncached.
ExtensionUtility::configurePlugin(
    'PitsDownloadcenter',
    'Pitsdownloadcenter',
    [DownloadController::class => 'list, show, forceDownload'],
    [DownloadController::class => 'show, forceDownload'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
$queryparams = ['category','keyword_search','file_types','cPage'];
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = array_merge_recursive($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'],$queryparams);
