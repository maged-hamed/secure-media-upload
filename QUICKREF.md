# Quick Reference Sheet

## Installation

```bash
# 1. Add to composer.json repositories
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

# 2. Install package
composer install

# 3. Publish config
php artisan vendor:publish --tag=secure-media-upload-config
```

## Basic Usage

### Service Class (Recommended)
```php
use Maged\SecureMediaUpload\SecureMediaUploader;

$uploader = app(SecureMediaUploader::class);
$result = $uploader->secureFileUpload($file, 'image', 'uploads/images', 's3');

// Access result
$result->path;           // uploads/images/ulid_image.jpg
$result->url;            // https://s3.../uploads/images/ulid_image.jpg
$result->mimeType;       // image/jpeg
$result->sizeBytes;      // 12345
$result->duration;       // For videos
$result->hash;           // Reserved for future use
```

### Using Facade
```php
use Maged\SecureMediaUpload\Facades\SecureMediaUpload;

$result = SecureMediaUpload::secureFileUpload($file, 'image');
```

### Helper Functions (BC)
```php
$result = secureFileUpload($file, 'image');                    // array
$info = validateFileOnly($file, 'image');                      // array
$url = storage_file_url($path, 's3', 60);                      // ?string
$normalized = storage_disk_path($path, 's3');                  // string
```

## File Types

| Type | Max Size | Extensions | Example |
|------|----------|-----------|---------|
| image | 10MB | jpg, png, gif, webp, svg | photos, avatars |
| video | 250MB | mp4, mov, avi | movies, uploads |
| document | 25MB | pdf, doc, docx, txt, rtf | reports, forms |
| excel | 20MB | xls, xlsx, csv | data, sheets |
| audio | 30MB | mp3, wav, ogg, m4a | music, recordings |
| compressed | 200MB | zip, rar, 7z, gz, tar | archives |

## Error Handling

```php
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;
use Maged\SecureMediaUpload\Exceptions\ErrorCode;

try {
    $result = $uploader->secureFileUpload($file, 'image');
} catch (UploadValidationException $e) {
    // Error codes:
    // UNSUPPORTED_TYPE, INVALID_EXTENSION, MIME_MISMATCH,
    // REAL_MIME_MISMATCH, UNSAFE_SVG_DETECTED, SIZE_EXCEEDED,
    // UNREADABLE_FILE, STORAGE_FAILURE
    
    $e->errorCode;      // ErrorCode enum
    $e->getMessage();   // Human-readable message
}
```

## Storage Disks

```env
# Use local storage
FILESYSTEM_DISK=local

# Use S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

## Common Patterns

### Pattern 1: Validate Only
```php
$validation = $uploader->validateFileOnly($file, 'image');
// Returns: ValidationResult
```

### Pattern 2: Store in Database
```php
$result = $uploader->secureFileUpload($file, 'image');
Model::create([
    'file_path' => $result->path,
    'file_url' => $result->url,
    'mime_type' => $result->mimeType,
    'size_bytes' => $result->sizeBytes,
]);
```

### Pattern 3: Queue Processing
```php
$result = $uploader->secureFileUpload($file, 'video');
ProcessVideo::dispatch($result->path);  // Queue job
```

### Pattern 4: Multiple Disks
```php
// Save to local + S3
$local = $uploader->secureFileUpload($file, 'image', 'uploads', 'local');
$s3 = $uploader->secureFileUpload($file, 'image', 'uploads', 's3');
```

### Pattern 5: User-Specific Path
```php
$result = $uploader->secureFileUpload(
    $file,
    'image',
    'users/' . auth()->id() . '/avatars',
    's3'
);
```

## Configuration

```php
// config/secure-media-upload.php
return [
    'default_disk' => env('SECURE_MEDIA_UPLOAD_DISK', env('FILESYSTEM_DISK', 'local')),
    'temporary_url_ttl' => (int) env('SECURE_MEDIA_TEMP_URL_TTL', 60),
    
    'types' => [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'mime_types' => ['image/jpeg', 'image/png', ...],
            'max_bytes' => 10 * 1024 * 1024,
        ],
        // ... other types
    ],
];
```

## Testing

```bash
# Run all tests
composer test

# With coverage
./vendor/bin/phpunit --coverage-html coverage/

# Specific test
./vendor/bin/phpunit tests/Unit/ValidationTest.php
```

## Publishing to GitHub

```bash
cd packages/secure-media-upload

# Run the push script
./push-to-github.sh

# Or manually:
git init
git add .
git commit -m "Initial release"
git remote add origin git@github.com:maged/secure-media-upload.git
git branch -M main
git push -u origin main
git tag -a v0.1.0 -m "Initial release"
git push origin v0.1.0
```

## API Response Format

```json
{
  "path": "uploads/images/01ar5zuvs41lqw3sfv1c7q8j0a_image.jpg",
  "url": "https://s3.amazonaws.com/bucket/uploads/images/01ar5zuvs41lqw3sfv1c7q8j0a_image.jpg",
  "name": "01ar5zuvs41lqw3sfv1c7q8j0a_image.jpg",
  "original_name": "my_photo.jpg",
  "mime_type": "image/jpeg",
  "size_bytes": 245632,
  "duration": null,
  "hash": null
}
```

## Security Features Checklist

✅ Extension validation  
✅ MIME type validation (client declared)  
✅ Real MIME validation (finfo)  
✅ SVG malicious pattern detection  
✅ File size limits per type  
✅ Private visibility by default  
✅ Randomized filenames (ULID)  
✅ HTML-escaped original names  
✅ No path traversal allowed  
✅ Typed error responses  

## Troubleshooting

### "ext-fileinfo is missing"
```bash
# Install PHP fileinfo extension
# macOS with Homebrew
brew install php@8.3-fileinfo

# Or use Ubuntu
apt-get install php-fileinfo
```

### "Config file not found"
```bash
php artisan vendor:publish --tag=secure-media-upload-config
```

### "S3 upload fails"
Check your `.env` has:
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `FILESYSTEM_DISK=s3`

### "Files not uploading to S3"
Ensure your S3 bucket:
- Exists in the configured region
- Has appropriate IAM permissions
- CORS is configured if needed

## Next Steps (v0.2+)

- Chunked upload sessions
- Direct S3 multipart presigned URLs
- Queue-based post-processing
- Content hashing & deduplication
- Video metadata extraction
- Image optimization

