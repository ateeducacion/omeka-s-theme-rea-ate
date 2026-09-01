<?php

declare(strict_types=1);

namespace ReaAteTest\Helper;

use OmekaTheme\Helper\CurriculumAnchor;
use PHPUnit\Framework\TestCase;

class CurriculumAnchorTest extends TestCase
{
    /** Extrae ['Etiqueta' => ['Nivel', ...]] para comparar sin ruido. */
    private function flatten(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[$row['label']] = array_column($row['levels'], 'label');
        }
        return $out;
    }

    /** Ítem 3181 — tres materias con el mismo título colapsan en una fila. */
    public function testAgrupaMateriasRepetidas(): void
    {
        $subjects = [
            ['label' => 'Conocimiento del Medio Natural, Social y cultural', 'levelIds' => [24573]],
            ['label' => 'Conocimiento del Medio Natural, Social y cultural', 'levelIds' => [24574]],
            ['label' => 'Conocimiento del Medio Natural, Social y cultural', 'levelIds' => [24575]],
        ];
        $declared = [24573 => '3º Primaria', 24574 => '4º Primaria', 24575 => '5º Primaria'];
        $stage = [24573 => 2, 24574 => 2, 24575 => 2];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        $this->assertCount(1, $rows);
        $this->assertSame(
            ['Conocimiento del Medio Natural, Social y cultural' => ['3º Primaria', '4º Primaria', '5º Primaria']],
            $this->flatten($rows)
        );
        $this->assertFalse($rows[0]['orphan']);
    }

    /** Ítem 4674 — dos etiquetas distintas, y niveles ordenados por etapa. */
    public function testOrdenaNivelesPorEtapaYGruposPorSuPrimerNivel(): void
    {
        $subjects = [
            ['label' => 'Matemáticas I', 'levelIds' => [24559]],
            ['label' => 'Matemáticas', 'levelIds' => [24561]],
            ['label' => 'Matemáticas', 'levelIds' => [24562]],
            ['label' => 'Matemáticas', 'levelIds' => [24571]],
            ['label' => 'Matemáticas', 'levelIds' => [24572]],
        ];
        $declared = [
            24559 => '1º Bachillerato', 24561 => '1º ESO', 24562 => '2º ESO',
            24571 => '1º Primaria', 24572 => '2º Primaria',
        ];
        $stage = [24559 => 4, 24561 => 3, 24562 => 3, 24571 => 2, 24572 => 2];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        // Primaria (2) antes que ESO (3) antes que Bachillerato (4).
        $this->assertSame(
            [
                'Matemáticas' => ['1º Primaria', '2º Primaria', '1º ESO', '2º ESO'],
                'Matemáticas I' => ['1º Bachillerato'],
            ],
            $this->flatten($rows)
        );
    }

    /** Ítem 4676 — 8 valores de about → 6 filas, empates resueltos alfabéticamente. */
    public function testDesempataAlfabeticamente(): void
    {
        $subjects = [
            ['label' => 'Física y Química', 'levelIds' => [24559]],
            ['label' => 'Matemáticas aplicadas a las ciencias sociales I', 'levelIds' => [24559]],
            ['label' => 'Matemáticas I', 'levelIds' => [24559]],
            ['label' => 'Física', 'levelIds' => [24560]],
            ['label' => 'Matemáticas II', 'levelIds' => [24560]],
            ['label' => 'Física y Química', 'levelIds' => [24562]],
            ['label' => 'Matemáticas', 'levelIds' => [24562]],
            ['label' => 'Matemáticas', 'levelIds' => [24573]],
        ];
        $declared = [
            24559 => '1º Bachillerato', 24560 => '2º Bachillerato',
            24562 => '2º ESO', 24573 => '3º Primaria',
        ];
        $stage = [24559 => 4, 24560 => 4, 24562 => 3, 24573 => 2];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        $this->assertSame(
            [
                'Matemáticas' => ['3º Primaria', '2º ESO'],
                'Física y Química' => ['2º ESO', '1º Bachillerato'],
                'Matemáticas aplicadas a las ciencias sociales I' => ['1º Bachillerato'],
                'Matemáticas I' => ['1º Bachillerato'],
                'Física' => ['2º Bachillerato'],
                'Matemáticas II' => ['2º Bachillerato'],
            ],
            $this->flatten($rows)
        );
    }

    /** Ítem 5047 — nivel declarado que ninguna materia reclama. */
    public function testNivelHuerfanoVaAFilaPropia(): void
    {
        $subjects = [['label' => 'Física', 'levelIds' => [24560]]];
        $declared = [24560 => '2º Bachillerato', 24562 => '2º ESO'];
        $stage = [24560 => 4, 24562 => 3];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        $this->assertSame(
            ['Física' => ['2º Bachillerato'], 'Otros niveles' => ['2º ESO']],
            $this->flatten($rows)
        );
        $this->assertTrue($rows[1]['orphan'], 'la fila de huérfanos se marca');
    }

    /** Ítem 5051 — la materia apunta a 4º ESO, que el recurso no declara: se descarta. */
    public function testIntersectaConLosNivelesDeclarados(): void
    {
        $subjects = [
            ['label' => 'Biología, Geología y Ciencias ambientales', 'levelIds' => [24559]],
            ['label' => 'Biología y Geología', 'levelIds' => [24564]], // 4º ESO, no declarado
        ];
        $declared = [24559 => '1º Bachillerato'];
        $stage = [24559 => 4, 24564 => 3];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        $this->assertSame(
            [
                'Biología, Geología y Ciencias ambientales' => ['1º Bachillerato'],
                'Biología y Geología' => [],
            ],
            $this->flatten($rows)
        );
    }

    /** Ítem 37129 — about literal sin nivel propio + un único huérfano: se unen. */
    public function testAdjuntaHuerfanoAlGrupoUnicoSinNiveles(): void
    {
        $subjects = [['label' => 'Matemáticas', 'levelIds' => []]];
        $declared = [24562 => '2º ESO'];
        $stage = [24562 => 3];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        $this->assertSame(['Matemáticas' => ['2º ESO']], $this->flatten($rows));
        $this->assertFalse($rows[0]['orphan']);
    }

    /** La excepción anterior NO se aplica con más de un grupo. */
    public function testNoAdjuntaHuerfanosSiHayVariosGrupos(): void
    {
        $subjects = [
            ['label' => 'Matemáticas', 'levelIds' => []],
            ['label' => 'Física', 'levelIds' => []],
        ];
        $declared = [24562 => '2º ESO'];
        $stage = [24562 => 3];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        // Los dos grupos se quedan sin niveles, así que empatan en la clave de
        // orden y desempatan alfabéticamente (§4.2): Física antes que
        // Matemáticas, aunque el recurso las declare al revés. El orden de
        // aparición no se respeta en ningún grupo, tenga niveles o no.
        $this->assertSame(
            ['Física' => [], 'Matemáticas' => [], 'Otros niveles' => ['2º ESO']],
            $this->flatten($rows)
        );
    }

    /** Normalización: espacios sobrantes y mayúsculas no crean grupos distintos. */
    public function testNormalizaEtiquetas(): void
    {
        $subjects = [
            ['label' => 'Matemáticas', 'levelIds' => [24571]],
            ['label' => '  matemáticas ', 'levelIds' => [24572]],
            ['label' => 'Matemáticas', 'levelIds' => [24571]], // duplicado exacto de nivel
        ];
        $declared = [24571 => '1º Primaria', 24572 => '2º Primaria'];
        $stage = [24571 => 2, 24572 => 2];

        $rows = CurriculumAnchor::groupRows($subjects, $declared, $stage, 'Otros niveles');

        $this->assertCount(1, $rows);
        $this->assertSame('Matemáticas', $rows[0]['label'], 'conserva la primera forma vista');
        $this->assertSame(['1º Primaria', '2º Primaria'], array_column($rows[0]['levels'], 'label'));
    }

    /** Sin materias no hay nada que pintar. */
    public function testSinMateriasDevuelveVacio(): void
    {
        $rows = CurriculumAnchor::groupRows([], [24571 => '1º Primaria'], [24571 => 2], 'Otros niveles');
        $this->assertSame([], $rows);
    }
}
