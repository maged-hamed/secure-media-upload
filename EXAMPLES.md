# Usage Examples

Complete examples showing how to use the `maged/secure-media-upload` package in your Laravel application.

## Example 1: Simple Image Upload

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;

class AvatarController extends Controller
{
    public function store(Request $request, SecureMediaUploader $uploader)
    {
        try {
            $result = $uploader->secureFileUpload(
                file: $request->file('avatar'),
                type: 'image',
                storagePath: 'avatars',
                disk: 's3'
            );

            return response()->json([
                'message' => 'Avatar uploaded successfully',
                'url' => $result->url,
                'path' => $result->path,
            ]);
        } catch (UploadValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->errorCode->value,
            ], 422);
        }
    }
}
```

## Example 2: File Upload with API Response

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maged\SecureMediaUpload\Facades\SecureMediaUpload;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;

class DocumentController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        try {
            $result = SecureMediaUpload::secureFileUpload(
                file: $request->file('file'),
                type: 'document',
                storagePath: 'documents'
            );

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
            ]);
        } catch (UploadValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->errorCode->value,
            ], 422);
        }
    }
}
```

## Example 3: Video Upload with Queue Processing

```php
<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoUpload;
use Illuminate\Http\Request;
use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;

class VideoController extends Controller
{
    public function upload(Request $request, SecureMediaUploader $uploader)
    {
        try {
            $result = $uploader->secureFileUpload(
                file: $request->file('video'),
                type: 'video',
                storagePath: 'videos',
                disk: 's3'
            );

            // Queue background processing
            ProcessVideoUpload::dispatch($result->path);

            return response()->json([
                'message' => 'Video uploaded and queued for processing',
                'result' => $result->toArray(),
            ]);
        } catch (UploadValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
```

## Example 4: Validation Only (No Upload)

```php
<?php

use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;

$uploader = app(SecureMediaUploader::class);

try {
    // Just validate, don't upload yet
    $validation = $uploader->validateFileOnly($file, 'image');
    
    echo "Extension: " . $validation->extension;
    echo "Real MIME: " . $validation->realMimeType;
    echo "Size: " . $validation->sizeBytes . " bytes";
    echo "Original: " . $validation->originalName;
    
} catch (UploadValidationException $e) {
    echo "Validation failed: " . $e->getMessage();
}
```

## Example 5: Multi-Type Upload Handler

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;

class MediaController extends Controller
{
    public function upload(Request $request, SecureMediaUploader $uploader)
    {
        $request->validate([
            'file' => 'required|file',
            'type' => 'required|in:image,video,document,excel,audio,compressed',
        ]);

        try {
            $result = $uploader->secureFileUpload(
                file: $request->file('file'),
                type: $request->input('type'),
                storagePath: 'media/' . $request->input('type'),
            );

            return response()->json([
                'success' => true,
                'path' => $result->path,
                'url' => $result->url,
                'name' => $result->name,
                'size' => $result->sizeBytes,
                'mime' => $result->mimeType,
            ]);
        } catch (UploadValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->errorCode->value,
            ], 422);
        }
    }
}
```

## Example 6: Background Job for Processing

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maged\SecureMediaUpload\SecureMediaUploader;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $videoPath,
    ) {}

    public function handle(SecureMediaUploader $uploader): void
    {
        // Get signed URL for processing
        $url = $uploader->storageFileUrl($this->videoPath, 's3', 120);

        // TODO: Send to video processor (FFmpeg, etc)
        logger()->info("Processing video: {$this->videoPath}");

        // Example: Generate thumbnail (pseudo code)
        // $thumbnail = generateThumbnail($url);
        // $thumbResult = $uploader->secureFileUpload($thumbnail, 'image', 'thumbnails', 's3');
    }
}
```

## Example 7: Store Upload Metadata in Database

```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;

class DocumentController extends Controller
{
    public function store(Request $request, SecureMediaUploader $uploader)
    {
        try {
            $result = $uploader->secureFileUpload(
                file: $request->file('document'),
                type: 'document',
                storagePath: 'documents',
            );

            // Save metadata to database
            $document = Document::create([
                'user_id' => auth()->id(),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'file_path' => $result->path,
                'file_url' => $result->url,
                'file_name' => $result->name,
                'original_name' => $result->originalName,
                'mime_type' => $result->mimeType,
                'file_size_bytes' => $result->sizeBytes,
            ]);

            return response()->json([
                'message' => 'Document saved successfully',
                'document' => $document,
            ]);
        } catch (UploadValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
```

## Example 8: Error Handling with Error Codes

```php
<?php

use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;
use Maged\SecureMediaUpload\Exceptions\ErrorCode;

$uploader = app(SecureMediaUploader::class);

try {
    $result = $uploader->secureFileUpload($file, 'image');
} catch (UploadValidationException $e) {
    match($e->errorCode) {
        ErrorCode::SIZE_EXCEEDED => response()->json([
            'error' => 'File is too large',
            'details' => $e->getMessage(),
        ], 422),
        
        ErrorCode::UNSAFE_SVG_DETECTED => response()->json([
            'error' => 'SVG contains unsafe content',
        ], 422),
        
        ErrorCode::MIME_MISMATCH => response()->json([
            'error' => 'File type does not match its content',
        ], 422),
        
        default => response()->json([
            'error' => 'Upload failed: ' . $e->errorCode->value,
        ], 422),
    };
}
```

## Example 9: Custom Storage Path

```php
<?php

use Maged\SecureMediaUpload\SecureMediaUploader;

$uploader = app(SecureMediaUploader::class);

// Upload to custom nested path
$result = $uploader->secureFileUpload(
    file: $request->file('image'),
    type: 'image',
    storagePath: 'uploads/users/' . auth()->id() . '/avatars',
    disk: 's3'
);

echo $result->path; // uploads/users/123/avatars/ulid_image.jpg
```

## Example 10: Using Both Local and S3

```php
<?php

use Maged\SecureMediaUpload\SecureMediaUploader;

$uploader = app(SecureMediaUploader::class);

// Upload to local disk
$localResult = $uploader->secureFileUpload(
    file: $request->file('file'),
    type: 'document',
    storagePath: 'local_uploads',
    disk: 'local'
);

// Upload to S3
$s3Result = $uploader->secureFileUpload(
    file: $request->file('file'),
    type: 'document',
    storagePath: 'remote_uploads',
    disk: 's3'
);

// Get signed URL for S3 file (expires in 60 minutes)
$url = $uploader->storageFileUrl($s3Result->path, 's3', 60);
```

## Configuration Example

In `config/secure-media-upload.php`:

```php
return [
    'default_disk' => env('SECURE_MEDIA_UPLOAD_DISK', 'local'),
    'temporary_url_ttl' => 60, // minutes
    
    'types' => [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'max_bytes' => 10 * 1024 * 1024, // 10MB
        ],
        // ... other types
    ],
];
```

