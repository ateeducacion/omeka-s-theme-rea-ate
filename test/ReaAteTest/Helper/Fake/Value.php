<?php

declare(strict_types=1);

namespace ReaAteTest\Helper\Fake;

/** Minimal stand-in for Omeka's ValueRepresentation: a literal or a linked item. */
class Value
{
    /** @var Item|string */
    private $content;

    /** @param Item|string $content */
    public function __construct($content)
    {
        $this->content = $content;
    }

    public function valueResource(): ?Item
    {
        return $this->content instanceof Item ? $this->content : null;
    }

    public function __toString(): string
    {
        // Like Omeka, a linked value casts to its resource URL.
        return is_string($this->content) ? $this->content : 'http://example.org/api/items/' . $this->content->id();
    }
}
