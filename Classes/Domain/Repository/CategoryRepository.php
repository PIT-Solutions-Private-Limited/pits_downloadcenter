<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2026 Developer <contact@pitsolutions.com>, PIT Solutions Pvt Ltd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 ***************************************************************/

/**
 * CategoryRepository
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Replaced deprecated ->execute()->fetchAll() with ->executeQuery()->fetchAllAssociative().
 * - Replaced deprecated ->execute()->fetchOne() with ->executeQuery()->fetchOne().
 * - Removed unused Typo3Version import.
 */
class CategoryRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var array<string,string>
     */
    protected $defaultOrderings = [
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
    ];

    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * getSubCategories
     *
     * @param int|string $categoryID
     * @return array<int,array<string,mixed>>
     */
    public function getSubCategories($categoryID): array
    {
        $siteLanguageObj = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
        $sys_language_uid = $siteLanguageObj->getLanguageId();
        $sys_language_ids = [-1, $sys_language_uid];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pitsdownloadcenter_domain_model_category');

        // TYPO3 v13: ->execute() is removed on QueryBuilder; use ->executeQuery()->fetchAllAssociative().
        return $queryBuilder
            ->select('*')
            ->from('tx_pitsdownloadcenter_domain_model_category')
            ->where(
                $queryBuilder->expr()->eq('parentcategory', $queryBuilder->createNamedParameter($categoryID)),
                $queryBuilder->expr()->in(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($sys_language_ids, Connection::PARAM_INT_ARRAY)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * getSubCategoriesCount
     *
     * @param int|string $categoryID
     * @return int
     */
    public function getSubCategoriesCount($categoryID): int
    {
        $siteLanguageObj = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
        $sys_language_uid = $siteLanguageObj->getLanguageId();
        $sys_language_ids = [-1, $sys_language_uid];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pitsdownloadcenter_domain_model_category');

        // TYPO3 v13: ->execute() is removed; use ->executeQuery()->fetchOne().
        return (int)$queryBuilder
            ->count('uid')
            ->from('tx_pitsdownloadcenter_domain_model_category')
            ->where(
                $queryBuilder->expr()->eq('parentcategory', $queryBuilder->createNamedParameter($categoryID)),
                $queryBuilder->expr()->in(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($sys_language_ids, Connection::PARAM_INT_ARRAY)
                )
            )
            ->executeQuery()
            ->fetchOne();
    }
}
