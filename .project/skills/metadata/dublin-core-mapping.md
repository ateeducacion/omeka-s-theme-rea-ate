# Dublin Core — mapeo en REA ATE

## Propósito
Referencia de los elementos Dublin Core y DC Terms usados en REA ATE, qué campos son obligatorios y cómo se mapean a los componentes visuales del tema.

## Cuándo invocar esta skill
- Al implementar plantillas que muestren metadatos descriptivos (título, descripción, autor, fecha).
- Al definir qué campos son obligatorios para que un ítem sea válido en REA ATE.
- Al diseñar el bloque de metadatos de `item/show`.
- Al decidir qué propiedad usar para relaciones entre recursos.

## Referencia técnica

### Los 15 elementos DC y su uso en REA ATE

| Elemento | Término Omeka-S | Uso en REA ATE | Obligatorio |
|---|---|---|---|
| Title | `dcterms:title` | Título del recurso | Sí |
| Creator | `dcterms:creator` | Autor/a del recurso | Recomendado |
| Subject | `dcterms:subject` | Área temática (fallback de `schema:about`) | No |
| Description | `dcterms:description` | Descripción larga | Sí |
| Publisher | `dcterms:publisher` | Institución publicadora | No |
| Contributor | `dcterms:contributor` | Colaboradores | No |
| Date | `dcterms:date` | Fecha de publicación | No |
| Type | `dcterms:type` | Tipo Dublin Core (usar `lrmi:learningResourceType` preferentemente) | No |
| Format | `dcterms:format` | Formato del archivo (MIME type) | No |
| Identifier | `dcterms:identifier` | Identificador único (DOI, ISBN, etc.) | No |
| Source | `dcterms:source` | Fuente original si es una adaptación | No |
| Language | `dcterms:language` | Idioma del recurso (BCP 47, p. ej. "es") | Recomendado |
| Relation | `dcterms:relation` | Recurso relacionado (enlace a otro ítem) | No |
| Coverage | `dcterms:coverage` | Ámbito geográfico o temporal | No |
| Rights | `dcterms:rights` | Licencia o declaración de derechos | Recomendado |

### Campos obligatorios en REA ATE
Un ítem debe tener como mínimo:
- `dcterms:title` — sin título no puede mostrarse en ninguna vista.
- `dcterms:description` — necesario para el hero de `item/show` y las cards de resultados.
- `lrmi:learningResourceType` — necesario para el badge de tipo de recurso.

Recomendados para que el anclaje curricular funcione:
- `lrmi:educationalLevel`
- `lrmi:educationalAlignment`

### Diferencia entre `dcterms:` y `dc:` en Omeka-S

| Namespace | Prefijo | Cuándo usar |
|---|---|---|
| `http://purl.org/dc/elements/1.1/` | `dc:` | Legado. Menos preciso, sin refinamientos. |
| `http://purl.org/dc/terms/` | `dcterms:` | **Usar siempre en REA ATE.** Incluye refinamientos (fecha ISO 8601, relaciones tipadas, etc.) |

En Omeka-S, el vocabulario DC Elements (`dc:`) y DC Terms (`dcterms:`) son vocabularios distintos. REA ATE usa exclusivamente `dcterms:`.

### Mapeo DC → componente visual

| Propiedad | Componente | Notas |
|---|---|---|
| `dcterms:title` | `<h1>` en hero de item show; título en cards | Via `$item->displayTitle()` |
| `dcterms:description` | Cuerpo de texto en item show; excerpt en cards | Via `$item->displayDescription()` |
| `dcterms:creator` | Metadata block | Campo simple |
| `dcterms:language` | Metadata block | Mostrar como badge si procede |
| `dcterms:rights` | Footer de item show / metadata block | Mostrar icono CC si es Creative Commons |
| `dcterms:relation` | Pill de recurso relacionado | Componente especial con `.resource-link-info` |
| `dcterms:date` | `.days-ago-tag` en hero | Calcular distancia desde hoy |

### Lectura en PHP

```php
// Título y descripción
$title       = $item->displayTitle();           // helper nativo (usa dcterms:title)
$description = $item->displayDescription();     // helper nativo (usa dcterms:description)

// Valores literales
$language = (string) $item->value('dcterms:language');
$rights   = (string) $item->value('dcterms:rights');

// Fecha y días desde publicación
$dateValue = $item->value('dcterms:date');
if ($dateValue) {
    $date    = new \DateTime((string) $dateValue);
    $now     = new \DateTime();
    $daysDiff = (int) $now->diff($date)->days;
}

// Relaciones (dcterms:relation) — pueden ser recursos vinculados
foreach ($item->value('dcterms:relation', ['all' => true]) ?: [] as $rel) {
    if ($rel->type() === 'resource') {
        $linked = $rel->valueResource(); // ItemRepresentation
    } else {
        echo $rel->value(); // URL externa o literal
    }
}
```

## Patrones frecuentes

```php
// Detectar si un ítem tiene dcterms:rights con Creative Commons
$rights = (string) $item->value('dcterms:rights');
$isCC   = str_contains($rights, 'creativecommons.org');
```

## Errores comunes
- Usar `dc:title` en lugar de `dcterms:title` — son propiedades de vocabularios distintos en Omeka-S y el valor puede no aparecer.
- Mostrar `dcterms:type` en el badge de tipo de recurso en lugar de `lrmi:learningResourceType` — DC Type es genérico, LRMI es el estándar educativo.
- No usar `$item->displayTitle()` y acceder directamente a `dcterms:title` — `displayTitle()` aplica la lógica de fallback de Omeka-S correctamente.

## Referencias externas
- Dublin Core Metadata Initiative: https://www.dublincore.org/specifications/dublin-core/
- DC Terms en Omeka-S: https://omeka.org/s/docs/user-manual/content/vocabularies/
