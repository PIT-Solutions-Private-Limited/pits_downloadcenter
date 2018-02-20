<?php
class DocumentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
     /**
     * Disables pid constraint
     *
     * @return void
     */
    public function initializeObject()
    {
        $querySettings = $this->objectManager->create('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
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
        $fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
        $query = $this->createQuery();
        $documents = $query->execute();
        $references = array();

        foreach ($documents as $document) {
            $references[] = $fileRepository->findFileReferenceByUid($document->getUid());
        }

        return $references;
    }
  
}