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

class SpdxLicensesTest extends TestCase
{
    /**
     * @var SpdxLicenses
     */
    private $licenses;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->licenses = new SpdxLicenses();
    }

    /**
     * @dataProvider provideValidLicenses
     *
     * @param string|string[] $license
     * @return void
     */
    public function testValidate($license)
    {
        $this->assertTrue($this->licenses->validate($license));
    }

    /**
     * @dataProvider provideInvalidLicenses
     *
     * @param string|string[] $invalidLicense
     * @return void
     */
    public function testInvalidLicenses($invalidLicense)
    {
        $this->assertFalse($this->licenses->validate($invalidLicense));
    }

    /**
     * @dataProvider provideInvalidArgument
     *
     * @param mixed $invalidArgument
     * @return void
     */
    public function testInvalidArgument($invalidArgument)
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->licenses->validate($invalidArgument);
    }

    /**
     * @testdox Resources directory exists at expected locatation.
     * @return void
     */
    public function testGetResourcesDir()
    {
        $dir = SpdxLicenses::getResourcesDir();

        $this->assertTrue(
            is_dir($dir),
            'Expected resources directory to exist.'
        );

        $this->assertEquals(
            realpath($dir),
            realpath(__DIR__ . '/../res'),
            'Expected resources directory to be "res" (relative to project root).'
        );
    }

    /**
     * @testdox Resources files exist at expected locations.
     * @dataProvider provideResourceFiles
     *
     * @param string $file
     * @return void
     */
    public function testResourceFilesExist($file)
    {
        $this->assertFileExists(
            SpdxLicenses::getResourcesDir() . '/' . $file,
            'Expected file to exist in resources dir: ' . $file
        );
    }

    /**
     * @testdox Resources files contain valid JSON.
     * @dataProvider provideResourceFiles
     *
     * @param string $file
     * @return void
     */
    public function testResourceFilesContainJson($file)
    {
        $contents = file_get_contents(SpdxLicenses::getResourcesDir() . '/' . $file);
        if (false === $contents) {
            $this->fail('Could not read the license file at '.SpdxLicenses::getResourcesDir() . '/' . $file);
            return;
        }
        $json = json_decode($contents, true);

        if (null === $json && json_last_error()) {
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    $error = ' - no errors';
                    break;
                case JSON_ERROR_DEPTH:
                    $error = ' - maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = ' - underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = ' - unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = ' - syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error = ' - malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error = ' - unknown error';
                    break;
            }

            $this->fail('Could not decode JSON within ' . $file . $error);
        }

        $this->assertNotEmpty($json);
    }

    /**
     * @return void
     */
    public function testGetLicenseByIdentifier()
    {
        /** @var SPDXLicense $license */
        $license = $this->licenses->getLicenseByIdentifier('AGPL-1.0-only');
        $this->assertTrue(is_array($license));
        $this->assertEquals('Affero General Public License v1.0 only', $license[0]);
        $this->assertFalse($license[1]);
        $this->assertStringStartsWith('https://spdx.org/licenses/', $license[2]);
        $this->assertFalse($license[3]);

        $licenseNull = $this->licenses->getLicenseByIdentifier('AGPL-1.0-Illegal');
        $this->assertNull($licenseNull);
    }

    /**
     * @return void
     */
    public function testGetLicenses()
    {
        $results = $this->licenses->getLicenses();

        $this->assertArrayHasKey('cc-by-sa-4.0', $results);
        $this->assertArrayHasKey(0, $results['cc-by-sa-4.0']);
        $this->assertEquals('CC-BY-SA-4.0', $results['cc-by-sa-4.0'][0]);
        $this->assertEquals('Creative Commons Attribution Share Alike 4.0 International', $results['cc-by-sa-4.0'][1]);
        $this->assertEquals(false, $results['cc-by-sa-4.0'][2]);
        $this->assertEquals(false, $results['cc-by-sa-4.0'][3]);
    }

    /**
     * @return void
     */
    public function testGetExceptionByIdentifier()
    {
        $licenseNull = $this->licenses->getExceptionByIdentifier('Font-exception-2.0-Errorl');
        $this->assertNull($licenseNull);

        /** @var SPDXLicenseException $license */
        $license = $this->licenses->getExceptionByIdentifier('Font-exception-2.0');
        $this->assertTrue(is_array($license));
        $this->assertSame('Font exception 2.0', $license[0]);
    }

    /**
     * @return void
     */
    public function testGetIdentifierByName()
    {
        $identifier = $this->licenses->getIdentifierByName('Affero General Public License v1.0');
        $this->assertEquals($identifier, 'AGPL-1.0');

        $identifier = $this->licenses->getIdentifierByName('BSD 2-Clause "Simplified" License');
        $this->assertEquals($identifier, 'BSD-2-Clause');

        $identifier = $this->licenses->getIdentifierByName('Font exception 2.0');
        $this->assertEquals($identifier, 'Font-exception-2.0');

        $identifier = $this->licenses->getIdentifierByName('null-identifier-name');
        $this->assertNull($identifier);
    }

    /**
     * @return void
     */
    public function testIsOsiApprovedByIdentifier()
    {
        $osiApproved = $this->licenses->isOsiApprovedByIdentifier('MIT');
        $this->assertTrue($osiApproved);

        $osiApproved = $this->licenses->isOsiApprovedByIdentifier('AGPL-1.0');
        $this->assertFalse($osiApproved);
    }

    /**
     * @return void
     */
    public function testIsDeprecatedByIdentifier()
    {
        $deprecated = $this->licenses->isDeprecatedByIdentifier('GPL-3.0');
        $this->assertTrue($deprecated);

        $deprecated = $this->licenses->isDeprecatedByIdentifier('GPL-3.0-only');
        $this->assertFalse($deprecated);
    }

    /**
     * @return array[]
     */
    public function provideResourceFiles()
    {
        return array(
            array(SpdxLicenses::LICENSES_FILE),
            array(SpdxLicenses::EXCEPTIONS_FILE),
        );
    }

    /**
     * @return array<int, string|string[]>
     */
    public function provideValidLicenses()
    {
        $contents = file_get_contents(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::LICENSES_FILE);
        if (false === $contents) {
            throw new \LogicException('Could not read the license file at '.SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::LICENSES_FILE);
        }
        $licenses = json_decode($contents, true);
        /** @var string[] $identifiers */
        $identifiers = array_keys($licenses);

        $valid = array_merge(
            array(
                'MIT',
                'MIT+',
                array('(MIT)'),
                'NONE',
                'NOASSERTION',
                'LicenseRef-3',
                array('LGPL-2.0-only', 'GPL-3.0-or-later'),
                '(LGPL-2.0-only or GPL-3.0-or-later)',
                '(LGPL-2.0-only OR GPL-3.0-or-later)',
                array('EUDatagrid and GPL-3.0-or-later'),
                '(EUDatagrid and GPL-3.0-or-later)',
                '(EUDatagrid AND GPL-3.0-or-later)',
                'GPL-2.0-only with Autoconf-exception-2.0',
                'GPL-2.0-only WITH Autoconf-exception-2.0',
                'GPL-2.0-or-later WITH Autoconf-exception-2.0',
                array('(GPL-3.0-only and GPL-2.0-only or GPL-3.0-or-later)'),
            ),
            $identifiers
        );

        foreach ($valid as &$r) {
            $r = array($r);
        }

        return $valid;
    }

    /**
     * @return array[]
     */
    public function provideInvalidLicenses()
    {
        return array(
            array(''),
            array(array()),
            array('The system pwns you'),
            array('()'),
            array('(MIT'),
            array('MIT)'),
            array('MIT NONE'),
            array('MIT AND NONE'),
            array('MIT (MIT and MIT)'),
            array('(MIT and MIT) MIT'),
            array(array('LGPL-2.0-only', 'The system pwns you')),
            array('and GPL-3.0-or-later'),
            array('(EUDatagrid and GPL-3.0-or-later and  )'),
            array('(EUDatagrid xor GPL-3.0-or-later)'),
            array('(NONE or MIT)'),
            array('(NOASSERTION or MIT)'),
            array('Autoconf-exception-2.0 WITH MIT'),
            array('MIT WITH'),
            array('MIT OR'),
            array('MIT AND'),
        );
    }

    /**
     * @return array[]
     */
    public function provideInvalidArgument()
    {
        return array(
            array(null),
            array(new \stdClass()),
            array(array(new \stdClass())),
            array(array('mixed', new \stdClass())),
            array(array(new \stdClass(), new \stdClass())),
        );
    }

    /**
     * @param string      $exception
     * @param string|null $message
     * @param int|null    $code
     * @return void
     */
    public function setExpectedException($exception, $message = null, $code = null)
    {
        if (!class_exists('PHPUnit\Framework\Error\Notice')) {
            $exception = str_replace('PHPUnit\\Framework\\Error\\', 'PHPUnit_Framework_Error_', $exception);
        }
        if (method_exists($this, 'expectException')) {
            $this->expectException($exception);
            if (null !== $message) {
                $this->expectExceptionMessage($message);
            }
        } else {
            /** @phpstan-ignore-next-line */
            parent::setExpectedException($exception, $message, $code);
        }
    }
}
