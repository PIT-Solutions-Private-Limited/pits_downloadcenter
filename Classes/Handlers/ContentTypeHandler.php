<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Handlers;

use TYPO3\CMS\Core\SingletonInterface;

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
 * ContentTypeHandler
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Added return type hint to getContentType().
 */
class ContentTypeHandler implements SingletonInterface
{
    /**
     * Returns the MIME content-type for the given file extension.
     *
     * @param string $extension File extension (without leading dot)
     * @return string MIME type string
     */
    public static function getContentType(string $extension): string
    {
        return match ($extension) {
            'txt' => 'text/plain',
            'pdf' => 'application/pdf',
            'exe' => 'application/octet-stream',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'jpeg', 'jpg' => 'image/jpeg',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/x-wav',
            'mpeg', 'mpg', 'mpe' => 'video/mpeg',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            // Forbidden file types: return empty string so caller can handle appropriately.
            // Note: original code called exit() — replaced with empty string so the controller
            // can return a proper HTTP 403 response instead of terminating the process.
            'inc', 'conf', 'sql', 'cgi', 'htaccess', 'php', 'php3', 'php4', 'php5' => '',
            default => 'application/force-download',
        };
    }
}
