# Advanced Search — integración en REA ATE

## Propósito
Referencia de cómo funciona el módulo AdvancedSearch (Daniel-KM, v3.4.60) y cómo el tema REA ATE lo integra, para que Developer pueda personalizar formulario, resultados y paginación sin romper la integración.

## Cuándo invocar esta skill
- Al modificar la barra de búsqueda del header.
- Al personalizar los resultados de búsqueda o los chips de filtro.
- Al añadir parámetros de filtrado por metadatos LRMI.
- Al depurar problemas de búsqueda en la instancia real.

## Referencia técnica

### Cómo funciona AdvancedSearch en Omeka-S

El módulo registra una ruta `site/search` que sirve la página de búsqueda y resultados. Cada "configuración de búsqueda" (Search Config) define:
- El formulario (campos visibles, tipos de input).
- El motor de búsqueda (índice interno o Solr).
- Las facetas disponibles.

En REA ATE se usa el **índice interno** (sin Solr). Las facetas configuradas son:
- `lrmi:educationalLevel`
- `schema:about`
- `lrmi:learningResourceType`
- `lrmi:timeRequired`
- `dcterms:relation`

### URL de búsqueda

```php
// URL de la página de búsqueda del sitio
try {
    $searchUrl = $this->url('site/search', [
        'site-slug' => $this->currentSite()->slug(),
    ]);
} catch (\Exception $e) {
    // Fallback si AdvancedSearch no está activo
    $searchUrl = $this->url('site/resource', [
        'site-slug'  => $this->currentSite()->slug(),
        'controller' => 'item',
        'action'     => 'browse',
    ]);
}
```

### Parámetros de query para filtrar

```
/s/repositorio/search
  ?fulltext_search=fracciones          # búsqueda por texto libre
  &facet[lrmi:educationalLevel][]=primaria   # faceta de nivel
  &facet[lrmi:learningResourceType][]=video  # faceta de tipo
  &sort=relevance                      # ordenación
  &page=2                              # paginación
```

### Renderizado de resultados — plantillas a sobrescribir

El módulo AdvancedSearch busca sus plantillas en:
1. `view/advanced-search/search/` del tema (si existen).
2. `modules/AdvancedSearch/view/advanced-search/search/` (fallback del módulo).

Plantillas relevantes para REA ATE:

| Ruta en el módulo | Ruta en el tema (override) | Contenido |
|---|---|---|
| `results.phtml` | `view/advanced-search/search/results.phtml` | Página completa de resultados |
| `facets.phtml` | (no sobrescrita actualmente) | Sidebar de facetas |
| `form.phtml` | (no sobrescrita actualmente) | Formulario de búsqueda |

El tema actualmente sobrescribe `results.phtml` con `view/omeka/search/results.phtml` (ruta legacy) — verificar la ruta exacta si cambia el módulo.

### Integración con `resource-values.phtml`

AdvancedSearch renderiza los valores de propiedades de los ítems en los resultados usando su propio motor de renderizado, **no** a través de `resource-values.phtml`. Esto significa que:

- El badge de `lrmi:learningResourceType` en search results **no se puede hacer desde PHP** con un partial.
- La solución actual es **client-side**: `asset/js/advanced-search-list.js` detecta los elementos con `data-term="lrmi:learningResourceType"` y les aplica el badge JS.

```js
// En advanced-search-list.js
function upgradeLrtProperty(propertyEl) {
    const vc = propertyEl.querySelector('.values');
    if (!vc) return;
    if (vc.querySelector('.resource-type-badge')) return; // ya procesado

    const dds = vc.querySelectorAll('dd.value');
    dds.forEach(function (dd) {
        const rawLabel = dd.textContent.trim();
        const badge    = buildLrtBadge(rawLabel);
        dd.textContent = '';
        dd.appendChild(badge);
    });
}
```

### Chips de filtro activos

Los chips se renderizan en `#active-filter-chips` (div con id). El tema los estiliza en `_search-results-page.scss`. Funcionamiento:

- Los chips aparecen solo si hay facetas activas.
- Cuando el div está vacío (`:empty`), se oculta con `display: none` para no dejar hueco en el layout.
- Un chip "Limpiar todo" aparece con clase `.chips-clear-all`.

### Paginación en resultados

AdvancedSearch usa el paginador nativo de Omeka-S. El estado del paginador se actualiza con la última llamada `$api->search()`. Si se hacen múltiples búsquedas en la misma vista, el contador puede quedar corrompido (ver QA-004).

### Motor de búsqueda: índice interno vs Solr

| Característica | Índice interno | Solr |
|---|---|---|
| Setup | Ninguno | Requiere servidor Solr |
| Búsqueda full-text | Sí (LIKE en MySQL) | Sí (relevancia real) |
| Facetas | Sí | Sí (más eficiente) |
| Rendimiento con > 10k ítems | Degradado | Escalable |
| En REA ATE | **Activo** | No instalado |

## Patrones frecuentes

```php
// Barra de búsqueda en header — formulario mínimo
<form action="<?= $searchUrl ?>" method="get" role="search">
    <input type="hidden" name="index" value="default">
    <input type="search" name="fulltext_search"
           placeholder="<?= __('Buscar recursos…') ?>"
           aria-label="<?= __('Buscar recursos educativos') ?>">
    <button type="submit" aria-label="<?= __('Buscar') ?>">
        <span class="material-symbols-outlined" aria-hidden="true">search</span>
    </button>
</form>
```

## Errores comunes
- Asumir que `resource-values.phtml` se invoca en search results — AdvancedSearch tiene su propio renderer.
- Usar `$api->search('items', [...])` para el contador de resultados en la misma vista que ya usa AdvancedSearch — sobrescribe el estado del paginador.
- Hardcodear el nombre del Search Config (`'default'`) — puede diferir entre instalaciones. Leer desde la configuración del sitio si es posible.

## Referencias externas
- Módulo AdvancedSearch (Daniel-KM): https://github.com/Daniel-KM/Omeka-S-module-AdvancedSearch
- Documentación de Omeka-S Search: https://omeka.org/s/docs/user-manual/sites/site_search/
