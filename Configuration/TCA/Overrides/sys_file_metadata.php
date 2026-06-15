<?php

declare(strict_types=1);

defined('TYPO3') or die();

/**
 * Extend sys_file_metadata with Download Center columns.
 *
 * Changes from v12:
 * - Removed deprecated 'wizards' sub-array from select column (removed in v10+, never valid in v12/v13).
 * - Removed deprecated 'foreign_types' from file-reference column (removed in v10; use 'overrideChildTca' instead).
 * - Replaced 'EXT:lang/locallang_tca.xlf' paths with 'EXT:core/Resources/Private/Language/locallang_tca.xlf'.
 * - Added declare(strict_types=1).
 * - Removed trailing closing PHP tag.
 */
$tempColumns = [
    'tx_pitsdownloadcenter_domain_model_download_filetype' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_filetypelabel',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_pitsdownloadcenter_domain_model_filetype',
            'foreign_table_where' => 'AND tx_pitsdownloadcenter_domain_model_filetype.sys_language_uid IN (-1,0) ',
            'size' => 10,
            'minitems' => 0,
            'maxitems' => 100,
        ],
    ],
    'tx_pitsdownloadcenter_domain_model_download_category' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_parentcategory',
        'config' => [
            'minitems' => 0,
            'maxitems' => 999,
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
    'tx_pitsdownloadcenter_domain_model_download_translate' => [
        'exclude' => true,
        'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_translatedfile',
        'config' => [
            'type' => 'file',
            'allowed' => 'jpeg,jpg,png,doc,docx,pdf',
            'maxitems' => 1,
            'minitems' => 0,
            'appearance' => [
                'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
            ],
            'overrideChildTca' => [
                'types' => [
                    '0' => [
                        'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                        'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                        'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                        'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                        'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                        'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                    ],
                ],
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file_metadata',
    $tempColumns
);
