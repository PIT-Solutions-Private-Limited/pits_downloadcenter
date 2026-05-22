<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'PitsDownloadcenter',
    'Pitsdownloadcenter',
    'Download Center',
    'extension-downloadcenter-main'
);

$pluginSignature = 'pitsdownloadcenter_pitsdownloadcenter';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:pits_downloadcenter/Configuration/Flexforms/flexform.xml',
    $pluginSignature
);

$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;headers,
        pi_flexform,
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
        --palette--;;frames,
        --palette--;;appearanceLinks,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access,
';

// Extend sys_file_metadata with custom download-center columns.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_metadata',
    '--div--;Download Manager,
    tx_pitsdownloadcenter_domain_model_download_category,
    tx_pitsdownloadcenter_domain_model_download_filetype,
    tx_pitsdownloadcenter_domain_model_download_translate',
    ''
);
