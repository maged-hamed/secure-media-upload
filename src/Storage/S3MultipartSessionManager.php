<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Storage;

use Maged\SecureMediaUpload\Contracts\MultipartSessionManager;
use Maged\SecureMediaUpload\Results\MultipartUploadCompleteResult;
use Maged\SecureMediaUpload\Results\MultipartUploadPartResult;
use Maged\SecureMediaUpload\Results\MultipartUploadSessionResult;
use RuntimeException;

class S3MultipartSessionManager implements MultipartSessionManager
{
    public function startSession(
        string $disk,
        string $key,
        string $contentType,
        array $metadata = [],
    ): MultipartUploadSessionResult {
        $diskConfig = $this->diskConfig($disk);
        $bucket = (string) ($diskConfig['bucket'] ?? '');

        $result = $this->client($disk)->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => ltrim($key, '/'),
            'ContentType' => $contentType,
            'Metadata' => $metadata,
        ]);

        return new MultipartUploadSessionResult(
            disk: $disk,
            bucket: $bucket,
            key: ltrim($key, '/'),
            uploadId: (string) $result->get('UploadId'),
        );
    }

    public function signPart(
        string $disk,
        string $key,
        string $uploadId,
        int $partNumber,
        ?int $ttlMinutes = null,
    ): MultipartUploadPartResult {
        $diskConfig = $this->diskConfig($disk);
        $bucket = (string) ($diskConfig['bucket'] ?? '');

        $command = $this->client($disk)->getCommand('UploadPart', [
            'Bucket' => $bucket,
            'Key' => ltrim($key, '/'),
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);

        $ttlMinutes ??= (int) config('secure-media-upload.multipart.part_ttl_minutes', 15);
        $request = $this->client($disk)->createPresignedRequest($command, "+{$ttlMinutes} minutes");

        return new MultipartUploadPartResult(
            key: ltrim($key, '/'),
            uploadId: $uploadId,
            partNumber: $partNumber,
            url: (string) $request->getUri(),
            headers: [],
        );
    }

    public function completeSession(
        string $disk,
        string $key,
        string $uploadId,
        array $parts,
    ): MultipartUploadCompleteResult {
        $diskConfig = $this->diskConfig($disk);
        $bucket = (string) ($diskConfig['bucket'] ?? '');

        $normalizedParts = array_map(static function (array $part): array {
            return [
                'PartNumber' => (int) $part['part_number'],
                'ETag' => (string) $part['e_tag'],
            ];
        }, $parts);

        $result = $this->client($disk)->completeMultipartUpload([
            'Bucket' => $bucket,
            'Key' => ltrim($key, '/'),
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => $normalizedParts,
            ],
        ]);

        return new MultipartUploadCompleteResult(
            disk: $disk,
            bucket: $bucket,
            key: ltrim($key, '/'),
            location: $result->get('Location') ? (string) $result->get('Location') : null,
            etag: $result->get('ETag') ? (string) $result->get('ETag') : null,
            versionId: $result->get('VersionId') ? (string) $result->get('VersionId') : null,
        );
    }

    public function abortSession(string $disk, string $key, string $uploadId): bool
    {
        $diskConfig = $this->diskConfig($disk);
        $bucket = (string) ($diskConfig['bucket'] ?? '');

        $this->client($disk)->abortMultipartUpload([
            'Bucket' => $bucket,
            'Key' => ltrim($key, '/'),
            'UploadId' => $uploadId,
        ]);

        return true;
    }

    private function diskConfig(string $disk): array
    {
        $diskConfig = config("filesystems.disks.{$disk}", []);

        if (!is_array($diskConfig) || ($diskConfig['driver'] ?? null) !== 's3') {
            throw new RuntimeException("Disk [{$disk}] must be configured as an s3 driver.");
        }

        if (!isset($diskConfig['bucket']) || trim((string) $diskConfig['bucket']) === '') {
            throw new RuntimeException("Disk [{$disk}] is missing required S3 bucket configuration.");
        }

        return $diskConfig;
    }

    private function client(string $disk): object
    {
        if (!class_exists(\Aws\S3\S3Client::class)) {
            throw new RuntimeException('aws/aws-sdk-php is required for multipart S3 session APIs.');
        }

        $diskConfig = $this->diskConfig($disk);

        $config = [
            'version' => 'latest',
            'region' => (string) ($diskConfig['region'] ?? 'us-east-1'),
        ];

        if (!empty($diskConfig['endpoint'])) {
            $config['endpoint'] = (string) $diskConfig['endpoint'];
        }

        if (array_key_exists('use_path_style_endpoint', $diskConfig)) {
            $config['use_path_style_endpoint'] = (bool) $diskConfig['use_path_style_endpoint'];
        }

        $key = $diskConfig['key'] ?? null;
        $secret = $diskConfig['secret'] ?? null;

        if (is_string($key) && $key !== '' && is_string($secret) && $secret !== '') {
            $config['credentials'] = [
                'key' => $key,
                'secret' => $secret,
            ];
        }

        return new \Aws\S3\S3Client($config);
    }
}

