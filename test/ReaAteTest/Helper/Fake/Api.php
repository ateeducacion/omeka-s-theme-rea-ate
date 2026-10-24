<?php

declare(strict_types=1);

namespace ReaAteTest\Helper\Fake;

/** Counts API searches and answers id batches from a fixed set of items. */
class Api
{
    /** @var int */
    public $calls = 0;
    /** @var Item[] */
    private $items;

    /** @param Item[] $items Indexed by id; ids left out behave as not visible. */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function search(string $resource, array $query): Response
    {
        $this->calls++;
        $found = [];
        foreach ($query['id'] ?? [] as $id) {
            if (isset($this->items[$id])) {
                $found[] = $this->items[$id];
            }
        }
        return new Response($found);
    }
}
