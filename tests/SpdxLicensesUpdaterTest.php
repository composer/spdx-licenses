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

    /**
     * @return void
     */
    public function setUp()
    {
        $this->updater = new SpdxLicensesUpdater();
        $this->licenseFile = __DIR__ . '/../res/licenses.json';
        $this->exceptionFile = __DIR__ . '/../res/exceptions.json';
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        @unlink($this->licenseFile);
        @unlink($this->exceptionFile);
    }

    /**
     * @return void
     */
    public function testDumpLicenses()
    {
        $this->updater->dumpLicenses();
        $this->assertFileExists(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::LICENSES_FILE);

        $this->updater->dumpLicenses($this->licenseFile);
        $this->assertFileExists($this->licenseFile);
    }

    /**
     * @return void
     */
    public function testDumpExceptions()
    {
        $this->updater->dumpExceptions();
        $this->assertFileExists(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::EXCEPTIONS_FILE);

        $this->updater->dumpExceptions($this->exceptionFile);
        $this->assertFileExists($this->exceptionFile);
    }
}
