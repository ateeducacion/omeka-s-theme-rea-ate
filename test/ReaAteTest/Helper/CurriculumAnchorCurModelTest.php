<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\CurriculumAnchor;
use PHPUnit\Framework\TestCase;
use ReaAteTest\Helper\Fake\Api;
use ReaAteTest\Helper\Fake\Item;
use ReaAteTest\Helper\Fake\View;

/**
 * CurriculumAnchor with the cur: curriculum model (curriculo_lomloe_canarias):
 * schema:about → cur:MateriaCurso, which carries cur:curso and cur:materia;
 * courses carry cur:etapa and stages skos:notation.
 */
class CurriculumAnchorCurModelTest extends TestCase
{
    private const SUBJECT = 'schema:about';
    private const LEVEL = 'lrmi:educationalLevel';
    private const ASSESSES = 'lrmi:assesses';

    /** @var Api */
    private $api;

    /** @var CurriculumAnchor */
    private $helper;

    /** @var Item */
    private $resource;

    protected function setUp(): void
    {
        // Ids in an order that would sort wrongly by id: BAC < ESO.
        $bac = new Item(901, 'Bachillerato', ['skos:notation' => ['BAC']]);
        $eso = new Item(902, 'Educación Secundaria Obligatoria', ['skos:notation' => ['ESO']]);

        $c2bac = new Item(101, '2º Bachillerato', ['skos:notation' => ['B02'], 'cur:etapa' => [$bac]]);
        $c4eso = new Item(102, '4º ESO', ['skos:notation' => ['S04'], 'cur:etapa' => [$eso]]);
        $c3eso = new Item(103, '3º ESO', ['skos:notation' => ['S03'], 'cur:etapa' => [$eso]]);

        $lenguaEso = new Item(301, 'Lengua Castellana y Literatura (ESO)', []);
        $litCan = new Item(302, 'Literatura Canaria (Bachillerato)', []);

        $lengua3 = new Item(201, 'Lengua Castellana y Literatura · 3º ESO', [
            'cur:curso' => [$c3eso], 'cur:materia' => [$lenguaEso],
        ]);
        $lengua4 = new Item(202, 'Lengua Castellana y Literatura · 4º ESO', [
            'cur:curso' => [$c4eso], 'cur:materia' => [$lenguaEso],
        ]);
        $litCan2 = new Item(203, 'Literatura Canaria · 2º Bachillerato', [
            'cur:curso' => [$c2bac], 'cur:materia' => [$litCan],
        ]);

        $crit = new Item(401, 'SLCL04CE1.1 · Comprender textos orales…', [
            'skos:notation' => ['SLCL04CE1.1'],
            'skos:definition' => ['Comprender textos orales y multimodales.'],
            'cur:materiaCurso' => [$lengua4],
            'cur:materia' => [$lenguaEso],
        ]);
        // Criterion of a subject-in-a-year the resource does not declare: it
        // falls back to its stage subject.
        $lengua1 = new Item(204, 'Lengua Castellana y Literatura · 1º ESO', []);
        $critOther = new Item(402, 'SLCL01CE2.1 · Otro criterio', [
            'skos:notation' => ['SLCL01CE2.1'],
            'cur:materiaCurso' => [$lengua1],
            'cur:materia' => [$lenguaEso],
        ]);

        $this->api = new Api([
            901 => $bac, 902 => $eso, 101 => $c2bac, 102 => $c4eso, 103 => $c3eso,
            301 => $lenguaEso, 302 => $litCan, 201 => $lengua3, 202 => $lengua4, 203 => $litCan2,
            204 => $lengua1, 401 => $crit, 402 => $critOther,
        ]);
        $this->helper = (new CurriculumAnchor())->setView(new View($this->api, []));

        $this->resource = new Item(1000, 'REA', [
            self::SUBJECT => [$litCan2, $lengua3, $lengua4],
            self::LEVEL => [$c2bac, $c3eso, $c4eso],
            self::ASSESSES => [$crit, $critOther],
        ]);
    }

    public function testGroupsSubjectsInAYearByTheirStageSubject(): void
    {
        $rows = ($this->helper)($this->resource);

        $this->assertSame(
            ['Lengua Castellana y Literatura (ESO)', 'Literatura Canaria (Bachillerato)'],
            array_column($rows, 'label')
        );
        $this->assertSame(['3º ESO', '4º ESO'], array_column($rows[0]['levels'], 'label'));
        $this->assertSame(['2º Bachillerato'], array_column($rows[1]['levels'], 'label'));
        $this->assertFalse($rows[0]['orphan']);
        $this->assertCount(2, $rows, 'no «Otros niveles» row: every course is claimed');
    }

    public function testPrimedPageStillUsesThreeQueries(): void
    {
        $this->helper->primeResources([$this->resource]);
        ($this->helper)($this->resource);

        $this->assertSame(3, $this->api->calls, 'subjects, courses and stages: one batch each');
    }

    public function testAlignedTermsHangFromTheirSubjectByMateriaCurso(): void
    {
        $rows = $this->helper->alignment($this->resource, [self::ASSESSES]);

        $this->assertCount(2, $rows);
        $entries = $rows[0]['aligned'][self::ASSESSES];
        $this->assertSame(['SLCL01CE2.1', 'SLCL04CE1.1'], array_column($entries, 'label'));
        $byLabel = array_column($entries, null, 'label');
        $this->assertSame('Comprender textos orales y multimodales.', $byLabel['SLCL04CE1.1']['description']);
        $this->assertSame([], $rows[1]['aligned']);
    }

    public function testStageRankUsesNotationThenPosition(): void
    {
        $order = CurriculumAnchor::DEFAULT_STAGE_ORDER;

        $this->assertSame(0, CurriculumAnchor::stageRank('inf', null, $order));
        $this->assertSame(3, CurriculumAnchor::stageRank(' BAC ', '1', $order));
        $this->assertSame(2, CurriculumAnchor::stageRank(null, '2', $order));
        $this->assertSame(PHP_INT_MAX, CurriculumAnchor::stageRank('FP', null, $order));
    }

    public function testRankLevelsSortsByStageNotationAndId(): void
    {
        $ranks = CurriculumAnchor::rankLevels([
            5 => ['stage' => 2, 'notation' => 'S10'],
            6 => ['stage' => 2, 'notation' => 'S02'],
            7 => ['stage' => 0, 'notation' => ''],
            8 => ['stage' => PHP_INT_MAX, 'notation' => ''],
        ]);

        $this->assertSame([7 => 0, 6 => 1, 5 => 2, 8 => 3], $ranks);
    }

    public function testStageOrderSetting(): void
    {
        $this->assertSame(CurriculumAnchor::DEFAULT_STAGE_ORDER, $this->helper->stageOrder());

        $custom = (new CurriculumAnchor())->setView(new View($this->api, [
            'curriculum_stage_order' => "pri, inf\nESO  bac pri",
        ]));
        $this->assertSame(['PRI', 'INF', 'ESO', 'BAC'], $custom->stageOrder());
    }
}
