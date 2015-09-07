# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 1.1.1 - 2015-09-07

### Fixed
- improved performance when looking up just one license.
- updated licenses/exceptions.

## 1.1.0 - 2015-07-17

### Changed
- updater now sorts licenses and exceptions by key.
- filenames now class constants of SpdxLicenses (`LICENSES_FILE` and `EXCEPTIONS_FILE`).
- resources directory now available via static method `SpdxLicenses::getResourcesDir()`.

### Fixed
- updated licenses/exceptions.
- removed json-schema from composer requirements.

## 1.0.0 - 2015-07-15

### Changed
- BC: the following classes and namespaces were renamed:
    - Namespace: `Composer\Util` -> `Composer\Spdx`
    - Classname: `SpdxLicense` -> `SpdxLicenses`
    - Classname: `SpdxLicenseTest` -> `SpdxLicensesTest`
    - Classname: `Updater` -> `SpdxLicensesUpdater`
- validation via regex implementation instead of lexer.
- code style using php-cs-fixer.

