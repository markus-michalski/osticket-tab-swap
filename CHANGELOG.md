# Changelog - tab-swap

All notable changes to this plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Nothing yet

### Changed
- Nothing yet

### Fixed
- Nothing yet

## [1.0.1] - 2025-10-26

### Fixed
- Remove VisibilityConstraint causing JavaScript syntax error
## [1.0.0] - 2025-10-22

### Added
- Add .releaseinclude for tab-swap and api-key-wildcard
- Add .releaseignore for release packaging
- Auto-configure /include/.htaccess for plugin static assets
- Add Tab Swap plugin for osTicket 1.18.x

### Changed
- Reset all plugins to v0.9.0 for first official release
- revert: Rollback versehentliches v1.0.4 Release in CHANGELOG
- doc: added Support Development to Readme's
- Standardize README.md structure and add LICENSE files
- doc: added License to README.md and fixed README.md
- Update CHANGELOG for v1.0.2 release
- Clean up CHANGELOG for fresh v1.0.0 release
- Add Tab Swap plugin to README and release symlink
- Add comprehensive test suite
- Add jQuery-compatible mocking for Jest tests

### Fixed
- Move v1.0.2 content back to Unreleased
- Remove incorrect permission check blocking tab swap
- Use correct osTicket API for singleton instance creation
- Auto-create instance for singleton plugins on enable
- removed release symlink
