# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added

- SHA-256 file hashing in `UploadResult` (configurable via `SECURE_MEDIA_HASH_ALGORITHM`)

### Fixed

- Corrected client MIME mismatch handling and messaging during validation

## [0.1.0] - 2026-04-02

### Added

- Initial release with core file upload functionality
- Strict multi-level validation (extension, MIME, real MIME via `finfo`)
- SVG safety scanning for script/event/url patterns
- Support for local and S3 storage backends
- Type-safe result objects (`UploadResult`, `ValidationResult`)
- Enumerated error codes (`ErrorCode`) for API responses
- Backward-compatible helper functions for gradual migration
- Service provider and facade for easy access
- Configurable file type policies with size limits
- Comprehensive test suite (unit + feature tests)
- GitHub Actions CI/CD workflow

### Security

- Private visibility by default
- Randomized filenames using ULID
- Server-side validation strictness
- HTML-escaped original filenames

