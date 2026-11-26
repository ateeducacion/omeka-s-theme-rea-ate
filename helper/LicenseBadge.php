<?php

namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Reconoce la URI de una licencia Creative Commons de las cinco que admite el
 * vocabulario del módulo omeka-s-OERManager (dcterms:license, decisión 0019)
 * y devuelve los datos de su distintivo oficial.
 *
 * Devuelve null para cualquier otra cosa (literal, otra licencia, URI mal
 * formada): el llamador decide entonces si muestra el valor tal cual.
 * Los SVG de 88x31 son los distintivos oficiales de Creative Commons y viven
 * en asset/img/cc/ para no pedir imágenes a un tercero en cada ficha.
 * `summary` resume en una frase lo que permite la licencia; es un msgid que la
 * plantilla pasa por translate().
 *
 * Uso en plantillas:
 *   $badge = $this->plugin('LicenseBadge')($value->uri());
 *   if ($badge): ?><img src="<?= $this->assetUrl('img/cc/' . $badge['image']) ?>"><?php endif;
 */
class LicenseBadge extends AbstractHelper
{
    /** Clave = ruta normalizada de la URI, sin esquema ni barra final. */
    private const LICENSES = [
        'creativecommons.org/publicdomain/zero/1.0' => [
            'CC0 1.0', 'zero.svg', 'Uso libre, sin condiciones', // @translate
        ],
        'creativecommons.org/licenses/by/4.0' => [
            'CC BY 4.0', 'by.svg', 'Reutilizable citando la autoría', // @translate
        ],
        'creativecommons.org/licenses/by-sa/4.0' => [
            'CC BY-SA 4.0', 'by-sa.svg', 'Citando la autoría y con la misma licencia', // @translate
        ],
        'creativecommons.org/licenses/by-nc/4.0' => [
            'CC BY-NC 4.0', 'by-nc.svg', 'Uso no comercial, citando la autoría', // @translate
        ],
        'creativecommons.org/licenses/by-nc-sa/4.0' => [
            'CC BY-NC-SA 4.0', 'by-nc-sa.svg', 'Uso no comercial, con la misma licencia', // @translate
        ],
    ];

    /**
     * @param string|null $uri URI de la licencia (dcterms:license).
     * @return array{uri: string, label: string, image: string, summary: string}|null
     */
    public function __invoke(?string $uri): ?array
    {
        $key = self::normalize($uri);
        if ($key === null || !isset(self::LICENSES[$key])) {
            return null;
        }
        [$label, $image, $summary] = self::LICENSES[$key];

        return [
            // URI canónica (https), aunque el valor guardado use http o no lleve barra final.
            'uri'   => 'https://' . $key . '/',
            'label' => $label,
            'image' => $image,
            'summary' => $summary,
        ];
    }

    private static function normalize(?string $uri): ?string
    {
        if ($uri === null) {
            return null;
        }
        $uri = trim($uri);
        if (!preg_match('#^https?://(?:www\.)?(creativecommons\.org/[A-Za-z0-9/._-]+?)/?$#i', $uri, $m)) {
            return null;
        }
        return strtolower($m[1]);
    }
}
