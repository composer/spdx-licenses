<?php

/*
 * This file is part of composer/spdx-licenses.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\Spdx\Test;

use Composer\Spdx\SpdxLicenses;

class SpdxLicensesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SpdxLicenses
     */
    private $licenses;

    public function setUp()
    {
        $this->licenses = new SpdxLicenses();
    }

    /**
     * @dataProvider provideValidLicenses
     * @param string|array $license
     */
    public function testValidate($license)
    {
        $this->assertTrue($this->licenses->validate($license));
    }

    /**
     * @dataProvider provideInvalidLicenses
     * @param string|array $invalidLicense
     */
    public function testInvalidLicenses($invalidLicense)
    {
        $this->assertFalse($this->licenses->validate($invalidLicense));
    }

    /**
     * @dataProvider provideInvalidArgument
     * @expectedException \InvalidArgumentException
     * @param mixed $invalidArgument
     */
    public function testInvalidArgument($invalidArgument)
    {
        $this->licenses->validate($invalidArgument);
    }

    public function testGetResourcesDir()
    {
        $dir = SpdxLicenses::getResourcesDir();

        $this->assertTrue(is_dir($dir));
        $this->assertEquals(realpath($dir), realpath(__DIR__ . '/../res'));
    }

    /**
     * @dataProvider provideResourceFiles
     * @param string $file
     */
    public function testResourceFilesExist($file)
    {
        $this->assertFileExists(SpdxLicenses::getResourcesDir() . '/' . $file);
    }

    /**
     * @dataProvider provideResourceFiles
     * @param string $file
     */
    public function testResourceFilesContainJson($file)
    {
        $json = json_decode(file_get_contents(SpdxLicenses::getResourcesDir() . '/' . $file));

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
    }

    public function testGetLicenseByIdentifier()
    {
        $license = $this->licenses->getLicenseByIdentifier('AGPL-1.0');
        $this->assertEquals($license[0], 'Affero General Public License v1.0'); // fullname
        $this->assertFalse($license[1]); // osi approved
    }

    public function testGetIdentifierByName()
    {
        $identifier = $this->licenses->getIdentifierByName('Affero General Public License v1.0');
        $this->assertEquals($identifier, 'AGPL-1.0');

        $identifier = $this->licenses->getIdentifierByName('BSD 2-clause "Simplified" License');
        $this->assertEquals($identifier, 'BSD-2-Clause');
    }

    public function testIsOsiApprovedByIdentifier()
    {
        $osiApproved = $this->licenses->isOsiApprovedByIdentifier('MIT');
        $this->assertTrue($osiApproved);

        $osiApproved = $this->licenses->isOsiApprovedByIdentifier('AGPL-1.0');
        $this->assertFalse($osiApproved);
    }

    /**
     * @return array
     */
    public function provideResourceFiles()
    {
        return array(
            array(SpdxLicenses::LICENSES_FILE),
            array(SpdxLicenses::EXCEPTIONS_FILE),
        );
    }

    /**
     * @return array
     */
    public function provideValidLicenses()
    {
        $json = file_get_contents(SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::LICENSES_FILE);
        $licenses = json_decode($json, true);
        $identifiers = array_keys($licenses);

        $valid = array_merge(
            array(
                'MIT',
                'MIT+',
                array('(MIT)'),
                'NONE',
                'NOASSERTION',
                'LicenseRef-3',
                array('LGPL-2.0', 'GPL-3.0+'),
                '(LGPL-2.0 or GPL-3.0+)',
                '(LGPL-2.0 OR GPL-3.0+)',
                array('EUDatagrid and GPL-3.0+'),
                '(EUDatagrid and GPL-3.0+)',
                '(EUDatagrid AND GPL-3.0+)',
                'GPL-2.0 with Autoconf-exception-2.0',
                'GPL-2.0 WITH Autoconf-exception-2.0',
                'GPL-2.0+ WITH Autoconf-exception-2.0',
                array('(GPL-3.0 and GPL-2.0 or GPL-3.0+)'),
            ),
            $identifiers
        );

        foreach ($valid as &$r) {
            $r = array($r);
        }

        return $valid;
    }

    /**
     * @return array
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
            array(array('LGPL-2.0', 'The system pwns you')),
            array('and GPL-3.0+'),
            array('(EUDatagrid and GPL-3.0+ and  )'),
            array('(EUDatagrid xor GPL-3.0+)'),
            array('(MIT Or MIT)'),
            array('(NONE or MIT)'),
            array('(NOASSERTION or MIT)'),
            array('Autoconf-exception-2.0 WITH MIT'),
            array('MIT WITH'),
            array('MIT OR'),
            array('MIT AND'),
        );
    }

    /**
     * @return array
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
}
