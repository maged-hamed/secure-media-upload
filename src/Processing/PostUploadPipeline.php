<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Processing;

use Maged\SecureMediaUpload\Contracts\PostUploadProcessor;
use Maged\SecureMediaUpload\Jobs\RunPostUploadProcessorJob;
use Maged\SecureMediaUpload\Results\UploadResult;

class PostUploadPipeline
{
    public function __construct(private readonly PostUploadProcessor $processor)
    {
    }

    public function dispatch(UploadResult $result, string $type, string $disk): void
    {
        if (!$this->isEnabledForType($type)) {
            return;
        }

        $context = [
            'type' => $type,
            'disk' => $disk,
        ];

        $mode = (string) config('secure-media-upload.post_upload.dispatch', 'sync');

        if ($mode === 'queue') {
            $job = new RunPostUploadProcessorJob($result->toArray(), $context);

            $connection = config('secure-media-upload.post_upload.queue_connection');
            if (is_string($connection) && $connection !== '') {
                $job->onConnection($connection);
            }

            $queue = config('secure-media-upload.post_upload.queue');
            if (is_string($queue) && $queue !== '') {
                $job->onQueue($queue);
            }

            dispatch($job);

            return;
        }

        try {
            $this->processor->process($result, $context);
        } catch (\Throwable $exception) {
            if ((bool) config('secure-media-upload.post_upload.fail_on_error', false)) {
                throw $exception;
            }

            logger()->warning('Post-upload processor failed: ' . $exception->getMessage());
        }
    }

    private function isEnabledForType(string $type): bool
    {
        if (!(bool) config('secure-media-upload.post_upload.enabled', false)) {
            return false;
        }

        $types = config('secure-media-upload.post_upload.types', []);
        if (!is_array($types) || $types === []) {
            return true;
        }

        return in_array($type, $types, true);
    }
}

