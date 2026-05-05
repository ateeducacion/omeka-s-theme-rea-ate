<?php
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

class SlugifyValues extends AbstractHelper
{
    /**
     * Converts an array of ValueRepresentation to URL-safe slugs.
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
            $str = mb_strtolower((string) $value->value(), 'UTF-8');
            $str = strtr($str, [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                'ü' => 'u', 'ñ' => 'n', 'ç' => 'c',
                'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
                'Ü' => 'u', 'Ñ' => 'n', 'Ç' => 'c',
            ]);
            $str = preg_replace('/[^a-z0-9]+/', '-', $str);
            $slug = trim($str, '-');
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }
        return $slugs;
    }
}
