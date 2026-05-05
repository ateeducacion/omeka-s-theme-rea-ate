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

---

## [2026-05-05] CIERRE CICLO 2 / BRIEFING CICLO 3

### Ciclo 2 — Entregado

Todos los ítems del backlog de ciclo 2 cerrados (8/8):

| # | Área | Descripción | Resultado |
|---|------|-------------|-----------|
| 1 | Search | Breadcrumb item/show | ✅ Usa `breadcrumbs()` helper nativo de Omeka-S |
| 2 | Item Show | Panel `resource-link-info` recortado | ✅ `position:absolute` + `max-height:none` + `overflow:visible` |
| 5 | Search | Chips standalone desplazan "sin resultados" | ✅ Selector `~` + `display:none` en `:empty` |
| 6 | Release | Pipeline GitHub Actions | ✅ Renombrado a `rea-ate`, tags legacy eliminados, v0.1.0 publicado |
| 8 | Tipografía | Inter como fuente única | ✅ Ya implementado; eliminada doble carga de Material Symbols |
| 4 | Item Show | Badge `lrmi:learningResourceType` valor bruto | ✅ URI cleanup, mapa de iconos, truncado a 35 chars |
| 7 | A11y | Contrastes WCAG AA | ✅ 3 elementos corregidos: `days-ago-tag`, `filter-chip__group`, `chips-clear-all` |
| 3 | Item Show | Left sidebar activo | ✅ Clases modificadoras + 4 variantes CSS del grid |

**Release entregada:** `v0.1.0` — primer tag del tema `rea-ate` con pipeline completo.

---

### Backlog — Ciclo 3

| # | Área | Descripción | Impacto | Esfuerzo |
|---|------|-------------|---------|----------|
| 1 | Home | Tarjetas de audiencia: Profesorado, Alumnado, Familias. Sección en la home que permita al administrador dirigir a cada perfil a su colección o búsqueda. | UX / Producto | M |
| 2 | Browse (grid) | Estilo de tarjetas en `item/browse.phtml` — grid de recursos con imagen, título, tipo de recurso y nivel educativo. Coherente con el sistema ATE. | Visual | M |
| 3 | Mobile | Auditoría de responsividad: header (top-bar + main-bar), facetas, item show y browse en pantallas < 768px. | Estabilidad | M |
| 4 | QA | QA sobre instancia real del ciclo 2 completo. Verificar breadcrumb, chips, badge, grid sidebars, contrastes. | Calidad | S |
| 5 | Release | Publicar `v0.2.0` tras QA y correcciones del ciclo 3. | DevOps | S |
| 6 | Browse (list) | Estilo de lista en `item/browse.phtml` cuando el layout es list — fila compacta con imagen thumbnail, título, tipo de recurso y nivel educativo. Coherente con el sistema ATE y complementario al grid (#2). | Visual | M |
| 7 | Item-set browse | Rediseño atractivo de `item-set/browse.phtml` para destacar colecciones: hero visual por colección, imagen de portada, contador de items, descripción. Diseño generado con Claude Design. | Visual / Producto | L | ✅ Diseño aprobado [2026-05-05] — Developer desbloqueado |

**Prioridad sugerida:** QA (#4) primero para detectar regresiones, luego Browse grid (#2) y list (#6) en paralelo con Home (#1) si el Diseñador libera las specs, después Mobile (#3), Item-set browse (#7) y cerrar con release v0.2.0 (#5).

**Decisiones pendientes antes de abordar #1, #2 y #6:**
- El Diseñador debe especificar el layout y tokens de las tarjetas de audiencia (home).
- El Diseñador debe especificar el diseño de las tarjetas de recurso en browse (grid y list).

**Proceso para #7 (item-set browse):**
- Generar el diseño visual con Claude Design antes de que el Desarrollador implemente.
- El Diseñador registra decisión ACEPTADA con las especificaciones resultantes de Claude Design.
- La implementación queda bloqueada hasta esa decisión.

---

## Estado actual del proyecto

| Aspecto | Estado |
|---------|--------|
| Fase | INICIO CICLO 3 |
| Dependencias cliente | ✅ Todas confirmadas |
| Decisiones Arquitecto | ✅ 5 decisiones ACEPTADAS |
| Decisiones Diseñador | ✅ D1–D6 + 4 iteraciones ACEPTADAS |
| Implementación ciclo 1 | ✅ Completado |
| Implementación ciclo 2 | ✅ Completado (8/8 ítems) |
| Release pipeline | ✅ Operativo — v0.1.0 publicado |
| QA sobre instancia real | ⏳ Pendiente (ciclo 3) |

## Estado de implementación

| Área | Estado |
|------|--------|
| Tokens CSS (`--ate-*`) + defaults de color | ✅ |
| Tipografía Inter (fuente única) | ✅ |
| Header sticky (top-bar + main-bar con búsqueda) | ✅ |
| Footer (border amarillo + hover yellow) | ✅ |
| Sidebar de facetas (estilos ATE) | ✅ |
| Pipeline de release (GitHub Actions + Makefile) | ✅ v0.1.0 publicado |
| Ficha de recurso (`item/show.phtml`) | ✅ |
| Search results — chips + contador + A11y | ✅ |
| Anclaje curricular (sidebar derecho) | ✅ |
| Tarjetas de audiencia en home | ⏳ Ciclo 3 |
| Browse de recursos — grid (`item/browse.phtml`) | ⏳ Ciclo 3 |
| Browse de recursos — list (`item/browse.phtml`) | ⏳ Ciclo 3 |
| Browse de colecciones (`item-set/browse.phtml`) | ✅ Implementado — pendiente QA sobre instancia real |
| Mobile responsiveness (auditoría) | ⏳ Ciclo 3 |
