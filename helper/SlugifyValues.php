<?php
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

class SlugifyValues extends AbstractHelper
{
    /**
     * Converts an array of ValueRepresentation to URL-safe slugs.
     *
     * A value may be a literal, a URI or a linked resource; its label is taken
     * from whichever it is (see label()), so linked curriculum terms slugify too.
     *
     * @param array $values Array of \Omeka\Api\Representation\ValueRepresentation
     * @return array
     */
    public function __invoke(array $values): array
    {
        $slugs = [];
        foreach ($values as $value) {
            if (!$value) {
                continue;
            }
            $slug = self::slug(self::label($value));
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }
        return $slugs;
    }

    /**
     * Display label of a value: the title of a linked resource, the literal text,
     * or the label (else the address) of a URI. value() is null for linked
     * resources and URIs, so reading it alone drops them.
     *
     * @param object $value ValueRepresentation-like
     */
    public static function label($value): string
    {
        if (method_exists($value, 'valueResource')) {
            $resource = $value->valueResource();
            if ($resource) {
                return trim((string) $resource->displayTitle());
            }
        }
        $text = trim((string) $value->value());
        if ($text !== '') {
            return $text;
        }
        return method_exists($value, 'uri') ? trim((string) $value->uri()) : '';
    }

    /** URL-safe slug of a label. */
    public static function slug(string $label): string
    {
        $str = mb_strtolower($label, 'UTF-8');
        $str = strtr($str, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n', 'ç' => 'c',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'Ü' => 'u', 'Ñ' => 'n', 'Ç' => 'c',
        ]);
        $str = preg_replace('/[^a-z0-9]+/', '-', $str);
        return trim($str, '-');
    }
}
