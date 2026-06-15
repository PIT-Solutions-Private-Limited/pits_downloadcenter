<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2026 Developer <contact@pitsolutions.com>, PIT Solutions Pvt Ltd
 *
 *  All rights reserved
 *
 ***************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * Test case for class \PITS\PitsDownloadcenter\Domain\Model\Download.
 *
 * Changes from v12 → v13:
 * - Replaced \TYPO3\CMS\Core\Tests\UnitTestCase with \PHPUnit\Framework\TestCase.
 * - Added declare(strict_types=1).
 * - setUp()/tearDown() now have void return types.
 */
class DownloadTest extends TestCase
{
    protected ?object $subject = null;

    protected function setUp(): void
    {
        $this->subject = new \PITS\PitsDownloadcenter\Domain\Model\Download();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function dummyTestToNotLeaveThisFileEmpty(): void
    {
        $this->markTestIncomplete();
    }
}
