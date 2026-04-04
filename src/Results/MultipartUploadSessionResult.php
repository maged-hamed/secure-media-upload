<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Results;

final readonly class MultipartUploadSessionResult
{
    public function __construct(
        public string $disk,
        public string $bucket,
        public string $key,
        public string $uploadId,
    ) {
    }

    public function toArray(): array
    {
        return [
            'disk' => $this->disk,
            'bucket' => $this->bucket,
            'key' => $this->key,
            'upload_id' => $this->uploadId,
        ];
    }
}

