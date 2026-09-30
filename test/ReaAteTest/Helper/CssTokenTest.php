<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\CssToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CssTokenTest extends TestCase
{
    private CssToken $helper;

    protected function setUp(): void
    {
        $this->helper = new CssToken();
    }

    public function testInvokeReturnsItselfForChaining(): void
    {
        $this->assertSame($this->helper, ($this->helper)());
    }

    public function testColorAcceptsHexNotation(): void
    {
        $this->assertSame('#0C2C84', $this->helper->color('#0C2C84', '#000'));
        $this->assertSame('#abc', $this->helper->color('#abc', '#000'));
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('rejectedColors')]
    public function testColorFallsBackOnAnythingElse($value): void
    {
        $this->assertSame('#000', $this->helper->color($value, '#000'));
    }

    public static function rejectedColors(): array
    {
        return [
            // The payload that would break out of the <style> block.
            'style breakout' => ['red;}</style><script>alert(1)</script>'],
            'named colour' => ['red'],
            'rgb notation' => ['rgb(1,2,3)'],
            'empty' => [''],
            'null' => [null],
        ];
    }

    public function testLengthAcceptsCssUnits(): void
    {
        $this->assertSame('320px', $this->helper->length('320px', '20vh'));
        $this->assertSame('1.5rem', $this->helper->length('1.5rem', '20vh'));
        $this->assertSame('50%', $this->helper->length('50%', '20vh'));
        $this->assertSame('-10px', $this->helper->length('-10px', '20vh'));
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('rejectedLengths')]
    public function testLengthFallsBackOnAnythingElse($value): void
    {
        $this->assertSame('20vh', $this->helper->length($value, '20vh'));
    }

    public static function rejectedLengths(): array
    {
        return [
            'style breakout' => ['100px;}</style><script>alert(1)</script>'],
            'unitless number' => ['100'],
            'calc expression' => ['calc(100% - 10px)'],
            'unknown unit' => ['10ch'],
            'empty' => [''],
            'null' => [null],
        ];
    }

    public function testKeywordMatchesAllowlistStrictly(): void
    {
        $allowed = ['left', 'center', 'right'];
        $this->assertSame('center', $this->helper->keyword('center', $allowed, 'left'));
        $this->assertSame('left', $this->helper->keyword('"><img src=x>', $allowed, 'left'));
        $this->assertSame('left', $this->helper->keyword('CENTER', $allowed, 'left'));
        $this->assertSame('left', $this->helper->keyword(null, $allowed, 'left'));
    }

    public function testCssClassAcceptsValidClassNames(): void
    {
        $this->assertSame('anclaje-curricular', $this->helper->cssClass('anclaje-curricular', 'fallback'));
        $this->assertSame('a b_c d-e', $this->helper->cssClass('a b_c d-e', 'fallback'));
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('rejectedClassNames')]
    public function testCssClassFallsBackOnAnythingElse($value): void
    {
        $this->assertSame('fallback', $this->helper->cssClass($value, 'fallback'));
    }

    public static function rejectedClassNames(): array
    {
        return [
            'attribute breakout' => ['"><img src=x onerror=alert(1)>'],
            'quote' => ['a"b'],
            'empty' => [''],
            'null' => [null],
        ];
    }
}
