<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'PITS.PitsDownloadcenter',
	'Pitsdownloadcenter',
	'Download Center'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('pits_downloadcenter', 'Configuration/TypoScript', 'Download Center');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pitsdownloadcenter_domain_model_category');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pitsdownloadcenter_domain_model_filetype');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_file_metadata',
	'--div--;Download Manager, 
	tx_pitsdownloadcenter_domain_model_download_category, 
	tx_pitsdownloadcenter_domain_model_download_filetype,
	tx_pitsdownloadcenter_domain_model_download_translate',
	''
);

$pluginSignature = 'pitsdownloadcenter_pitsdownloadcenter';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:pits_downloadcenter/Configuration/Flexforms/flexform.xml');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	'pits_downloadcenter', 'Configuration/TypoScript/Themes/Red',
	'Download Center Red Theme'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	'pits_downloadcenter', 'Configuration/TypoScript/Themes/Blue',
	'Download Center Blue Theme'
);


/**
 * ContentElementWizard for Download Center Plugin
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:pits_downloadcenter/Configuration/TSConfig/ContentElementWizard.typoscript">'
);


/**
 * Register icons
 */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'extension-downloadcenter-main',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:pits_downloadcenter/Resources/Public/Icons/download-center.svg']
);
