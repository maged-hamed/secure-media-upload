<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Maged\SecureMediaUpload\MediaUploadServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('filesystems.disks.testing', [
            'driver' => 'local',
            'root' => storage_path('testing'),
            'url' => 'http://localhost/testing',
            'visibility' => 'private',
        ]);

        $app['config']->set('secure-media-upload', require __DIR__ . '/../config/secure-media-upload.php');
    }
}
