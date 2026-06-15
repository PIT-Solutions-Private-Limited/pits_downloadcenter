<?php

declare(strict_types=1);

defined('TYPO3') || die();


/**
 * Register icons
 */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
	'extension-downloadcenter-main',
	\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
	['source' => 'EXT:pits_downloadcenter/Resources/Public/Icons/download-center.svg']
);


