<?php
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

class ShadeColor extends AbstractHelper
{

    /**
     * Darken/lighten a hex color programmatically.
     *
     * @param string $color The hex color.
     * @param float $percent The shade percent.
     * @return string
     */
    public function __invoke($color, $percent)
    {
        $num = base_convert(substr($color, 1), 16, 10);
        $amt = round(2.55 * $percent);
        $r = ($num >> 16) + $amt;
        $b = ($num >> 8 & 0x00ff) + $amt;
        $g = ($num & 0x0000ff) + $amt;

        $value = 0x1000000
            + $this->clamp($r) * 0x10000
            + $this->clamp($b) * 0x100
            + $this->clamp($g);

        return '#' . substr(base_convert($value, 10, 16), 1);
    }

    /**
     * Clamp a colour channel to the 0-255 range.
     *
     * @param int|float $value
     * @return int
     */
    private function clamp($value)
    {
        if ($value >= 255) {
            return 255;
        }
        return $value < 1 ? 0 : (int) $value;
    }
}
