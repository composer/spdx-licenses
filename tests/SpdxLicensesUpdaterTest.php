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
     * @var SpdxLicenses
     */
    private $updater;
    private $licenseFiles;
    private $exceptionFile;

    public function setUp()
    {
        $this->updater = new SpdxLicensesUpdater();
        $this->licenseFile = __DIR__ . '/../res/licenses.json';
        $this->exceptionFile = __DIR__ . '/../res/exceptions.json';
    }

    public function tearDown()
    {
        @unlink($this->licenseFile);
        @unlink($this->exceptionFile);
    }

    public function testDumpLicenses()
    {
        $this->updater->dumpLicenses();
        $this->assertTrue(file_exists(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::LICENSES_FILE));

        $this->updater->dumpLicenses($this->licenseFile);
        $this->assertTrue(file_exists($this->licenseFile));
    }

    public function testDumpExceptions()
    {
        $this->updater->dumpExceptions();
        $this->assertTrue(file_exists(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::EXCEPTIONS_FILE));

        $this->updater->dumpExceptions($this->exceptionFile);
        $this->assertTrue(file_exists($this->exceptionFile));
    }
}
