<?php
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper that reduces admin-authored HTML to a conservative allowlist.
 *
 * The footer settings (footer_site_info, footer_content, footer_copyright) are
 * Omeka\Form\Element\HtmlTextarea, so they are echoed raw by design: the admin is
 * trusted. That assumption is fine for a stock install but too generous for a public
 * administration deployment (ENS, ASVS L2), where a site editor is not necessarily
 * trusted with script execution on every page of the site.
 *
 * This helper keeps prose markup working while removing anything that can execute:
 * script/style/iframe/object/embed/form subtrees, every on* handler, every style
 * attribute, and any href whose scheme is not http(s).
 *
 * Implemented with DOMDocument on purpose. Omeka S themes do not autoload their own
 * Composer dependencies, so HTMLPurifier is not installable here; a parser-based
 * allowlist is the next best thing and is far safer than regex stripping.
 *
 * Usage in templates:
 *   <?php echo $this->HtmlAllowlist($this->themeSetting('footer_content')); ?>
 */
class HtmlAllowlist extends AbstractHelper
{
    /**
     * Tags that survive sanitisation, mapped to the attributes each one may keep.
     *
     * Anything not listed here is unwrapped: the tag is dropped but its text content
     * is preserved, so an unexpected <section> degrades gracefully instead of
     * silently deleting a paragraph of the footer.
     */
    private const ALLOWED_TAGS = [
        'a' => ['href', 'title', 'class', 'lang', 'target', 'rel'],
        'p' => ['class', 'lang'],
        'span' => ['class', 'lang'],
        'div' => ['class', 'lang'],
        'br' => [],
        'hr' => [],
        'strong' => ['class'],
        'b' => ['class'],
        'em' => ['class'],
        'i' => ['class'],
        'small' => ['class'],
        'ul' => ['class'],
        'ol' => ['class'],
        'li' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'h4' => ['class'],
        'h5' => ['class'],
        'h6' => ['class'],
    ];

    /**
     * Link targets that may be kept. _parent and _top are dropped: footer prose has no
     * legitimate reason to escape a frame.
     */
    private const ALLOWED_TARGETS = ['_blank', '_self'];

    /**
     * Link relationship tokens that may be kept. Anything else is dropped.
     */
    private const ALLOWED_REL_TOKENS = ['noopener', 'noreferrer', 'nofollow', 'external', 'me'];

    /**
     * Tags removed together with their entire subtree.
     *
     * Their content is code or structure, not prose, so unwrapping them would leak
     * script bodies as visible text.
     */
    private const FORBIDDEN_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form',
        'input', 'button', 'select', 'textarea', 'link', 'meta', 'base', 'svg', 'math',
    ];

    /**
     * Sanitise a fragment of admin-authored HTML.
     *
     * @param string|null $html
     * @return string Safe HTML, or an empty string when nothing survives.
     */
    public function __invoke($html)
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Admin-authored HTML is rarely well-formed; libxml warnings about it are
        // noise, not signal.
        $previous = libxml_use_internal_errors(true);

        // The XML declaration forces UTF-8 parsing (loadHTML assumes ISO-8859-1
        // otherwise), and the wrapper gives us a stable root to serialise back from.
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return '';
        }

        $root = null;
        foreach ($dom->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $root = $node;
                break;
            }
        }

        if ($root === null) {
            return '';
        }

        $this->sanitizeChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    /**
     * Recursively sanitise the children of a node, depth first.
     *
     * Depth first matters: by the time a disallowed element is unwrapped, its children
     * have already been cleaned, so promoting them cannot reintroduce unsafe markup.
     *
     * @param \DOMNode $node
     * @return void
     */
    private function sanitizeChildren(\DOMNode $node)
    {
        // Snapshot the list: removing and moving nodes mutates the live DOMNodeList.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }

            // Comments, processing instructions and CDATA have no place in footer prose,
            // and conditional comments are a known script-execution vector in old engines.
            if (!$child instanceof \DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::FORBIDDEN_TAGS, true)) {
                $node->removeChild($child);
                continue;
            }

            $this->sanitizeChildren($child);

            if (!isset(self::ALLOWED_TAGS[$tag])) {
                $this->unwrap($node, $child);
                continue;
            }

            $this->filterAttributes($child, self::ALLOWED_TAGS[$tag]);

            if ($tag === 'a') {
                $this->enforceLinkSafety($child);
            }
        }
    }

    /**
     * Guarantee that a link opening in a new tab cannot reach back through window.opener.
     *
     * Modern browsers imply noopener for target="_blank", but older ones do not, and the
     * sanitiser should not depend on the reader's browser version for a security property.
     *
     * @param \DOMElement $element
     * @return void
     */
    private function enforceLinkSafety(\DOMElement $element)
    {
        if ($element->getAttribute('target') !== '_blank') {
            return;
        }

        $tokens = preg_split('/\s+/', $element->getAttribute('rel'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_unique(array_merge($tokens, ['noopener', 'noreferrer']));

        $element->setAttribute('rel', implode(' ', $tokens));
    }

    /**
     * Replace an element with its children, preserving text content.
     *
     * @param \DOMNode $parent
     * @param \DOMElement $element
     * @return void
     */
    private function unwrap(\DOMNode $parent, \DOMElement $element)
    {
        foreach (iterator_to_array($element->childNodes) as $grandChild) {
            $parent->insertBefore($grandChild, $element);
        }

        $parent->removeChild($element);
    }

    /**
     * Strip every attribute that is not allowed for this tag, then validate the rest.
     *
     * @param \DOMElement $element
     * @param array $allowed Attribute names permitted on this tag.
     * @return void
     */
    private function filterAttributes(\DOMElement $element, array $allowed)
    {
        $view = $this->getView();
        $safeUrl = $view->plugin('SafeUrl');
        $cssToken = $view->plugin('CssToken');

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            // Covers on* handlers, style, srcdoc, formaction and anything else we have
            // not explicitly blessed for this tag.
            if (!in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            $value = $attribute->nodeValue;

            if ($name === 'href') {
                $safe = $safeUrl($value, ['http', 'https', 'mailto']);
                if ($safe === null) {
                    // Keep the link text, drop the destination.
                    $element->removeAttribute($attribute->nodeName);
                } else {
                    $element->setAttribute('href', $safe);
                }
                continue;
            }

            if ($name === 'target') {
                if (!in_array($value, self::ALLOWED_TARGETS, true)) {
                    $element->removeAttribute($attribute->nodeName);
                }
                continue;
            }

            if ($name === 'rel') {
                $tokens = array_values(array_intersect(
                    preg_split('/\s+/', strtolower(trim($value)), -1, PREG_SPLIT_NO_EMPTY) ?: [],
                    self::ALLOWED_REL_TOKENS
                ));
                if ($tokens) {
                    $element->setAttribute('rel', implode(' ', $tokens));
                } else {
                    $element->removeAttribute($attribute->nodeName);
                }
                continue;
            }

            if ($name === 'class') {
                $safe = $cssToken->cssClass($value, '');
                if ($safe === '') {
                    $element->removeAttribute($attribute->nodeName);
                }
                continue;
            }
        }
    }
}
