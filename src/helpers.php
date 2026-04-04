<?php

declare(strict_types=1);

use Maged\SecureMediaUpload\SecureMediaUploader;

if (!function_exists('validateFileOnly')) {
    /**
     * Backward-compatible helper that delegates validation to the package service.
     * Returns array for BC or can be type-hinted to ValidationResult.
     *
     * @return array{ext:string,real_mime:string,size:int,original_name:string}
     */
    function validateFileOnly(mixed $file, string $type = 'image'): array
    {
        $result = app(SecureMediaUploader::class)->validateFileOnly($file, $type);
        return $result->toArray();
    }
}

if (!function_exists('secureFileUpload')) {
    /**
     * Backward-compatible helper that validates and stores files securely.
     * Returns array for BC or can be type-hinted to UploadResult.
     *
     * @return array{path:string,url:string,name:string,original_name:string,mime:string,size:int,duration:float|int|null,hash:?string}
     */
    function secureFileUpload(
        mixed $file,
        string $type = 'image',
        string $storagePath = 'uploads/files',
        ?string $disk = null
    ): array {
        $result = app(SecureMediaUploader::class)->secureFileUpload($file, $type, $storagePath, $disk);
        return [
            'path' => $result->path,
            'url' => $result->url,
            'name' => $result->name,
            'original_name' => $result->originalName,
            'mime' => $result->mimeType,
            'size' => $result->sizeBytes,
            'duration' => $result->duration,
            'hash' => $result->hash,
        ];
    }
}

if (!function_exists('storage_disk_path')) {
    function storage_disk_path(string $path, string $disk = 's3'): string
    {
        return app(SecureMediaUploader::class)->storageDiskPath($path, $disk);
    }
}

if (!function_exists('storage_file_url')) {
    function storage_file_url(?string $path, string $disk = 's3', ?int $ttlMinutes = null): ?string
    {
        return app(SecureMediaUploader::class)->storageFileUrl($path, $disk, $ttlMinutes);
    }
}
