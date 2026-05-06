# LRMI Schema — metadatos de recursos educativos

## Propósito
Referencia de las propiedades LRMI relevantes para REA ATE, su mapeo a campos de Omeka-S y su representación en plantillas HTML, para que Architect, Designer y Developer trabajen con el mismo contrato de datos.

## Cuándo invocar esta skill
- Al diseñar cómo se muestra un metadato educativo en una plantilla.
- Al filtrar recursos por nivel educativo, tipo o área temática.
- Al implementar microdata o JSON-LD en vistas de ítem.
- Al decidir qué propiedades leer para un componente (badge, sidebar, filtro).

## Referencia técnica

### Propiedades LRMI relevantes en REA ATE

| Propiedad LRMI | Término en Omeka-S | Concepto | Tipo de valor |
|---|---|---|---|
| `lrmi:learningResourceType` | `lrmi:learningResourceType` | Tipo de recurso (vídeo, actividad, guía…) | Literal / URI |
| `lrmi:educationalLevel` | `lrmi:educationalLevel` | Nivel educativo (Primaria, ESO, Bachillerato…) | Literal / URI |
| `lrmi:educationalAlignment` | `lrmi:educationalAlignment` | Etapa curricular (objeto de alineación) | Recurso vinculado / Literal |
| `lrmi:educationalUse` | `lrmi:educationalUse` | Uso previsto (instrucción, evaluación…) | Literal |
| `lrmi:typicalAgeRange` | `lrmi:typicalAgeRange` | Rango de edad (ej. "6-12") | Literal |
| `lrmi:timeRequired` | `lrmi:timeRequired` | Duración (formato ISO 8601 o literal) | Literal |
| `lrmi:interactivityType` | `lrmi:interactivityType` | Tipo de interactividad | Literal |
| `lrmi:assesses` | `lrmi:assesses` | Competencia que evalúa | Literal / URI |
| `lrmi:teaches` | `lrmi:teaches` | Competencia que enseña | Literal / URI |

### Vocabulario en Omeka-S
El vocabulario LRMI debe estar importado con el prefijo `lrmi` y el namespace `http://purl.org/dcx/lrmi-terms/`. Verificar en Admin → Vocabularies que aparece.

### Mapeo propiedad → plantilla → elemento HTML

| Propiedad | Dónde se muestra | Componente HTML |
|---|---|---|
| `lrmi:learningResourceType` | Hero de item show + search results card | `.resource-type-badge` (eyebrow pill con icono) |
| `lrmi:educationalLevel` | Sidebar derecho de item show + filtros de colecciones | `.anclaje-curricular` pill azul sólido |
| `lrmi:educationalAlignment` | Sidebar derecho de item show + filtros de colecciones | `.anclaje-curricular` panel (etapa) |
| `lrmi:assesses` / `lrmi:teaches` | Sidebar derecho de item show | `.anclaje-curricular` chip neutro |
| `lrmi:timeRequired` | Metadata block en item show | `dd` dentro de `.values-group.metadata` |
| `dcterms:relation` | Metadata block en item show | Pill con enlace a recurso relacionado |

### Lectura en PHP

```php
// Tipo de recurso (primer valor)
$lrt = $item->value('lrmi:learningResourceType');
$lrtLabel = $lrt ? (string) $lrt->value() : null;

// Nivel educativo (todos los valores)
$levels = $item->value('lrmi:educationalLevel', ['all' => true]) ?: [];
foreach ($levels as $level) {
    echo htmlspecialchars((string) $level->value());
}

// Etapa (alineación curricular)
$alignments = $item->value('lrmi:educationalAlignment', ['all' => true]) ?: [];

// Competencias (assesses + teaches juntas)
$competencias = array_merge(
    $item->value('lrmi:assesses', ['all' => true]) ?: [],
    $item->value('lrmi:teaches', ['all' => true]) ?: []
);
```

### Ejemplo de microdata Schema.org para un REA

```html
<article itemscope itemtype="https://schema.org/LearningResource">
    <h1 itemprop="name"><?= $item->displayTitle() ?></h1>
    <meta itemprop="educationalLevel" content="Educación Primaria">
    <meta itemprop="learningResourceType" content="actividad interactiva">
    <meta itemprop="inLanguage" content="es">
    <meta itemprop="license" content="https://creativecommons.org/licenses/by/4.0/">
    <p itemprop="description"><?= $item->displayDescription() ?></p>
</article>
```

## Patrones frecuentes

```php
// Normalizar label de lrmi:learningResourceType (URI → texto legible)
function normalizeLrtLabel(string $raw): string {
    // Extraer último segmento de URI
    if (str_contains($raw, '/') || str_contains($raw, '#')) {
        $raw = basename(str_replace('#', '/', $raw));
    }
    // camelCase → palabras
    $raw = preg_replace('/([a-z])([A-Z])/', '$1 $2', $raw);
    return mb_strtolower(trim($raw));
}
```

## Errores comunes
- Confundir `lrmi:educationalLevel` (nivel: Primaria, ESO) con `lrmi:educationalAlignment` (etapa/alineación curricular: objeto complejo). Son conceptos distintos.
- Asumir que el valor de `lrmi:learningResourceType` es siempre un literal — puede ser una URI de un vocabulario controlado. Normalizar siempre.
- Usar `dcterms:educationLevel` (DC Terms) cuando el proyecto ha migrado a `lrmi:educationalLevel` — consultar `context/metadata_model.md` para la fuente de verdad.

## Referencias externas
- Especificación LRMI: https://www.dublincore.org/specifications/lrmi/lrmi_terms/
- Schema.org LearningResource: https://schema.org/LearningResource
