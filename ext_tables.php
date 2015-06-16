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

/**
 * extend sys_file_metadata fields sys_file_metadata
 */
//\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('sys_file_metadata');
$tempColumns = array (
	'tx_pitsdownloadcenter_domain_model_download_filetype' => array(
		'exclude' => 1,		
		'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_filetypelabel',
		"config" => Array (
			"type" => "select",
			"foreign_table" => "tx_pitsdownloadcenter_domain_model_filetype",
			"foreign_table_where" => "AND tx_pitsdownloadcenter_domain_model_filetype.sys_language_uid IN (-1,0) ",
			"size" => 10,
			"minitems" => 0,
			"maxitems" => 100,
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"list" => Array(
					"type" => "script",
					"title" => "List",
					"icon" => "list.gif",
					"params" => Array(
						"table"=>"tx_pitsdownloadcenter_domain_model_download_filetype"
					),
					"script" => "wizard_list.php",
				),
			),
		)
	),
	'tx_pitsdownloadcenter_domain_model_download_category' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_parentcategory',
		'config' => array(
			'minitems' => 0,
			'maxitems' => 999,
			'type' => 'select',
			'renderMode' => 'tree',
			'foreign_table' => 'tx_pitsdownloadcenter_domain_model_category',
			'foreign_table_where' => ' AND tx_pitsdownloadcenter_domain_model_category.sys_language_uid IN (-1,0) ORDER BY tx_pitsdownloadcenter_domain_model_category.sorting ASC',
			//'MM' =>'tx_pitsdownloadcenter_domain_model_categoryrecordmm',
			'treeConfig' => array(
				'parentField' => 'parentcategory',
				'appearance' => array(
					'expandAll' => TRUE,
					'showHeader' => TRUE,
					'maxLevels' => 99,
				)
			)
		)
	),
	'tx_pitsdownloadcenter_domain_model_download_translate' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_translatedfile',
		'config' => array(
			'type' => 'group',
			'internal_type' => 'file',
			'allowed' => 'jpeg,jpg,doc,docx,pdf',
			'size' => '5',
			'maxitems' => '1', 
			'minitems' => '0',
			'foreign_table' => 'sys_file',
			'foreign_table_where' => ' AND sys_file_metadata.sys_language_uid = sys_file.sys_language_uid ',
			'show_thumbs' => '1',
			'wizards' => array(
				'suggest' => array(
						'type' => 'suggest',
				),
			),
		),
	)
		
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'sys_file_metadata',
	$tempColumns,
	1
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
t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/Flexforms/flexform.xml');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	$_EXTKEY, 'Configuration/TypoScript/Themes/Red',
	'Download Center Red Theme'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	$_EXTKEY, 'Configuration/TypoScript/Themes/Blue',
	'Download Center Blue Theme'
);
