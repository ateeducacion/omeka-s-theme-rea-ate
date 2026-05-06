# Omeka-S API

## Propósito
Referencia técnica de la API REST y los view helpers de Omeka-S para que Architect y Developer accedan a datos de items, colecciones y medias desde plantillas PHP.

## Cuándo invocar esta skill
- Al diseñar cómo el tema obtiene ítems, item sets o medias.
- Al escribir código PHP en plantillas que llamen a `$this->api()`.
- Al necesitar filtrar recursos por clase, vocabulario o propiedad.
- Al implementar paginación en vistas de listado.

## Referencia técnica

### Endpoint REST `/api/items`
Parámetros de filtrado frecuentes:

```
GET /api/items
  ?resource_class_id=<int>     # filtrar por clase RDF
  ?resource_template_id=<int>  # filtrar por plantilla
  ?item_set_id=<int>           # filtrar por colección
  ?property[0][property_id]=<int>&property[0][type]=eq&property[0][text]=<valor>
  ?site_id=<int>               # solo ítems del sitio
  &is_public=1                 # solo públicos
  &page=1&per_page=25          # paginación
  &sort_by=created&sort_order=desc
```

### Acceso en plantillas PHP

```php
// Obtener la API
$api = $this->api();

// Leer un ítem por ID
$item = $api->read('items', $id)->getContent();

// Buscar ítems
$response = $api->search('items', [
    'item_set_id' => $setId,
    'site_id'     => $this->currentSite()->id(),
    'is_public'   => true,
    'per_page'    => 25,
]);
$items      = $response->getContent();
$totalItems = $response->getTotalResults();

// Valor de una propiedad
$title    = $item->value('dcterms:title');           // primer valor
$allLevels = $item->value('lrmi:educationalLevel', ['all' => true]); // todos
$titleStr = (string) $title;                         // cast a string

// Iterar múltiples valores
foreach ($item->value('schema:about', ['all' => true]) ?: [] as $v) {
    echo $v->value();    // valor literal
    echo $v->uri();      // URI si es recurso vinculado
}
```

### `resource_template` vs `resource_class`
- **`resource_class`**: clase RDF asignada al ítem (p. ej. `lrmi:LearningResource`). Define el *tipo* semántico del recurso.
- **`resource_template`**: plantilla de edición que predefine qué propiedades se muestran en el formulario de admin. No implica clase ni tiene efecto semántico propio.
- En REA ATE los filtros por tipo de recurso deben usar `resource_class_id`, no `resource_template_id`.

### Iterar medias de un ítem

```php
foreach ($item->media() as $media) {
    echo $media->mediaType();       // 'image/jpeg', 'video/mp4', etc.
    echo $media->originalUrl();     // URL original del archivo
    echo $media->thumbnailUrl('large');  // thumbnail grande
    echo $media->renderer();        // 'iiif-image', 'youtube', 'html', etc.
}
```

### Paginación

```php
$currentPage = $this->params()->fromQuery('page', 1);
$response    = $api->search('items', ['page' => $currentPage, 'per_page' => 25]);
echo $this->pagination($response->getTotalResults(), $currentPage, 25);
```

### Autenticación REST (acceso externo)
Las llamadas desde el tema PHP no requieren autenticación (ya están autenticadas por la sesión). Para acceso externo (scripts, Postman):
```
?key_identity=<usuario>&key_credential=<clave_api>
```

## Patrones frecuentes

```php
// Colecciones a las que pertenece un ítem
foreach ($item->itemSets() as $set) {
    echo $set->displayTitle();
    echo $set->siteUrl(); // URL en el sitio actual
}

// Recursos vinculados (valores que son ítems de Omeka-S)
$value = $item->value('dcterms:relation');
if ($value && $value->type() === 'resource') {
    $linked = $value->valueResource(); // ItemRepresentation
}
```

## Errores comunes
- Usar `$item->value()` sin `['all' => true]` cuando hay múltiples valores y solo se necesita el primero — está bien. Usarlo si se necesitan todos sin el flag — silencia el resto.
- Llamar `$api->search()` dentro de un bucle `foreach` en una plantilla con muchos ítems — problema de rendimiento. Pre-calcular fuera del bucle.
- `$response->getTotalResults()` puede quedar sobreescrito si se hacen múltiples búsquedas sucesivas en la misma vista (el paginador de Omeka-S es estado compartido).

## Referencias externas
- Documentación API REST Omeka-S: https://omeka.org/s/docs/developer/api/
- PHP API interno: https://omeka.org/s/docs/developer/internal_api/
