<?php
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper that validates theme settings before they are interpolated into CSS.
 *
 * Values printed inside a <style> block or a style="" attribute are not protected by
 * escapeHtml()/escapeHtmlAttr(): those encoders know nothing about CSS syntax, so a
 * value such as "red;}</style><script>alert(1)</script>" can break out of the block.
 * Every value that reaches CSS must therefore be validated against the grammar it is
 * supposed to follow, with a safe fallback when it does not match.
 *
 * The helper returns itself so templates can chain a named method, which keeps the
 * intent readable at the call site:
 *
 *   --primary: <?php echo $this->CssToken()->color($this->themeSetting('primary_color'), '#0C2C84'); ?>;
 *   height: <?php echo $this->CssToken()->length($bannerHeight, '20vh'); ?>;
 */
class CssToken extends AbstractHelper
{
    /**
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Validate a hex colour (#abc or #aabbcc).
     *
     * Note that named colours and rgb()/hsl() notation are intentionally rejected:
     * every colour setting in this theme is a Laminas\Form\Element\Color, which emits
     * hex only. Widening the grammar would only widen the attack surface.
     *
     * @param string|null $value
     * @param string $fallback A known-good hex colour.
     * @return string
     */
    public function color($value, $fallback)
    {
        $value = trim((string) $value);

        return preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $value) ? $value : $fallback;
    }

    /**
     * Validate a CSS length (e.g. "320px", "20vh", "1.5rem", "50%").
     *
     * @param string|null $value
     * @param string $fallback A known-good length.
     * @return string
     */
    public function length($value, $fallback)
    {
        $value = trim((string) $value);

        return preg_match('/^-?\d+(\.\d+)?(px|rem|em|vh|vw|%)$/', $value) ? $value : $fallback;
    }

    /**
     * Validate a value against an explicit allowlist of keywords.
     *
     * Used for the Select-backed settings (banner position, banner width, header
     * layout). The form already constrains them, so this is defence in depth against
     * values written straight to the database or through the API.
     *
     * @param string|null $value
     * @param array $allowed
     * @param string $fallback A member of $allowed.
     * @return string
     */
    public function keyword($value, array $allowed, $fallback)
    {
        $value = trim((string) $value);

        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    /**
     * Validate a space-separated list of CSS class names.
     *
     * @param string|null $value
     * @param string $fallback A known-good class name.
     * @return string
     */
    public function cssClass($value, $fallback)
    {
        $value = trim((string) $value);

        return preg_match('/^[A-Za-z0-9_-]+( [A-Za-z0-9_-]+)*$/', $value) ? $value : $fallback;
    }
}
