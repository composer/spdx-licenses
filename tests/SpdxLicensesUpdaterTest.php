<?php

/*
 * This file is part of composer/spdx-licenses.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\Spdx;

use PHPUnit\Framework\TestCase;

class SpdxLicensesUpdaterTest extends TestCase
{
    /**
     * @var SpdxLicensesUpdater
     */
    private $updater;
    /**
     * @var string
     */
    private $licenseFile;
    /**
     * @var string
     */
    private $exceptionFile;

    public function setUp(): void
    {
        $this->updater = new SpdxLicensesUpdater();
        $this->licenseFile = __DIR__ . '/../res/licenses.json';
        $this->exceptionFile = __DIR__ . '/../res/exceptions.json';
    }

    public function tearDown(): void
    {
        @unlink($this->licenseFile);
        @unlink($this->exceptionFile);
    }

    public function testDumpLicenses(): void
    {
        $this->updater->dumpLicenses();
        $this->assertFileExists(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::LICENSES_FILE);

        $this->updater->dumpLicenses($this->licenseFile);
        $this->assertFileExists($this->licenseFile);
    }

    public function testDumpExceptions(): void
    {
        $this->updater->dumpExceptions();
        $this->assertFileExists(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::EXCEPTIONS_FILE);

        $this->updater->dumpExceptions($this->exceptionFile);
        $this->assertFileExists($this->exceptionFile);
    }
}
