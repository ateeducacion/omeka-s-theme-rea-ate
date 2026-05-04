# Decisiones del Desarrollador

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
