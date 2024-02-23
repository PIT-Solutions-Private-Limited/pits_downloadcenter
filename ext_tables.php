<?php

declare(strict_types=1);

defined('TYPO3') || die();

// \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pitsdownloadcenter_domain_model_category');
// \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pitsdownloadcenter_domain_model_filetype');

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


