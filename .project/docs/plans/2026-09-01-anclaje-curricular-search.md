# Anclaje curricular en la búsqueda facetada — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Agrupar `schema:about` y `lrmi:educationalLevel` en los resultados de la búsqueda facetada en píldoras compuestas «materia › niveles», sin materias repetidas y sin enlaces que devuelvan cero resultados.

**Architecture:** Un view helper del tema (`CurriculumAnchor`) resuelve la relación materia→nivel leyendo el `lrmi:educationalLevel` de cada ítem *Asignatura* enlazado, con tres consultas por lote por página. La lógica de agrupación vive en un método estático puro, sin dependencias de Omeka, para poder probarla en PHPUnit. Un override de `view/search/results.phtml` sustituye el renderizado de esas dos propiedades por un partial.

**Tech Stack:** PHP 7.4+ (Laminas view helpers), Omeka S 4.2 API de representaciones, módulo AdvancedSearch, SASS (gulp), JavaScript sin dependencias, PHPUnit 9.5.

**Spec:** `.project/docs/specs/2026-09-01-anclaje-curricular-busqueda-facetada-design.md`

## Global Constraints

- Términos de propiedad **siempre por nombre cualificado** (`prefix:term`), nunca por ID interno de la instancia. Fuente: `.project/context/metadata_model.md`.
- El tema **no codifica valores** de vocabularios controlados; los lee del recurso.
- Convención de commits: `type(scope): descripción en imperativo`, máx. 72 caracteres, sin punto final. Scopes de este trabajo: `php`, `sass`, `js`, `search`. Fuente: `.project/skills/process/git-commit-convention.md`.
- Los view helpers del tema viven en `helper/<Nombre>.php`, namespace `OmekaTheme\Helper`, y se registran con `helpers[] = "<Nombre>"` en `config/theme.ini`.
- Todo texto visible pasa por `translate()` y lleva el comentario `// @translate` donde el extractor lo necesite.
- Toda salida a HTML pasa por `escapeHtml` / `escapeHtmlAttr`. Ningún `echo` directo de valor de catálogo.
- Degradación obligatoria: si el modelo de datos no encaja, el helper devuelve `null` y el template cae al renderizado original del módulo.
- Breakpoint móvil: `$md: 768px` (`asset/sass/abstracts/variables/_breakpoints.scss`).
- Tokens de color disponibles: `--ate-surface-soft`, `--ate-hairline`, `--ate-text-muted`, `--ate-text-body`, `--ate-radius-pill`.

## Dependencia previa

La suite PHPUnit (`test/phpunit.xml`, `test/bootstrap.php`, `test/Stubs/`) y el `composer.json` con `require-dev` **viven en la rama `security/audit-fixes`, no en `master`**. Esta rama sale de `master`.

**Decisión:** no se duplica el andamiaje aquí, porque `security/audit-fixes` reescribe `composer.json` entero y copiarlo garantiza un conflicto de merge. La Tarea 1 crea únicamente el fichero de test, que no colisiona con nada. Para **ejecutarlo** hay que estar sobre una rama que tenga el andamiaje. La Tarea 1 incluye el paso para conseguirlo.

## File Structure

| Fichero | Responsabilidad |
|---|---|
| `helper/CurriculumAnchor.php` | **Crear.** Dos capas: `groupRows()` estático puro (agrupación, intersección, huérfanos, orden) y `__invoke()` (lee la representación, lanza las consultas por lote, delega). |
| `test/ReaAteTest/Helper/CurriculumAnchorTest.php` | **Crear.** Prueba `groupRows()` con fixtures reales de la instancia. |
| `config/theme.ini` | **Modificar.** Registro del helper + 2 settings. |
| `view/common/curriculum-anchor.phtml` | **Crear.** Renderiza las filas que devuelve el helper. |
| `view/search/results.phtml` | **Crear** (override del módulo). Intercepta los dos términos y delega en el partial. |
| `asset/sass/components/search-results/_search-results-list.scss` | **Modificar.** Sustituye el bloque de píldoras de nivel/materia. |
| `asset/js/advanced-search-list.js` | **Modificar.** `processItem()` ignora el bloque de anclaje. |

---

### Task 1: Capa pura de agrupación

**Files:**
- Create: `helper/CurriculumAnchor.php`
- Test: `test/ReaAteTest/Helper/CurriculumAnchorTest.php`

**Interfaces:**
- Consumes: nada.
- Produces: `OmekaTheme\Helper\CurriculumAnchor::groupRows(array $subjects, array $declaredLevels, array $levelStage, string $orphanLabel): array`
  - `$subjects`: `[['label' => string, 'levelIds' => int[]], ...]` en orden de aparición en el recurso.
  - `$declaredLevels`: `[int $levelId => string $label]`, los niveles que declara el recurso.
  - `$levelStage`: `[int $levelId => int $position]`, posición de la etapa del curso.
  - `$orphanLabel`: rótulo de la fila de huérfanos.
  - Devuelve: `[['label' => string, 'orphan' => bool, 'levels' => [['id' => int, 'label' => string], ...]], ...]`

- [ ] **Step 1: Preparar una rama con el andamiaje de tests**

El andamiaje está en `security/audit-fixes`. Para poder ejecutar PHPUnit sin duplicarlo, se trabaja sobre una rama temporal de integración:

```bash
git checkout -b tmp/anclaje-con-tests feature/anclaje-curricular-search
git merge --no-edit security/audit-fixes
composer install
```

Si el merge da conflictos, resolverlos a favor de `security/audit-fixes` en `composer.json` y `.gitignore`. Esta rama es **solo para ejecutar tests**: los commits del trabajo se hacen en `feature/anclaje-curricular-search` (Step 8).

- [ ] **Step 2: Escribir el test que falla**

Crear `test/ReaAteTest/Helper/CurriculumAnchorTest.php`. Las fixtures son datos reales del volcado de desarrollo; los IDs son ítems existentes.

```php
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
```

- [ ] **Step 3: Ejecutar el test para verificar que falla**

Run: `./vendor/bin/phpunit -c test/phpunit.xml --filter CurriculumAnchorTest`
Expected: FAIL con `Class "OmekaTheme\Helper\CurriculumAnchor" not found`.

- [ ] **Step 4: Escribir la implementación mínima**

Crear `helper/CurriculumAnchor.php` con solo la capa pura (el `__invoke()` llega en la Tarea 2):

```php
<?php

namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Agrupa las materias (schema:about) de un recurso con los niveles
 * (lrmi:educationalLevel) que les corresponden, para pintarlas como una
 * píldora compuesta por materia en lugar de dos tiras paralelas.
 *
 * La relación materia→nivel no es deducible por posición: un recurso puede
 * declarar 4 niveles y 8 materias. Se lee del propio ítem Asignatura, que
 * lleva su lrmi:educationalLevel.
 *
 * Diseño: .project/docs/specs/2026-09-01-anclaje-curricular-busqueda-facetada-design.md
 */
class CurriculumAnchor extends AbstractHelper
{
    /**
     * Capa pura: sin dependencias de Omeka, para poder probarla en PHPUnit.
     *
     * @param array $subjects       [['label' => string, 'levelIds' => int[]], ...]
     * @param array $declaredLevels [int $levelId => string $label] niveles del recurso
     * @param array $levelStage     [int $levelId => int $position] posición de la etapa
     * @param string $orphanLabel   rótulo para los niveles que ninguna materia reclama
     * @return array [['label' => string, 'orphan' => bool, 'levels' => [['id' => int, 'label' => string], ...]], ...]
     */
    public static function groupRows(
        array $subjects,
        array $declaredLevels,
        array $levelStage,
        string $orphanLabel
    ): array {
        $groups = [];
        foreach ($subjects as $subject) {
            $label = trim(preg_replace('/\s+/u', ' ', (string) ($subject['label'] ?? '')));
            if ($label === '') {
                continue;
            }
            $key = mb_strtolower($label, 'UTF-8');
            if (!isset($groups[$key])) {
                // Se conserva la primera forma vista de la etiqueta.
                $groups[$key] = ['label' => $label, 'levelIds' => []];
            }
            foreach ($subject['levelIds'] ?? [] as $levelId) {
                // Intersección: solo sobreviven los niveles que el recurso declara.
                // Sin esto se pintarían enlaces que devuelven cero resultados.
                if (isset($declaredLevels[$levelId])
                    && !in_array($levelId, $groups[$key]['levelIds'], true)
                ) {
                    $groups[$key]['levelIds'][] = $levelId;
                }
            }
        }

        if (!$groups) {
            return [];
        }

        $used = [];
        foreach ($groups as $group) {
            foreach ($group['levelIds'] as $levelId) {
                $used[$levelId] = true;
            }
        }
        $orphans = array_keys(array_diff_key($declaredLevels, $used));

        // Un único grupo sin niveles absorbe los huérfanos: es el caso del
        // about literal suelto, donde el nivel solo puede venir del recurso.
        // Con dos o más grupos, o dos o más huérfanos, no se puede desambiguar.
        if (count($groups) === 1 && $orphans) {
            $onlyKey = array_key_first($groups);
            if (!$groups[$onlyKey]['levelIds']) {
                $groups[$onlyKey]['levelIds'] = $orphans;
                $orphans = [];
            }
        }

        $sortLevels = static function (array $levelIds) use ($levelStage): array {
            usort($levelIds, static function ($a, $b) use ($levelStage) {
                $sa = $levelStage[$a] ?? PHP_INT_MAX;
                $sb = $levelStage[$b] ?? PHP_INT_MAX;
                return ($sa <=> $sb) ?: ($a <=> $b);
            });
            return $levelIds;
        };

        $expand = static function (array $levelIds) use ($declaredLevels): array {
            return array_map(
                static fn ($id) => ['id' => $id, 'label' => $declaredLevels[$id]],
                $levelIds
            );
        };

        $rows = [];
        foreach ($groups as $group) {
            $rows[] = [
                'label' => $group['label'],
                'orphan' => false,
                'levels' => $expand($sortLevels($group['levelIds'])),
            ];
        }

        // Los grupos se ordenan por su primer nivel ya ordenado; los que se
        // quedaron sin ninguno caen al final.
        usort($rows, static function ($a, $b) use ($levelStage) {
            $key = static function (array $row) use ($levelStage): array {
                if (!$row['levels']) {
                    return [PHP_INT_MAX, PHP_INT_MAX];
                }
                $first = $row['levels'][0]['id'];
                return [$levelStage[$first] ?? PHP_INT_MAX, $first];
            };
            return ($key($a) <=> $key($b))
                ?: (mb_strtolower($a['label'], 'UTF-8') <=> mb_strtolower($b['label'], 'UTF-8'));
        });

        if ($orphans) {
            $rows[] = [
                'label' => $orphanLabel,
                'orphan' => true,
                'levels' => $expand($sortLevels($orphans)),
            ];
        }

        return $rows;
    }
}
```

- [ ] **Step 5: Ejecutar los tests para verificar que pasan**

Run: `./vendor/bin/phpunit -c test/phpunit.xml --filter CurriculumAnchorTest`
Expected: PASS, 9 tests.

- [ ] **Step 6: Verificar que no se ha roto la suite existente**

Run: `./vendor/bin/phpunit -c test/phpunit.xml`
Expected: PASS, toda la suite en verde.

- [ ] **Step 7: Comprobar la sintaxis**

Run: `php -l helper/CurriculumAnchor.php`
Expected: `No syntax errors detected`.

> No se ejecuta `phpcs`: el repositorio no tiene `phpcs.xml` ni ruleset, así que correría
> contra el estándar por defecto de la máquina y daría ruido no accionable.

- [ ] **Step 8: Commit en la rama de trabajo**

La rama temporal solo sirve para ejecutar. El commit va a la rama real.

> **Nada de `git stash` aquí.** Los dos ficheros son nuevos y sin seguimiento, y
> `git stash push <rutas>` con rutas exclusivamente *untracked* **no guarda nada**
> (hace falta `-u`), así que el `git stash pop` siguiente desapilaría un stash
> ajeno del usuario y volcaría cambios no relacionados sobre la rama. No hace
> falta: los ficheros no existen en ninguna de las dos ramas, así que sobreviven
> al cambio de rama por sí solos.

```bash
git checkout feature/anclaje-curricular-search
git status --short   # → ?? helper/CurriculumAnchor.php  ?? test/
git add helper/CurriculumAnchor.php test/ReaAteTest/Helper/CurriculumAnchorTest.php
git commit -m "feat(php): agrupar materias y niveles con CurriculumAnchor::groupRows"
```

Comprobar antes de commitear que `git stash list` sigue teniendo las mismas
entradas que antes de empezar la tarea.

---

### Task 2: Capa de acceso y registro del helper

**Files:**
- Modify: `helper/CurriculumAnchor.php` (añadir `__invoke()` y privados)
- Modify: `config/theme.ini`

**Interfaces:**
- Consumes: `CurriculumAnchor::groupRows()` de la Tarea 1.
- Produces: `$this->curriculumAnchor($resource): ?array` — invocable desde cualquier template. Devuelve las filas de `groupRows()` enriquecidas con `url` por nivel, o `null` si el modelo de datos no encaja.
  - Forma exacta: `[['label' => string, 'orphan' => bool, 'levels' => [['id' => int, 'label' => string, 'url' => string], ...]], ...]`

- [ ] **Step 1: Añadir los settings a `config/theme.ini`**

Localizar la sección donde están `advancedsearch_grouped_facets` y `advancedsearch_termset_property` (alrededor de la línea 407) y añadir a continuación:

```ini
elements.curriculum_anchor_subject_property.name                 = "curriculum_anchor_subject_property"
elements.curriculum_anchor_subject_property.type                 = "Text"
elements.curriculum_anchor_subject_property.options.label        = "Subject property for the curriculum anchor"
elements.curriculum_anchor_subject_property.options.info         = "Property holding the subject on the resource. Its values must be linked items that carry their own educational level. Empty disables the grouped curriculum anchor in search results."
elements.curriculum_anchor_subject_property.attributes.value     = "schema:about"

elements.curriculum_anchor_level_property.name                   = "curriculum_anchor_level_property"
elements.curriculum_anchor_level_property.type                   = "Text"
elements.curriculum_anchor_level_property.options.label          = "Educational level property"
elements.curriculum_anchor_level_property.options.info           = "Property holding the educational level, both on the resource and on each linked subject item. Default: lrmi:educationalLevel"
elements.curriculum_anchor_level_property.attributes.value       = "lrmi:educationalLevel"
```

- [ ] **Step 2: Registrar el helper**

En `config/theme.ini`, tras `helpers[] = "HtmlAllowlist"` (línea 18):

```ini
helpers[] = "CurriculumAnchor"
```

- [ ] **Step 3: Implementar `__invoke()` y los privados**

Añadir dentro de la clase `CurriculumAnchor`, **antes** de `groupRows()`:

```php
    /**
     * @param \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource
     * @return array|null Filas listas para pintar, o null si el modelo no encaja.
     */
    public function __invoke($resource): ?array
    {
        $view = $this->getView();

        $subjectTerm = trim((string) $view->themeSetting('curriculum_anchor_subject_property'));
        $levelTerm = trim((string) $view->themeSetting('curriculum_anchor_level_property'));
        if ($subjectTerm === '' || $levelTerm === '') {
            return null;
        }

        // Niveles que declara el propio recurso: son el universo permitido.
        $declaredLevels = [];
        foreach ($resource->value($levelTerm, ['all' => true]) ?: [] as $value) {
            $linked = $this->linkedResource($value);
            if ($linked) {
                $declaredLevels[$linked->id()] = (string) $linked->displayTitle();
            }
        }

        // Materias, en orden de aparición.
        $subjects = [];
        $subjectIds = [];
        foreach ($resource->value($subjectTerm, ['all' => true]) ?: [] as $value) {
            $linked = $this->linkedResource($value);
            if ($linked) {
                $subjects[] = ['id' => $linked->id(), 'label' => (string) $linked->displayTitle()];
                $subjectIds[$linked->id()] = true;
            } else {
                // Literal suelto: sin ítem detrás, no tiene nivel propio.
                $subjects[] = ['id' => null, 'label' => (string) $value];
            }
        }
        if (!$subjects) {
            return null;
        }

        // Consulta por lote 1: los ítems asignatura, para leer su nivel.
        $subjectLevels = [];
        foreach ($this->fetchItems(array_keys($subjectIds)) as $item) {
            $ids = [];
            foreach ($item->value($levelTerm, ['all' => true]) ?: [] as $value) {
                $linked = $this->linkedResource($value);
                if ($linked) {
                    $ids[] = $linked->id();
                }
            }
            $subjectLevels[$item->id()] = $ids;
        }
        foreach ($subjects as &$subject) {
            $subject['levelIds'] = $subject['id'] === null
                ? []
                : ($subjectLevels[$subject['id']] ?? []);
        }
        unset($subject);

        $levelStage = $this->levelStages(array_keys($declaredLevels));

        $rows = self::groupRows(
            $subjects,
            $declaredLevels,
            $levelStage,
            $view->translate('Otros niveles') // @translate
        );
        if (!$rows) {
            return null;
        }

        // Enlace por nivel: el mismo filtro que ya genera el módulo.
        foreach ($rows as &$row) {
            foreach ($row['levels'] as &$level) {
                $level['url'] = $this->levelUrl($levelTerm, $level['id']);
            }
            unset($level);
        }
        unset($row);

        return $rows;
    }

    /**
     * Posición de la etapa de cada curso, para ordenar Infantil → Bachillerato.
     * Consultas por lote 2 y 3.
     *
     * @param int[] $levelIds
     * @return array [int $levelId => int $position]
     */
    private function levelStages(array $levelIds): array
    {
        $view = $this->getView();
        $termsetProperty = trim((string) $view->themeSetting('advancedsearch_termset_property'))
            ?: 'schema:inDefinedTermSet';

        $levelToTermset = [];
        $termsetIds = [];
        foreach ($this->fetchItems($levelIds) as $item) {
            foreach ($item->value($termsetProperty, ['all' => true]) ?: [] as $value) {
                $linked = $this->linkedResource($value);
                if ($linked) {
                    $levelToTermset[$item->id()] = $linked->id();
                    $termsetIds[$linked->id()] = true;
                    break;
                }
            }
        }

        $positions = [];
        foreach ($this->fetchItems(array_keys($termsetIds)) as $item) {
            $position = $item->value('schema:position');
            $positions[$item->id()] = $position === null ? PHP_INT_MAX : (int) (string) $position;
        }

        $stages = [];
        foreach ($levelIds as $levelId) {
            $termsetId = $levelToTermset[$levelId] ?? null;
            $stages[$levelId] = $termsetId === null
                ? PHP_INT_MAX
                : ($positions[$termsetId] ?? PHP_INT_MAX);
        }
        return $stages;
    }

    /**
     * Una sola consulta por lote sobre IDs, columna indexada.
     *
     * @param int[] $ids
     * @return \Omeka\Api\Representation\ItemRepresentation[]
     */
    private function fetchItems(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        return $this->getView()->api()
            ->search('items', ['id' => $ids, 'limit' => count($ids)])
            ->getContent();
    }

    /**
     * @param \Omeka\Api\Representation\ValueRepresentation $value
     * @return \Omeka\Api\Representation\AbstractResourceEntityRepresentation|null
     */
    private function linkedResource($value)
    {
        $type = (string) $value->type();
        // El tipo llega como "resource" o como "resource:item" según el origen.
        if ($type !== 'resource' && strpos($type, 'resource') === false) {
            return null;
        }
        return $value->valueResource();
    }

    private function levelUrl(string $levelTerm, int $levelId): string
    {
        return $this->getView()->url(
            'site/resource',
            ['controller' => 'item', 'action' => 'browse'],
            [
                'query' => [
                    'property' => [
                        ['property' => $levelTerm, 'type' => 'res', 'text' => (string) $levelId],
                    ],
                ],
            ],
            true
        );
    }
```

- [ ] **Step 4: Verificar que la capa pura sigue verde**

Run: `./vendor/bin/phpunit -c test/phpunit.xml --filter CurriculumAnchorTest`
Expected: PASS, 9 tests. Los tests solo tocan `groupRows()`, así que no deben verse afectados.

- [ ] **Step 5: Verificar la URL generada contra la que produce el módulo**

Con la instancia arrancada (`docker compose up -d`), comparar el `href` que genera el helper con el que ya emite el módulo hoy:

```bash
curl -s "http://localhost:8080/s/ceiplajares/rea?fulltext_search=" \
  | grep -o 'href="[^"]*educationalLevel[^"]*"' | head -1
```

Expected: la ruta base coincide con `/s/ceiplajares/item?property%5B0%5D%5Bproperty%5D=lrmi%3AeducationalLevel&property%5B0%5D%5Btype%5D=res&property%5B0%5D%5Btext%5D=<id>`. Si `url()` produce otra ruta, ajustar el nombre de ruta en `levelUrl()` antes de seguir.

- [ ] **Step 6: Commit**

```bash
git add helper/CurriculumAnchor.php config/theme.ini
git commit -m "feat(php): resolver materias y niveles por lotes en CurriculumAnchor"
```

---

### Task 3: Partial de renderizado y estilos

**Files:**
- Create: `view/common/curriculum-anchor.phtml`
- Modify: `asset/sass/components/search-results/_search-results-list.scss:139-160`

**Interfaces:**
- Consumes: `$this->curriculumAnchor($resource)` de la Tarea 2.
- Produces: el partial `common/curriculum-anchor`, que espera la variable `$rows` (array del helper) y `$label` (string, rótulo de la fila). Emite un `<div class="property property--curriculum-anchor">`.

- [ ] **Step 1: Crear el partial**

Crear `view/common/curriculum-anchor.phtml`:

```php
<?php
/**
 * Píldoras compuestas «materia › niveles» para los resultados de búsqueda.
 *
 * @var array  $rows  Filas de OmekaTheme\Helper\CurriculumAnchor
 * @var string $label Rótulo de la fila
 */
$escape = $this->plugin('escapeHtml');
$escapeAttr = $this->plugin('escapeHtmlAttr');

if (!$rows) {
    return;
}
?>
<div class="property property--curriculum-anchor">
    <dt><?php echo $escape($label); ?></dt>
    <?php foreach ($rows as $row): ?>
    <dd class="curriculum-anchor__group<?php echo $row['orphan'] ? ' curriculum-anchor__group--orphan' : ''; ?>">
        <span class="curriculum-anchor__subject"><?php echo $escape($row['label']); ?></span>
        <?php foreach ($row['levels'] as $level): ?>
        <a class="curriculum-anchor__level" href="<?php echo $escapeAttr($level['url']); ?>"><?php
            echo $escape($level['label']);
        ?></a>
        <?php endforeach; ?>
    </dd>
    <?php endforeach; ?>
</div>
```

- [ ] **Step 2: Sustituir el bloque de estilos**

En `asset/sass/components/search-results/_search-results-list.scss`, **eliminar** el bloque que empieza en `.property[data-term="lrmi:educationalLevel"],` y su pareja `.property[data-term="schema:about"] {` (el que contiene `max-width: 18ch`), y poner en su lugar:

```scss
        // ---- Anclaje curricular: píldora compuesta materia | niveles ----
        // Fila de ancho completo, igual que teaches y relation. La materia es
        // rótulo; cada nivel es un enlace independiente separado por hairline.
        .property--curriculum-anchor {
            // order 9 lo coloca justo bajo el título y ANTES de la descripción
            // (order 10), como en la maqueta aprobada. Es deliberado: no
            // "corregirlo" a un valor mayor. Funciona porque el contenedor
            // `dl.properties` es `display: contents` (línea 51 de este fichero)
            // y los .property son items flex directos del `li`.
            order: 9;
            flex: 0 0 100%;
            margin: 0;
            padding: 0.5rem 0 0;
            border-top: 1px solid $color__gray-93;

            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 0.4rem 0.5rem;

            dt {
                flex: 0 0 auto;
                margin: 0 0.25rem 0 0;
                padding-top: 3px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: $color__gray-46;
            }
        }

        .curriculum-anchor__group {
            flex: 0 0 auto;
            // Neutraliza `.resource-list .resource { width: 100% !important }`,
            // que alcanza a los dd por la clase compartida `resource`.
            width: auto !important;
            margin: 0;
            display: inline-flex;
            flex-wrap: wrap;
            align-items: stretch;
            max-width: 100%;
            border: 1px solid var(--ate-hairline);
            border-radius: var(--ate-radius-pill);
            background: $color__white;
            overflow: hidden;
            font-size: 0.75rem;
        }

        .curriculum-anchor__subject {
            display: flex;
            align-items: center;
            padding: 3px 11px;
            background: var(--ate-surface-soft);
            color: var(--ate-text-body);
            font-weight: 600;
            // Sin max-width: los nombres largos envuelven en dos líneas en vez
            // de truncarse. «Matemáticas aplicadas a las ciencias sociales I»
            // es indistinguible truncada.
            white-space: normal;
            line-height: 1.3;
        }

        .curriculum-anchor__level {
            display: flex;
            align-items: center;
            padding: 3px 10px;
            border-left: 1px solid var(--ate-hairline);
            color: var(--ate-text-muted);
            text-decoration: none;
            white-space: nowrap;

            &:hover,
            &:focus-visible {
                background: var(--ate-surface-soft);
                color: var(--ate-text-body);
                text-decoration: underline;
            }
        }

        .curriculum-anchor__group--orphan .curriculum-anchor__subject {
            font-weight: 500;
            font-style: italic;
            color: var(--ate-text-muted);
        }
```

Y dentro del bloque `@media (max-width: $md)` de ese mismo fichero, añadir:

```scss
            .property--curriculum-anchor {
                flex-direction: column;
                gap: 0.35rem;

                dt { padding-top: 0; }
            }

            .curriculum-anchor__group {
                width: 100% !important;
            }
```

- [ ] **Step 3: Compilar los estilos**

Run: `npx gulp css`
Expected: sin errores de SASS; `asset/css/style.css` se regenera.

- [ ] **Step 4: Verificar que las clases llegan al CSS compilado**

Run: `grep -c "curriculum-anchor__level" asset/css/style.css`
Expected: un número mayor que 0.

- [ ] **Step 5: Verificar que el truncado a 18ch ha desaparecido**

Run: `grep -c "18ch" asset/css/style.css`
Expected: `0`.

- [ ] **Step 6: Commit**

```bash
git add view/common/curriculum-anchor.phtml asset/sass/components/search-results/_search-results-list.scss asset/css/style.css
git commit -m "feat(sass): pildora compuesta materia y niveles en resultados"
```

---

### Task 4: Override de `results.phtml` y ajuste del JS

**Files:**
- Create: `view/search/results.phtml`
- Modify: `asset/js/advanced-search-list.js:194-224`

**Interfaces:**
- Consumes: el partial `common/curriculum-anchor` (Tarea 3) y el helper `curriculumAnchor` (Tarea 2).
- Produces: nada consumido por tareas posteriores.

- [ ] **Step 1: Copiar el template del módulo**

El contenedor en marcha (`omeka-s-moduletemplate-omekas-1`) pertenece a otro proyecto
compose, así que `docker compose cp` desde este repositorio no resuelve el servicio. Copiar
con `docker exec`:

```bash
mkdir -p view/search
docker exec omeka-s-moduletemplate-omekas-1 \
  cat /var/www/html/volume/modules/AdvancedSearch/view/search/results.phtml > view/search/results.phtml
```

Verificar que la copia no está vacía y trae el bucle de propiedades:

```bash
grep -c 'foreach ($properties as $property)' view/search/results.phtml   # → 1
```

- [ ] **Step 2: Anotar la procedencia en la cabecera**

Añadir al principio del bloque de comentario `/** ... */` que ya trae el fichero, tras la primera línea:

```php
 * Override del tema REA ATE. Copiado de AdvancedSearch para interceptar el
 * renderizado de materia y nivel y sustituirlo por la píldora compuesta del
 * anclaje curricular. Al actualizar el módulo hay que rebasar este fichero.
 * Ver .project/docs/specs/2026-09-01-anclaje-curricular-busqueda-facetada-design.md
 *
```

- [ ] **Step 3: Resolver el anclaje una vez por recurso**

Dentro del `foreach ($resources as $resource):`, justo después de la línea que calcula `$resourceThumbnail`, añadir:

```php
        $curriculumRows = $showProperties ? $this->curriculumAnchor($resource) : null;
        $curriculumTerms = $curriculumRows
            ? [
                trim((string) $this->themeSetting('curriculum_anchor_subject_property')),
                trim((string) $this->themeSetting('curriculum_anchor_level_property')),
            ]
            : [];
```

- [ ] **Step 4: Saltar los dos términos y emitir el partial**

Sustituir la apertura del bucle de propiedades:

```php
                <?php foreach ($properties as $property):
                    $values = $resource->value($property, ['all' => true, 'lang' => $langValue]);
                    if ($values): ?>
```

por:

```php
                <?php if ($curriculumRows): ?>
                <?= $this->partial('common/curriculum-anchor', [
                    'rows' => $curriculumRows,
                    'label' => $translate('Currículo'), // @translate
                ]) ?>
                <?php endif; ?>
                <?php foreach ($properties as $property):
                    if (in_array($property, $curriculumTerms, true)) {
                        // Los pinta la píldora compuesta del anclaje curricular.
                        continue;
                    }
                    $values = $resource->value($property, ['all' => true, 'lang' => $langValue]);
                    if ($values): ?>
```

- [ ] **Step 5: Impedir que el JS arrastre el bloque**

`processItem()` deduce `data-term` del primer `a.metadata-search-link`. Los enlaces de nivel del anclaje llevan `property[0][property]=lrmi:educationalLevel` en el href, así que el bloque se etiquetaría como nivel y `groupMetaProperties()` lo movería a la tira de la derecha del título.

En `asset/js/advanced-search-list.js`, dentro de `processItem()`, sustituir:

```js
        const properties = item.querySelectorAll('dl.properties > .property');
        properties.forEach(function (prop) {
            if (!prop.dataset.term) {
```

por:

```js
        const properties = item.querySelectorAll('dl.properties > .property');
        properties.forEach(function (prop) {
            // El anclaje curricular ya viene resuelto y maquetado desde el
            // servidor. Sus enlaces de nivel llevan lrmi:educationalLevel en el
            // href, así que sin esta guarda se etiquetaría como esa propiedad y
            // groupMetaProperties() lo arrastraría al grupo de la derecha.
            if (prop.classList.contains('property--curriculum-anchor')) {
                return;
            }
            if (!prop.dataset.term) {
```

- [ ] **Step 6: Comprobar la sintaxis del PHP y del JS**

```bash
php -l view/search/results.phtml
php -l view/common/curriculum-anchor.phtml
node --check asset/js/advanced-search-list.js
```

Expected: `No syntax errors detected` en los dos PHP y ninguna salida del `node --check`.

- [ ] **Step 7: Verificar en la instancia que el bloque se pinta**

```bash
curl -s "http://localhost:8080/s/ceiplajares/rea?fulltext_search=" \
  | grep -c 'class="curriculum-anchor__group'
```

Expected: **31**. La página 1 pagina a 15 resultados (`pagination_per_page: 15`) y sus filas
suman 31 según la simulación del algoritmo: 3181:1, 4359:1, 4362:2, 4674:2, 4676:6, 5045:3,
5047:2, 5051:2, 37129:1, 37132:1, 40419:1, 40422:2, 40425:2, 40427:4, 40431:1. (Las 35 filas
que cita el spec son el catálogo completo, 19 recursos, no una sola página.)

Si sale 0, revisar que el override se está cargando: el tema debe estar activo en el sitio `ceiplajares`.

- [ ] **Step 8: Commit**

```bash
git add view/search/results.phtml asset/js/advanced-search-list.js
git commit -m "feat(search): pintar el anclaje curricular agrupado en resultados"
```

---

### Task 5: Verificación funcional contra la instancia

**Files:**
- Modify: `.project/docs/qa-findings.md`

**Interfaces:**
- Consumes: todo lo anterior.
- Produces: nada.

- [ ] **Step 1: Comprobar que no queda ninguna materia repetida**

```bash
curl -s "http://localhost:8080/s/ceiplajares/rea?fulltext_search=" \
  | grep -o 'class="curriculum-anchor__subject">[^<]*' \
  | sed 's/.*>//' | sort | uniq -d
```

Expected: la salida puede contener etiquetas repetidas **entre tarjetas distintas** (eso es correcto), pero ninguna debe repetirse dentro de la misma tarjeta. Para comprobarlo por tarjeta, contar en el ítem 4674, que era el peor caso de duplicación:

```bash
curl -s "http://localhost:8080/s/ceiplajares/rea?fulltext_search=" \
  | tr '<' '\n' | grep -A200 'data-id="4674"' | grep -c 'curriculum-anchor__subject'
```

Expected: `2` (antes eran 5 píldoras de materia, cuatro de ellas «Matemáticas»).

- [ ] **Step 2: Comprobar que ningún enlace de nivel devuelve cero resultados**

```bash
curl -s "http://localhost:8080/s/ceiplajares/rea?fulltext_search=" \
  | grep -o 'class="curriculum-anchor__level" href="[^"]*"' \
  | sed 's/.*href="//;s/"$//' | sort -u \
  | while read -r u; do
      n=$(curl -s "http://localhost:8080$(printf '%s' "$u" | sed 's/&amp;/\&/g')" | grep -c 'class="resource item"')
      printf '%s resultados  %s\n' "$n" "$u"
    done
```

Expected: ninguna línea empieza por `0`. Es la comprobación que valida la regla de intersección (§4.3 del spec).

- [ ] **Step 3: Comprobar que la tarjeta ya no desborda**

```bash
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  --headless --disable-gpu --window-size=1400,2600 --virtual-time-budget=9000 \
  --screenshot=/tmp/anclaje-despues.png \
  "http://localhost:8080/s/ceiplajares/rea?fulltext_search="
```

Abrir la captura y verificar en la tarjeta *Cuerpos geométricos* que las píldoras quedan dentro del borde y que se leen «Matemáticas aplicadas a las ciencias sociales I» y «Conocimiento del Medio Natural, Social y cultural» completas.

- [ ] **Step 4: Comprobar el móvil**

Repetir la captura con `--window-size=390,2600` y verificar que las píldoras ocupan el ancho completo, una por línea, y que el rótulo queda encima.

- [ ] **Step 5: Comprobar la degradación**

En el panel de ajustes del tema (`http://localhost:8080/admin/site/s/ceiplajares/theme`), vaciar el campo «Subject property for the curriculum anchor», guardar y recargar la búsqueda.

Expected: los resultados vuelven a mostrar materia y nivel como dos listas de píldoras, sin error de PHP. Restaurar el valor `schema:about` después.

- [ ] **Step 6: Anotar los resultados**

Añadir al final de `.project/docs/qa-findings.md` una entrada con la fecha, los cinco pasos anteriores y su resultado.

- [ ] **Step 7: Commit**

```bash
git add .project/docs/qa-findings.md
git commit -m "docs(search): registrar la verificacion del anclaje curricular"
```

- [ ] **Step 8: Borrar la rama temporal de tests**

```bash
git branch -D tmp/anclaje-con-tests
```

---

## Notas de ejecución

- **Los pasos que consultan `localhost:8080`** requieren la instancia arrancada y el tema montado. El `docker-compose.yml` del repo monta `./` en `/var/www/html/volume/themes/rea-ate`, así que los cambios se ven sin reconstruir. Los cambios en SASS sí requieren `npx gulp css`.
- **El número 35 del paso 7 de la Tarea 4** sale de la simulación del algoritmo sobre los 19 recursos del volcado. Si el volcado cambia, recalcularlo en vez de dar el test por roto.
- **`results.properties` es configuración de la instancia.** Si un administrador quita `schema:about` de la lista de propiedades mostradas del search config `rea`, el anclaje no se pinta. No es un fallo del tema.
