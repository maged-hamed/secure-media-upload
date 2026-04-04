<?php

declare(strict_types=1);

return [
    'default_disk' => env('SECURE_MEDIA_UPLOAD_DISK', env('FILESYSTEM_DISK', 'local')),

    'hash_algorithm' => env('SECURE_MEDIA_HASH_ALGORITHM', 'sha256'),

    'post_upload' => [
        'enabled' => (bool) env('SECURE_MEDIA_POST_UPLOAD_ENABLED', false),
        'dispatch' => env('SECURE_MEDIA_POST_UPLOAD_DISPATCH', 'sync'),
        'processor' => \Maged\SecureMediaUpload\Processing\NullPostUploadProcessor::class,
        'queue_connection' => env('SECURE_MEDIA_POST_UPLOAD_QUEUE_CONNECTION'),
        'queue' => env('SECURE_MEDIA_POST_UPLOAD_QUEUE'),
        'types' => [],
        'fail_on_error' => (bool) env('SECURE_MEDIA_POST_UPLOAD_FAIL_ON_ERROR', false),
    ],

    'temporary_url_ttl' => (int) env('SECURE_MEDIA_TEMP_URL_TTL', 60),

    'multipart' => [
        'default_disk' => env('SECURE_MEDIA_MULTIPART_DISK', 's3'),
        'part_ttl_minutes' => (int) env('SECURE_MEDIA_MULTIPART_PART_TTL', 15),
    ],

    'types' => [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'max_bytes' => 10 * 1024 * 1024,
        ],
        'video' => [
            'extensions' => ['mp4', 'mov', 'avi'],
            'mime_types' => ['video/mp4', 'video/quicktime', 'video/x-msvideo'],
            'max_bytes' => 250 * 1024 * 1024,
        ],
        'excel' => [
            'extensions' => ['xls', 'xlsx', 'csv'],
            'mime_types' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv',
            ],
            'max_bytes' => 20 * 1024 * 1024,
        ],
        'compressed' => [
            'extensions' => ['zip', 'rar', '7z', 'gz', 'tar'],
            'mime_types' => [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/gzip',
                'application/x-tar',
            ],
            'max_bytes' => 200 * 1024 * 1024,
        ],
        'audio' => [
            'extensions' => ['mp3', 'wav', 'ogg', 'm4a'],
            'mime_types' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4'],
            'max_bytes' => 30 * 1024 * 1024,
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
                'application/rtf',
            ],
            'max_bytes' => 25 * 1024 * 1024,
        ],
    ],
];

