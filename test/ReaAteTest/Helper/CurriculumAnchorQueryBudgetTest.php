<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\CurriculumAnchor;
use PHPUnit\Framework\TestCase;
use ReaAteTest\Helper\Fake\Api;
use ReaAteTest\Helper\Fake\Item;
use ReaAteTest\Helper\Fake\View;

/**
 * Query budget of CurriculumAnchor on a results page (spec §5.4): three batch
 * queries per page, whatever the number of results.
 */
class CurriculumAnchorQueryBudgetTest extends TestCase
{
    private const SUBJECT = 'schema:about';
    private const LEVEL = 'lrmi:educationalLevel';
    private const TERMSET = 'schema:inDefinedTermSet';

    /** @var Api */
    private $api;

    /** @var CurriculumAnchor */
    private $helper;

    /** @var Item[] */
    private $resources = [];

    protected function setUp(): void
    {
        // Etapa (termset) 900 → curso 100 → asignatura 200. La asignatura 201 existe
        // en los valores pero la API no la devuelve (privada para el visitante).
        $stage = new Item(900, 'ESO', ['schema:position' => ['2']]);
        $level = new Item(100, '1º ESO', [self::TERMSET => [$stage]]);
        $subject = new Item(200, 'Matemáticas', [self::LEVEL => [$level]]);
        $hidden = new Item(201, 'Oculta', []);

        $this->api = new Api([900 => $stage, 100 => $level, 200 => $subject]);
        $this->helper = (new CurriculumAnchor())->setView(new View($this->api, []));

        for ($i = 1; $i <= 5; $i++) {
            $this->resources[] = new Item(1000 + $i, 'Recurso ' . $i, [
                self::SUBJECT => [$subject, $hidden],
                self::LEVEL => [$level],
            ]);
        }
    }

    public function testPrimedPageUsesThreeQueries(): void
    {
        $this->helper->primeResources($this->resources);
        foreach ($this->resources as $resource) {
            $this->assertNotNull(($this->helper)($resource));
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
        $unsaved = (new CurriculumAnchor())->setView(new View($this->api, []));
        $this->assertSame([self::SUBJECT, self::LEVEL], $unsaved->anchorTerms());

        $disabled = (new CurriculumAnchor())->setView(new View($this->api, [
            'curriculum_anchor_subject_property' => '',
            'curriculum_anchor_level_property' => ' dcterms:educationLevel ',
        ]));
        $this->assertSame(['', 'dcterms:educationLevel'], $disabled->anchorTerms());
    }
}
