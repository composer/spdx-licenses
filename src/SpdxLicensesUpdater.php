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
    public function dumpLicenses($file = null, $url = 'https://spdx.org/licenses/index.html')
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
    public function dumpExceptions($file = null, $url = 'https://spdx.org/licenses/exceptions-index.html')
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

        $dom = new \DOMDocument();
        @$dom->loadHTMLFile($url); /* use silence operator to ignore warnings about invalid dom content */

        $xPath = new \DOMXPath($dom);
        $trs = $xPath->query('//table//tbody//tr');

        /** @var \DOMElement $tr */
        foreach ($trs as $tr) {
            $tds = $tr->getElementsByTagName('td');

            if ($tds->length !== 4) {
                continue;
            }

            if ('License Text' === trim($tds->item(3)->nodeValue)) {
                $fullname = trim($tds->item(0)->nodeValue);
                $identifier = trim($tds->item(1)->nodeValue);
                $osiApproved = ((isset($tds->item(2)->nodeValue) && $tds->item(2)->nodeValue === 'Y')) ? true : false;

                $licenses += array($identifier => array($fullname, $osiApproved));
            }
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

        $dom = new \DOMDocument();
        @$dom->loadHTMLFile($url); /* use silence operator to ignore warnings about invalid dom content */

        $xPath = new \DOMXPath($dom);
        $trs = $xPath->query('//table//tbody//tr');

        /** @var \DOMElement $tr */
        foreach ($trs as $tr) {
            $tds = $tr->getElementsByTagName('td');

            if ($tds->length !== 3) {
                continue;
            }

            if ('License Exception Text' === trim($tds->item(2)->nodeValue)) {
                $fullname = trim($tds->item(0)->nodeValue);
                $identifier = trim($tds->item(1)->nodeValue);

                $exceptions += array($identifier => array($fullname));
            }
        }

        uksort($exceptions, 'strcasecmp');

        return $exceptions;
    }
}
