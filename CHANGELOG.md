# Changelog

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

## [2.0.0] - 2025-12-16

### Changed
- refactor!: remove .htaccess manipulation, use inline JavaScript
- remove console.log expectations from Jest tests

## [2.0.0] - 2025-12-16

### Removed
- **BREAKING:** Removed .htaccess manipulation (was an anti-pattern causing webserver compatibility issues)
- **BREAKING:** Removed external `js/tab-swap.js` file (JavaScript is now inline)

### Added
- Inline JavaScript injection (no webserver configuration required)
- MutationObserver pattern for reliable DOM detection (replaces triple-init fallback)
- CSP Nonce support (future-proof for stricter osTicket CSP policies)
- Version validation against XSS attacks
- Return type declarations (PHP 7.0+ standard)
- 16 new PHP unit tests for inline script and version tracking

### Changed
- Logging now only in DEBUG mode (fixes information disclosure)
- JavaScript is now self-contained and portable
- Plugin now works on nginx/IIS/Caddy without modifications

### Security
- Fixed information disclosure via error_log in production
- Added XSS prevention for version field
- Prepared CSP Nonce support for stricter Content Security Policies

## [1.0.2] - 2025-11-08

### Changed
- added folder to .gitignore
- Remove console warning from production code
- remove debug error_log statements from production code

## [1.0.1] - 2025-11-06

### Changed
- Reset CHANGELOG after deleting v1.0.1 release
- Remove debug console logs from production code
- Add build artifacts to .gitignore

### Fixed
- Resolve tab swap not working on first page load

## [1.0.0] - 2025-11-05

### Changed
- Reset CHANGELOG for fresh 1.0.0 release
- Simplify README and add FAQ page links
- Add CI workflow with test fixes and PHP 7.4+ support
- Intital Release

### Fixed
- Remove empty Integration test suite from phpunit.xml
- Ensure composer.lock is PHP 7.4 compatible
