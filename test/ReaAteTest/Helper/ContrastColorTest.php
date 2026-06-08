<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\ContrastColor;
use PHPUnit\Framework\TestCase;

class ContrastColorTest extends TestCase
{
    private ContrastColor $helper;

    protected function setUp(): void
    {
        $this->helper = new ContrastColor();
    }

    public function testPicksWhiteAgainstBlack(): void
    {
        $this->assertSame('#ffffff', ($this->helper)('#000000', ['#000000', '#ffffff']));
    }

    public function testPicksBlackAgainstWhite(): void
    {
        $this->assertSame('#000000', ($this->helper)('#ffffff', ['#ffffff', '#000000']));
    }

    public function testPicksMostContrastingFromList(): void
    {
        // Against white, the darkest candidate has the highest contrast ratio.
        $this->assertSame('#111111', ($this->helper)('#ffffff', ['#eeeeee', '#111111']));
    }
}
