<?php
defined('TYPO3') || die();

call_user_func(function () {

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('pits_downloadcenter', 'Configuration/TypoScript', 'Download Center');



\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	'pits_downloadcenter', 'Configuration/TypoScript/Themes/Red',
	'Download Center Red Theme'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	'pits_downloadcenter', 'Configuration/TypoScript/Themes/Blue',
	'Download Center Blue Theme'
);

});

