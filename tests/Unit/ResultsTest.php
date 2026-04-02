<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Unit;

use Maged\SecureMediaUpload\Results\UploadResult;
use Maged\SecureMediaUpload\Results\ValidationResult;
use PHPUnit\Framework\TestCase;

class ResultsTest extends TestCase
{
    public function test_validation_result_to_array(): void
    {
        $result = new ValidationResult(
            extension: 'jpg',
            realMimeType: 'image/jpeg',
            sizeBytes: 5120,
            originalName: 'photo.jpg',
        );

        $array = $result->toArray();

        $this->assertEquals('jpg', $array['ext']);
        $this->assertEquals('image/jpeg', $array['real_mime']);
        $this->assertEquals(5120, $array['size']);
        $this->assertEquals('photo.jpg', $array['original_name']);
    }

    public function test_upload_result_to_array(): void
    {
        $result = new UploadResult(
            path: 'uploads/images/abc123_image.jpg',
            url: 'https://storage.example.com/uploads/images/abc123_image.jpg',
            name: 'abc123_image.jpg',
            originalName: 'photo.jpg',
            mimeType: 'image/jpeg',
            sizeBytes: 5120,
            duration: null,
            hash: 'abc123def456',
        );

        $array = $result->toArray();

        $this->assertEquals('uploads/images/abc123_image.jpg', $array['path']);
        $this->assertEquals('photo.jpg', $array['original_name']);
        $this->assertEquals(5120, $array['size_bytes']);
        $this->assertEquals('abc123def456', $array['hash']);
    }

    public function test_upload_result_from_array(): void
    {
        $data = [
            'path' => 'uploads/images/abc123_image.jpg',
            'url' => 'https://storage.example.com/uploads/images/abc123_image.jpg',
            'name' => 'abc123_image.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 5120,
            'duration' => null,
            'hash' => 'abc123def456',
        ];

        $result = UploadResult::fromArray($data);

        $this->assertEquals('uploads/images/abc123_image.jpg', $result->path);
        $this->assertEquals('photo.jpg', $result->originalName);
        $this->assertEquals('abc123def456', $result->hash);
    }
}

