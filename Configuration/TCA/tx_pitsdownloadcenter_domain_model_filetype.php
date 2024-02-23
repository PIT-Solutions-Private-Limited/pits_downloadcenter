<?php
defined('TYPO3') or die();
return [
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
		'security' => [
            'ignorePageTypeRestriction' => true,
        ],
		'searchFields' => 'filetype',
		'iconfile' => 'EXT:pits_downloadcenter/Resources/Public/Icons/tx_pitsdownloadcenter_domain_model_filetype.png'
	),
	//'interface' => array(
	//	'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, filetype,hidden',
	//),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, filetype,hidden;;1, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
	
		'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            // 'config' => [
            //     'type' => 'select',
            //     'renderType' => 'selectSingle',
            //     'special' => 'languages',
            //     'items' => [
            //         [
            //             'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
            //             -1,
            //             'flags-multiple'
            //         ]
            //     ],
            //     'default' => -1,
            // ],
			'config' => [
                'type' => 'language',
            ],
        ],
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_pitsdownloadcenter_domain_model_filetype',
				'foreign_table_where' => 'AND tx_pitsdownloadcenter_domain_model_filetype.pid=###CURRENT_PID### AND tx_pitsdownloadcenter_domain_model_filetype.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),

		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'filetype' => array(
			'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_filetypelabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
			'config' => [
				'type' => 'check',
				'renderType' => 'checkboxToggle',
				'items' => [
					[
						0 => '',
						1 => '',
						'invertStateDisplay' => true
					]
				],
			],
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

	),
];
