# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Fixed
- Fixed [Issue 19](https://github.com/getcandy/getcandy/issues/19)
- Fixed [Issue 18](https://github.com/getcandy/getcandy/issues/18), wrong name on Activity Log
- Fixed [Issue 23](https://github.com/getcandy/getcandy/issues/23)

### Changed
- Removed requirement to specify `staff` guard in `config/auth.php`. This can be safely removed.

## 2.0-beta4 - 2021-12-24
### Fixed
- Fixed group by statement on dashboard query by [@itcyborg](https://github.com/itcyborg)
- Fixed support for PostgreSQL on dashboard queries

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta3...2.0-beta4)

## 2.0-beta3 - 2021-12-23
### Fixed
- Fixed issue where a staffs password confirmation wasn't working correctly.
- There was an issue deleting a nested collection, this has been resolved.
- Translation fixes various screens.

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta2...2.0-beta3)

### Changed
- UX tweaks to option selector when creating/editing product variants.
- Translatable inputs will now only show translations when you have more than one language.

## 2.0-beta2 - 2021-12-23
### Fixed
- Fixed issue where compare price was set to 0 by default, causing validation issues.
- Unit quantity validation messages now show when editing a product.
- Fixed issue where dialog modals appeared behind the overlay, rendering them unusable.

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta...2.0-beta2)

## 2.0-beta - 2021-12-22

Initial release.
