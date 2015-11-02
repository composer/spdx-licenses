# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Changed
- added github diff links to versions in changelog.
- ran php-cs-fixer for style fixes.
- updated .travis.yml, composer.json and .php_cs files.

## [1.1.2] - 2015-10-05

### Changed
- added new licenses.

## [1.1.1] - 2015-09-07

### Fixed
- improved performance when looking up just one license.
- updated licenses/exceptions.

## [1.1.0] - 2015-07-17

### Changed
- updater now sorts licenses and exceptions by key.
- filenames now class constants of SpdxLicenses (`LICENSES_FILE` and `EXCEPTIONS_FILE`).
- resources directory now available via static method `SpdxLicenses::getResourcesDir()`.

### Fixed
- updated licenses/exceptions.
- removed json-schema from composer requirements.

## [1.0.0] - 2015-07-15

### Changed
- BC: the following classes and namespaces were renamed:
    - Namespace: `Composer\Util` -> `Composer\Spdx`
    - Classname: `SpdxLicense` -> `SpdxLicenses`
    - Classname: `SpdxLicenseTest` -> `SpdxLicensesTest`
    - Classname: `Updater` -> `SpdxLicensesUpdater`
- validation via regex implementation instead of lexer.
- code style using php-cs-fixer.


[1.1.2]: https://github.com/composer/spdx-licenses/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/composer/spdx-licenses/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/composer/spdx-licenses/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/composer/spdx-licenses/compare/0281a7fe7820c990db3058844e7d448d7b70e3ac...1.0.0
