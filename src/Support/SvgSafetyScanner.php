<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Support;

final class SvgSafetyScanner
{
    public static function isUnsafe(string $svgContent): bool
    {
        return preg_match('/<script\b/i', $svgContent) === 1
            || preg_match('/on\w+\s*=\s*/i', $svgContent) === 1
            || preg_match('/javascript\s*:/i', $svgContent) === 1
            || preg_match('/xlink:href\s*=\s*/i', $svgContent) === 1;
    }
}

