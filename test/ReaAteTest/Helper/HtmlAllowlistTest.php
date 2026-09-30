<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\CssToken;
use OmekaTheme\Helper\HtmlAllowlist;
use OmekaTheme\Helper\SafeUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlAllowlistTest extends TestCase
{
    private HtmlAllowlist $helper;

    protected function setUp(): void
    {
        $this->helper = new HtmlAllowlist();

        // The helper delegates href and class validation to the other two helpers, so
        // the fake view only needs to resolve them by the exact name theme.ini uses.
        $plugins = [
            'SafeUrl' => new SafeUrl(),
            'CssToken' => new CssToken(),
        ];

        $view = new class ($plugins) {
            /** @var array<string, object> */
            private array $plugins;

            public function __construct(array $plugins)
            {
                $this->plugins = $plugins;
            }

            public function plugin(string $name): object
            {
                return $this->plugins[$name];
            }
        };

        $this->helper->setView($view);
    }

    public function testKeepsProseMarkup(): void
    {
        $this->assertSame(
            '<p>Hola <strong>mundo</strong></p>',
            ($this->helper)('<p>Hola <strong>mundo</strong></p>')
        );
        $this->assertSame('a<br>b', ($this->helper)('a<br>b'));
        $this->assertSame('<ul><li>a</li><li>b</li></ul>', ($this->helper)('<ul><li>a</li><li>b</li></ul>'));
    }

    public function testPreservesUtf8(): void
    {
        $this->assertSame('<p>Canarias · Educación ñ</p>', ($this->helper)('<p>Canarias · Educación ñ</p>'));
    }

        #[DataProvider('executableMarkup')]
    public function testRemovesExecutableSubtrees(string $input, string $expected): void
    {
        $this->assertSame($expected, ($this->helper)($input));
    }

    public static function executableMarkup(): array
    {
        return [
            'script' => ['<p>a</p><script>alert(1)</script>', '<p>a</p>'],
            'iframe' => ['<iframe src="//evil"></iframe>ok', 'ok'],
            'svg' => ['<svg onload="alert(1)"><circle/></svg>z', 'z'],
            'form' => ['<form action="/x"><input name="a"></form>y', 'y'],
            'nested script' => ['<div><section><script>alert(1)</script>t</section></div>', '<div>t</div>'],
            // Conditional comments are a script-execution vector in old engines.
            'conditional comment' => ['<p>a</p><!--[if IE]><script>1</script><![endif]-->', '<p>a</p>'],
        ];
    }

    #[DataProvider('dangerousAttributes')]
    public function testStripsDangerousAttributes(string $input, string $expected): void
    {
        $this->assertSame($expected, ($this->helper)($input));
    }

    public static function dangerousAttributes(): array
    {
        return [
            'onclick' => ['<p onclick="alert(1)">x</p>', '<p>x</p>'],
            'onerror uppercase' => ['<span ONERROR="alert(1)">x</span>', '<span>x</span>'],
            'style' => ['<div style="position:fixed">x</div>', '<div>x</div>'],
            'javascript href' => ['<a href="javascript:alert(1)">click</a>', '<a>click</a>'],
            'invalid class' => ['<p class="a&quot;b">x</p>', '<p>x</p>'],
        ];
    }

    public function testKeepsSafeHrefs(): void
    {
        $this->assertSame('<a href="https://e.org">e</a>', ($this->helper)('<a href="https://e.org">e</a>'));
        $this->assertSame('<a href="mailto:a@b.c">m</a>', ($this->helper)('<a href="mailto:a@b.c">m</a>'));
    }

    public function testKeepsValidClass(): void
    {
        $this->assertSame('<p class="ok-class">x</p>', ($this->helper)('<p class="ok-class">x</p>'));
    }

    /**
     * An unknown tag is unwrapped rather than deleted, so an unexpected <section> does
     * not silently swallow a paragraph of the footer.
     */
    public function testUnwrapsUnknownTags(): void
    {
        $this->assertSame('<p>keep</p>', ($this->helper)('<section><p>keep</p></section>'));
    }

    /**
     * The real footer_copyright in production uses target="_blank" with
     * rel="noreferrer noopener"; stripping either would be a visible regression.
     */
    public function testKeepsTargetBlankAndForcesRel(): void
    {
        $this->assertSame(
            '<a href="https://e.org" target="_blank" rel="noopener noreferrer">e</a>',
            ($this->helper)('<a href="https://e.org" target="_blank">e</a>')
        );
    }

    public function testPreservesExistingRelOnTargetBlank(): void
    {
        $input = '<a href="https://e.org" target="_blank" rel="noreferrer noopener">e</a>';
        $this->assertSame($input, ($this->helper)($input));
    }

    #[DataProvider('targetAndRelCases')]
    public function testFiltersTargetAndRel(string $input, string $expected): void
    {
        $this->assertSame($expected, ($this->helper)($input));
    }

    public static function targetAndRelCases(): array
    {
        return [
            'target _self kept' => [
                '<a href="https://e.org" target="_self">e</a>',
                '<a href="https://e.org" target="_self">e</a>',
            ],
            'target _top dropped' => [
                '<a href="https://e.org" target="_top">e</a>',
                '<a href="https://e.org">e</a>',
            ],
            'named frame dropped' => [
                '<a href="https://e.org" target="evilframe">e</a>',
                '<a href="https://e.org">e</a>',
            ],
            'bogus rel token dropped' => [
                '<a href="https://e.org" rel="stylesheet">e</a>',
                '<a href="https://e.org">e</a>',
            ],
            'rel nofollow kept' => [
                '<a href="https://e.org" rel="nofollow">e</a>',
                '<a href="https://e.org" rel="nofollow">e</a>',
            ],
            'rel filtered and _blank forced' => [
                '<a href="https://e.org" target="_blank" rel="stylesheet nofollow">e</a>',
                '<a href="https://e.org" target="_blank" rel="nofollow noopener noreferrer">e</a>',
            ],
        ];
    }

    /**
     * @param mixed $input
     */
    #[DataProvider('emptyInputs')]
    public function testReturnsEmptyStringForEmptyInput($input): void
    {
        $this->assertSame('', ($this->helper)($input));
    }

    public static function emptyInputs(): array
    {
        return [
            'empty string' => [''],
            'whitespace' => ["  \n "],
            'null' => [null],
        ];
    }
}
