<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Exceptions;

use RuntimeException;

class UploadValidationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ErrorCode $errorCode,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function unsupportedType(string $type): self
    {
        return new self("Unsupported file type: {$type}", ErrorCode::UNSUPPORTED_TYPE);
    }

    public static function invalidExtension(string $ext, array $allowed): self
    {
        return new self(
            "Invalid extension '{$ext}'. Allowed: " . implode(', ', $allowed),
            ErrorCode::INVALID_EXTENSION
        );
    }

    public static function mimeMismatch(string $clientMime, string $actual): self
    {
        return new self(
            "MIME type mismatch: got '{$actual}', expected '{$clientMime}'",
            ErrorCode::MIME_MISMATCH
        );
    }

    public static function realMimeMismatch(string $realMime, array $allowed): self
    {
        return new self(
            "Real MIME type '{$realMime}' not in allow-list: " . implode(', ', $allowed),
            ErrorCode::REAL_MIME_MISMATCH
        );
    }

    public static function unsafeSvgDetected(): self
    {
        return new self('Unsafe SVG file detected (script/event/url patterns found).', ErrorCode::UNSAFE_SVG_DETECTED);
    }

    public static function sizeExceeded(int $size, int $maxSize): self
    {
        return new self(
            "File size {$size} bytes exceeds maximum {$maxSize} bytes.",
            ErrorCode::SIZE_EXCEEDED
        );
    }

    public static function unreadableFile(): self
    {
        return new self('Unable to read uploaded file temporary path.', ErrorCode::UNREADABLE_FILE);
    }

    public static function storageFailure(string $disk): self
    {
        return new self("Failed to upload file to disk [{$disk}].", ErrorCode::STORAGE_FAILURE);
    }
}

