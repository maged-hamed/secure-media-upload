<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload;

use Illuminate\Support\ServiceProvider;
use Maged\SecureMediaUpload\Contracts\PostUploadProcessor;
use Maged\SecureMediaUpload\Processing\NullPostUploadProcessor;
use Maged\SecureMediaUpload\Processing\PostUploadPipeline;

class MediaUploadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/secure-media-upload.php', 'secure-media-upload');

        $this->app->bind(PostUploadProcessor::class, function ($app) {
            $processorClass = (string) config(
                'secure-media-upload.post_upload.processor',
                NullPostUploadProcessor::class
            );

            if (!is_a($processorClass, PostUploadProcessor::class, true)) {
                $processorClass = NullPostUploadProcessor::class;
            }

            return $app->make($processorClass);
        });

        $this->app->singleton(PostUploadPipeline::class, function ($app) {
            return new PostUploadPipeline($app->make(PostUploadProcessor::class));
        });

        $this->app->singleton(SecureMediaUploader::class, function ($app) {
            return new SecureMediaUploader($app->make(PostUploadPipeline::class));
        });
        $this->app->alias(SecureMediaUploader::class, 'secure-media-upload');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/secure-media-upload.php' => config_path('secure-media-upload.php'),
        ], 'secure-media-upload-config');
    }
}

