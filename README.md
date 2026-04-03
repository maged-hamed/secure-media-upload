# Secure Media Upload

[![Tests](https://github.com/maged-hamed/secure-media-upload/workflows/Tests/badge.svg)](https://github.com/maged-hamed/secure-media-upload/actions)

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

Add to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/maged-hamed/secure-media-upload.git"
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
echo $result->extension;   // jpg
echo $result->mimeType;    // image/jpeg
echo $result->sizeBytes;   // 12345
```

### Using the Facade

```php
use Maged\SecureMediaUpload\Facades\SecureMediaUpload;

$result = SecureMediaUpload::secureFileUpload($request->file('file'), 'image');
```

### Backward-Compatible Helpers

```php
$result = secureFileUpload($request->file('file'), 'image');
// Returns array: ['path', 'url', 'name', 'original_name', 'mime', 'size', 'duration']
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

## Supported File Types

| Type | Max Size | Extensions |
|------|----------|-----------|
| image | 10 MB | jpg, jpeg, png, gif, webp, svg |
| video | 250 MB | mp4, mov, avi |
| document | 25 MB | pdf, doc, docx, txt, rtf |
| excel | 20 MB | xls, xlsx, csv |
| audio | 30 MB | mp3, wav, ogg, m4a |
| compressed | 200 MB | zip, rar, 7z, gz, tar |

## Error Codes

| Code | Meaning |
|------|---------|
| `UNSUPPORTED_TYPE` | File type not in allow-list |
| `INVALID_EXTENSION` | Extension rejected |
| `MIME_MISMATCH` | Client MIME rejected |
| `REAL_MIME_MISMATCH` | Actual file content MIME rejected |
| `UNSAFE_SVG_DETECTED` | SVG contains script/event/url patterns |
| `SIZE_EXCEEDED` | File too large |
| `UNREADABLE_FILE` | Temp file inaccessible |
| `STORAGE_FAILURE` | Upload to disk failed |

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=secure-media-upload-config
```

Set the storage disk in `.env`:

```env
# Local disk
FILESYSTEM_DISK=local

# AWS S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

## Testing

```bash
composer install
./vendor/bin/phpunit
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT — see [LICENSE](LICENSE).
