<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload;

use finfo;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;
use Maged\SecureMediaUpload\Results\UploadResult;
use Maged\SecureMediaUpload\Results\ValidationResult;
use Maged\SecureMediaUpload\Support\SvgSafetyScanner;

class SecureMediaUploader
{
    /**
     * Validate an uploaded file against package type policies.
     */
    public function validateFileOnly(mixed $file, string $type = 'image'): ValidationResult
    {
        if (!$file instanceof UploadedFile) {
            throw new InvalidArgumentException('No valid uploaded file was provided.');
        }

        $allowed = $this->allowedType($type);

        $ext = strtolower((string) $file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();
        $size = (int) $file->getSize();
        $realPath = $file->getRealPath();

        if ($realPath === false) {
            throw UploadValidationException::unreadableFile();
        }

        if ($ext === 'svg') {
            $svgContent = @file_get_contents($realPath);
            if (!is_string($svgContent) || SvgSafetyScanner::isUnsafe($svgContent)) {
                throw UploadValidationException::unsafeSvgDetected();
            }
        }

        if (!in_array($ext, $allowed['extensions'], true)) {
            throw UploadValidationException::invalidExtension($ext, $allowed['extensions']);
        }

        if (!in_array($mime, $allowed['mime_types'], true)) {
            throw UploadValidationException::mimeMismatch($mime, $allowed['mime_types']);
        }

        $realMime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($realPath);
        if (!in_array($realMime, $allowed['mime_types'], true)) {
            throw UploadValidationException::realMimeMismatch($realMime, $allowed['mime_types']);
        }

        if (isset($allowed['max_bytes']) && $size > $allowed['max_bytes']) {
            throw UploadValidationException::sizeExceeded($size, $allowed['max_bytes']);
        }

        return new ValidationResult(
            extension: $ext,
            realMimeType: $realMime,
            sizeBytes: $size,
            originalName: htmlspecialchars($file->getClientOriginalName(), ENT_QUOTES, 'UTF-8'),
        );
    }

    /**
     * Validate and upload a file to the configured storage disk.
     */
    public function secureFileUpload(
        mixed $file,
        string $type = 'image',
        string $storagePath = 'uploads/files',
        ?string $disk = null
    ): UploadResult {
        if (!$file instanceof UploadedFile) {
            throw new InvalidArgumentException('No valid uploaded file was provided.');
        }

        $info = $this->validateFileOnly($file, $type);
        $targetDisk = $disk ?: (string) config('secure-media-upload.default_disk', config('filesystems.default', 'local'));

        $fileName = Str::ulid() . '_' . $type . '.' . $info->extension;
        $normalizedStoragePath = trim($storagePath, '/');

        $storedPath = $this->disk($targetDisk)->putFileAs($normalizedStoragePath, $file, $fileName, [
            'visibility' => 'private',
        ]);

        if (!$storedPath) {
            throw UploadValidationException::storageFailure($targetDisk);
        }

        return new UploadResult(
            path: $storedPath,
            url: $this->storageFileUrl($storedPath, $targetDisk) ?? $storedPath,
            name: $fileName,
            extension: $info->extension,
            originalName: $info->originalName,
            mimeType: $info->realMimeType,
            sizeBytes: $info->sizeBytes,
            duration: $type === 'video' ? $this->resolveVideoDuration($file) : null,
            hash: $this->resolveFileHash($file),
        );
    }

    public function storageDiskPath(string $path, string $disk = 's3'): string
    {
        $normalizedPath = trim($path);

        if (filter_var($normalizedPath, FILTER_VALIDATE_URL)) {
            $parsedPath = parse_url($normalizedPath, PHP_URL_PATH) ?: $normalizedPath;
            $normalizedPath = ltrim((string) $parsedPath, '/');

            $bucket = (string) config("filesystems.disks.{$disk}.bucket", '');
            if ($bucket !== '' && str_starts_with($normalizedPath, $bucket . '/')) {
                $normalizedPath = substr($normalizedPath, strlen($bucket) + 1);
            }
        }

        return ltrim($normalizedPath, '/');
    }

    public function storageFileUrl(?string $path, string $disk = 's3', ?int $ttlMinutes = null): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $filesystem = $this->disk($disk);
        $normalizedPath = $this->storageDiskPath($path, $disk);

        if ($disk === 's3' && method_exists($filesystem, 'temporaryUrl')) {
            $ttlMinutes ??= (int) config('secure-media-upload.temporary_url_ttl', 60);
            return $filesystem->temporaryUrl($normalizedPath, now()->addMinutes($ttlMinutes));
        }

        return $filesystem->url($normalizedPath);
    }

    /**
     * @return array{extensions:array<int,string>,mime_types:array<int,string>,max_bytes?:int}
     */
    private function allowedType(string $type): array
    {
        $types = (array) config('secure-media-upload.types', []);
        $allowed = $types[$type] ?? null;

        if (!is_array($allowed)) {
            throw UploadValidationException::unsupportedType($type);
        }

        return $allowed;
    }

    private function disk(string $name): Filesystem
    {
        return Storage::disk($name);
    }

    private function resolveVideoDuration(UploadedFile $file): float|int|null
    {
        if (!is_callable('getVideoDuration')) {
            return null;
        }

        try {
            $path = $file->getRealPath();
            if ($path === false) {
                return null;
            }

            $duration = call_user_func('getVideoDuration', $path);
            return is_numeric($duration) ? $duration + 0 : null;
        } catch (\Throwable $e) {
            logger()->warning('Failed to extract video duration: ' . $e->getMessage());
            return null;
        }
    }

    private function resolveFileHash(UploadedFile $file): ?string
    {
        $algorithm = (string) config('secure-media-upload.hash_algorithm', 'sha256');

        if ($algorithm === '') {
            return null;
        }

        if (!in_array($algorithm, hash_algos(), true)) {
            return null;
        }

        $path = $file->getRealPath();
        if ($path === false) {
            return null;
        }

        $hash = hash_file($algorithm, $path);

        return is_string($hash) ? $hash : null;
    }
}

