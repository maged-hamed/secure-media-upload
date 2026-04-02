<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Results;

final readonly class ValidationResult
{
    public function __construct(
        public string $extension,
        public string $realMimeType,
        public int $sizeBytes,
        public string $originalName,
    ) {
    }

    public function toArray(): array
    {
        return [
            'ext' => $this->extension,
            'real_mime' => $this->realMimeType,
            'size' => $this->sizeBytes,
            'original_name' => $this->originalName,
        ];
    }
}

