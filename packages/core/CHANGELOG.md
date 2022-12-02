# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## 0.1.5

### Fixed

- Cart manager will now check for active order before associating and merging carts.
- Price update will now correctly save the compare price.

## 0.1.4

### Fixed

- Prices are now stored as `bigInt` to avoid out of range errors for certain currencies.

## 0.1.3

### Fixed

- Transaction activity log should now store the correct properties.

### Added

- Added user emails to customer index on search.

## 0.1.2

### Added

- Added shorthand `attr` which can be used instead of `translateAttribute`

## 0.1.1

> No notable changes

## 0.1.0

> No notable changes

## 0.1.0-rc.5

### Changed

- Updated publishing tag to be more consistent

## 0.1.0-rc.4

### Fixed

- Lunar migration command will now add brands into the correct table.
- Added additional check for language existence when running the brand update state class.
- Publishing commands now use a consistent syntax.

## 0.1.0-rc.3

> No notable changes

## 0.1.0-rc.2

> No notable changes

## 0.1.0-rc.1

Initial release.
