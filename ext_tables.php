<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pitsdownloadcenter',
	'Download Center'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Download Center');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pitsdownloadcenter_domain_model_download_category');
$GLOBALS['TCA']['tx_pitsdownloadcenter_domain_model_category'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_category',
		'label' => 'categoryname',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'sortby'		=> 'sorting',		
		'default_sortby'	=> 'ORDER BY sorting',		
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => '',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Category.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_pitsdownloadcenter_domain_model_download.gif'
	),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pitsdownloadcenter_domain_model_download_filetype');
$GLOBALS['TCA']['tx_pitsdownloadcenter_domain_model_filetype'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_filetype',
		'label' => 'filetype',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'sortby'		=> 'sorting',		
		'default_sortby'	=> 'ORDER BY sorting',				
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'filetype',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Filetype.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_pitsdownloadcenter_domain_model_download.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_file_metadata',
	'--div--;Download Manager, 
	tx_pitsdownloadcenter_domain_model_download_category, 
	tx_pitsdownloadcenter_domain_model_download_filetype,
	tx_pitsdownloadcenter_domain_model_download_translate',
	''
);

$pluginSignature = str_replace('_','',$_EXTKEY) . '_pitsdownloadcenter';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/Flexforms/flexform.xml');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	$_EXTKEY, 'Configuration/TypoScript/Themes/Red',
	'Download Center Red Theme'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	$_EXTKEY, 'Configuration/TypoScript/Themes/Blue',
	'Download Center Blue Theme'
);
