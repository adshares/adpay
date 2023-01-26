# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.5.0] - 2022-01-25
### Added
- Fetch reports
### Changed
- URL to fetch payments
- Data key in fetch payments response
### Fixed
- Remove logging 4XX errors

## [1.4.2] - 2022-12-15
### Fixed
- Default medium value

## [1.4.1] - 2022-12-13
### Added
- Medium and vendor support

## [1.4.0] - 2022-07-05
### Changed
- Support PHP 8.1
- Upgrade to Symfony 5.4

## [1.3.3] - 2022-02-23
### Added
- Support model type banners

## [1.3.2] - 2022-02-16
### Added
- Support video type banners

## [1.3.1] - 2022-01-28
### Changed
- Automatic CPM tweaking

## [1.3.0] - 2022-01-17
### Added
- Automatic maximal CPM calculation

## [1.2.2] - 2021-10-08
### Changed
- Allow rank greater than 0, default values for any and missing category

## [1.2.1] - 2021-06-29
### Fixed
- Symfony configuration

## [1.2.0] - 2021-06-28
### Changed
- Upgrade to PHP 7.4
- Upgrade to Composer 2
- Upgrade to Symfony 5
### Fixed
- Scale CPM for the same user

## [1.1.4] - 2021-06-01
### Added
- Scale CPM
- Bid strategy soft delete/update

## [1.1.3] - 2020-01-07
### Added 
- CPA only pages support

## [1.1.2] - 2019-12-18
### Fixed 
- Campaign expiration time support

## [1.1.1] - 2019-12-03
### Changed 
- Case time support
- Lowering the human score threshold for conversions

## [1.1.0] - 2019-12-02
### Added 
- Pops support
- Direct banners support
### Removed
- Conversion limit

## [1.0.0] - 2019-10-25
### Added
- Conversions support
### Changed
- Rewriting from Python to PHP


[Unreleased]: https://github.com/adshares/adpay/compare/v1.2.0...HEAD
[1.5.0]: https://github.com/adshares/adpay/compare/v1.4.2...v1.5.0
[1.4.2]: https://github.com/adshares/adpay/compare/v1.4.1...v1.4.2
[1.4.1]: https://github.com/adshares/adpay/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/adshares/adpay/compare/v1.3.3...v1.4.0
[1.3.3]: https://github.com/adshares/adpay/compare/v1.3.2...v1.3.3
[1.3.2]: https://github.com/adshares/adpay/compare/v1.3.1...v1.3.2
[1.3.1]: https://github.com/adshares/adpay/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/adshares/adpay/compare/v1.2.2...v1.3.0
[1.2.2]: https://github.com/adshares/adpay/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/adshares/adpay/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/adshares/adpay/compare/v1.1.4...v1.2.0
[1.1.4]: https://github.com/adshares/adpay/compare/v1.1.3...v1.1.4
[1.1.3]: https://github.com/adshares/adpay/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/adshares/adpay/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/adshares/adpay/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/adshares/adpay/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/adshares/adpay/compare/v0.1.0...v1.0.0
[0.1.0]: https://github.com/adshares/adpay/releases/tag/v0.1.0
