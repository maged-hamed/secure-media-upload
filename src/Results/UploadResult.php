<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Results;

final readonly class UploadResult
{
    public function __construct(
        public string $path,
        public string $url,
        public string $name,
        public string $extension,
        public string $originalName,
        public string $mimeType,
        public int $sizeBytes,
        public ?float $duration = null,
        public ?string $hash = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'url' => $this->url,
            'name' => $this->name,
            'extension' => $this->extension,
            'original_name' => $this->originalName,
            'mime_type' => $this->mimeType,
            'size_bytes' => $this->sizeBytes,
            'duration' => $this->duration,
            'hash' => $this->hash,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'],
            url: $data['url'],
            name: $data['name'],
            extension: $data['extension'] ?? strtolower((string) pathinfo((string) $data['name'], PATHINFO_EXTENSION)),
            originalName: $data['original_name'],
            mimeType: $data['mime_type'],
            sizeBytes: $data['size_bytes'],
            duration: $data['duration'] ?? null,
            hash: $data['hash'] ?? null,
        );
    }
}
