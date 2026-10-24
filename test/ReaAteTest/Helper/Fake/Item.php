<?php

declare(strict_types=1);

namespace ReaAteTest\Helper\Fake;

/** Minimal stand-in for Omeka's ItemRepresentation. */
class Item
{
    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var array<string, Value[]> */
    private $values = [];

    /** @param array<string, array<Item|string>> $values */
    public function __construct(int $id, string $title, array $values)
    {
        $this->id = $id;
        $this->title = $title;
        foreach ($values as $term => $list) {
            $this->values[$term] = array_map(static fn ($v) => new Value($v), $list);
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function displayTitle(): string
    {
        return $this->title;
    }

    /** @return Value|Value[]|null */
    public function value(string $term, array $options = [])
    {
        $list = $this->values[$term] ?? [];
        if (!empty($options['all'])) {
            return $list;
        }
        return $list[0] ?? null;
    }
}
