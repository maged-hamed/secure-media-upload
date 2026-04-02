<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Tests\Unit;

use Maged\SecureMediaUpload\Support\SvgSafetyScanner;
use PHPUnit\Framework\TestCase;

class SvgSafetyTest extends TestCase
{
    public function test_detects_script_tag(): void
    {
        $unsafe = '<svg><script>alert("xss")</script></svg>';
        $this->assertTrue(SvgSafetyScanner::isUnsafe($unsafe));
    }

    public function test_detects_event_handler(): void
    {
        $unsafe = '<svg onload="alert(\'xss\')"></svg>';
        $this->assertTrue(SvgSafetyScanner::isUnsafe($unsafe));
    }

    public function test_detects_javascript_protocol(): void
    {
        $unsafe = '<svg><a href="javascript:alert(\'xss\')">click</a></svg>';
        $this->assertTrue(SvgSafetyScanner::isUnsafe($unsafe));
    }

    public function test_detects_xlink_href_attack(): void
    {
        $unsafe = '<svg><use xlink:href="data:image/svg,<svg onload=alert(1)>"></use></svg>';
        $this->assertTrue(SvgSafetyScanner::isUnsafe($unsafe));
    }

    public function test_allows_safe_svg(): void
    {
        $safe = '<svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="red" /></svg>';
        $this->assertFalse(SvgSafetyScanner::isUnsafe($safe));
    }

    public function test_allows_svg_with_namespace(): void
    {
        $safe = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="blue" /></svg>';
        $this->assertFalse(SvgSafetyScanner::isUnsafe($safe));
    }

    public function test_case_insensitive_detection(): void
    {
        $unsafe = '<SVG><SCRIPT>alert("xss")</SCRIPT></SVG>';
        $this->assertTrue(SvgSafetyScanner::isUnsafe($unsafe));
    }
}

