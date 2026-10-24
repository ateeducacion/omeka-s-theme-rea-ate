<?php

declare(strict_types=1);

namespace ReaAteTest\Helper\Fake;

/** Minimal stand-in for Omeka's API response. */
class Response
{
    /** @var array */
    private $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function getContent(): array
    {
        return $this->content;
    }
}
