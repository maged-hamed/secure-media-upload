<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Facades;

use Illuminate\Support\Facades\Facade;

class SecureMediaUpload extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'secure-media-upload';
    }
}

