<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Domain\Model;

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
 * Filetype
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Added explicit typed property declaration.
 * - Added PHP 8.2 compatible typed method signatures.
 */
class Filetype extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    protected string $filetype = '';

    public function getFiletype(): string
    {
        return $this->filetype;
    }

    public function setFiletype(string $filetype): self
    {
        $this->filetype = $filetype;
        return $this;
    }
}
