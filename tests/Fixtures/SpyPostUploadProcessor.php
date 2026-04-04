<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Fixtures;

use Maged\SecureMediaUpload\Contracts\PostUploadProcessor;
use Maged\SecureMediaUpload\Results\UploadResult;

class SpyPostUploadProcessor implements PostUploadProcessor
{
    /** @var array<int,array{result:UploadResult,context:array{type:string,disk:string}}> */
    public static array $calls = [];

    public function process(UploadResult $result, array $context): void
    {
        self::$calls[] = [
            'result' => $result,
            'context' => $context,
        ];
    }

    public static function reset(): void
    {
        self::$calls = [];
    }
}

