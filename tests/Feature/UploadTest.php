<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maged\SecureMediaUpload\SecureMediaUploader;
use Maged\SecureMediaUpload\Tests\TestCase;

class UploadTest extends TestCase
{
    private SecureMediaUploader $uploader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploader = new SecureMediaUploader();
        Storage::fake('testing');
    }

    protected function getPackageProviders($app): array
    {
        return ['Maged\SecureMediaUpload\MediaUploadServiceProvider'];
    }

    public function test_uploads_image_to_local_disk(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

        $result = $this->uploader->secureFileUpload($file, 'image', 'uploads/images', 'testing');

        $this->assertNotEmpty($result->path);
        $this->assertNotEmpty($result->name);
        $this->assertEquals('jpg', $result->extension);
        $this->assertGreaterThan(0, $result->sizeBytes);
        $this->assertNotNull($result->hash);
        $this->assertSame(
            hash_file('sha256', Storage::disk('testing')->path($result->path)),
            $result->hash
        );
    }

    public function test_can_disable_hash_generation_via_config(): void
    {
        config()->set('secure-media-upload.hash_algorithm', '');

        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);
        $result = $this->uploader->secureFileUpload($file, 'image', 'uploads/images', 'testing');

        $this->assertNull($result->hash);
    }

    public function test_generates_random_filename(): void
    {
        $file1 = UploadedFile::fake()->image('photo.jpg', 100, 100);
        $file2 = UploadedFile::fake()->image('photo.jpg', 100, 100);

        $result1 = $this->uploader->secureFileUpload($file1, 'image', 'uploads/images', 'testing');
        $result2 = $this->uploader->secureFileUpload($file2, 'image', 'uploads/images', 'testing');

        $this->assertNotEquals($result1->name, $result2->name);
    }

    public function test_uploads_pdf_document(): void
    {
        $file = $this->createPdfUpload('document.pdf');

        $result = $this->uploader->secureFileUpload($file, 'document', 'uploads/docs', 'testing');

        $this->assertEquals('pdf', $result->extension);
        $this->assertEquals('application/pdf', $result->mimeType);
    }

    public function test_result_can_be_converted_to_array(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);
        $result = $this->uploader->secureFileUpload($file, 'image', 'uploads/images', 'testing');

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('path', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('mime_type', $array);
        $this->assertArrayHasKey('size_bytes', $array);
    }

    public function test_result_can_be_reconstructed_from_array(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);
        $original = $this->uploader->secureFileUpload($file, 'image', 'uploads/images', 'testing');

        $array = $original->toArray();
        $reconstructed = \Maged\SecureMediaUpload\Results\UploadResult::fromArray($array);

        $this->assertEquals($original->path, $reconstructed->path);
        $this->assertEquals($original->name, $reconstructed->name);
        $this->assertEquals($original->mimeType, $reconstructed->mimeType);
    }

    private function createPdfUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($path, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        return new UploadedFile(
            $path,
            $name,
            'application/pdf',
            null,
            true
        );
    }
}
