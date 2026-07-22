<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\SafeUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SafeUrlTest extends TestCase
{
    private SafeUrl $helper;

    protected function setUp(): void
    {
        $this->helper = new SafeUrl();
    }

    #[DataProvider('acceptedUrls')]
    public function testAcceptsSafeUrls(string $url): void
    {
        $this->assertSame($url, ($this->helper)($url));
    }

    public static function acceptedUrls(): array
    {
        return [
            'https absolute' => ['https://example.org/a'],
            'http absolute' => ['http://example.org'],
            'site-relative path' => ['/s/demo/item'],
            'bare relative with colon in query' => ['item?q=a:b'],
            'anchor' => ['#main'],
        ];
    }

    /**
     * A rejected URL returns null so the caller can omit the link entirely.
     *
     * @param mixed $url
     */
    #[DataProvider('rejectedUrls')]
    public function testRejectsDangerousUrls($url): void
    {
        $this->assertNull(($this->helper)($url));
    }

    public static function rejectedUrls(): array
    {
        return [
            'javascript scheme' => ['javascript:alert(1)'],
            'javascript mixed case' => ['JaVaScRiPt:alert(1)'],
            // Browsers strip control characters before parsing the scheme, so a tab or
            // newline inside "javascript" must not be treated as an unknown scheme.
            'javascript split by tab' => ["java\tscript:alert(1)"],
            'javascript split by newline' => ["java\nscript:alert(1)"],
            'javascript with surrounding space' => ['  javascript:alert(1)  '],
            'data scheme' => ['data:text/html,<script>1</script>'],
            'vbscript scheme' => ['vbscript:msgbox(1)'],
            'file scheme' => ['file:///etc/passwd'],
            // Protocol-relative URLs inherit the current scheme and silently go off-site.
            'protocol relative' => ['//evil.example/x'],
            'scheme outside allowlist' => ['ftp://example.org'],
            'empty string' => [''],
            'null' => [null],
        ];
    }

    public function testSchemeAllowlistIsConfigurable(): void
    {
        $this->assertSame('mailto:a@b.c', ($this->helper)('mailto:a@b.c', ['http', 'https', 'mailto']));
    }

    public function testEmptyAllowlistRejectsEveryAbsoluteUrl(): void
    {
        $this->assertNull(($this->helper)('https://example.org', []));
    }

    public function testRelativeUrlsCanBeRejected(): void
    {
        $this->assertNull(($this->helper)('/path', ['http', 'https'], false));
    }
}
