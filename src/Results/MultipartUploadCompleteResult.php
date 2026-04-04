<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Results;

final readonly class MultipartUploadCompleteResult
{
    public function __construct(
        public string $disk,
        public string $bucket,
        public string $key,
        public ?string $location,
        public ?string $etag,
        public ?string $versionId,
    ) {
    }

    public function toArray(): array
    {
        return [
            'disk' => $this->disk,
            'bucket' => $this->bucket,
            'key' => $this->key,
            'location' => $this->location,
            'etag' => $this->etag,
            'version_id' => $this->versionId,
        ];
    }
}

