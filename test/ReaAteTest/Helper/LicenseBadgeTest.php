<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\LicenseBadge;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LicenseBadgeTest extends TestCase
{
    /** @return array<string, array{string, string, string}> */
    public static function licenciasAdmitidas(): array
    {
        return [
            'cc0'       => ['https://creativecommons.org/publicdomain/zero/1.0/', 'CC0 1.0', 'zero.png'],
            'by'        => ['https://creativecommons.org/licenses/by/4.0/', 'CC BY 4.0', 'by.png'],
            'by-sa'     => ['https://creativecommons.org/licenses/by-sa/4.0/', 'CC BY-SA 4.0', 'by-sa.png'],
            'by-nc'     => ['https://creativecommons.org/licenses/by-nc/4.0/', 'CC BY-NC 4.0', 'by-nc.png'],
            'by-nc-sa'  => ['https://creativecommons.org/licenses/by-nc-sa/4.0/', 'CC BY-NC-SA 4.0', 'by-nc-sa.png'],
        ];
    }

    #[DataProvider('licenciasAdmitidas')]
    public function testReconoceLasCincoLicencias(string $uri, string $label, string $image): void
    {
        $badge = (new LicenseBadge())($uri);

        $this->assertSame(['uri' => $uri, 'label' => $label, 'image' => $image], $badge);
    }

    public function testToleraHttpSinBarraFinalYMayusculas(): void
    {
        $badge = (new LicenseBadge())('http://CreativeCommons.org/licenses/by-sa/4.0');

        $this->assertSame('https://creativecommons.org/licenses/by-sa/4.0/', $badge['uri']);
        $this->assertSame('by-sa.png', $badge['image']);
    }

    /** @return array<string, array{?string}> */
    public static function valoresNoReconocidos(): array
    {
        return [
            'null'               => [null],
            'vacio'              => [''],
            'literal antiguo'    => ['CC BY-SA'],
            'codigo antiguo'     => ['ccbysa'],
            'version 3.0'        => ['https://creativecommons.org/licenses/by/3.0/'],
            'licencia no admitida' => ['https://creativecommons.org/licenses/by-nd/4.0/'],
            'otro dominio'       => ['https://example.org/licenses/by/4.0/'],
            'dominio con sufijo' => ['https://creativecommons.org.evil.example/licenses/by/4.0/'],
            'esquema peligroso'  => ['javascript://creativecommons.org/licenses/by/4.0/'],
            'ruta con extra'     => ['https://creativecommons.org/licenses/by/4.0/deed.es'],
        ];
    }

    #[DataProvider('valoresNoReconocidos')]
    public function testDevuelveNullSiNoEsUnaDeLasCinco(?string $uri): void
    {
        $this->assertNull((new LicenseBadge())($uri));
    }
}
