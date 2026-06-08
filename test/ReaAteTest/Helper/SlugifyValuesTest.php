<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\SlugifyValues;
use PHPUnit\Framework\TestCase;

class SlugifyValuesTest extends TestCase
{
    private SlugifyValues $helper;

    protected function setUp(): void
    {
        $this->helper = new SlugifyValues();
    }

    public function testSlugifiesAccentsAndSpaces(): void
    {
        $values = [
            $this->value('Educación Primaria'),
            $this->value('3º ESO'),
        ];

        $this->assertSame(['educacion-primaria', '3-eso'], ($this->helper)($values));
    }

    public function testSkipsFalsyAndEmptyValues(): void
    {
        $values = [
            null,
            $this->value('   '),
            $this->value('Ñandú'),
        ];

        $this->assertSame(['nandu'], ($this->helper)($values));
    }

    /**
     * Build a minimal ValueRepresentation-like double exposing value().
     */
    private function value(string $text): object
    {
        return new class ($text) {
            private string $text;

            public function __construct(string $text)
            {
                $this->text = $text;
            }

            public function value(): string
            {
                return $this->text;
            }
        };
    }
}
