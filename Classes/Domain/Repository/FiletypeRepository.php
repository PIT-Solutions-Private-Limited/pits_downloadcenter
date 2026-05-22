<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Domain\Repository;

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
 * FiletypeRepository
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Removed deprecated setLanguageOverlayMode(): this method was removed in TYPO3 v13.
 *   Language overlay behaviour is now controlled via the site configuration's language settings.
 */
class FiletypeRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
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
     * findAll
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAll()
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectSysLanguage(true);
        // TYPO3 v13: setLanguageOverlayMode() is removed from QuerySettings.
        // Language overlay is now governed by the site configuration. Remove this call.
        return $query->execute();
    }
}
