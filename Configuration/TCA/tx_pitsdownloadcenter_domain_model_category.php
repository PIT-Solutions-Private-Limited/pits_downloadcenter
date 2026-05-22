<?php

declare(strict_types=1);

defined('TYPO3') or die();

/**
 * TCA for tx_pitsdownloadcenter_domain_model_category.
 *
 * Changes from v12 → v13:
 * - Removed 'cruser_id': removed from TCA ctrl in v13 (field still exists in DB but no longer declared in TCA ctrl).
 * - Removed 'dividers2tabs': removed in v10, was a no-op in v12.
 * - Removed 'versioningWS' and 'versioning_followPages': 'versioningWS' now defaults to true; integer value form removed.
 * - Removed 't3ver_label' column definition: workspace label is handled internally since v11.
 * - Fixed 'starttime'/'endtime' columns: replaced deprecated 'eval'=>'datetime' with type='datetime'.
 * - Replaced 'EXT:lang/locallang_general.xlf' with 'EXT:core/Resources/Private/Language/locallang_general.xlf'.
 * - Replaced 'EXT:cms/locallang_ttc.xlf' with 'EXT:frontend/Resources/Private/Language/locallang_ttc.xlf'.
 * - Removed deprecated 'checkbox' key from input config.
 */
return [
    'ctrl' => [
        'title' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_category',
        'label' => 'categoryname',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'sortby' => 'sorting',
        'default_sortby' => 'ORDER BY sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'searchFields' => '',
        'iconfile' => 'EXT:pits_downloadcenter/Resources/Public/Icons/tx_pitsdownloadcenter_domain_model_category.png',
    ],
    'types' => [
        '1' => [
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, parentcategory, categoryname, description, hidden;;1,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, hidden'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'tx_pitsdownloadcenter_domain_model_category',
                'foreign_table_where' => 'AND tx_pitsdownloadcenter_domain_model_category.pid=###CURRENT_PID### AND tx_pitsdownloadcenter_domain_model_category.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'categoryname' => [
            'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_categoryname',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'parentcategory' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_parentcategory',
            'config' => [
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
                'type' => 'select',
                'renderType' => 'selectTree',
                'foreign_table' => 'tx_pitsdownloadcenter_domain_model_category',
                'foreign_table_where' => ' AND tx_pitsdownloadcenter_domain_model_category.sys_language_uid IN (-1,0) ORDER BY tx_pitsdownloadcenter_domain_model_category.sorting ASC',
                'treeConfig' => [
                    'parentField' => 'parentcategory',
                    'appearance' => [
                        'expandAll' => true,
                        'showHeader' => true,
                        'maxLevels' => 99,
                    ],
                ],
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_categorydescription',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'wrap' => 'off',
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        // TYPO3 v13: starttime/endtime use type='datetime' instead of type='input' + eval='datetime'.
        'starttime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y')),
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y')),
                ],
            ],
        ],
    ],
];
