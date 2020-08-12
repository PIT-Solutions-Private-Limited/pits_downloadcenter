<?php
namespace PITS\PitsDownloadcenter\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

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
 * @todo cleanup required in this class
 *
 * DownloadRepository
 * The repository for Downloads
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
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
		    'TYPO3\\CMS\\Core\\Resource\\FileRepository'
        );
		return $fileRepository->findAll();
	}

	/**
	* Disables pid constraint
	*
	* @return void
	*/
	public function initializeObject()
    {
		$querySettings = $this->objectManager->get(
		    'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings'
        );
		$querySettings->setRespectStoragePage(FALSE);
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
		return $fileRepository->findAll();
	}

	/**
	 * find Processed File
	 *
     * @param $data
	 * @return array
	 **/
	public function getProcessedFile($data)
    {
		$processedFileRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
		    'TYPO3\\CMS\\Core\\Resource\\ProcessedFileRepository'
        );
		$taskType = "Image.Preview";
		$processingConfig = array();
		return $processedFileRep->findOneByOriginalFileAndTaskTypeAndConfiguration($data, $taskType, $processingConfig);
	}

    /**
     * isProcessed
     *
     * @param array $fileDetails
     * @return bool
     */
	public function isProcessed(array $fileDetails)
    {
		$processedFile = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Resource\\ProcessedFile',
            // fileObject , processing type, processing configurations
            [$fileDetails['fileObj'], $fileDetails['processType'], $fileDetails['processConfig']]
        );
		$isProcessed = $processedFile->isProcessed();
		return $isProcessed;
	}

    /**
     * getFileDetails
     *
     * @param $storageUid
     * @param $fileID
     * @return mixed
     */
	public function getFileDetails($storageUid , $fileID)
    {
		if(version_compare(TYPO3_version, '8.7.99', '<=')){
			$response = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow ( 
				"identifier,name",
				"sys_file",
				" storage = $storageUid AND uid = $fileID ",
				$groupBy= '',
				$orderBy= '',
				$numIndex=FALSE
			);
		}
		else {
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
			$response = $queryBuilder
				->select('identifier','name')
				->from('sys_file')
				->where(
					$queryBuilder->expr()->eq('storage', $queryBuilder->createNamedParameter($storageUid)),
					$queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileID))
					)
				->execute()
				->fetch();
		}
		return $response;
	}

    /**
     * checkTranslations
     *
     * @todo deprecated function TYPO3 _DB needs to change
     * @param $file
     * @param $sys_language_uid
     * @return array|bool
     */
	public function checkTranslations($file , $sys_language_uid)
    {
		$file_uid = $file->getUid();
		if(version_compare(TYPO3_version, '8.7.99', '<=')){
			$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
			$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow ( 
				"uid",
				"sys_file_metadata",
				" sys_language_uid = $sys_language_uid AND file = $file_uid ",
				$groupBy= '',
				$orderBy= '',
				$numIndex=FALSE
			);
			if(!is_null($record['uid'])) {
				$file_uid = $record['uid'];
				$getTranslatedFile = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow (
					"uid_foreign,uid_local",
					"sys_file_reference",
					"sys_language_uid = $sys_language_uid AND uid_foreign= $file_uid AND tablenames = 'sys_file_metadata' AND fieldname = 'tx_pitsdownloadcenter_domain_model_download_translate' AND deleted = '0' AND hidden = '0' ",
					$groupBy= '',
					$orderBy= '',
					$numIndex=FALSE
				);
			}
		}
		else {
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_metadata');
			$record = $queryBuilder
				->select('uid')
				->from('sys_file_metadata')
				->where(
					$queryBuilder->expr()->eq('file', $queryBuilder->createNamedParameter($file_uid)),
					$queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_uid))
				)
				->execute()
				->fetch();
			if(!is_null($record['uid'])) {
				$file_uid = $record['uid'];
				$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
				$getTranslatedFile = $queryBuilder
				->select('uid_foreign','uid_local')
				->from('sys_file_reference')
				->where(
					$queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_uid)),
					$queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($file_uid)),
					$queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('sys_file_metadata')),
					$queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('tx_pitsdownloadcenter_domain_model_download_translate'))
					)
				->execute()
				->fetch();
			}
		}
		
		// Query
		if (is_array( $getTranslatedFile )):
			$response = (!empty(array_filter( $getTranslatedFile )))?$getTranslatedFile:false;
		else:
			return  false;
		endif;
		return $response;
	}
}
