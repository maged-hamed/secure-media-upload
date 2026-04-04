<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Contracts;

use Maged\SecureMediaUpload\Results\MultipartUploadCompleteResult;
use Maged\SecureMediaUpload\Results\MultipartUploadPartResult;
use Maged\SecureMediaUpload\Results\MultipartUploadSessionResult;

interface MultipartSessionManager
{
    public function startSession(
        string $disk,
        string $key,
        string $contentType,
        array $metadata = [],
    ): MultipartUploadSessionResult;

    public function signPart(
        string $disk,
        string $key,
        string $uploadId,
        int $partNumber,
        ?int $ttlMinutes = null,
    ): MultipartUploadPartResult;

    /**
     * @param array<int,array{part_number:int,e_tag:string}> $parts
     */
    public function completeSession(
        string $disk,
        string $key,
        string $uploadId,
        array $parts,
    ): MultipartUploadCompleteResult;

    public function abortSession(string $disk, string $key, string $uploadId): bool;
}

