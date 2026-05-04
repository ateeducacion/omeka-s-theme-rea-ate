# Decisiones del Diseñador

---

## [2026-04-27] ACEPTADA

### Decisión
Sistema de tokens CSS: paleta completa de Canarias Educativa disponible como CSS custom properties `--ate-*` en `:root`.

### Contexto
El tema base usa CSS custom properties (`--primary`, `--secondary`, `--accent`) generadas dinámicamente en `layout.phtml` con valores del admin de Omeka-S. El diseño "Canarias Educativa" (DESIGN.md) define una paleta completa. Ambos sistemas deben convivir.

**Estrategia adoptada:**
- Las CSS vars `--primary/--secondary/--accent` se mantienen para compatibilidad con el sistema de colores configurable del tema (admin de Omeka-S). Sus valores por defecto se actualizan a la paleta ATE.
- Se añade un bloque `--ate-*` en el `:root` del `<style>` inline de `layout.phtml` con la paleta completa de Canarias Educativa.
- Los archivos Sass de componentes usan `var(--ate-*)` directamente para componentes nuevos o rediseñados.
- El prefijo `--ate-` evita colisiones con las vars del tema base.

**Tokens definidos:**

| Token CSS | Valor | Uso |
|-----------|-------|-----|
| `--ate-color-brand-yellow` | `#FFB300` | Acento primario, borde nav, CTA secundario |
| `--ate-color-brand-yellow-light` | `#FFCC00` | Decorativo |
| `--ate-color-brand-blue-dark` | `#0C2C84` | CTAs, headers, tarjetas profesorado |
| `--ate-color-brand-blue-mid` | `#0768AC` | Links, activos, tarjetas familias |
| `--ate-color-brand-sage` | `#A3AD99` | Badge "Novedad", uso especial |
| `--ate-color-brand-red` | `#FE000C` | Solo errores y alertas |
| `--ate-color-brand-ochre` | `#D88F1F` | Badges secundarios |
| `--ate-surface-canvas` | `#FFFFFF` | Fondo base |
| `--ate-surface-soft` | `#F5F7FA` | Secciones alternas |
| `--ate-surface-card` | `#EEF2F8` | Tarjetas neutras |
| `--ate-surface-yellow` | `#FFF8E1` | Tarjetas destacadas |
| `--ate-surface-dark` | `#0C2C84` | Footer, secciones oscuras |
| `--ate-surface-dark-soft` | `#0E3599` | Hover sobre dark |
| `--ate-text-ink` | `#0A0A0A` | Titulares y texto primario |
| `--ate-text-body` | `#2C2C2C` | Texto corrido |
| `--ate-text-body-soft` | `#4A4A4A` | Texto secundario |
| `--ate-text-muted` | `#6B7280` | Fechas, metadatos |
| `--ate-text-muted-light` | `#9CA3AF` | Placeholders |
| `--ate-text-on-dark` | `#FFFFFF` | Texto sobre superficies oscuras |
| `--ate-text-on-dark-soft` | `#B8C8E8` | Texto secundario sobre dark |
| `--ate-text-on-yellow` | `#0C2C84` | Texto sobre amarillo |
| `--ate-hairline` | `#DDE3EC` | Bordes y separadores |
| `--ate-hairline-soft` | `#EEF0F4` | Bordes sutiles |
| `--ate-success` | `#16A34A` | Estado éxito |
| `--ate-warning` | `#D97706` | Advertencia |
| `--ate-error` | `#DC2626` | Error |
| `--ate-info` | `#0768AC` | Información |
| `--ate-font-heading` | `Lato, sans-serif` | Titulares, UI |
| `--ate-font-body` | `'Source Serif 4', Georgia, serif` | Texto corrido |
| `--ate-radius-xs` | `4px` | |
| `--ate-radius-sm` | `6px` | |
| `--ate-radius-md` | `10px` | Inputs, botones |
| `--ate-radius-lg` | `14px` | Cards |
| `--ate-radius-xl` | `20px` | Cards grandes |
| `--ate-radius-2xl` | `28px` | CTA band |
| `--ate-radius-pill` | `9999px` | Badges |

**Valores por defecto de las CSS vars del tema (en `layout.phtml`):**
- `--primary` → `#0C2C84` (brand-blue-dark)
- `--secondary` → `#0C2C84` (surface-dark)
- `--accent` → `#0768AC` (brand-blue-mid)

### Alternativas consideradas
- **Sobrescribir completamente las vars `--primary/--secondary/--accent`**: rompe la opción de personalización desde el admin. Descartado.
- **Definir los tokens en un archivo CSS externo importado**: añade una petición HTTP extra y pierde la flexibilidad de valores dinámicos via PHP. Descartado.

### Consecuencias
- El Desarrollador añade el bloque `--ate-*` en `layout.phtml` y actualiza `config/theme.ini` con los nuevos defaults.
- Todos los componentes nuevos o rediseñados usan `var(--ate-*)`.
- Los componentes existentes no rediseñados siguen usando las vars Sass anteriores.

### Dependencias
- Desbloquea: decisiones D2–D6 y toda la implementación.

---

## [2026-04-27] ACEPTADA

### Decisión
Tipografía: sustituir Open Sans + Noto Serif por Lato + Source Serif 4.

### Contexto
El tema base usa Open Sans para todo (headings y body). DESIGN.md especifica Lato (sans-serif) para UI y Source Serif 4 (serif) para contenido corrido. El cambio mejora la identidad visual y la legibilidad en contenido educativo extenso.

**Cambios:**
- Google Fonts en `layout.phtml`: sustituir `Open Sans` y `Noto Serif` por `Lato:wght@400;600;700;900` + `Source+Serif+4:ital,wght@0,400;0,600;1,400`.
- `_typography.scss`: `$font__headings` → `var(--ate-font-heading)` (Lato); `$font__body` → `var(--ate-font-body)` (Source Serif 4).
- Escala de tamaños existente se mantiene (compatible con DESIGN.md).

### Alternativas consideradas
- **Mantener Open Sans**: pierde identidad visual ATE. Descartado.
- **Self-hosting de fuentes**: reduce dependencia de Google, pero añade complejidad de setup. Aplazado para cuando el proyecto esté más maduro.

### Consecuencias
- `_footer.scss` referencia directa `'Open Sans'` en `font-family` → cambiar a `var(--ate-font-body)` o simplemente eliminar la referencia hardcoded.
- Verificar que la escala tipográfica sigue siendo correcta tras el cambio.

### Dependencias
- Requiere: D1 (token `--ate-font-heading` y `--ate-font-body`).
- Desbloquea: implementación de estilos tipográficos en componentes.

---

## [2026-04-27] ACEPTADA

### Decisión
Estados visuales del header: top-bar institucional + main-bar sticky con búsqueda siempre visible.

### Contexto
El Arquitecto ha definido la estructura HTML (decisión 2). El Diseñador especifica el tratamiento visual de cada estado.

**Estado inicial (scroll = 0):**
- Top-bar: fondo blanco (`var(--ate-surface-canvas)`), altura ~80px. Logo GobCan como background-image a la izquierda. Sin sombra.
- Separador `<hr>`: color `var(--ate-hairline)`, 1px, sin margen vertical.
- Main-bar: fondo blanco, altura 68px, `border-bottom: 3px solid var(--ate-color-brand-yellow)`. Logo del site + nav + área de búsqueda distribuidos. Sin sombra inicial.
- `body`: `padding-top = height(main-header)` para que el contenido no quede bajo el header.

**Estado scrolled (`.header-scrolled` en `<header>`):**
- Top-bar: oculta via `style.top` (JS existente, se complementa con clase CSS).
- Main-bar: sticky en `top: 0`, `box-shadow: 0 2px 8px rgba(12,44,132,0.08)` (sombra ATE azulada).
- Borde amarillo inferior se mantiene como firma constante.
- Transición: `transition: top 0.3s ease-out` ya en el CSS base.

**Área de búsqueda en main-bar:**
- Contenedor `.main-header__search-area`: `display: flex; align-items: center; gap: 12px`.
- Enlace "Búsqueda avanzada": `font-size: 11px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase; color: var(--ate-color-brand-blue-mid)`. Oculto en mobile (<`$lg`).
- Input de búsqueda: `border: 1.5px solid var(--ate-hairline); border-radius: var(--ate-radius-md); height: 40px`. Focus: `border-color: var(--ate-color-brand-blue-mid); outline: 2px solid var(--ate-color-brand-blue-mid); outline-offset: 2px`.
- Botón: sin fondo, color `var(--ate-text-muted)`, icono FA lupa. Hover: `color: var(--ate-color-brand-blue-mid)`.
- En desktop: ancho del form ~280px.

### Alternativas consideradas
- **Búsqueda colapsable con icono en mobile**: la búsqueda siempre visible es el requisito. En mobile, el nav colapsa a hamburguesa; la búsqueda puede reducirse a icono que despliega un input overlay — aplazado para segunda iteración, en esta primera el search-form se oculta en mobile.
- **Sombra siempre visible**: contamina el estado inicial limpio. El sistema ATE prefiere contraste de superficies. Descartado.

### Consecuencias
- En mobile, el `__search-form` se oculta (`display: none` en `< $lg`) en esta primera iteración.
- El JS existente en `script.js` gestiona el desplazamiento del header; se añade toggle de `.header-scrolled` para CSS adicional.
- El `$header-min-height` en `_layout.scss` se actualiza si es necesario.

### Dependencias
- Requiere: D1 (tokens de color ATE).
- Desbloquea: implementación del header por el Desarrollador.

---

## [2026-04-27] ACEPTADA

### Decisión
Estilos del footer: tokens ATE sobre estructura existente.

### Contexto
El footer mantiene su estructura y opciones configurables (decisión del cliente). Solo se actualizan los colores para usar la paleta ATE.

**Cambios:**
- `.main-footer`: añadir `border-top: 4px solid var(--ate-color-brand-yellow)`.
- `&__top` (background): `$color__secondary` ya mapeará a `var(--primary)` = `#0C2C84` con los nuevos defaults. Correcto.
- `&__bottom` (copyright): `$color__secondary-dark` = `#0E3599` (10% más oscuro). Correcto.
- Links en footer: añadir hover `color: var(--ate-color-brand-yellow)`.
- Eliminar la referencia hardcoded a `'Open Sans'` en `font-family` del footer — usar herencia tipográfica.

### Alternativas consideradas
N/A — cambio mínimo acordado con el cliente.

### Consecuencias
- Solo los colores y la border-top cambian. Estructura intacta.

### Dependencias
- Requiere: D1.

---

## [2026-04-27] ACEPTADA

### Decisión
Estilos del sidebar de facetas en la página de búsqueda.

### Contexto
La página `/item/search` renderiza un sidebar `aside.search-facets` con los filtros del módulo Advanced Search. El tema base tiene estilos mínimos para este componente. Se aplica el diseño ATE.

**Especificación:**
- `aside.search-facets`: `background-color: var(--ate-surface-soft); padding: 24px; border-radius: var(--ate-radius-lg)`.
- Cada grupo de faceta: título en `font-size: 11px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase; color: var(--ate-color-brand-blue-dark); margin-bottom: 8px`.
- Valores de faceta: `color: var(--ate-text-body)`, hover subrayado.
- Faceta/valor activo (checked): `background-color: var(--ate-surface-yellow)`, `border-left: 4px solid var(--ate-color-brand-yellow)`, `padding-left: 8px`, `border-radius: var(--ate-radius-xs)`.
- Separador entre grupos: `border-bottom: 1px solid var(--ate-hairline); margin-bottom: 16px`.
- `.search-filters` (chips de filtros activos): `background-color: var(--ate-surface-card); border-left: 3px solid var(--ate-color-brand-blue-mid); border-radius: var(--ate-radius-xs)`.

### Alternativas consideradas
- **Estilos inline en el template `search/facets-list`**: menos mantenible, mezcla presentación y estructura. Descartado.

### Consecuencias
- Los estilos se añaden a `_advanced-search.scss` como sección nueva `.search-facets`.

### Dependencias
- Requiere: D1.
- No bloquea ninguna otra decisión.

---

## [2026-05-01] SUPERSEDED

### Decisión
D2 original — Lato + Source Serif 4 — supersedida por D2c [2026-05-04].

### Contexto
El Orquestador abrió una decisión PENDIENTE el 2026-05-01 cuestionando la adecuación de Source Serif 4. Tras evaluar alternativas y revisar el tipo de consumo de contenido del repositorio (ráfagas cortas, no lectura extensa), se descartó la arquitectura serif/sans en favor de una fuente sans-serif única.

### Consecuencias
- Sustituida por: D2c [2026-05-04].

---

## [2026-05-01] SUPERSEDED

**Supera: D2 [2026-04-27]**

### Decisión
D2b (PT Serif) — supersedida por D2c [2026-05-04] antes de implementarse.

### Contexto
Propuesta intermedia evaluada y descartada en la misma sesión al cuestionar la premisa de mantener una arquitectura serif/sans. Ver D2c.

### Consecuencias
- Sustituida por: D2c [2026-05-04].

---

## [2026-05-04] ACEPTADA

**Supera: D2 [2026-04-27] y D2b [2026-05-01]**

### Decisión
Tipografía (D2c): **Inter como fuente única** para todo el tema — UI, titulares y contenido corrido. Se elimina la arquitectura sans+serif. Los tokens `--ate-font-heading` y `--ate-font-body` se unifican en `Inter, sans-serif`.

### Contexto
Tras evaluar PT Serif, Merriweather e Inter con muestra visual sobre contenido real del repositorio, se descartó la premisa de necesitar una fuente serif. El repositorio se consume en ráfagas cortas (títulos, metadatos de 2-3 líneas, labels de facetas); el argumento clásico de legibilidad serif aplica a lectura extensa, no a este patrón de uso. Los repositorios educativos digitales de referencia (Google Classroom, Khan Academy, plataformas GovTech europeas) usan sistemas sans-serif únicos. Inter está diseñada específicamente para UI digital con texto denso a 13–14px, que es exactamente el caso de uso dominante del tema.

**Evaluación de candidatas:**

| Candidata | Legibilidad UI (13–14px) | Perfil | Sistema resultante | Veredicto |
|-----------|--------------------------|--------|--------------------|-----------|
| PT Serif | Aceptable | Institucional-gubernamental | Dos fuentes (+ Lato) | Descartada — premisa serif innecesaria |
| Merriweather | Aceptable | Neutral/educativo | Dos fuentes (+ Lato) | Descartada — misma razón |
| **Inter** | Excelente | UI digital moderno | **Una fuente** | ✅ Elegida |

**Cambios respecto a D2:**
- Google Fonts: eliminar `Source+Serif+4` y `Lato`; cargar solo `Inter:wght@400;500;600;700;800`.
- Token `--ate-font-heading`: `Lato, sans-serif` → `Inter, sans-serif`.
- Token `--ate-font-body`: `'Source Serif 4', Georgia, serif` → `Inter, sans-serif`.
- `_typography.scss`: `$font__headings` y `$font__body` → `var(--ate-font-heading)` (mismo valor).
- `_footer.scss`: eliminar referencia hardcoded a `'Open Sans'` (igual que en D2).

### Alternativas consideradas
- **Mantener arquitectura serif/sans (PT Serif + Lato)**: válida pero innecesariamente compleja para el patrón de consumo real del repositorio.
- **Lato como fuente única**: posible, pero Inter tiene mejor rendimiento en UI densa a tamaños pequeños y es variable font (menos peticiones HTTP).

### Consecuencias
- Los tokens `--ate-font-heading` y `--ate-font-body` pasan ambos a `Inter, sans-serif` (ver entrada siguiente).
- Carga de Google Fonts simplificada: una familia, un eje de peso.
- El Desarrollador puede proceder con backlog #8 usando esta especificación.

### Dependencias
- Requiere: D1 (actualización de tokens, ver entrada siguiente).
- Desbloquea: implementación de tipografía (backlog #8). Resuelve la decisión PENDIENTE del Orquestador [2026-05-01].

---

## [2026-05-04] ACEPTADA — Actualización de tokens D1

**Actualiza tokens en: D1 [2026-04-27]**

### Decisión
Los tokens `--ate-font-heading` y `--ate-font-body` de D1 se unifican en `Inter, sans-serif` como consecuencia de D2c.

### Contexto
D1 es append-only; no se modifica la entrada original. Esta entrada registra el valor correcto para la implementación.

**Tokens actualizados:**

| Token CSS | Valor anterior | Valor nuevo |
|-----------|---------------|-------------|
| `--ate-font-heading` | `Lato, sans-serif` | `Inter, sans-serif` |
| `--ate-font-body` | `'Source Serif 4', Georgia, serif` | `Inter, sans-serif` |

### Consecuencias
- El Desarrollador usa `Inter, sans-serif` en ambos tokens en `layout.phtml`.
- Google Fonts: cargar `Inter:wght@400;500;600;700;800` (variable font, una sola petición).

### Dependencias
- Derivada de: D2c [2026-05-04].

---

## [2026-05-04] ACEPTADA

### Decisión
Rediseño de la sección de medios en la página de ítem: grid unificado y eliminación de metadatos redundantes.

### Contexto
La vista de ítem (`show.phtml`) agrupaba los medios por tipo ("Archivos", "Otros medios"), lo que fragmentaba la experiencia visual cuando un ítem tiene múltiples tipos de recursos. Además, se mostraba el nombre del archivo o la URL original como título ("Source"), lo cual resultaba ruidoso e irrelevante para el usuario final en la mayoría de los casos (ej: nombres de archivos técnicos o hashes).

**Especificación:**
- **Grid unificado**: Se sustituyen las secciones por un único `masonry-grid` que contiene todos los medios del recurso (`$resource->media()`).
- **Jerarquía de ancho**:
    - Medios interactivos/embebidos (HTML, SCORM) ocupan el **ancho completo** (`grid-column: span 2`) para permitir su uso directo en la página.
    - Medios visuales/descargables (Imágenes, PDF, etc.) se mantienen en **dos columnas** (comportamiento base del grid).
- **Eliminación del campo "Source"**: Se oculta el título del medio (frecuentemente el nombre del archivo) tanto en las tarjetas del grid como en la lista lateral de medios.
- **Simplificación de tarjetas**: Se eliminan las etiquetas de tipo de medio para reducir el ruido visual. En el caso de archivos, se mantiene únicamente el botón de descarga alineado a la derecha.

### Alternativas consideradas
- **Mantener agrupamiento por tipos**: Descartado por fragmentar demasiado el contenido principal del ítem.
- **Mostrar el título solo si es diferente al nombre del archivo**: Difícil de implementar de forma robusta en Omeka S sin lógica compleja. La decisión es priorizar la limpieza visual.

### Consecuencias
- Mejora la cohesión visual de la página de detalle.
- Las miniaturas en la barra lateral son puramente visuales, eliminando el texto descriptivo redundante.

### Dependencias
- Desbloquea: implementación en `media-embeds.phtml` y `media-list.phtml`.


