<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
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
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 ***************************************************************/

/**
 * DownloadRepository
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Replaced string-based GeneralUtility::makeInstance('TYPO3\\...') with ::class references.
 * - Replaced deprecated ->execute()->fetch() with ->executeQuery()->fetchAssociative().
 * - Replaced deprecated ->execute()->fetchAll() with ->executeQuery()->fetchAllAssociative().
 * - Removed unused Typo3Version import.
 */
class DownloadRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * findAll
     *
     * @return mixed
     */
    public function findAll()
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        return $fileRepository->findAll();
    }

    /**
     * Disables pid constraint.
     */
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Finds all referenced documents returning them as File objects.
     *
     * @return mixed
     */
    public function findAllReferenced()
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        return $fileRepository->findAll();
    }

    /**
     * find Processed File
     *
     * @param mixed $data
     * @return mixed
     */
    public function getProcessedFile($data)
    {
        $processedFileRep = GeneralUtility::makeInstance(ProcessedFileRepository::class);
        $taskType = 'Image.Preview';
        $processingConfig = [];
        return $processedFileRep->findOneByOriginalFileAndTaskTypeAndConfiguration($data, $taskType, $processingConfig);
    }

    /**
     * getFileDetails
     *
     * @param int|string $storageUid
     * @param int|string $fileID
     * @return array<string,mixed>|false
     */
    public function getFileDetails($storageUid, $fileID): array|false
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
        // TYPO3 v13: ->execute() on QueryBuilder is removed; use ->executeQuery() which returns a Result object.
        // ->fetch() on the old Statement is replaced by ->fetchAssociative() on the Result.
        $result = $queryBuilder
            ->select('identifier', 'name')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->eq('storage', $queryBuilder->createNamedParameter($storageUid)),
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileID))
            )
            ->executeQuery()
            ->fetchAssociative();
        return $result;
    }

    /**
     * checkTranslations
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @param int $sys_language_uid
     * @return array<string,mixed>|false
     */
    public function checkTranslations($file, int $sys_language_uid): array|false
    {
        $file_uid = $file->getUid();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_metadata');
        // TYPO3 v13: ->execute()->fetch() replaced with ->executeQuery()->fetchAssociative().
        $record = $queryBuilder
            ->select('uid')
            ->from('sys_file_metadata')
            ->where(
                $queryBuilder->expr()->eq('file', $queryBuilder->createNamedParameter($file_uid)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_uid))
            )
            ->executeQuery()
            ->fetchAssociative();

        if (!$record) {
            return false;
        }

        $getTranslatedFile = false;
        if (!is_null($record['uid'])) {
            $file_uid = $record['uid'];
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
            $getTranslatedFile = $queryBuilder
                ->select('uid_foreign', 'uid_local')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_uid)),
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($file_uid)),
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('sys_file_metadata')),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('tx_pitsdownloadcenter_domain_model_download_translate'))
                )
                ->executeQuery()
                ->fetchAssociative();
        }

        if (is_array($getTranslatedFile)) {
            return !empty(array_filter($getTranslatedFile)) ? $getTranslatedFile : false;
        }

        return false;
    }
}
