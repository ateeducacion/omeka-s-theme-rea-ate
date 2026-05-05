# Decisiones del Desarrollador

---

## [2026-05-05] ACEPTADA — Ciclo 3 #7: item-set browse (colecciones)

### Decisión
Implementar la página de colecciones según D7 + D7b del Diseñador [2026-05-05] y el prompt de implementación revisado. Cuatro correcciones técnicas obligatorias sobre el prompt original antes de proceder.

### Ficheros a crear / modificar

| Acción | Fichero |
|--------|---------|
| Reescribir | `view/omeka/site/item-set/browse.phtml` |
| Crear | `asset/sass/components/item-set-browse/_item-set-browse.scss` |
| Importar en | `asset/sass/components/_components.scss` |
| Crear helper | `helper/SlugifyValues.php` |
| Registrar helper | `config/theme.ini` — añadir `helpers[] = "SlugifyValues"` |
| Crear partial | `view/omeka/site/item-set/_browse-filter-script.phtml` |

### Correcciones técnicas sobre el prompt de implementación

**[C1] Eliminar el bloque `:root {}` del CSS propuesto**
El prompt incluye un bloque `:root { --primary: ...; --blue-mid: ...; }` en `_collections.scss`. **No añadirlo.** Los tokens están definidos en `layout.phtml`. En el SCSS usar directamente `var(--ate-color-brand-blue-dark)`, `var(--ate-color-brand-yellow)`, etc.

Mapa de sustitución (CSS propuesto → token ATE real):
```
var(--primary)       → var(--ate-color-brand-blue-dark)
var(--blue-mid)      → var(--ate-color-brand-blue-mid)
var(--yellow)        → var(--ate-color-brand-yellow)
var(--canvas)        → var(--ate-surface-canvas)
var(--surface-soft)  → var(--ate-surface-soft)
var(--surface-card)  → var(--ate-surface-card)
var(--body)          → var(--ate-text-body)
var(--muted)         → var(--ate-text-muted)
var(--hairline)      → var(--ate-hairline)
```

**[C2] Registrar el helper `SlugifyValues` en `theme.ini`**
En `config/theme.ini`, dentro del bloque `[info]`, añadir la línea:
```ini
helpers[] = "SlugifyValues"
```
El helper sigue el patrón de `helper/ResourceTags.php`: namespace `OmekaTheme\Helper`, clase `SlugifyValues`, método `__invoke(array $values): array`. Implementación:
```php
public function __invoke(array $values): array
{
    $slugs = [];
    foreach ($values as $value) {
        if (!$value) continue;
        $str = mb_strtolower((string) $value->value(), 'UTF-8');
        $str = strtr($str, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','ç'=>'c']);
        $str = preg_replace('/[^a-z0-9]+/', '-', $str);
        $slugs[] = trim($str, '-');
    }
    return array_filter($slugs);
}
```

**[C3] Construir `$etapas`, `$niveles`, `$tematicas` en el template — no son view variables inyectadas**
El prompt los usa como `foreach ($etapas as $slug => $label)` como si vinieran del controlador. No es así. Construirlos en el template antes del `foreach ($itemSets)`:
```php
$etapas = $niveles = $tematicas = [];
foreach ($itemSets as $is) {
    foreach ($is->value('dcterms:educationLevel', ['all' => true]) ?: [] as $v) {
        $label = (string) $v->value();
        $slug  = implode('', $this->SlugifyValues([$v]));
        if ($slug && !isset($etapas[$slug])) $etapas[$slug] = $label;
    }
    foreach ($is->value('lom:educationalLevel', ['all' => true]) ?: [] as $v) {
        $label = (string) $v->value();
        $slug  = implode('', $this->SlugifyValues([$v]));
        if ($slug && !isset($niveles[$slug])) $niveles[$slug] = $label;
    }
    foreach ($is->value('dcterms:subject', ['all' => true]) ?: [] as $v) {
        $label = (string) $v->value();
        $slug  = implode('', $this->SlugifyValues([$v]));
        if ($slug && !isset($tematicas[$slug])) $tematicas[$slug] = $label;
    }
}
```

**[C4] Reemplazar `$itemSet->itemCount()` — método inexistente en Omeka-S 4.2**
El método `itemCount()` no existe en `ItemSetRepresentation`. Usar el patrón del template original (pre-calcula el conteo por colección con la API, filtrando por `site_id` e `is_public`):
```php
// Al inicio del foreach de tarjetas:
$api    = $this->api();
$siteId = $this->currentSite()->id();

foreach ($itemSets as $itemSet):
    $itemCount = $api->search('items', [
        'item_set_id' => $itemSet->id(),
        'site_id'     => $siteId,
        'is_public'   => true,
        'limit'       => 1,
    ])->getTotalResults();
    if ($itemCount === 0) continue; // ocultar colecciones vacías
    // …resto de la tarjeta, usar $itemCount en la pill
```

### Checklist de aceptación (backlog #7)

- [x] Grid 3 cols (≥1025px) → 2 cols (601–1024px) → 1 col (≤600px)
- [x] Tarjeta: imagen 16/9, fallback con icono `folder_open`, franja azul + pill amarilla, descripción line-clamp 2, "Ver colección →"
- [x] Filtros: 3 selects poblados dinámicamente, filtrado client-side, botón "Limpiar" visible solo si hay filtro activo, `empty-state` si 0 resultados
- [x] Contador del header actualiza al filtrar
- [x] Sin bloque `:root` en el SCSS — tokens ATE de layout.phtml
- [x] Helper `SlugifyValues` registrado en `theme.ini`
- [x] `$itemSet->itemCount()` sustituido por `$api->search()` con site_id
- [x] Colecciones sin ítems visibles en el sitio se excluyen (mismo criterio que el template original)
- [x] `npm run build` pasa sin errores

### Dependencias
- Requiere: D7 + D7b del Diseñador [2026-05-05] ACEPTADAS.
- No bloquea: QA (#4), Browse grid/list (#2/#6), Home (#1).

---

## [2026-05-04] ACEPTADA — Ciclo 2 bugs #1, #2, #5

### Decisión
Resolución de tres bugs independientes del backlog de ciclo 2: breadcrumb del item/show, panel de info recortado por overflow, y hueco del div de chips sin resultados.

### Contexto

**Bug #1 — Breadcrumb item/show apunta al browse genérico**
El helper `$this->breadcrumbs()` de Omeka-S no expone opciones para sobrescribir URLs individuales. Se construye el breadcrumb manualmente en el template para esta vista: se intenta resolver la ruta `site/search` (Advanced Search); si no está registrada (módulo inactivo), se captura la excepción y se cae al browse genérico `site/resource` controller=item action=browse.

**Bug #2 — `.resource-link-info__panel` recortado por overflow del pill**
En `_item-show.scss`, dentro de `.property.dcterms-relation > dd.value`, se añadió `position: relative` al pill container y se sobreescribió el panel cuando `.resource-link-info.expanded` con `position: absolute; top: 100%; left: 0; z-index: 10; overflow: visible; max-height: none; max-width: none; width: 260px` para sacarlo del flujo inline. El z-index 10 queda muy por debajo del header sticky (z-index: 1040).

**Bug #5 — Chips standalone desplazan mensaje "sin resultados"**
En `_search-results-page.scss`, el bloque standalone `.active-filter-chips:not(.search-results-header &):empty` ya tenía `margin-bottom: 0` pero seguía siendo un flex container visible. Se añadió `display: none` para colapsarlo completamente. Además se añadió la regla `.active-filter-chips:not(:empty) + .noresults { margin-top: 12px }` para separar visualmente los chips del mensaje cuando hay filtros activos y cero resultados.

### Consecuencias

**Ficheros modificados:**
- `view/omeka/site/item/show.phtml` — eliminado `$this->breadcrumbs()`, sustituido por nav manual con try/catch para resolver `site/search` o fallback a `site/resource`.
- `asset/sass/components/item-show/_item-show.scss` — añadido `position: relative` en `dd.value` del contexto `.dcterms-relation`; añadida regla `.resource-link-info.expanded .resource-link-info__panel` con posicionamiento absoluto.
- `asset/sass/components/search-results/_search-results-page.scss` — añadido `display: none` al selector `:empty` del standalone chips; añadida regla de separación `+ .noresults`.
- `asset/css/style.css` — regenerado con `npm run build`.
- `asset/css/style.css.map` — source map actualizado.

---

## [2026-05-04] ACEPTADA — Backlog #8: Inter como fuente única (D2c)

### Decisión
Implementar la decisión D2c del Diseñador [2026-05-04]: sustituir Open Sans por Inter como fuente única del tema, eliminando todas las referencias a Open Sans de la capa de presentación.

### Contexto
Basado en la decisión D2c ACEPTADA del Diseñador y la actualización de tokens D1 [2026-05-04]. La variable Sass `$font__body` ya apuntaba a `var(--ate-font-body)` y `$font__headings` a `var(--ate-font-heading)`, por lo que solo fue necesario actualizar los tokens CSS y la URL de Google Fonts. No se encontraron referencias hardcoded a "Open Sans", "Lato" ni "Source Serif 4" en los ficheros `.scss` del tema (solo en `asset/sass/style.css`, que es un artefacto compilado de la versión base del tema padre y no forma parte del flujo de compilación del proyecto).

### Consecuencias

**Ficheros modificados:**
- `view/layout/layout.phtml` — línea 21: URL de Google Fonts cambiada de Open Sans (`ital,wght@0,400;0,500;0,600;0,700;0,800;1,400`) a Inter (`wght@400;500;600;700;800`); líneas 79–81: tokens CSS `--ate-font-display`, `--ate-font-heading` y `--ate-font-body` actualizados a `'Inter', sans-serif`.
- `asset/css/style.css` — regenerado con `npm run build` (gulp css); no contiene referencias de fuente explícitas ya que toda la tipografía se resuelve vía variables CSS en runtime.
- `asset/css/style.css.map` — source map actualizado por la compilación.

### Dependencias
- Basado en: decisión Diseñador D2c [2026-05-04] ACEPTADA.

---

## [2026-04-29] ACEPTADA

### Decisión
Implementación del bloque "Anclaje Curricular" mediante `block.phtml` de BlockPlus.

### Contexto
Implementación directa de la decisión arquitectónica [2026-04-29] de `architect.md`.

### Alternativas consideradas
N/A — implementación sin ambigüedad de la estrategia del Arquitecto.

### Consecuencias

**Ficheros creados:**
- `view/common/resource-page-block-layout/block.phtml` — plantilla que lee
  `anclaje_div_class` y `anclaje_properties` desde theme settings; silencia la
  salida si ninguna propiedad tiene valor en el ítem actual; distingue valores
  literales de recursos vinculados.

**Ficheros modificados:**
- `config/theme.ini` — añadido grupo `anclaje` con `anclaje_div_class` (Text,
  default `anclaje-curricular`) y `anclaje_properties` (Textarea, 6 filas, 4
  propiedades LRMI por defecto); `block` añadido a `resource_page_blocks.items.main[]`
  y `resource_page_blocks.items.right[]`.

**Nota operacional:** el gestor del sitio debe ir a **Sites → Configure resource
pages → Items** y arrastrar el bloque "Simple Resource Block" (BlockPlus) a la
región deseada para activarlo en el sitio.

### Dependencias
- Basado en: decisión Arquitecto [2026-04-29] ACEPTADA.

_Antes de actuar, el Desarrollador debe leer:_

- _`.project/skills/developer.md`_
- _`.project/skills/theme-creator.md`_
- _`.project/skills/omeka-integration.md`_
- _`.project/skills/decision-logging.md`_
- _Las decisiones `ACEPTADA` relevantes en `.project/decisions/architect.md` 
  y `.project/decisions/designer.md`_

---

## [2026-05-04] ACEPTADA — Rediseño de medios y optimización de panel

### Decisión
Implementar el rediseño de la sección de medios (grid unificado y eliminación de títulos) y eliminar la transición del panel de información de recursos.

### Contexto
Basado en las directrices del Diseñador [2026-05-04] para mejorar la visualización de medios en la página de ítem y optimizar la UX del panel de información de recursos vinculados eliminando la latencia visual de la transición.

### Consecuencias

**Ficheros modificados:**
- `asset/sass/components/resource-link-info/_resource-link-info.scss` — eliminada la propiedad `transition` de `.resource-link-info__panel` para una apertura instantánea.
- `view/common/resource-page-block-layout/media-embeds.phtml` — refactorizado para eliminar el agrupamiento por tipos; implementado grid unificado; eliminada la impresión de `displayTitle()` (nombres de archivo/source) en tarjetas de archivos y SCORM.
- `view/common/resource-page-block-layout/media-list.phtml` — sustituido `linkPretty()` por `linkRaw()` con miniatura para eliminar el texto del título en la barra lateral.
- `asset/sass/components/media-embeds/media-embeds.scss` — simplificado para soportar el grid unificado; añadida lógica de `grid-column: span 2` para HTML/SCORM; añadida regla CSS para ocultar el enlace "Source" residual inyectado por Omeka S; ajustada alineación del botón de descarga.
- `asset/css/style.css` — regenerado con `npm run build`.

### Dependencias
- Basado en: decisión Diseñador [2026-05-04] (Rediseño medios) y petición directa de optimización de UX (panel transition).
