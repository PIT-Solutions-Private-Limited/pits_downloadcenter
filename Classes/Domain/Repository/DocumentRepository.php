<?php
namespace PITS\PitsDownloadcenter\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Class DocumentRepository
 * @package version 2.1.0
 */
class DocumentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Disables pid constraint
     *
     * @return void
     */
    public function initializeObject()
    {
        /** @var QuerySettingsInterface $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Finds all referenced documents returning them as File modules
     *
     * @return void
     */
    public function findAllReferenced()
    {
        $fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Resource\\FileRepository'
        );

        $query = $this->createQuery();
        $documents = $query->execute();
        $references = array();
        foreach ($documents as $document) {
            $references[] = $fileRepository->findFileReferenceByUid($document->getUid());
        }
        return $references;
    }
  
}
