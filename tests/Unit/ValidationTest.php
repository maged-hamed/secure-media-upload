<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Maged\SecureMediaUpload\Exceptions\ErrorCode;
use Maged\SecureMediaUpload\Exceptions\UploadValidationException;
use Maged\SecureMediaUpload\SecureMediaUploader;
use Orchestra\Testbench\TestCase;

class ValidationTest extends TestCase
{
    private SecureMediaUploader $uploader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploader = new SecureMediaUploader();
    }

    public function test_validates_jpg_image_successfully(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $result = $this->uploader->validateFileOnly($file, 'image');

        $this->assertEquals('jpg', $result->extension);
        $this->assertStringContainsString('image', $result->realMimeType);
        $this->assertGreaterThan(0, $result->sizeBytes);
    }

    public function test_validates_png_image_successfully(): void
    {
        $file = UploadedFile::fake()->image('test.png', 100, 100);

        $result = $this->uploader->validateFileOnly($file, 'image');

        $this->assertEquals('png', $result->extension);
    }

    public function test_validates_pdf_document_successfully(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $result = $this->uploader->validateFileOnly($file, 'document');

        $this->assertEquals('pdf', $result->extension);
    }

    public function test_rejects_invalid_extension(): void
    {
        $file = UploadedFile::fake()->create('test.exe', 100);

        $this->expectException(UploadValidationException::class);
        $this->uploader->validateFileOnly($file, 'image');
    }

    public function test_rejects_oversized_file(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $file->size = 1024 * 1024 * 100; // Simulate 100MB (exceeds 10MB limit)

        $this->expectException(UploadValidationException::class);
        $this->expectExceptionMessage('exceeds maximum');

        $this->uploader->validateFileOnly($file, 'image');
    }

    public function test_throws_exception_with_error_code(): void
    {
        $file = UploadedFile::fake()->create('test.exe', 100);

        try {
            $this->uploader->validateFileOnly($file, 'image');
        } catch (UploadValidationException $e) {
            $this->assertInstanceOf(ErrorCode::class, $e->errorCode);
            $this->assertNotEmpty($e->errorCode->value);
        }
    }

    public function test_sanitizes_original_filename(): void
    {
        $file = UploadedFile::fake()->image('test<script>.jpg', 100, 100);

        $result = $this->uploader->validateFileOnly($file, 'image');

        $this->assertStringNotContainsString('<script>', $result->originalName);
    }
}

