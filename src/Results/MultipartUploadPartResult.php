<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Results;

final readonly class MultipartUploadPartResult
{
    /**
     * @param array<string,string> $headers
     */
    public function __construct(
        public string $key,
        public string $uploadId,
        public int $partNumber,
        public string $url,
        public array $headers = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'upload_id' => $this->uploadId,
            'part_number' => $this->partNumber,
            'url' => $this->url,
            'headers' => $this->headers,
        ];
    }
}

