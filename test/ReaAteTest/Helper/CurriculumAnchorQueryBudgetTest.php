<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\CurriculumAnchor;
use PHPUnit\Framework\TestCase;

/**
 * Query budget of CurriculumAnchor on a results page (spec §5.4): three batch
 * queries per page, whatever the number of results.
 */
class CurriculumAnchorQueryBudgetTest extends TestCase
{
    private const SUBJECT = 'schema:about';
    private const LEVEL = 'lrmi:educationalLevel';
    private const TERMSET = 'schema:inDefinedTermSet';

    /** @var CurriculumAnchorFakeApi */
    private $api;

    /** @var CurriculumAnchor */
    private $helper;

    protected function setUp(): void
    {
        // Etapa (termset) 900 → curso 100 → asignatura 200. La asignatura 201 existe
        // en los valores pero la API no la devuelve (privada para el visitante).
        $stage = new CurriculumAnchorFakeItem(900, 'ESO', ['schema:position' => ['2']]);
        $level = new CurriculumAnchorFakeItem(100, '1º ESO', [self::TERMSET => [$stage]]);
        $subject = new CurriculumAnchorFakeItem(200, 'Matemáticas', [self::LEVEL => [$level]]);
        $hidden = new CurriculumAnchorFakeItem(201, 'Oculta', []);

        $this->api = new CurriculumAnchorFakeApi([900 => $stage, 100 => $level, 200 => $subject]);
        $view = new CurriculumAnchorFakeView($this->api, []);
        $this->helper = (new CurriculumAnchor())->setView($view);

        $this->resources = [];
        for ($i = 1; $i <= 5; $i++) {
            $this->resources[] = new CurriculumAnchorFakeItem(1000 + $i, 'Recurso ' . $i, [
                self::SUBJECT => [$subject, $hidden],
                self::LEVEL => [$level],
            ]);
        }
    }

    /** @var CurriculumAnchorFakeItem[] */
    private $resources;

    public function testPrimedPageUsesThreeQueries(): void
    {
        $this->helper->primeResources($this->resources);
        foreach ($this->resources as $resource) {
            $rows = ($this->helper)($resource);
            $this->assertNotNull($rows);
        }

        $this->assertSame(3, $this->api->calls, 'subjects, levels and termsets: one batch each');
    }

    public function testIdsTheApiDoesNotReturnAreNotQueriedAgain(): void
    {
        // Sin cebar: la primera tarjeta paga sus consultas, las siguientes no repiten
        // ni siquiera la de la asignatura que la API nunca devuelve.
        ($this->helper)($this->resources[0]);
        $afterFirst = $this->api->calls;
        foreach (array_slice($this->resources, 1) as $resource) {
            ($this->helper)($resource);
        }

        $this->assertSame($afterFirst, $this->api->calls);
    }

    public function testRowsGroupSubjectWithItsLevel(): void
    {
        $rows = ($this->helper)($this->resources[0]);

        $this->assertSame('Matemáticas', $rows[0]['label']);
        $this->assertSame([100], array_column($rows[0]['levels'], 'id'));
    }

    public function testAnchorTermsDefaultOnlyWhenNeverSaved(): void
    {
        $unsaved = (new CurriculumAnchor())->setView(new CurriculumAnchorFakeView($this->api, []));
        $this->assertSame([self::SUBJECT, self::LEVEL], $unsaved->anchorTerms());

        $disabled = (new CurriculumAnchor())->setView(new CurriculumAnchorFakeView($this->api, [
            'curriculum_anchor_subject_property' => '',
            'curriculum_anchor_level_property' => ' dcterms:educationLevel ',
        ]));
        $this->assertSame(['', 'dcterms:educationLevel'], $disabled->anchorTerms());
    }
}

/** Minimal stand-in for Omeka's ValueRepresentation. */
class CurriculumAnchorFakeValue
{
    /** @var mixed */
    private $content;

    /** @param CurriculumAnchorFakeItem|string $content */
    public function __construct($content)
    {
        $this->content = $content;
    }

    public function valueResource(): ?CurriculumAnchorFakeItem
    {
        return $this->content instanceof CurriculumAnchorFakeItem ? $this->content : null;
    }

    public function __toString(): string
    {
        return is_string($this->content) ? $this->content : 'http://example.org/api/items/' . $this->content->id();
    }
}

/** Minimal stand-in for Omeka's ItemRepresentation. */
class CurriculumAnchorFakeItem
{
    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var array<string, CurriculumAnchorFakeValue[]> */
    private $values = [];

    public function __construct(int $id, string $title, array $values)
    {
        $this->id = $id;
        $this->title = $title;
        foreach ($values as $term => $list) {
            $this->values[$term] = array_map(static fn ($v) => new CurriculumAnchorFakeValue($v), $list);
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

    /** @return CurriculumAnchorFakeValue|CurriculumAnchorFakeValue[]|null */
    public function value(string $term, array $options = [])
    {
        $list = $this->values[$term] ?? [];
        if (!empty($options['all'])) {
            return $list;
        }
        return $list[0] ?? null;
    }
}

/** Counts API searches and answers id batches from a fixed set. */
class CurriculumAnchorFakeApi
{
    /** @var int */
    public $calls = 0;
    /** @var CurriculumAnchorFakeItem[] */
    private $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function search(string $resource, array $query): CurriculumAnchorFakeResponse
    {
        $this->calls++;
        $found = [];
        foreach ($query['id'] ?? [] as $id) {
            if (isset($this->items[$id])) {
                $found[] = $this->items[$id];
            }
        }
        return new CurriculumAnchorFakeResponse($found);
    }
}

class CurriculumAnchorFakeResponse
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

class CurriculumAnchorFakeView
{
    /** @var CurriculumAnchorFakeApi */
    private $api;
    /** @var array */
    private $settings;

    public function __construct(CurriculumAnchorFakeApi $api, array $settings)
    {
        $this->api = $api;
        $this->settings = $settings;
    }

    public function api(): CurriculumAnchorFakeApi
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
