<?php
namespace PITS\PitsDownloadcenter\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 HOJA <hoja.ma@pitsolutions.com>, PIT Solutions Pvt Ltd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * CategoryRepository
 * The repository for Category
 */
class CategoryRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * $defaultOrderings
     *
     * @var array
     */
    protected $defaultOrderings = array(
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    );

    /**
     * initializeObject
     */
    public function initializeObject()
    {
        /** @var QuerySettingsInterface $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        // don't add the pid constraint
        $querySettings->setRespectStoragePage(FALSE);
        $this->setDefaultQuerySettings($querySettings);
        $typo3VersionObj = GeneralUtility::makeInstance(Typo3Version::class);
        $this->typo3Version = $typo3VersionObj->getVersion();
    }

    /**
     * getSubCategories
     * createQuery() changed to querybuilder to avoid phpDocumenter 5.2.0v exception
     * 
     * @param $categoryID
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
	public function getSubCategories($categoryID)
    {
        $siteLanguageObj = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
        $sys_language_uid = $siteLanguageObj->getLanguageId();
        $sys_language_ids = [-1,$sys_language_uid];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_pitsdownloadcenter_domain_model_category');
        $statement = $queryBuilder
                        ->select('*')
                        ->from('tx_pitsdownloadcenter_domain_model_category')
                        ->where(
                            $queryBuilder->expr()->eq('parentcategory', $queryBuilder->createNamedParameter($categoryID)),
                            $queryBuilder->expr()->in('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_ids, Connection::PARAM_INT_ARRAY))
                            )
                        ->execute()
                        ->fetchAll();
        return $statement;
	}

    /**
     * getSubCategoriesCount
     * createQuery() changed to querybuilder to avoid phpDocumenter 5.2.0v exception
     * 
     * @param $categoryID
     * @return int
     */
	public function getSubCategoriesCount($categoryID)
    {
        $siteLanguageObj = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
        $sys_language_uid = $siteLanguageObj->getLanguageId();
        $sys_language_ids = [-1,$sys_language_uid];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_pitsdownloadcenter_domain_model_category');
        $count = $queryBuilder
                        ->count('uid')
                        ->from('tx_pitsdownloadcenter_domain_model_category')
                        ->where(
                            $queryBuilder->expr()->eq('parentcategory', $queryBuilder->createNamedParameter($categoryID)),
                            $queryBuilder->expr()->in('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_ids, Connection::PARAM_INT_ARRAY))
                            )
                        ->execute()
                        ->fetchColumn(0);
        return $count;
	}
}
