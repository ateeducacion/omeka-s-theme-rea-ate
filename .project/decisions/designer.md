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

---

## [2026-05-05] ACEPTADA — Especificación item-set browse (colecciones)

### Decisión
Galería de colecciones como página de destino editorial: grid de tarjetas grandes con imagen de portada a sangre, franja azul institucional con título y contador, y descripción sobre fondo suave. Diseño generado con Claude Design antes de implementar.

### Contexto
La página `item-set/browse.phtml` actual usa el mismo `resource-grid` que los ítems individuales — tarjeta de 320px de alto, thumbnail 200px, título y tags. Las colecciones (item-sets) son el punto de entrada principal al repositorio; merecen un tratamiento más editorial y diferenciado que transmita la riqueza de cada colección antes de entrar en ella.

**Principios de diseño para esta página:**
- Las colecciones son pocas (estimación: 5–15) — el grid no necesita paginación en la mayoría de los casos.
- Cada colección debe sentirse como una "puerta de entrada" con identidad propia.
- La imagen de portada es el elemento visual dominante; el título y el contador de ítems son los datos más relevantes.
- La descripción es opcional y se muestra truncada.
- El sistema de tokens ATE debe ser la fuente de verdad para colores, tipografía y radios.

### Especificación visual

**Layout:**
- Grid de **2 columnas** en desktop (≥ 900px), **1 columna** en mobile (< 600px), **2 columnas** intermedias (600–899px) con cards más compactas.
- Gap entre tarjetas: `32px` desktop, `20px` mobile.
- La primera tarjeta puede ocupar las 2 columnas si es "featured" (aplazado para ciclo futuro — por ahora todas las tarjetas son iguales).

**Anatomía de la tarjeta (`collection-card`):**

```
┌─────────────────────────────────────────────┐
│                                             │
│          IMAGEN DE PORTADA                  │  ← aspect-ratio 16/9, object-fit: cover
│          (fondo surface-card si no hay img) │     sin imagen: icono folder_open centrado
│                                             │
├─────────────────────────────────────────────┤
│  ██  TÍTULO DE LA COLECCIÓN          [N]    │  ← franja brand-blue-dark, texto on-dark
│      Subtítulo / eyebrow opcional    ítems  │     contador como pill brand-yellow/on-yellow
├─────────────────────────────────────────────┤
│  Descripción truncada a 2 líneas...         │  ← surface-canvas, text-body, font-size 14px
│                                 Ver todo →  │  ← link brand-blue-mid, esquina inferior dcha
└─────────────────────────────────────────────┘
```

**Tokens por zona:**

| Zona | Token | Valor |
|------|-------|-------|
| Card background | `--ate-surface-canvas` | `#FFFFFF` |
| Card border | `--ate-hairline` | `#DDE3EC` |
| Card radius | `--ate-radius-xl` | `20px` |
| Card shadow | `0 2px 8px rgba(12,44,132,0.07)` | — |
| Card shadow hover | `0 10px 28px rgba(12,44,132,0.14)` | — |
| Card hover transform | `translateY(-4px)` | — |
| Imagen placeholder bg | `--ate-surface-card` | `#EEF2F8` |
| Imagen placeholder icon | `--ate-color-brand-blue-mid` | `#0768AC` |
| Franja título bg | `--ate-color-brand-blue-dark` | `#0C2C84` |
| Franja título texto | `--ate-text-on-dark` | `#FFFFFF` |
| Contador pill bg | `--ate-color-brand-yellow` | `#FFB300` |
| Contador pill texto | `--ate-text-on-yellow` | `#0C2C84` |
| Descripción bg | `--ate-surface-canvas` | `#FFFFFF` |
| Descripción texto | `--ate-text-body` | `#2C2C2C` |
| Link "Ver todo" | `--ate-color-brand-blue-mid` | `#0768AC` |

**Tipografía:**
- Título: `Inter` · 18px · weight 700 · color `--ate-text-on-dark` · 2 líneas máx con `line-clamp: 2`
- Contador label "ítems": 11px · weight 600 · uppercase · `--ate-text-on-yellow`
- Descripción: `Inter` · 14px · weight 400 · `--ate-text-body` · `line-clamp: 2`
- Link: 13px · weight 700 · `--ate-color-brand-blue-mid`

**Estado sin imagen de portada:**
- Fondo del área de imagen: `--ate-surface-card` (`#EEF2F8`)
- Icono `folder_open` de Material Symbols, 48px, `--ate-color-brand-blue-mid`, centrado.
- Mismo aspect-ratio 16/9.

**Estados interactivos:**
- Hover sobre la card: `box-shadow` elevado + `translateY(-4px)` (igual que `media-card`).
- El enlace "Ver todo →" es el CTA explícito; el título en la franja también es enlace (`color: inherit; text-decoration: none`).
- Focus visible con `outline: 2px solid var(--ate-color-brand-blue-mid); outline-offset: 2px`.

**Sección de encabezado de página:**
- Título de página: `h1` con texto "Colecciones" (via `pageTitle()`), `font-size: clamp(1.4rem, 3vw, 2rem)`, `font-weight: 800`, `color: --ate-color-brand-blue-dark`.
- Subtítulo opcional: `font-size: 15px`, `color: --ate-text-muted`, `margin-bottom: 32px`.
- Sin `browse-controls` de layout toggle (las colecciones siempre en grid — no tiene sentido el toggle list para este contexto de alta jerarquía visual).

### Prompt para Claude Design

> **Diseña la página "Colecciones" de un repositorio educativo institucional del Gobierno de Canarias (España).**
>
> **Sistema de diseño:**
> - Tipografía: Inter (única fuente). Titulares en weight 800/700, body en 400/500.
> - Paleta: azul institucional `#0C2C84`, azul atlántico `#0768AC`, amarillo solar `#FFB300`, blanco `#FFFFFF`, gris suave `#EEF2F8`.
> - Canvas blanco limpio, acentos en amarillo y azul. Estilo sobrio e institucional pero accesible y moderno.
>
> **Página a diseñar — "Colecciones" (`/colecciones`):**
>
> Muestra un grid de 2 columnas con tarjetas de colección. Cada tarjeta tiene:
> 1. **Imagen de portada** (aspecto 16:9, `object-fit: cover`). Si no hay imagen, fondo gris pálido `#EEF2F8` con icono carpeta centrado en `#0768AC`.
> 2. **Franja de título** — fondo `#0C2C84`, texto blanco, peso 700, 18px, máx 2 líneas. A la derecha de la franja: pill amarillo `#FFB300` con el número de recursos en azul `#0C2C84` (ej. "34 recursos"), texto 11px uppercase.
> 3. **Área de descripción** — fondo blanco, texto `#2C2C2C`, 14px, 2 líneas máx con ellipsis. Esquina inferior derecha: enlace "Ver colección →" en `#0768AC`, 13px, bold.
>
> **Tarjeta:** radio `20px`, sombra suave `0 2px 8px rgba(12,44,132,0.07)`. Hover: sombra `0 10px 28px rgba(12,44,132,0.14)` + `translateY(-4px)`.
>
> **Encabezado de la página:** título "Colecciones" en `#0C2C84`, 32px, weight 800. Subtítulo "Explora los recursos por colección temática" en `#6B7280`, 15px.
>
> **Layout:** grid 2 columnas, gap 32px. En mobile (< 600px): 1 columna. El header sticky del sitio ya existe (azul oscuro con borde amarillo inferior) — no lo diseñes, asume que está presente.
>
> **Muestra 4 tarjetas de ejemplo** con colecciones reales de un repositorio educativo canario:
> - "Recursos para Matemáticas" — 48 recursos — con imagen de aula
> - "Ciencias Naturales" — 23 recursos — con imagen de naturaleza
> - "Lengua y Literatura" — 61 recursos — con imagen de libros
> - "Historia de Canarias" — 17 recursos — sin imagen de portada (muestra el fallback)

### Alternativas consideradas
- **Masonry grid como el item-browse**: descartado. Las colecciones necesitan consistencia visual entre tarjetas; el masonry variable resta jerarquía. Grid regular con ratio fijo.
- **Hero de colección destacada + grid de las demás**: atractivo pero añade complejidad editorial (¿quién decide cuál es la destacada?). Aplazado para ciclo futuro cuando haya un campo de metadatos para ello.
- **Vista list solamente**: las colecciones se benefician de la imagen de portada como identificador visual. El grid es el modo principal; no se implementa toggle list para esta página.

### Consecuencias
- El Desarrollador crea `asset/sass/components/item-set-browse/_item-set-browse.scss` nuevo.
- El template `view/omeka/site/item-set/browse.phtml` se reescribe: elimina `resource-grid` / `resource-list` genéricos y usa la estructura `.collection-card` específica.
- El `browse-controls` (layout toggle, sort) se simplifica: se mantiene el sort, se elimina el toggle de layout.
- El campo `item_count_tag` inline actual (con `style="background-color: hsl(120, 50%, 85%)"`) se elimina; el contador pasa al componente `.collection-card__counter` con tokens ATE.

### Proceso de implementación
1. ~~El usuario genera el diseño visual con Claude Design usando el prompt de esta decisión.~~ ✅ [2026-05-05]
2. ~~El diseño resultante se revisa y se ajusta el prompt si es necesario.~~ ✅ [2026-05-05] — ver D7b
3. El Diseñador actualiza esta decisión con cualquier ajuste derivado del output visual. → completado en D7b.
4. El Desarrollador implementa basándose en la especificación y el diseño visual aprobado. → desbloqueado.

### Dependencias
- Requiere: D1 (tokens `--ate-*`), D2c (Inter).
- ~~Bloquea: implementación del Desarrollador hasta que el output de Claude Design esté aprobado.~~ → DESBLOQUEADO [2026-05-05]
- Desbloquea: backlog Ciclo 3 #7.

---

## [2026-05-05] ACEPTADA — D7b: Validación Claude Design + ajustes a spec item-set browse

**Complementa y ajusta: D7 [2026-05-05]**

### Decisión
El output de Claude Design queda aprobado. Se introducen cuatro ajustes sobre la spec D7 derivados del diseño visual generado. El Desarrollador queda desbloqueado para implementar.

### Ajustes sobre D7

**1. Grid: 3 columnas en desktop (era 2)**
- El diseño renderiza 3 columnas a ancho completo. Visualmente resulta más rico y aprovecha mejor el espacio en colecciones de 5–15 ítems.
- **Nuevo breakpoint:** 3 cols (≥ 1025px) → 2 cols (601–1024px) → 1 col (≤ 600px).
- Gap: `24px` (en lugar de `32px` de D7 — ajustado al css generado).

**2. CTA: "Ver colección →" (era "Ver todo →")**
- Más específico y coherente con la entidad que representa la tarjeta.
- El HTML usa `.collection-card__cta` con texto `$translate('Ver colección')`.

**3. Barra de filtros Etapa/Nivel/Temática: validada como adición**
- No estaba en D7. El diseño la incluye como una barra pill sobre `--ate-surface-soft` con icono `tune` de Material Symbols.
- Tres `<select>` para Etapa (`dcterms:educationLevel`), Nivel (`lom:educationalLevel`), Temática (`dcterms:subject`).
- El filtrado es 100% client-side (JS inline en partial `_browse-filter-script.phtml`).
- Los `<select>` se pueblan dinámicamente desde los valores únicos de `$itemSets` en PHP — **no hardcoded**.

**4. Subtítulo del encabezado**
- D7 proponía "Explora los recursos por colección temática". El diseño usa "Recursos agrupados por colección temática." — más conciso y descriptivo. ✅ Aceptado.

### Consecuencias
- La spec D7 queda actualizada con los puntos 1–4 de esta entrada.
- El Desarrollador usa este D7b como fuente de verdad para implementación, junto con las correcciones técnicas registradas en developer.md.

### Dependencias
- Desbloquea: implementación del Desarrollador (backlog Ciclo 3 #7).

---

## [2026-05-05] ACEPTADA — Consistencia de badges de tipo de recurso (QA-002)

### Decisión
Unificar el tratamiento visual de `lrmi:learningResourceType` en todas las vistas (search results e item show) utilizando el componente `.resource-type-badge`. Se elimina el estilo simplificado `.lrt-badge` de los resultados de búsqueda.

### Contexto
Existía una inconsistencia entre la vista de búsqueda (badge plano, sin icono) y la vista de ítem (badge con icono contextual y normalización de etiqueta). Unificar el componente mejora la coherencia semántica y el reconocimiento visual del tipo de recurso.

**Especificación:**
- **Componente único:** `.resource-type-badge`.
- **Iconografía:** Se mantiene la lógica de mapeo de iconos por tipo de recurso definida en `item/show.phtml`.
- **Tratamiento textual:** Normalización de casing (Sentence case) y limpieza de etiquetas.
- **Tokens ATE:**
    - Background: `--ate-surface-card` (`#EEF2F8`).
    - Texto e Icono: `--ate-color-brand-blue-mid` (`#0768AC`).
    - Radio: `--ate-radius-pill` (`9999px`).

### Consecuencias
- El Desarrollador debe extraer la lógica de renderizado del badge a un partial o helper para su reutilización.
- Se actualiza `search/search.phtml` para usar el nuevo componente.

### Dependencias
- Requiere: D1 (tokens ATE).

---

## [2026-05-05] ACEPTADA — Ajuste jerárquico del filtro en item-set browse (QA-003)

### Decisión
Reducir el peso visual del bloque de filtros en `item-set/browse` y reposicionar el contador de resultados para mejorar la jerarquía de la página.

### Contexto
El bloque de filtros actual (añadido en D7b) compite en exceso con el título de la página y las colecciones. Además, el contador de resultados (`item-set-browse-header__count`) debe alinearse con el grid de colecciones para actuar como metadato del listado y no de la cabecera.

**Cambios:**
- **Integración del filtro:** El contenedor del filtro pasa de un bloque destacado a una barra más discreta integrada en `item-set-browse-header`. Se reduce el padding vertical y se usa `--ate-surface-soft` como fondo sutil.
- **Reposicionamiento del contador:** `.item-set-browse-header__count` se mueve fuera del bloque de cabecera y se coloca inmediatamente encima del `collections-grid`, alineado a la derecha (`text-align: right` o `margin-left: auto`).
- **Estilo del contador:** `font-size: 13px`, `color: var(--ate-text-muted)`, `font-weight: 500`.

### Consecuencias
- Mejora la "limpieza" visual al entrar en la página de colecciones.
- El usuario identifica rápidamente cuántas colecciones hay disponibles justo antes de empezar a explorarlas.

### Dependencias
- Complementa: D7 y D7b.

---

## [2026-05-06] ACEPTADA — Ajuste visual de los selects de filtro (QA-009)

### Decisión
Refinar las proporciones y alinear correctamente el indicador desplegable (chevron) de los selects de filtro en la página de colecciones.

### Contexto
Se reportaron dos problemas visuales en QA-009: el chevron del `<select>` estaba desalineado verticalmente (desplazado hacia arriba) y las proporciones del clip de texto (padding, altura, font-size) no se percibían armoniosas.

**Cambios definidos:**
- **Proporciones del Select:**
  - Incrementar `font-size` de 12px a 13px para mejorar legibilidad.
  - Aumentar el padding a `6px 32px 6px 14px` para darle un aspecto de pastilla más equilibrado.
- **Alineación y Estandarización del Chevron:**
  - Sustituir el triángulo CSS por el glifo `\f107` de Font Awesome 5 Free, para mantener coherencia con las facetas de búsqueda.
  - Posicionarlo a `right: 12px` y centrarlo con `translateY(-50%)`.
  - Ajustar su tamaño a `13px`.

### Consecuencias
- Mejora la percepción de calidad ("fit and finish") de la interfaz.
- Los selects se sienten más coherentes con otros controles interactivos del diseño (como los botones secundarios).

### Dependencias
- QA-009: Implementar estos estilos en `_item-set-browse.scss`.

---

## [2026-05-06] ACEPTADA — Refactorización del contador y cabecera de colecciones (QA-005)

### Decisión
Transformar el contador de resultados en un componente de tipo "clip" y unificar la cabecera de la página para mejorar la compacidad y alineación visual.

### Contexto
El contador de resultados ocupaba demasiado espacio y carecía de separación respecto al grid. Además, la barra de filtros causaba saltos de línea innecesarios en la cabecera.

### Cambios definidos
- **Contador (QA-005):**
  - Rediseñado como pastilla compacta con fondo `var(--ate-surface-soft)` y borde suave.
  - Padding ajustado a `4px 14px` y fuente a `12px`.
  - Incremento del margen inferior a `24px` para separar claramente la información del grid de contenidos.
- **Cabecera (Regresión):**
  - Implementación de Flexbox en `.item-set-browse-header` para alinear el título a la izquierda y los filtros a la derecha en la misma línea.
  - Eliminación de bordes internos redundantes para una integración más limpia.
  - Adaptabilidad: Salto a columna a los `800px` para evitar colisiones en pantallas pequeñas.

### Consecuencias
- Interfaz más moderna y equilibrada.
- El usuario percibe el contador como un dato de estado y no como un titular secundario.
- Mayor aprovechamiento del espacio vertical ("above the fold").

### Dependencias
- QA-005.

