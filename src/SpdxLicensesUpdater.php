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

/**
 * The SPDX Licenses Updater scrapes licenses from the spdx website
 * and updates the "res/*.json" file accordingly.
 *
 * The class is used by the update script "bin/update-spdx-licenses".
 */
class SpdxLicensesUpdater
{
    /**
     * @param string $file
     * @param string $url
     */
    public function dumpLicenses($file = null, $url = 'https://raw.githubusercontent.com/spdx/license-list-data/master/json/licenses.json')
    {
        if (null === $file) {
            $file = SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::LICENSES_FILE;
        }

        $options = 0;

        if (defined('JSON_PRETTY_PRINT')) {
            $options |= JSON_PRETTY_PRINT;
        }

        if (defined('JSON_UNESCAPED_SLASHES')) {
            $options |= JSON_UNESCAPED_SLASHES;
        }

        $licenses = json_encode($this->getLicenses($url), $options);
        file_put_contents($file, $licenses);
    }

    /**
     * @param string $file
     * @param string $url
     */
    public function dumpExceptions($file = null, $url = 'https://raw.githubusercontent.com/spdx/license-list-data/master/json/exceptions.json')
    {
        if (null === $file) {
            $file = SpdxLicenses::getResourcesDir() . '/' . SpdxLicenses::EXCEPTIONS_FILE;
        }

        $options = 0;

        if (defined('JSON_PRETTY_PRINT')) {
            $options |= JSON_PRETTY_PRINT;
        }

        if (defined('JSON_UNESCAPED_SLASHES')) {
            $options |= JSON_UNESCAPED_SLASHES;
        }

        $exceptions = json_encode($this->getExceptions($url), $options);
        file_put_contents($file, $exceptions);
    }

    /**
     * @param string $url
     *
     * @return array
     */
    private function getLicenses($url)
    {
        $licenses = array();

        $data = json_decode(file_get_contents($url), true);

        foreach ($data['licenses'] as $info) {
            $licenses[$info['licenseId']] = array(
                trim($info['name']), $info['isOsiApproved'], $info['isDeprecatedLicenseId']
            );
        }

        uksort($licenses, 'strcasecmp');

        return $licenses;
    }

    /**
     * @param string $url
     *
     * @return array
     */
    private function getExceptions($url)
    {
        $exceptions = array();

        $data = json_decode(file_get_contents($url), true);

        foreach ($data['exceptions'] as $info) {
            $exceptions[$info['licenseExceptionId']] = array(trim($info['name']));
        }

        uksort($exceptions, 'strcasecmp');

        return $exceptions;
    }
}
