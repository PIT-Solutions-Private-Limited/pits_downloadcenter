<?php
defined('TYPO3_MODE') or die();
/**
 * extend sys_file_metadata fields sys_file_metadata
 */
$allowedFileExtensions = 'jpeg,jpg,doc,docx,pdf';
$tempColumns = array(
    'tx_pitsdownloadcenter_domain_model_download_filetype' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_filetypelabel',
        "config" => Array(
            "type" => "select",
            "renderType" => "selectMultipleSideBySide",
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
                        "table" => "tx_pitsdownloadcenter_domain_model_download_file_type"
                    ),
                    'module' => array(
                        'name' => 'wizard_list',
                        'urlParameters' => array(
                            'mode' => 'wizard',
                            'act' => 'file'
                        )
                    )
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
            "renderType" => "selectTree",
            'renderMode' => 'tree',
            'foreign_table' => 'tx_pitsdownloadcenter_domain_model_category',
            'foreign_table_where' => ' AND tx_pitsdownloadcenter_domain_model_category.sys_language_uid IN (-1,0) ORDER BY tx_pitsdownloadcenter_domain_model_category.sorting ASC',
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
    'tx_pitsdownloadcenter_domain_model_download_translate' => [
        'exclude' => true,
        'label' => 'LLL:EXT:pits_downloadcenter/Resources/Private/Language/locallang_db.xlf:tx_pitsdownloadcenter_domain_model_download_translatedfile',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'tx_pitsdownloadcenter_domain_model_download_translate',
            [
                'appearance' => [
                    'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                ],
                'foreign_types' => [
                    '0' => [
                        'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                        'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                        'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                        'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                        'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                        'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                    ]
                ],
                'maxitems' => 1,
                'minitems' => 0
            ],
            'jpeg,jpg,png,doc,docx,pdf'
        ),
    ]

);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file_metadata',
    $tempColumns,
    1
);

?>
