<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Unit;

use Illuminate\Support\Facades\Queue;
use Maged\SecureMediaUpload\Contracts\PostUploadProcessor;
use Maged\SecureMediaUpload\Jobs\RunPostUploadProcessorJob;
use Maged\SecureMediaUpload\Processing\PostUploadPipeline;
use Maged\SecureMediaUpload\Results\UploadResult;
use Maged\SecureMediaUpload\Tests\Fixtures\SpyPostUploadProcessor;
use Maged\SecureMediaUpload\Tests\TestCase;

class PostUploadPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SpyPostUploadProcessor::reset();
    }

    public function test_sync_pipeline_invokes_processor_when_enabled(): void
    {
        config()->set('secure-media-upload.post_upload.enabled', true);
        config()->set('secure-media-upload.post_upload.dispatch', 'sync');

        $pipeline = new PostUploadPipeline(new SpyPostUploadProcessor());
        $pipeline->dispatch($this->uploadResult(), 'image', 'testing');

        $this->assertCount(1, SpyPostUploadProcessor::$calls);
        $this->assertSame('image', SpyPostUploadProcessor::$calls[0]['context']['type']);
    }

    public function test_queue_pipeline_dispatches_job_when_enabled(): void
    {
        Queue::fake();
        config()->set('secure-media-upload.post_upload.enabled', true);
        config()->set('secure-media-upload.post_upload.dispatch', 'queue');

        $pipeline = new PostUploadPipeline(new SpyPostUploadProcessor());
        $pipeline->dispatch($this->uploadResult(), 'image', 'testing');

        Queue::assertPushed(RunPostUploadProcessorJob::class);
        $this->assertCount(0, SpyPostUploadProcessor::$calls);
    }

    public function test_pipeline_is_skipped_when_disabled(): void
    {
        config()->set('secure-media-upload.post_upload.enabled', false);

        $pipeline = new PostUploadPipeline(new SpyPostUploadProcessor());
        $pipeline->dispatch($this->uploadResult(), 'image', 'testing');

        $this->assertCount(0, SpyPostUploadProcessor::$calls);
    }

    public function test_processor_binding_uses_configured_processor_class(): void
    {
        config()->set('secure-media-upload.post_upload.processor', SpyPostUploadProcessor::class);

        $this->assertInstanceOf(SpyPostUploadProcessor::class, app(PostUploadProcessor::class));
    }

    private function uploadResult(): UploadResult
    {
        return new UploadResult(
            path: 'uploads/images/a.jpg',
            url: 'uploads/images/a.jpg',
            name: 'a.jpg',
            extension: 'jpg',
            originalName: 'a.jpg',
            mimeType: 'image/jpeg',
            sizeBytes: 123,
            duration: null,
            hash: null,
        );
    }
}


