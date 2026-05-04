# Decisiones del Orquestador

---

## [2026-04-27] ACEPTADA

### Decisión
Inicio del proyecto. Estado inicial registrado.

### Contexto
El repositorio del tema Omeka-S ha sido inicializado con el sistema 
multiagente en `.project/`. El tema parte de un tema Omeka-S existente 
que ya está en el repositorio. Los requisitos iniciales están documentados 
en `.project/context/requirements.md` y el modelo de metadatos de 
referencia en `.project/context/metadata_model.md`.

### Alternativas consideradas
N/A — entrada de estado inicial.

### Consecuencias
- El Arquitecto debe iniciar con un análisis del tema base existente y 
  proponer la estrategia de integración con Advanced Search y la 
  estructura de plantillas del header.
- El Diseñador debe iniciar proponiendo el sistema de tokens CSS y el 
  comportamiento del header en sus distintos estados.
- El Desarrollador queda bloqueado hasta que Arquitecto y Diseñador 
  registren sus primeras decisiones `ACEPTADA`.

### Dependencias
- Desbloquea: primera ronda de decisiones de Arquitecto y Diseñador 
  en paralelo.
- Bloquea: toda implementación del Desarrollador.

---

---

## [2026-04-27] ACEPTADA

### Decisión
Dependencias del cliente confirmadas. Arquitecto y Diseñador desbloqueados para sus primeras decisiones.

### Contexto
El cliente ha confirmado todas las dependencias técnicas y de producto pendientes. Ver `.project/context/requirements.md` actualizado.

Resumen de lo confirmado:
- **Omeka-S 4.2** en producción
- **Advanced Search 3.4.60** instalado
- **Facetas**: `lrmi:EducationalLevel`, `schema:about`, `lrmi:LearningResourceType`, `lrmi:timeRequired`, `dcterms:relation`
- **Motor de búsqueda**: índice interno (sin Solr/Elasticsearch)
- **Menú**: gestionado por Omeka-S, el tema solo renderiza
- **Footer**: opciones del tema por defecto, logo GobCan existente
- **Audiencias**: Profesorado y Alumnado (principales), Familias (secundario)
- **Release**: GitHub Actions (a diseñar por el Arquitecto)

### Consecuencias
- Arquitecto puede iniciar el análisis del tema base y las decisiones de integración.
- Diseñador puede iniciar el sistema de tokens CSS.
- El Desarrollador sigue bloqueado hasta las primeras decisiones ACEPTADAS de ambos.

### Dependencias
- Desbloquea: primera ronda de decisiones de Arquitecto y Diseñador.

---

---

## [2026-04-27] ACEPTADA

### Decisión
Decisiones de Arquitecto y Diseñador registradas como ACEPTADAS. Desarrollador desbloqueado.

### Contexto
Se han registrado 5 decisiones del Arquitecto y 5 del Diseñador, todas ACEPTADAS.
La implementación inicial (tokens CSS, tipografía, header sticky, facetas, footer, release pipeline) está en curso.

### Consecuencias
- El Desarrollador puede implementar basándose en las decisiones registradas.
- Las decisiones de Arquitecto y Diseñador son la fuente de verdad para la implementación.

### Dependencias
- Desbloquea: toda la implementación del Desarrollador.

---

---

## [2026-05-01→05-04] RESUELTA — Selección de tipografía del tema

### Decisión
**Inter como fuente única** para todo el tema. Se elimina la arquitectura sans+serif (Lato + Source Serif 4). Decisión D2c del Diseñador ACEPTADA [2026-05-04].

### Contexto
La decisión D2 del Diseñador aprobó la pareja Lato (sans) + Source Serif 4 (serif), pero la implementación está bloqueada (backlog #8). El orquestador abre esta decisión como PENDIENTE antes de proceder: Source Serif 4 es una fuente de propósito editorial/periodístico que puede no ser la más adecuada para un repositorio de recursos educativos orientado a profesorado y alumnado.

### Criterios para la evaluación
- Legibilidad en pantalla a tamaños de cuerpo de texto (16–18px) y en UI de metadatos (13–14px).
- Idoneidad para un contexto educativo institucional (no editorial).
- Disponibilidad en Google Fonts con buen rendimiento de carga.
- Compatibilidad visual con Lato (sans-serif principal del tema).
- Opciones serif a valorar: Merriweather, PT Serif, Noto Serif, IBM Plex Serif, Literata.
- Opción sin serif a valorar: **Inter** como fuente única (sans-serif diseñada para pantalla; valorar si sustituye también a Lato o si conviven).

### Alternativas consideradas
- Mantener Source Serif 4: buena legibilidad, variable font, pero perfil más editorial.
- Eliminar la serif completamente y usar **Inter** como fuente única: diseñada específicamente para UI digital, excelente legibilidad en pantalla, variable font, muy usada en entornos educativos e institucionales. Implica revisar si Lato se mantiene o se unifica todo en Inter.

### Consecuencias
- Bloquea la implementación del ítem #8 del backlog hasta que se resuelva.
- El Diseñador debe proponer 2–3 candidatas con muestra visual antes de aceptar.

### Dependencias
- Bloquea: implementación de tipografía (backlog #8).
- Desbloquea: cuando el Diseñador registre decisión ACEPTADA con la fuente elegida.

---

## [2026-05-01] BRIEFING — Ciclo de mejoras UI/UX: Item Show + Search Results

### Contexto
Cierre del primer ciclo de implementación de la identidad visual ATE sobre el
tema Freedom. El Diseñador completó las decisiones D1–D6 originales y añadió 4
iteraciones adicionales derivadas de pruebas sobre instancia real. Este briefing
consolida lo ejecutado y abre el backlog del próximo ciclo.

---

### Mejoras ejecutadas (Diseñador + Desarrollador)

#### A. Item Show — rediseño completo

**Ficheros afectados:**
`view/omeka/site/item/show.phtml` · `_item-show.scss` · `_anclaje-curricular.scss` ·
`_regions.scss` · `media-embeds.scss` · `media-embeds.phtml`

**Cambios estructurales:**
- `show.phtml` reescrito: breadcrumb nativo Omeka-S → hero (eyebrow badge + título
  clamp + days-ago + admin-btn) → `.item-body` grid dos columnas (main + sidebar
  sticky con `top: calc(--header-compact-height + 20px)`).
- `.item-header-container` eliminado de `_regions.scss`; ancho sidebar `20%` → `300px`.
- Triggers `view.show.before/after` y delete modal conservados íntegros.

**Cambios visuales:**
- Breadcrumb: usa `$this->breadcrumbs()` nativo; estilos sobre `.breadcrumbs-parent /
  .breadcrumbs` con tokens ATE (muted → blue-mid hover, separador `/` via CSS).
- Hero: borde inferior amarillo 3px, título `clamp(1.6rem, 3.5vw, 2.25rem)` peso 800,
  badge de tipo de recurso (`lrmi:learningResourceType`), pill de item-set via
  `$itemSet->siteUrl()`.
- Admin actions: botones 32×32 con icon Material Symbols; delete con hover rojo.
- Sidebar sticky con paneles `.anclaje-curricular` diferenciados por propiedad:
  - `lrmi/schema:educationalLevel` → pill azul sólido (`brand-blue-dark`).
  - `schema:about` → pill amarillo (`surface-yellow` + borde `brand-yellow`).
  - `lrmi:assesses / lrmi:teaches` → chip neutro (`surface-card`) con hover amarillo.
- SCORM: `media-embeds.phtml` detecta renderer `LearningObject` y lo envuelve en
  `.scorm-card` (cabecera `brand-blue-dark`, botones `.btn-scorm-launch /
  .btn-scorm-download` con `!important` sobre inline styles del módulo).

#### B. Metadata block — relaciones como pills

**Fichero afectado:** `_item-show.scss`

- Label `dt` oculto para propiedades no-relación dentro de `.values-group.metadata`
  (selector `.property:not(.dcterms-relation) > dt { display: none }`).
- `dcterms:relation`: el `dd.value` completo es el pill container (`inline-flex`,
  `background: surface-card`, `border: hairline`, `border-radius: pill`).
  `.metadata-search-link` sin padding propio (`padding: 0`); el `dd.value` lleva
  `padding: 5px 0 5px 14px`. Botón `+` con `align-self: stretch` y `border-left`
  actúa como borde derecho del pill (`padding: 0 14px`).
- `.field-term` oculto dentro de `dcterms:relation > dt`.
- Coherencia tipográfica: `font-size: 13px / font-weight: 700` en `.metadata-search-link`,
  igual que los pills de `schema:about` en `.anclaje-curricular`.

#### C. Search Results — chips unificados con barra de conteo

**Ficheros afectados:** `search.phtml` · `_search-results-page.scss`

- `#active-filter-chips` movido dentro del `.search-results-header` (cuando
  `$displayPartsHeader` es true); fallback standalone en el `else` para garantizar
  que el JS lo encuentre por id.
- `.search-results-header` pasa a `display: flex; flex-wrap: wrap; gap: 8px`.
  Chips: `flex: 1`; vacíos: `display: none` (sin hueco flex). Contador:
  `margin-left: auto; flex-shrink: 0` → siempre pegado a la derecha.
- Elimina el efecto de salto de scroll al activar facetas: el header ya ocupa su
  espacio antes de que aparezcan chips.

---

### Decisiones de arquitectura implícitas (a ratificar)

1. **`show.phtml` usa `.item-body` en lugar de `.regions-container`** para la página
   de item. El grid dos columnas es CSS puro en `_item-show.scss`; la clase
   `.regions-container` (y su CSS en `_regions.scss`) sigue activa para otras vistas
   (item-set show, media show). No hay conflicto.

2. **`$itemSet->siteUrl()`** para enlaces de item-set en el hero: patrón idiomático
   de Omeka-S, no depende de nombres de ruta del router. A mantener.

3. **Breadcrumb via `$this->breadcrumbs()`**: helper de core de Omeka-S (disponible
   desde v4). No requiere módulo adicional. A mantener.

4. **Left sidebar**: declarado en template (con fallback) pero sin CSS de columnas.
   `theme.ini` no tiene bloques configurados para `items.left`. Aceptable para v0.4.

---

### Backlog — próximo ciclo (propuestas)

| # | Área | Descripción | Impacto | Esfuerzo |
|---|------|-------------|---------|----------|
| 1 | Search | Back-link del breadcrumb apunta al items browse genérico, no a la búsqueda facetada. Valorar si la ruta del módulo Advanced Search es `site/search` o si se debe leer desde config. | UX | S |
| 2 | Item Show | `.resource-link-info__panel` puede quedar cortado por `overflow` dentro del pill. Verificar posicionamiento absoluto y z-index en contexto real. | Visual | S |
| 3 | Item Show | Probar la vista cuando el item tiene left sidebar activo (ningún bloque configurado actualmente). | Estabilidad | S |
| 4 | Item Show | La etiqueta `.resource-type-badge` (eyebrow) muestra `lrmi:learningResourceType` con el valor bruto. Valorar truncado y si mostrar el icono `school` o el icono propio del tipo de recurso. | UX | M |
| 5 | Search | Revisar comportamiento del contador cuando hay 0 resultados — el header no se renderiza (`$displayPartsHeader` false) pero el branch `else` del chips sí. Verificar que el div chips standalone no desplace el mensaje "no results". | Estabilidad | S |
| 6 | Release | Pipeline GitHub Actions con tag `v*.*.*` pendiente desde el plan original. Necesario antes de entrega a producción. | DevOps | M |
| 7 | Accesibilidad | Revisar contraste de `.days-ago-tag` (muted sobre surface-soft) y de los chips de faceta (yellow sobre blue-dark) con herramienta WCAG AA. | A11y | S |
| 8 | Tipografía | El tema sigue cargando Open Sans; la decisión D2 (Lato + Source Serif 4) está aprobada pero no implementada. Desbloquear cuando el ciclo actual esté estabilizado. | Visual | M |

**Prioridad inmediata sugerida:** ítems 1, 2 y 5 (bugs visibles en uso real). El ítem 6 (release) es prerequisito de producción.

### Dependencias
- Requiere: todas las decisiones D1–D6 del Diseñador como base.
- Desbloquea: ciclo de QA sobre instancia real + entrega v0.4.

---

## Estado actual del proyecto

| Aspecto | Estado |
|---------|--------|
| Fase | CIERRE CICLO 1 / INICIO CICLO 2 |
| Dependencias cliente | ✅ Todas confirmadas |
| Decisiones Arquitecto | ✅ 5 decisiones ACEPTADAS |
| Decisiones Diseñador | ✅ D1–D6 + 4 iteraciones ACEPTADAS |
| Implementación ciclo 1 | ✅ Completado |
| QA sobre instancia real | ⏳ Pendiente |

## Próximos pasos

| Área | Estado |
|------|--------|
| Tokens CSS (`--ate-*`) + defaults de color | ✅ Implementado |
| Tipografía (Inter fuente única) | ⚠️ Decisión D2c ACEPTADA, pendiente implementar (backlog #8) |
| Header sticky (top-bar + main-bar con búsqueda) | ✅ Implementado |
| Footer (border amarillo + hover yellow) | ✅ Implementado |
| Sidebar de facetas (estilos ATE) | ✅ Implementado |
| Pipeline de release (GitHub Actions + Makefile) | ⚠️ Parcial — tag `v*.*.*` pendiente (backlog #6) |
| Ficha de recurso (`item/show.phtml`) | ✅ Implementado (ciclo 1) |
| Search results — chips + contador | ✅ Implementado (ciclo 1) |
| Tarjetas de audiencia en home | ⏳ Siguiente iteración |
| QA + correcciones backlog ciclo 2 | ⏳ Próximo ciclo |
