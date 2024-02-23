<?php
defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'PitsDownloadcenter',
    'Pitsdownloadcenter',
    'Download Center'
);


$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['pitsdownloadcenter_pitsdownloadcenter'] = 'recursive,select_key,pages';

$pluginSignature = 'pitsdownloadcenter_pitsdownloadcenter';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:pits_downloadcenter/Configuration/Flexforms/flexform.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_file_metadata',
	'--div--;Download Manager, 
	tx_pitsdownloadcenter_domain_model_download_category, 
	tx_pitsdownloadcenter_domain_model_download_filetype,
	tx_pitsdownloadcenter_domain_model_download_translate',
	''
);
