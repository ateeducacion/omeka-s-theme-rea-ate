<?php

declare(strict_types=1);

namespace ReaAteTest\Helper\Fake;

/** The view plugins CurriculumAnchor calls: api, themeSetting, translate, url. */
class View
{
    /** @var Api */
    private $api;
    /** @var array */
    private $settings;

    /** @param array $settings Theme settings; a missing key behaves as never saved (null). */
    public function __construct(Api $api, array $settings)
    {
        $this->api = $api;
        $this->settings = $settings;
    }

    public function api(): Api
    {
        return $this->api;
    }

    /** @return mixed */
    public function themeSetting(string $id)
    {
        return $this->settings[$id] ?? null;
    }

    public function translate(string $text): string
    {
        return $text;
    }

    public function url(...$args): string
    {
        return '/item?' . http_build_query($args[2]['query'] ?? []);
    }
}
