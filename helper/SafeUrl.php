<?php
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper that validates a URL before it is rendered into an href/src attribute.
 *
 * Escaping alone is not enough for URL attributes: escapeHtmlAttr() happily emits
 * href="javascript:alert(1)" because the payload contains no characters that need
 * encoding. This helper rejects dangerous schemes (javascript:, data:, vbscript:,
 * file:) and, by default, protocol-relative URLs (//evil.example) that would turn a
 * theme setting into an open redirect.
 *
 * Returns null when the URL is rejected, so the caller decides what to do:
 * omit the link entirely, fall back to a safe default, or render a plain <span>.
 *
 * Usage in templates:
 *   $href = $this->safeUrl($this->themeSetting('logos_bar_link_1'));
 *   if ($href !== null): ?><a href="<?php echo $escAttr($href); ?>">…</a><?php endif;
 *
 *   // Site-relative paths only (no absolute URLs at all):
 *   $href = $this->safeUrl($value, []);
 */
class SafeUrl extends AbstractHelper
{
    /**
     * Schemes that are never allowed, whatever the allowlist says.
     *
     * Kept as an explicit denylist purely for documentation: the scheme allowlist
     * below is what actually enforces the rule.
     */
    private const DANGEROUS_SCHEMES = ['javascript', 'data', 'vbscript', 'file'];

    /**
     * Validate a URL for use in an href or src attribute.
     *
     * @param string|null $url The candidate URL.
     * @param array $schemes Allowed absolute schemes. Pass [] to reject every
     *                       absolute URL and accept only relative paths.
     * @param bool $allowRelative Whether to accept relative URLs ("/path", "path?x=1",
     *                            "#anchor"). Protocol-relative URLs are never accepted.
     * @return string|null The trimmed URL, or null if it was rejected.
     */
    public function __invoke($url, array $schemes = ['http', 'https'], $allowRelative = true)
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        // Control characters and embedded whitespace are used to smuggle schemes past
        // naive checks ("java\tscript:alert(1)"), and browsers strip them before parsing.
        if (preg_match('/[\x00-\x20\x7F]/', $url)) {
            return null;
        }

        // Protocol-relative ("//evil.example/path"): inherits the current scheme and
        // silently points off-site. Always rejected.
        if (strncmp($url, '//', 2) === 0) {
            return null;
        }

        $scheme = $this->extractScheme($url);

        if ($scheme === null) {
            // No scheme at all: a relative URL.
            return $allowRelative ? $url : null;
        }

        if (in_array($scheme, self::DANGEROUS_SCHEMES, true)) {
            return null;
        }

        $allowed = array_map('strtolower', $schemes);

        return in_array($scheme, $allowed, true) ? $url : null;
    }

    /**
     * Extract the lowercased scheme of an absolute URL.
     *
     * Deliberately does not use parse_url(): that function is lenient about malformed
     * input and returns false in cases where a browser would still resolve a scheme.
     * A strict regex over the RFC 3986 scheme grammar is safer here.
     *
     * @param string $url
     * @return string|null The scheme, or null when the URL is relative.
     */
    private function extractScheme($url)
    {
        // RFC 3986: scheme = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
        if (!preg_match('~^([A-Za-z][A-Za-z0-9+.\-]*):~', $url, $matches)) {
            return null;
        }

        return strtolower($matches[1]);
    }
}
