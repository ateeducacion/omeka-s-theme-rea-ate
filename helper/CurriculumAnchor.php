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
     * Caché de ítems por id, de instancia (no static): Laminas crea un
     * helper por render, así que esta caché vive lo que dura la página.
     * Existe para cumplir el presupuesto de consultas de la spec §5.4 (tres
     * consultas por lote por página de resultados, no por recurso listado):
     * el vocabulario de asignaturas/niveles/termsets se repite mucho entre
     * los recursos de una misma página, así que sin caché cada llamada a
     * curriculumAnchor() repetiría las mismas consultas.
     *
     * @var \Omeka\Api\Representation\ItemRepresentation[] Indexado por id.
     */
    private $itemCache = [];

    /**
     * @param \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource
     * @return array|null Filas listas para pintar, o null si el modelo no encaja.
     */
    public function __invoke($resource): ?array
    {
        $view = $this->getView();

        [$subjectTerm, $levelTerm] = $this->anchorTerms();
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
                $level['url'] = $this->searchUrl($levelTerm, $level['id']);
            }
            unset($level);
        }
        unset($row);

        return $rows;
    }

    /**
     * Filas de __invoke() con los valores alineados (criterios, saberes…)
     * colgados de la materia a la que pertenecen, para la ficha del recurso.
     *
     * Cada ítem alineado se asigna a su materia por el grafo curricular: su
     * propiedad de termset (schema:inDefinedTermSet) apunta a la Asignatura.
     * Si esa Asignatura no es una de las del recurso, se cae al nombre de la
     * materia (schema:about del propio criterio). Lo que no casa con nada va
     * a un grupo final «Otros», para no perder ningún valor.
     *
     * @param string[] $alignedTerms Términos a colgar, en orden de pintado.
     * @return array|null Filas de __invoke() con clave 'aligned' => [term => [entrada, ...]],
     *                    o null si el modelo no encaja.
     */
    public function alignment($resource, array $alignedTerms): ?array
    {
        $rows = $this($resource);
        if (!$rows) {
            return null;
        }

        $view = $this->getView();
        [$subjectTerm] = $this->anchorTerms();
        $termsetProperty = trim((string) $view->themeSetting('advancedsearch_termset_property'))
            ?: 'schema:inDefinedTermSet';

        $subjectKeyById = [];
        foreach ($resource->value($subjectTerm, ['all' => true]) ?: [] as $value) {
            $linked = $this->linkedResource($value);
            if ($linked) {
                $subjectKeyById[$linked->id()] = self::groupKey((string) $linked->displayTitle());
            }
        }

        $entries = [];
        foreach ($alignedTerms as $term) {
            $ids = [];
            foreach ($resource->value($term, ['all' => true]) ?: [] as $value) {
                $linked = $this->linkedResource($value);
                if ($linked) {
                    $ids[$linked->id()] = true;
                } elseif (trim((string) $value) !== '') {
                    // Literal suelto: sin grafo detrás, va a «Otros».
                    $entries[] = ['term' => $term, 'id' => null, 'label' => trim((string) $value),
                        'description' => '', 'url' => null, 'subjectIds' => [], 'subjectLabels' => []];
                }
            }
            // Una consulta por lote por término.
            foreach ($this->fetchItems(array_keys($ids)) as $item) {
                $subjectIds = [];
                foreach ($item->value($termsetProperty, ['all' => true]) ?: [] as $value) {
                    $linked = $this->linkedResource($value);
                    if ($linked) {
                        $subjectIds[] = $linked->id();
                    }
                }
                $subjectLabels = [];
                foreach ($item->value($subjectTerm, ['all' => true]) ?: [] as $value) {
                    $linked = $this->linkedResource($value);
                    $subjectLabels[] = $linked ? (string) $linked->displayTitle() : (string) $value;
                }
                $description = $item->value('dcterms:description');
                $entries[] = [
                    'term' => $term,
                    'id' => $item->id(),
                    'label' => (string) $item->displayTitle(),
                    'description' => $description ? trim(strip_tags((string) $description)) : '',
                    'url' => $this->searchUrl($term, $item->id()),
                    'subjectIds' => $subjectIds,
                    'subjectLabels' => $subjectLabels,
                ];
            }
        }

        return self::attachAligned(
            $rows,
            $subjectKeyById,
            $alignedTerms,
            $entries,
            $view->translate('Otros') // @translate
        );
    }

    /**
     * Rellena la caché de instancia de una sola pasada para todos los
     * recursos de una página de resultados, para cumplir el presupuesto de
     * tres consultas por lote por página (spec §5.4) en vez de hasta tres
     * por recurso listado. Opcional: __invoke() funciona igual de correcto
     * sin llamar antes a esto, solo que con más consultas (memoización caso
     * a caso en vez de un lote único). No toca groupRows() ni el resultado.
     *
     * Mismo patrón que ya usa view/search/facets-list.phtml: recoger todos
     * los IDs por adelantado y consultarlos una sola vez por lote.
     *
     * @param \Omeka\Api\Representation\AbstractResourceEntityRepresentation[] $resources
     */
    public function primeResources(array $resources): void
    {
        [$subjectTerm, $levelTerm] = $this->anchorTerms();
        if ($subjectTerm === '' || $levelTerm === '') {
            return;
        }

        $subjectIds = [];
        $levelIds = [];
        foreach ($resources as $resource) {
            foreach ($resource->value($subjectTerm, ['all' => true]) ?: [] as $value) {
                $linked = $this->linkedResource($value);
                if ($linked) {
                    $subjectIds[$linked->id()] = true;
                }
            }
            foreach ($resource->value($levelTerm, ['all' => true]) ?: [] as $value) {
                $linked = $this->linkedResource($value);
                if ($linked) {
                    $levelIds[$linked->id()] = true;
                }
            }
        }

        // Consulta por lote 1: todos los ítems asignatura de la página.
        $this->fetchItems(array_keys($subjectIds));
        // Consultas por lote 2 y 3: todos los niveles declarados de la
        // página y los termsets a los que pertenecen.
        $this->levelStages(array_keys($levelIds));
    }

    /**
     * Términos de materia y nivel resueltos, con valor por defecto de código
     * cuando el ajuste de tema nunca se ha guardado. `theme.ini` solo aporta
     * el valor que ve el formulario de ajustes al abrirse (`attributes.value`),
     * no un valor efectivo: mientras nadie guarde el formulario del tema,
     * `themeSetting()` devuelve `null` para estas dos claves, incluso en una
     * instalación nueva. Sin este default de código el anclaje se
     * autodesactivaba en silencio en cualquier sitio sin ese guardado manual.
     *
     * Se distingue el ajuste nunca guardado (`null`, usa el default de
     * código) del ajuste guardado explícitamente vacío (`''`, respeta el
     * apagado deliberado documentado en __invoke()): solo el primer caso cae
     * al default.
     *
     * Única fuente de verdad para estos dos términos: __invoke(),
     * primeResources() y view/search/results.phtml pasan por aquí en vez de
     * leer `curriculum_anchor_*` directamente.
     *
     * @return array{0: string, 1: string} [$subjectTerm, $levelTerm]
     */
    public function anchorTerms(): array
    {
        return [
            $this->resolveAnchorTerm('curriculum_anchor_subject_property', 'schema:about'),
            $this->resolveAnchorTerm('curriculum_anchor_level_property', 'lrmi:educationalLevel'),
        ];
    }

    private function resolveAnchorTerm(string $settingId, string $default): string
    {
        $raw = $this->getView()->themeSetting($settingId);
        return $raw === null ? $default : trim((string) $raw);
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
     * Una sola consulta por lote sobre los IDs que aún no están en caché,
     * columna indexada. Los ya cacheados se devuelven sin volver a pedirlos.
     *
     * @param int[] $ids
     * @return \Omeka\Api\Representation\ItemRepresentation[]
     */
    private function fetchItems(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $missingIds = array_values(array_diff($ids, array_keys($this->itemCache)));
        if ($missingIds) {
            $fetched = $this->getView()->api()
                ->search('items', ['id' => $missingIds, 'limit' => count($missingIds)])
                ->getContent();
            foreach ($fetched as $item) {
                $this->itemCache[$item->id()] = $item;
            }
        }

        $items = [];
        foreach ($ids as $id) {
            if (isset($this->itemCache[$id])) {
                $items[] = $this->itemCache[$id];
            }
        }
        return $items;
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

    private function searchUrl(string $term, int $id): string
    {
        return $this->getView()->url(
            'site/resource',
            ['controller' => 'item', 'action' => 'browse'],
            [
                'query' => [
                    'property' => [
                        ['property' => $term, 'type' => 'res', 'text' => (string) $id],
                    ],
                ],
            ],
            true
        );
    }

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
            $key = self::groupKey($label);
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

    /** Clave con la que se agrupan materias homónimas. */
    public static function groupKey(string $label): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $label)), 'UTF-8');
    }

    /**
     * Capa pura: cuelga cada entrada alineada de la fila de su materia.
     *
     * @param array    $rows           Filas de groupRows()
     * @param array    $subjectKeyById [int $subjectId => string groupKey] materias del recurso
     * @param string[] $alignedTerms   Términos, en orden de pintado
     * @param array    $entries        [['term', 'id', 'label', 'subjectIds' => int[],
     *                                   'subjectLabels' => string[], ...], ...]
     * @param string   $otherLabel     Rótulo del grupo final cuando no hay fila huérfana
     * @return array Filas con 'aligned' => [term => [entrada, ...]] (solo términos no vacíos)
     */
    public static function attachAligned(
        array $rows,
        array $subjectKeyById,
        array $alignedTerms,
        array $entries,
        string $otherLabel
    ): array {
        $rowIndexByKey = [];
        $orphanIndex = null;
        foreach ($rows as $i => $row) {
            $rows[$i]['aligned'] = [];
            if (!empty($row['orphan'])) {
                $orphanIndex = $i;
            } else {
                $rowIndexByKey[self::groupKey($row['label'])] = $i;
            }
        }

        $buckets = [];
        foreach ($entries as $entry) {
            $targets = [];
            foreach ($entry['subjectIds'] ?? [] as $subjectId) {
                $key = $subjectKeyById[$subjectId] ?? null;
                if ($key !== null && isset($rowIndexByKey[$key])) {
                    $targets[$rowIndexByKey[$key]] = true;
                }
            }
            if (!$targets) {
                foreach ($entry['subjectLabels'] ?? [] as $label) {
                    $key = self::groupKey((string) $label);
                    if (isset($rowIndexByKey[$key])) {
                        $targets[$rowIndexByKey[$key]] = true;
                    }
                }
            }
            if (!$targets) {
                $targets = ['other' => true];
            }
            foreach (array_keys($targets) as $target) {
                // Sin id (literal), se deduplica por etiqueta.
                $dedupe = $entry['id'] !== null ? 'id:' . $entry['id'] : 'label:' . self::groupKey($entry['label']);
                $buckets[$target][$entry['term']][$dedupe] = $entry;
            }
        }

        if (isset($buckets['other'])) {
            if ($orphanIndex === null) {
                $rows[] = ['label' => $otherLabel, 'orphan' => true, 'levels' => [], 'aligned' => []];
                $orphanIndex = array_key_last($rows);
            }
            foreach ($buckets['other'] as $term => $list) {
                $buckets[$orphanIndex][$term] = array_merge($buckets[$orphanIndex][$term] ?? [], $list);
            }
            unset($buckets['other']);
        }

        foreach ($buckets as $i => $byTerm) {
            foreach ($alignedTerms as $term) {
                if (empty($byTerm[$term])) {
                    continue;
                }
                $list = array_values($byTerm[$term]);
                usort($list, static fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));
                $rows[$i]['aligned'][$term] = $list;
            }
        }

        return array_values($rows);
    }
}
