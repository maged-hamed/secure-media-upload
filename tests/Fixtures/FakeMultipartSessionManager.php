<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Fixtures;

use Maged\SecureMediaUpload\Contracts\MultipartSessionManager;
use Maged\SecureMediaUpload\Results\MultipartUploadCompleteResult;
use Maged\SecureMediaUpload\Results\MultipartUploadPartResult;
use Maged\SecureMediaUpload\Results\MultipartUploadSessionResult;

class FakeMultipartSessionManager implements MultipartSessionManager
{
    public static array $calls = [];

    public function startSession(string $disk, string $key, string $contentType, array $metadata = []): MultipartUploadSessionResult
    {
        self::$calls[] = ['method' => 'start', 'disk' => $disk, 'key' => $key, 'content_type' => $contentType, 'metadata' => $metadata];

        return new MultipartUploadSessionResult(
            disk: $disk,
            bucket: 'test-bucket',
            key: $key,
            uploadId: 'upload-id-123',
        );
    }

    public function signPart(string $disk, string $key, string $uploadId, int $partNumber, ?int $ttlMinutes = null): MultipartUploadPartResult
    {
        self::$calls[] = ['method' => 'sign', 'disk' => $disk, 'key' => $key, 'upload_id' => $uploadId, 'part' => $partNumber, 'ttl' => $ttlMinutes];

        return new MultipartUploadPartResult(
            key: $key,
            uploadId: $uploadId,
            partNumber: $partNumber,
            url: 'https://example.test/signed-part-url',
            headers: [],
        );
    }

    public function completeSession(string $disk, string $key, string $uploadId, array $parts): MultipartUploadCompleteResult
    {
        self::$calls[] = ['method' => 'complete', 'disk' => $disk, 'key' => $key, 'upload_id' => $uploadId, 'parts' => $parts];

        return new MultipartUploadCompleteResult(
            disk: $disk,
            bucket: 'test-bucket',
            key: $key,
            location: 'https://example.test/final-object',
            etag: 'etag-123',
            versionId: null,
        );
    }

    public function abortSession(string $disk, string $key, string $uploadId): bool
    {
        self::$calls[] = ['method' => 'abort', 'disk' => $disk, 'key' => $key, 'upload_id' => $uploadId];

        return true;
    }

    public static function reset(): void
    {
        self::$calls = [];
    }
}

