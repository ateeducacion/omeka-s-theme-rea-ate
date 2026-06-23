<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\ShadeColor;
use PHPUnit\Framework\TestCase;

class ShadeColorTest extends TestCase
{
    private ShadeColor $helper;

    protected function setUp(): void
    {
        $this->helper = new ShadeColor();
    }

    public function testZeroPercentReturnsSameColor(): void
    {
        $this->assertSame('#000000', ($this->helper)('#000000', 0));
        $this->assertSame('#808080', ($this->helper)('#808080', 0));
    }

    public function testFullLightenTurnsBlackIntoWhite(): void
    {
        $this->assertSame('#ffffff', ($this->helper)('#000000', 100));
    }

    public function testFullDarkenTurnsWhiteIntoBlack(): void
    {
        $this->assertSame('#000000', ($this->helper)('#ffffff', -100));
    }
}
