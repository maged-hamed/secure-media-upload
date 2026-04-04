<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Unit;

use InvalidArgumentException;
use Maged\SecureMediaUpload\Contracts\MultipartSessionManager;
use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Tests\Fixtures\FakeMultipartSessionManager;
use Maged\SecureMediaUpload\Tests\TestCase;

class MultipartSessionApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FakeMultipartSessionManager::reset();
        config()->set('secure-media-upload.multipart.default_disk', 's3');

        $this->app->singleton(MultipartSessionManager::class, fn () => new FakeMultipartSessionManager());
        $this->app->singleton(SecureMediaUploader::class, fn ($app) => new SecureMediaUploader(
            postUploadPipeline: $app->make(\Maged\SecureMediaUpload\Processing\PostUploadPipeline::class),
            multipartSessionManager: $app->make(MultipartSessionManager::class),
        ));
    }

    public function test_start_upload_session_returns_session_result(): void
    {
        $result = app(SecureMediaUploader::class)->startUploadSession(
            key: 'uploads/large/file.mp4',
            contentType: 'video/mp4',
            metadata: ['owner' => 'user-1'],
        );

        $this->assertSame('upload-id-123', $result->uploadId);
        $this->assertSame('uploads/large/file.mp4', $result->key);
    }

    public function test_sign_upload_part_rejects_invalid_part_number(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SecureMediaUploader::class)->signUploadPart(
            uploadId: 'upload-id-123',
            key: 'uploads/large/file.mp4',
            partNumber: 0,
        );
    }

    public function test_complete_upload_session_requires_parts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SecureMediaUploader::class)->completeUploadSession(
            uploadId: 'upload-id-123',
            key: 'uploads/large/file.mp4',
            parts: [],
        );
    }

    public function test_abort_upload_session_returns_true(): void
    {
        $result = app(SecureMediaUploader::class)->abortUploadSession(
            uploadId: 'upload-id-123',
            key: 'uploads/large/file.mp4',
        );

        $this->assertTrue($result);
    }
}

