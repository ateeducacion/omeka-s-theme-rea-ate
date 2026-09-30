<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\CurriculumAnchor;
use PHPUnit\Framework\TestCase;

class CurriculumAnchorAlignmentTest extends TestCase
{
    private const T_ASSESSES = 'lrmi:assesses';
    private const T_TEACHES = 'lrmi:teaches';

    private function rows(): array
    {
        return [
            ['label' => 'Lengua Castellana y Literatura', 'orphan' => false, 'levels' => [['id' => 1, 'label' => '1º ESO']]],
            ['label' => 'Historia de España', 'orphan' => false, 'levels' => [['id' => 5, 'label' => '2º Bachillerato']]],
        ];
    }

    private function entry(string $term, ?int $id, string $label, array $subjectIds = [], array $subjectLabels = []): array
    {
        return compact('term', 'id', 'label', 'subjectIds', 'subjectLabels') + ['description' => '', 'url' => null];
    }

    private function attach(array $entries, ?array $rows = null): array
    {
        // Las tres asignaturas homónimas (una por curso) comparten clave de grupo.
        $subjectKeyById = [
            24674 => 'lengua castellana y literatura',
            24686 => 'lengua castellana y literatura',
            24644 => 'historia de españa',
        ];
        return CurriculumAnchor::attachAligned(
            $rows ?? $this->rows(), $subjectKeyById, [self::T_ASSESSES, self::T_TEACHES], $entries, 'Otros'
        );
    }

    private function labels(array $row, string $term): array
    {
        return array_column($row['aligned'][$term] ?? [], 'label');
    }

    public function testCuelgaCadaCriterioDeSuMateriaPorElTermset(): void
    {
        $rows = $this->attach([
            $this->entry(self::T_ASSESSES, 1, 'BHIA02CE1.1', [25174, 24644]),
            $this->entry(self::T_TEACHES, 2, 'SLCL01SB.1', [24674]),
            $this->entry(self::T_TEACHES, 3, 'SLCL02SB.1', [24686]),
        ]);

        $this->assertCount(2, $rows);
        $this->assertSame(['SLCL01SB.1', 'SLCL02SB.1'], $this->labels($rows[0], self::T_TEACHES));
        $this->assertSame(['BHIA02CE1.1'], $this->labels($rows[1], self::T_ASSESSES));
        $this->assertArrayNotHasKey(self::T_TEACHES, $rows[1]['aligned']);
    }

    public function testCaeAlNombreDeLaMateriaSiLaAsignaturaNoEsDelRecurso(): void
    {
        $rows = $this->attach([$this->entry(self::T_TEACHES, 9, 'SLCL04SB.1', [99999], ['Lengua Castellana y  literatura'])]);

        $this->assertSame(['SLCL04SB.1'], $this->labels($rows[0], self::T_TEACHES));
    }

    public function testLoQueNoCasaVaAUnGrupoOtrosAlFinal(): void
    {
        $rows = $this->attach([
            $this->entry(self::T_ASSESSES, 7, 'XYZ1', [4242], ['Matemáticas']),
            $this->entry(self::T_ASSESSES, null, 'literal suelto'),
        ]);

        $this->assertCount(3, $rows);
        $this->assertSame('Otros', $rows[2]['label']);
        $this->assertTrue($rows[2]['orphan']);
        $this->assertSame([], $rows[2]['levels']);
        $this->assertSame(['literal suelto', 'XYZ1'], $this->labels($rows[2], self::T_ASSESSES));
    }

    public function testReutilizaLaFilaHuerfanaDeNiveles(): void
    {
        $rows = $this->rows();
        $rows[] = ['label' => 'Otros niveles', 'orphan' => true, 'levels' => [['id' => 8, 'label' => '4º ESO']]];

        $rows = $this->attach([$this->entry(self::T_ASSESSES, 7, 'XYZ1')], $rows);

        $this->assertCount(3, $rows);
        $this->assertSame(['XYZ1'], $this->labels($rows[2], self::T_ASSESSES));
    }

    public function testOrdenaNaturalmenteYDeduplica(): void
    {
        $rows = $this->attach([
            $this->entry(self::T_ASSESSES, 3, 'CE10', [24644]),
            $this->entry(self::T_ASSESSES, 2, 'CE2', [24644]),
            $this->entry(self::T_ASSESSES, 2, 'CE2', [24644]),
        ]);

        $this->assertSame(['CE2', 'CE10'], $this->labels($rows[1], self::T_ASSESSES));
    }

    public function testRespetaElOrdenDeLosTerminos(): void
    {
        $rows = $this->attach([
            $this->entry(self::T_TEACHES, 2, 'SB', [24644]),
            $this->entry(self::T_ASSESSES, 1, 'CE', [24644]),
        ]);

        $this->assertSame([self::T_ASSESSES, self::T_TEACHES], array_keys($rows[1]['aligned']));
    }
}
