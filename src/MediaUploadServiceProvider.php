<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload;

use Illuminate\Support\ServiceProvider;

class MediaUploadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/secure-media-upload.php', 'secure-media-upload');

        $this->app->singleton(SecureMediaUploader::class, fn () => new SecureMediaUploader());
        $this->app->alias(SecureMediaUploader::class, 'secure-media-upload');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/secure-media-upload.php' => config_path('secure-media-upload.php'),
        ], 'secure-media-upload-config');
    }
}

