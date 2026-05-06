# Schema.org para REA

## Propósito
Referencia de tipos y propiedades Schema.org aplicables a Recursos Educativos Abiertos en REA ATE, para embeber datos estructurados JSON-LD que mejoren el SEO y la interoperabilidad del repositorio.

## Cuándo invocar esta skill
- Al implementar JSON-LD en las plantillas de `item/show`.
- Al definir qué propiedades Schema.org corresponden a cada metadato del ítem.
- Al validar que el marcado estructurado es correcto antes de indexación.

## Referencia técnica

### Tipos Schema.org aplicables

| Tipo | Cuándo usar |
|---|---|
| `schema:LearningResource` | **Tipo principal para todos los REA.** Subtipo de `CreativeWork` con propiedades educativas específicas. |
| `schema:CreativeWork` | Fallback genérico si el recurso no encaja en los subtipos. |
| `schema:Course` | Para recursos con estructura de curso (módulos, lecciones secuenciales). |
| `schema:VideoObject` | Para recursos cuyo medio principal es un vídeo. |
| `schema:ImageObject` | Para recursos cuyo medio principal es una imagen educativa. |
| `schema:AudioObject` | Para podcasts o recursos de audio educativo. |

### Propiedades para SEO estructurado en REA

| Propiedad Schema.org | Mapeo en Omeka-S | Notas |
|---|---|---|
| `name` | `dcterms:title` | Obligatorio |
| `description` | `dcterms:description` | Obligatorio |
| `educationalLevel` | `lrmi:educationalLevel` | P. ej. "Educación Primaria" |
| `learningResourceType` | `lrmi:learningResourceType` | P. ej. "actividad interactiva" |
| `inLanguage` | `dcterms:language` | Código BCP 47: "es", "en" |
| `license` | `dcterms:rights` | URL de la licencia CC |
| `creator` | `dcterms:creator` | Puede ser `Person` u `Organization` |
| `datePublished` | `dcterms:date` | Formato ISO 8601 |
| `publisher` | `dcterms:publisher` | `Organization` |
| `url` | URL del ítem en el sitio | `$this->itemUrl($item)` |
| `thumbnailUrl` | Thumbnail del ítem | URL de la imagen en tamaño medium |
| `keywords` | `dcterms:subject` o `schema:about` | Lista de términos separados por coma |
| `teaches` | `lrmi:teaches` | Competencias que enseña |
| `assesses` | `lrmi:assesses` | Competencias que evalúa |
| `timeRequired` | `lrmi:timeRequired` | Duración ISO 8601 (P1H30M) |

### Embeber JSON-LD en plantillas PHP

```php
// En view/omeka/site/item/show.phtml, justo antes del cierre </article> o al final
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'LearningResource',
    'name'                => $item->displayTitle(),
    'description'         => $item->displayDescription() ?: '',
    'url'                 => $this->itemUrl($item),
    'inLanguage'          => (string) ($item->value('dcterms:language') ?? 'es'),
    'learningResourceType'=> (string) ($item->value('lrmi:learningResourceType') ?? ''),
    'educationalLevel'    => (string) ($item->value('lrmi:educationalLevel') ?? ''),
    'license'             => (string) ($item->value('dcterms:rights') ?? ''),
    'datePublished'       => (string) ($item->value('dcterms:date') ?? ''),
];

// Eliminar campos vacíos
$jsonLd = array_filter($jsonLd, fn($v) => $v !== '' && $v !== null);

// Añadir thumbnail si existe
$thumbnail = $this->thumbnail($item, 'medium', ['class' => null]);
if ($item->primaryMedia()) {
    $jsonLd['thumbnailUrl'] = $item->primaryMedia()->thumbnailUrl('medium');
}
?>
<script type="application/ld+json">
<?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
```

### Ejemplo completo de JSON-LD para un item REA

```json
{
    "@context": "https://schema.org",
    "@type": "LearningResource",
    "name": "Las fracciones en la vida cotidiana",
    "description": "Actividad interactiva para trabajar las fracciones con ejemplos del entorno próximo del alumnado de 5º de Primaria.",
    "url": "https://recursoseducativos.canarias.es/s/repositorio/item/1234",
    "inLanguage": "es",
    "learningResourceType": "actividad interactiva",
    "educationalLevel": "Educación Primaria",
    "teaches": "Operaciones con fracciones",
    "timeRequired": "PT45M",
    "license": "https://creativecommons.org/licenses/by-sa/4.0/",
    "creator": {
        "@type": "Person",
        "name": "María García López"
    },
    "publisher": {
        "@type": "Organization",
        "name": "Consejería de Educación del Gobierno de Canarias"
    },
    "datePublished": "2024-09-01",
    "thumbnailUrl": "https://recursoseducativos.canarias.es/files/medium/abc123.jpg",
    "keywords": "fracciones, matemáticas, Primaria, interactivo"
}
```

## Patrones frecuentes

```php
// Construir keywords desde dcterms:subject
$subjects = $item->value('dcterms:subject', ['all' => true]) ?: [];
$keywords = implode(', ', array_map(fn($v) => (string) $v->value(), $subjects));

// Añadir JSON-LD al head en lugar de inline (más limpio)
$this->headScript()->appendScript(
    json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'application/ld+json'
);
```

## Errores comunes
- Usar `schema:educationalLevel` directamente en el JSON-LD con un literal libre — Google espera un valor controlado o al menos consistente. Normalizar el valor LRMI antes de incluirlo.
- No escapar HTML en valores del JSON-LD — usar `json_encode` siempre, nunca concatenar strings directamente.
- Poner el `<script type="application/ld+json">` dentro de un `<div>` — debe ir en `<head>` o directamente en `<body>`, no anidado en otros elementos.
- Olvidar `@context` — sin él el JSON-LD no es válido para Google Search.

## Referencias externas
- Schema.org LearningResource: https://schema.org/LearningResource
- Google Rich Results para cursos: https://developers.google.com/search/docs/appearance/structured-data/course
- Validador de datos estructurados: https://validator.schema.org/
