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

class SpdxLicense
{
    /**
     * Contains all the licenses.
     *
     * The array is indexed by license identifiers, which contain
     * a numerically indexed array with license details.
     *
     *  [ license identifier =>
     *      [ 0 => full name (string), 1 => osi certified (bool) ]
     *    , ...
     *  ]
     *
     * @var array
     */
    private $licenses;

    /**
     * Contains all the license exceptions.
     *
     * The array is indexed by license exception identifiers, which contain
     * a numerically indexed array with license exception details.
     *
     *  [ exception identifier =>
     *      [ 0 => full name (string) ]
     *    , ...
     *  ]
     *
     * @var array
     */
    private $exceptions;

    public function __construct()
    {
        $this->loadLicenses();
        $this->loadExceptions();
    }

    /**
     * Returns license metadata by license identifier.
     *
     * This function adds a link to the full license text to the license metadata.
     * The array returned is in the form of:
     *
     *  [ 0 => full name (string), 1 => osi certified, 2 => link to license text (string) ]
     *
     * @param string $identifier
     *
     * @return array|null
     */
    public function getLicenseByIdentifier($identifier)
    {
        if (!isset($this->licenses[$identifier])) {
            return;
        }

        $license = $this->licenses[$identifier];
        $license[] = 'https://spdx.org/licenses/' . $identifier . '.html#licenseText';

        return $license;
    }

    /**
     * Returns license exception metadata by license exception identifier.
     *
     * This function adds a link to the full license exception text to the license exception metadata.
     * The array returned is in the form of:
     *
     *  [ 0 => full name (string), 1 => link to license text (string) ]
     *
     * @param string $identifier
     *
     * @return array|null
     */
    public function getExceptionByIdentifier($identifier)
    {
        if (!isset($this->exceptions[$identifier])) {
            return;
        }

        $license = $this->exceptions[$identifier];
        $license[] = 'https://spdx.org/licenses/' . $identifier . '.html#licenseExceptionText';

        return $license;
    }

    /**
     * Returns the short identifier of a license (or license exception) by full name.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getIdentifierByName($name)
    {
        foreach ($this->licenses as $identifier => $licenseData) {
            if ($licenseData[0] === $name) {
                return $identifier;
            }
        }

        foreach ($this->exceptions as $identifier => $licenseData) {
            if ($licenseData[0] === $name) {
                return $identifier;
            }
        }
    }

    /**
     * Returns the OSI Approved status for a license by identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isOsiApprovedByIdentifier($identifier)
    {
        return $this->licenses[$identifier][1];
    }

    /**
     * Check if the identifier for a license is valid.
     *
     * @param string $identifier
     *
     * @return bool
     */
    private function isValidLicenseIdentifier($identifier)
    {
        $identifiers = array_keys($this->licenses);

        return in_array($identifier, $identifiers);
    }

    /**
     * Check, if the identifier for a exception is valid.
     *
     * @param string $identifier
     *
     * @return bool
     */
    private function isValidExceptionIdentifier($identifier)
    {
        $identifiers = array_keys($this->exceptions);

        return in_array($identifier, $identifiers);
    }

    /**
     * @param array|string $license
     *
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function validate($license)
    {
        if (is_array($license)) {
            $count = count($license);
            if ($count !== count(array_filter($license, 'is_string'))) {
                throw new \InvalidArgumentException('Array of strings expected.');
            }
            $license = $count > 1  ? '(' . implode(' OR ', $license) . ')' : (string) reset($license);
        }

        if (!is_string($license)) {
            throw new \InvalidArgumentException(sprintf(
                'Array or String expected, %s given.',
                gettype($license)
            ));
        }

        return $this->isValidLicenseString($license);
    }

    /**
     * @return array
     */
    private function loadLicenses()
    {
        if (is_array($this->licenses)) {
            return $this->licenses;
        }

        $jsonFile = file_get_contents(__DIR__ . '/../res/spdx-licenses.json');
        $this->licenses = json_decode($jsonFile, true);

        return $this->licenses;
    }

    /**
     * @return array
     */
    private function loadExceptions()
    {
        if (is_array($this->exceptions)) {
            return $this->exceptions;
        }

        $jsonFile = file_get_contents(__DIR__ . '/../res/spdx-exceptions.json');
        $this->exceptions = json_decode($jsonFile, true);

        return $this->exceptions;
    }

    /**
     * @param string $license
     *
     * @throws \RuntimeException
     * @return bool
     */
    private function isValidLicenseString($license)
    {
        $tokens = array(
            'po' => '\(',
            'pc' => '\)',
            'op' => '(?:or|OR|and|AND)',
            'wi' => '(?:with|WITH)',
            'lix' => '(?:NONE|NOASSERTION)',
            'lir' => 'LicenseRef-\d+',
            'lic' => '[-_.a-zA-Z0-9]{3,}\+?',
            'ws' => '\s+',
            '_' => '.',
        );

        $next = function () use ($license, $tokens) {
            static $offset = 0;

            if ($offset >= strlen($license)) {
                return;
            }

            foreach ($tokens as $name => $token) {
                if (false === $r = preg_match('{' . $token . '}', $license, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                    throw new \RuntimeException('Pattern for token %s failed (regex error).', $name);
                }
                if ($r === 0) {
                    continue;
                }
                if ($matches[0][1] !== $offset) {
                    continue;
                }
                $offset += strlen($matches[0][0]);

                return array($name, $matches[0][0]);
            }

            throw new \RuntimeException(
                'At least the last pattern needs to match, but it did not (dot-match-all is missing?).'
            );
        };

        $open = 0;
        $with = false;
        $require = true;
        $lastop = null;

        while (list($token, $string) = $next()) {
            switch ($token) {
                case 'po':
                    if ($open || !$require || $with) {
                        return false;
                    }
                    $open = 1;
                    break;
                case 'pc':
                    if ($open !== 1 || $require || !$lastop || $with) {
                        return false;
                    }
                    $open = 2;
                    break;
                case 'op':
                    if ($require || !$open || $with) {
                        return false;
                    }
                    $lastop || $lastop = $string;
                    if ($lastop !== $string) {
                        return false;
                    }
                    $require = true;
                    break;
                case 'wi':
                    $with = true;
                    break;
                case 'lix':
                    if ($open || $with) {
                        return false;
                    }
                    goto lir;
                case 'lic':
                    if ($with && $this->isValidExceptionIdentifier($string)) {
                        $require = true;
                        $with = false;
                        goto lir;
                    }
                    if ($with) {
                        return false;
                    }
                    if (!$this->isValidLicenseIdentifier(rtrim($string, '+'))) {
                        return false;
                    }
                    // Fall-through intended
                case 'lir':
                    lir:
                    if (!$require) {
                        return false;
                    }
                    $require = false;
                    break;
                case 'ws':
                    break;
                case '_':
                    return false;
                default:
                    throw new \RuntimeException(sprintf('Unparsed token: %s.', print_r($token, true)));
            }
        }

        return !($open % 2 || $require || $with);
    }
}
