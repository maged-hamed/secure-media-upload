<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Contracts;

use Maged\SecureMediaUpload\Results\UploadResult;

interface PostUploadProcessor
{
    /**
     * @param array{type:string,disk:string} $context
     */
    public function process(UploadResult $result, array $context): void;
}

