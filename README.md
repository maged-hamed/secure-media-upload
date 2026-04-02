# Secure Media Upload

[![Tests](https://github.com/maged/secure-media-upload/workflows/Tests/badge.svg)](https://github.com/maged/secure-media-upload/actions)

Secure, configurable file uploads for Laravel with strict server-side validation, private-by-default storage, and local/S3 support.

## Features

- **Strict Validation**: Extension + MIME + real MIME type checking with `finfo`
- **Security First**: SVG/malicious pattern filtering, private visibility by default
- **Storage Flexibility**: Seamless switching between local and S3 via config
- **Type-Safe Results**: Structured response objects instead of loose arrays
- **Error Codes**: Enumerated error codes for API consumers
- **Backward Compatible**: Helper functions for gradual migration
- **Tested**: Comprehensive unit and feature tests

## Installation

From GitHub:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/maged/secure-media-upload.git"
    }
  ],
  "require": {
    "maged/secure-media-upload": "^0.1"
  }
}
```

Then:

```bash
composer install
php artisan vendor:publish --tag=secure-media-upload-config
```

## Quick Start

### Using the Service

```php
use Maged\SecureMediaUpload\SecureMediaUploader;

$result = app(SecureMediaUploader::class)->secureFileUpload(
    file: $request->file('file'),
    type: 'image',
    storagePath: 'uploads/images',
    disk: 's3'
);

echo $result->path;        // uploads/images/ulid_image.jpg
echo $result->url;         // https://s3.../uploads/images/ulid_image.jpg
echo $result->mimeType;    // image/jpeg
echo $result->sizeBytes;   // 12345
```

### Error Handling

```php
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;

try {
    $result = app(SecureMediaUploader::class)->secureFileUpload($file, 'image');
} catch (UploadValidationException $e) {
    echo $e->errorCode->value;  // 'SIZE_EXCEEDED', 'UNSAFE_SVG_DETECTED', etc.
    return response()->json(['error' => $e->getMessage()], 422);
}
```

## Error Codes

- `UNSUPPORTED_TYPE` - File type not allowed
- `INVALID_EXTENSION` - Extension not in whitelist
- `MIME_MISMATCH` - Client MIME rejected
- `REAL_MIME_MISMATCH` - Actual file MIME rejected
- `UNSAFE_SVG_DETECTED` - SVG has dangerous patterns
- `SIZE_EXCEEDED` - File too large
- `UNREADABLE_FILE` - Temp file inaccessible
- `STORAGE_FAILURE` - Upload to disk failed

## Configuration

```bash
php artisan vendor:publish --tag=secure-media-upload-config
```

Edit `config/secure-media-upload.php` to customize allowed types, sizes, and storage disk.

## Testing

```bash
composer test
```

## License

MIT

