<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Domain\Repository;

use TYPO3\CMS\Core\Resource\FileRepository;
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
 * DocumentRepository
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Replaced string-based GeneralUtility::makeInstance('TYPO3\\...') with ::class reference.
 * - Added void return type to initializeObject().
 * - Added return type annotation to findAllReferenced().
 */
class DocumentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Finds all referenced documents and returns them as file reference objects.
     *
     * @return array<int,mixed>
     */
    public function findAllReferenced(): array
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $query = $this->createQuery();
        $documents = $query->execute();
        $references = [];
        foreach ($documents as $document) {
            $references[] = $fileRepository->findFileReferenceByUid($document->getUid());
        }
        return $references;
    }
}
