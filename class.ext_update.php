<?php
namespace PITS\PitsDownloadcenter;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This class updates pits_downloadcenter from version 2.0.x to 2.1.x
 * @todo plan to rename tables
 */
class ext_update
{
    /** @var \TYPO3\CMS\Core\Database\DatabaseConnection */
    protected $databaseConnection;

    /**
     * Creates the instance of the class.
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Checks if the script should execute. We check for everything except table
     * structure.
     *
     * @return bool
     */
    public function access()
    {
        return $this->hasOldCacheTables();
    }

    /**
     * Checks if the system has old cache tables.
     *
     * @return bool
     */
    protected function hasOldCacheTables()
    {
        $tables = $this->databaseConnection->admin_get_tables();
        return isset($tables['tx_pitsdownloadcenter_domain_model_download_filetype____']);
    }

    /**
     * Runs the update.
     */
    public function main()
    {
        $locker = $this->getLocker();
        try {
            if ($locker) {
                $locker->acquire();
            }
        }
        catch (\Exception $e) {
            // Nothing
        }

        $this->checkAndRenameTables();
        if ($locker &&
            (method_exists($locker, 'isAcquired') &&
                $locker->isAcquired() ||
                method_exists($locker, 'getLockStatus') && $locker->getLockStatus()
            )
        ) {
            $locker->release();
        }
    }

    /**
     * Obtains the locker depending on the TYPO3 version.
     *
     * @return \TYPO3\CMS\Core\Locking\Locker|\TYPO3\CMS\Core\Locking\LockingStrategyInterface
     */
    protected function getLocker()
    {
        if (class_exists('\\TYPO3\\CMS\\Core\\Locking\\LockFactory')) {
            $locker = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Locking\\LockFactory'
            )->createLocker('tx_pitsdownloadcenter_update');
        }
        elseif (class_exists('\\TYPO3\\CMS\\Core\\Locking\\Locker')) {
            $locker = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Locking\\Locker',
                'tx_pitsdownloadcenter_update'
            );
        }
        else {
            $locker = null;
        }

        return $locker;
    }

    /**
     * Checks and renames tables
     */
    protected function checkAndRenameTables()
    {
        $tableMap = array(
            'tx_pitsdownloadcenter_domain_model_download_filetype' => 'tx_pitsdownloadcenter_domain_model_download_file_type'
        );

        $tables = $this->databaseConnection->admin_get_tables();
        foreach ($tableMap as $oldTableName => $newTableName) {
            if (isset($tables[$oldTableName])) {
                if (!isset($tables[$newTableName])) {
                    $this->databaseConnection->sql_query('ALTER TABLE ' . $oldTableName . ' RENAME TO ' . $newTableName);
                }
                else {
                    if ((int)$tables[$newTableName]['Rows'] === 0) {
                        $this->databaseConnection->sql_query('DROP TABLE ' . $newTableName);
                        $this->databaseConnection->sql_query('CREATE TABLE ' . $newTableName . ' LIKE ' . $oldTableName);
                        $this->databaseConnection->sql_query('INSERT INTO ' . $newTableName . ' SELECT * FROM ' . $oldTableName);
                    }
                    $this->databaseConnection->sql_query('DROP TABLE ' . $oldTableName);
                }
            }
        }
    }
}
