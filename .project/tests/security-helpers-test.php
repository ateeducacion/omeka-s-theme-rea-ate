<?php
/**
 * Regression tests for the theme's security helpers (SafeUrl, CssToken, HtmlAllowlist).
 *
 * Omeka S themes have no test runner and do not autoload their own Composer
 * dependencies, so this script stubs Laminas' AbstractHelper and a minimal view and
 * exercises the helpers directly.
 *
 * Run from the theme root:
 *   php .project/tests/security-helpers-test.php
 *
 * Exits non-zero on the first failing assertion set, so it can be wired into CI later.
 */

namespace Laminas\View\Helper {
    abstract class AbstractHelper
    {
        protected $view;
        public function setView($view) { $this->view = $view; return $this; }
        public function getView() { return $this->view; }
    }
}

namespace {

class FakeView
{
    private $plugins = [];
    public function register($name, $helper)
    {
        $this->plugins[strtolower($name)] = $helper;
        $helper->setView($this);
    }
    public function plugin($name) { return $this->plugins[strtolower($name)]; }
}

$helperDir = dirname(__DIR__, 2) . '/helper';

require $helperDir . '/SafeUrl.php';
require $helperDir . '/CssToken.php';
require $helperDir . '/HtmlAllowlist.php';

$view = new FakeView();
$safeUrl = new \OmekaTheme\Helper\SafeUrl();
$cssToken = new \OmekaTheme\Helper\CssToken();
$htmlAllowlist = new \OmekaTheme\Helper\HtmlAllowlist();
$view->register('safeUrl', $safeUrl);
$view->register('cssToken', $cssToken);
$view->register('htmlAllowlist', $htmlAllowlist);

$pass = 0; $fail = 0;
function check($label, $actual, $expected) {
    global $pass, $fail;
    $ok = $actual === $expected;
    $ok ? $pass++ : $fail++;
    printf("%s %-58s got: %s\n", $ok ? 'ok  ' : 'FAIL', $label,
        var_export($actual, true) . ($ok ? '' : ' | want: ' . var_export($expected, true)));
}

echo "--- SafeUrl ---\n";
check('https absolute',        $safeUrl('https://example.org/a'), 'https://example.org/a');
check('http absolute',         $safeUrl('http://example.org'), 'http://example.org');
check('relative path',         $safeUrl('/s/demo/item'), '/s/demo/item');
check('relative bare',         $safeUrl('item?q=a:b'), 'item?q=a:b');
check('anchor',                $safeUrl('#main'), '#main');
check('javascript:',           $safeUrl('javascript:alert(1)'), null);
check('JaVaScRiPt: mixed',     $safeUrl('JaVaScRiPt:alert(1)'), null);
check('javascript with tab',   $safeUrl("java\tscript:alert(1)"), null);
check('leading whitespace js', $safeUrl('  javascript:alert(1)  '), null);
check('data:',                 $safeUrl('data:text/html,<script>1</script>'), null);
check('vbscript:',             $safeUrl('vbscript:msgbox(1)'), null);
check('file:',                 $safeUrl('file:///etc/passwd'), null);
check('protocol-relative',     $safeUrl('//evil.example/x'), null);
check('ftp not allowed',       $safeUrl('ftp://example.org'), null);
check('mailto opt-in',         $safeUrl('mailto:a@b.c', ['http','https','mailto']), 'mailto:a@b.c');
check('empty',                 $safeUrl(''), null);
check('null',                  $safeUrl(null), null);
check('no absolute allowed',   $safeUrl('https://example.org', []), null);
check('relative disallowed',   $safeUrl('/path', ['http','https'], false), null);
check('newline smuggling',     $safeUrl("java\nscript:alert(1)"), null);

echo "\n--- CssToken ---\n";
check('color 6 hex',           $cssToken->color('#0C2C84', '#000'), '#0C2C84');
check('color 3 hex',           $cssToken->color('#abc', '#000'), '#abc');
check('color breakout',        $cssToken->color('red;}</style><script>alert(1)</script>', '#000'), '#000');
check('color named',           $cssToken->color('red', '#000'), '#000');
check('color empty',           $cssToken->color(null, '#000'), '#000');
check('length px',             $cssToken->length('320px', '20vh'), '320px');
check('length decimal rem',    $cssToken->length('1.5rem', '20vh'), '1.5rem');
check('length percent',        $cssToken->length('50%', '20vh'), '50%');
check('length breakout',       $cssToken->length('100px;}</style><script>alert(1)</script>', '20vh'), '20vh');
check('length unitless',       $cssToken->length('100', '20vh'), '20vh');
check('length expression',     $cssToken->length('calc(100% - 10px)', '20vh'), '20vh');
check('keyword ok',            $cssToken->keyword('center', ['left','center','right'], 'left'), 'center');
check('keyword reject',        $cssToken->keyword('"><img src=x>', ['left','center','right'], 'left'), 'left');
check('cssClass single',       $cssToken->cssClass('anclaje-curricular', 'fallback'), 'anclaje-curricular');
check('cssClass multiple',     $cssToken->cssClass('a b_c d-e', 'fallback'), 'a b_c d-e');
check('cssClass breakout',     $cssToken->cssClass('"><img src=x onerror=alert(1)>', 'fallback'), 'fallback');
check('cssClass empty',        $cssToken->cssClass('', 'fallback'), 'fallback');

echo "\n--- HtmlAllowlist ---\n";
check('plain prose kept',      $htmlAllowlist('<p>Hola <strong>mundo</strong></p>'), '<p>Hola <strong>mundo</strong></p>');
check('script dropped',        $htmlAllowlist('<p>a</p><script>alert(1)</script>'), '<p>a</p>');
check('iframe dropped',        $htmlAllowlist('<iframe src="//evil"></iframe>ok'), 'ok');
check('onclick stripped',      $htmlAllowlist('<p onclick="alert(1)">x</p>'), '<p>x</p>');
check('onerror stripped',      $htmlAllowlist('<span ONERROR="alert(1)">x</span>'), '<span>x</span>');
check('style stripped',        $htmlAllowlist('<div style="position:fixed">x</div>'), '<div>x</div>');
check('js href stripped',      $htmlAllowlist('<a href="javascript:alert(1)">click</a>'), '<a>click</a>');
check('https href kept',       $htmlAllowlist('<a href="https://e.org">e</a>'), '<a href="https://e.org">e</a>');
check('mailto href kept',      $htmlAllowlist('<a href="mailto:a@b.c">m</a>'), '<a href="mailto:a@b.c">m</a>');
check('unknown tag unwrapped', $htmlAllowlist('<section><p>keep</p></section>'), '<p>keep</p>');
check('comment removed',       $htmlAllowlist('<p>a</p><!--[if IE]><script>1</script><![endif]-->'), '<p>a</p>');
check('class validated',       $htmlAllowlist('<p class="ok-class">x</p>'), '<p class="ok-class">x</p>');
check('bad class stripped',    $htmlAllowlist('<p class="a&quot;b">x</p>'), '<p>x</p>');
check('utf8 preserved',        $htmlAllowlist('<p>Canarias · Educación ñ</p>'), '<p>Canarias · Educación ñ</p>');
check('nested script in div',  $htmlAllowlist('<div><section><script>alert(1)</script>t</section></div>'), '<div>t</div>');
check('form dropped',          $htmlAllowlist('<form action="/x"><input name="a"></form>y'), 'y');
check('empty input',           $htmlAllowlist(''), '');
check('null input',            $htmlAllowlist(null), '');
check('br kept',               $htmlAllowlist('a<br>b'), 'a<br>b');
check('list kept',             $htmlAllowlist('<ul><li>a</li><li>b</li></ul>'), '<ul><li>a</li><li>b</li></ul>');
check('svg dropped',           $htmlAllowlist('<svg onload="alert(1)"><circle/></svg>z'), 'z');
check('target dropped',        $htmlAllowlist('<a href="https://e.org" target="_blank">e</a>'), '<a href="https://e.org">e</a>');

printf("\n%d passed, %d failed\n", $pass, $fail);
exit($fail === 0 ? 0 : 1);
}
