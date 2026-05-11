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

---

## [2026-05-06] ACEPTADA — Home: sección de audiencias priorizadas

### Decisión
La home incorpora una sección editorial de entrada por audiencias con tres tarjetas: **Profesorado**, **Alumnado** y **Familias**. La jerarquía visual prioriza Profesorado y Alumnado como accesos principales y relega Familias a un tratamiento secundario, manteniendo el mismo lenguaje visual ATE.

### Contexto
El proyecto define tres perfiles claros y explicita que Profesorado y Alumnado son la prioridad de producto, con Familias como audiencia secundaria. La home necesita una pieza simple y orientativa que permita a cada usuario entrar al repositorio por intención de uso, no por vocabulario técnico o estructura interna de Omeka-S.

La sección debe servir tanto si el administrador dirige la tarjeta a una colección como si la dirige a una búsqueda guardada o browse filtrado. Por tanto, el diseño debe ser agnóstico del destino y comunicar "puerta de entrada" más que "categoría fija".

### Especificación visual

**Objetivo visual**
- La sección debe sentirse como un bloque editorial de orientación, situado cerca del comienzo de la home.
- Profesorado y Alumnado son los dos accesos principales.
- Familias aparece como tercer acceso, visible pero con menos peso.

**Layout**
- Contenedor: `.audience-rail`
- Cabecera: `.audience-rail__header`
- Grid: `.audience-rail__grid`
- Desktop (≥ 1025px): grid de 2 columnas para las tarjetas principales + una tercera tarjeta secundaria a ancho completo debajo.
- Tablet (601–1024px): 2 columnas, con Familias ocupando ambas columnas.
- Mobile (≤ 600px): 1 columna, orden Profesorado → Alumnado → Familias.

**Anatomía**
```text
.audience-rail
  .audience-rail__header
    h2.audience-rail__title
    p.audience-rail__intro
  .audience-rail__grid
    a.audience-card.audience-card--teachers
    a.audience-card.audience-card--students
    a.audience-card.audience-card--families
```

**Tarjeta (`.audience-card`)**
- Estructura:
  - `.audience-card__icon`
  - `.audience-card__eyebrow`
  - `.audience-card__title`
  - `.audience-card__body`
  - `.audience-card__cta`
- Radio: `var(--ate-radius-xl)`
- Borde: `1px solid var(--ate-hairline)`
- Sombra base: `0 2px 8px rgba(12,44,132,0.06)`
- Hover: `transform: translateY(-3px)` + sombra `0 10px 24px rgba(12,44,132,0.12)`
- Focus visible: `outline: 2px solid var(--ate-color-brand-blue-mid); outline-offset: 3px`

**Jerarquía por audiencia**
- `.audience-card--teachers`
  - fondo `var(--ate-color-brand-blue-dark)`
  - texto `var(--ate-text-on-dark)`
  - CTA secundario sobre fondo claro o pill blanca
- `.audience-card--students`
  - fondo `linear-gradient(135deg, var(--ate-color-brand-blue-mid), var(--ate-color-brand-blue-dark))`
  - texto `var(--ate-text-on-dark)`
- `.audience-card--families`
  - fondo `var(--ate-surface-soft)`
  - texto `var(--ate-text-body)`
  - borde más visible, sin competir con las dos principales

**Iconografía**
- Profesorado: `school`
- Alumnado: `auto_stories`
- Familias: `groups`
- Material Symbols, 28–32px, `aria-hidden="true"`

**Copy de ejemplo**
- Profesorado
  - Eyebrow: `Recursos para enseñar`
  - Título: `Profesorado`
  - Body: `Secuencias, guías y materiales listos para llevar al aula.`
  - CTA: `Explorar recursos`
- Alumnado
  - Eyebrow: `Aprender por materias`
  - Título: `Alumnado`
  - Body: `Contenidos visuales e interactivos para descubrir y practicar.`
  - CTA: `Descubrir recursos`
- Familias
  - Eyebrow: `Acompañar el aprendizaje`
  - Título: `Familias`
  - Body: `Recursos accesibles para reforzar el trabajo en casa.`
  - CTA: `Ver recursos`

**Cabecera de sección**
- `h2.audience-rail__title`: `clamp(1.35rem, 2.8vw, 1.9rem)`, `font-weight: 800`, `color: var(--ate-color-brand-blue-dark)`
- `p.audience-rail__intro`: `15px`, `color: var(--ate-text-muted)`, ancho máximo `62ch`
- Acento decorativo opcional a la izquierda del título con `var(--ate-color-brand-yellow)`

### Alternativas consideradas
- **Tres tarjetas con el mismo peso visual**: descartado. Ignora la prioridad funcional explícita del proyecto.
- **Una sola banda con tabs por audiencia**: descartado. Añade interacción innecesaria y reduce claridad de entrada.
- **Hero principal + dos tarjetas secundarias**: descartado para esta fase. Exige una priorización editorial demasiado rígida.

### Consecuencias
- El Desarrollador queda desbloqueado para implementar la sección Home con las clases `.audience-rail` y `.audience-card`.
- El destino de cada tarjeta debe ser configurable, pero el diseño no depende de si apunta a colección o búsqueda.
- Familias no requiere un componente diferente: comparte anatomía con modificador visual.

### Dependencias
- Requiere: D1 (tokens ATE), D2c (Inter).
- Desbloquea: backlog Ciclo 3 #1 (Home).

---

## [2026-05-06] ACEPTADA — Browse de recursos: modo grid editorial de catálogo

### Decisión
La vista `item/browse` en modo grid adopta un lenguaje de catálogo editorial, inspirado en el boceto visual aportado por el usuario: encabezado compacto de página, herramientas claras de exploración y tarjetas densas que priorizan thumbnail, tipo de recurso, título y nivel educativo.

### Contexto
La implementación actual de `item/browse.phtml` reutiliza un grid genérico del tema base. Funciona, pero no expresa suficientemente que el repositorio es un catálogo educativo. El boceto del usuario acierta en tres aspectos: densidad de catálogo, badge de tipo sobre imagen y lectura rápida por nivel/tema.

La vista debe converger con el sistema ya consolidado en `item-set/browse`: limpieza institucional, clips compactos, sombras suaves y separación clara entre control de página y contenido.

### Especificación visual

**Objetivo visual**
- Sensación de catálogo amplio, navegable y editorial.
- Lectura rápida del card en tres golpes de vista: tipo, título, nivel.
- Mayor densidad que `item-set/browse`, pero sin caer en apariencia técnica o fría.

**Encabezado de página**
- Contenedor: `.resource-browse-header`
- Subbloques:
  - `.resource-browse-header__title-group`
  - `.resource-browse-header__count`
  - `.resource-browse-toolbar`
- Título de página: `Recursos`
- Subtítulo opcional: línea breve de contexto tipo `Catálogo completo del canal de REA.`
- Contador: clip compacto alineado a la derecha, siguiendo QA-005
  - fondo `var(--ate-surface-soft)`
  - texto `var(--ate-color-brand-blue-dark)`
  - borde `1px solid var(--ate-hairline-soft)`
  - radio `var(--ate-radius-pill)`

**Toolbar**
- Búsqueda integrada visualmente como control principal
- Orden y toggle grid/list como controles secundarios
- Clases sugeridas:
  - `.resource-browse-toolbar`
  - `.resource-browse-toolbar__search`
  - `.resource-browse-toolbar__sort`
  - `.resource-browse-toolbar__layout`
- La barra de búsqueda debe verse como cápsula prominente, no como input técnico:
  - altura `44px`
  - fondo `var(--ate-surface-canvas)`
  - borde `1px solid var(--ate-hairline)`
  - radio `9999px`
- Toggle grid/list en pill segmentado de fondo `var(--ate-surface-soft)`

**Grid**
- Wrapper existente puede mantenerse como `resource-grid`, pero cada item pasa a tratarse como componente `.resource-card`
- Breakpoints:
  - ≥ 1280px: 4 columnas
  - 900–1279px: 3 columnas
  - 600–899px: 2 columnas
  - ≤ 599px: 1 columna
- Gap:
  - desktop `22px`
  - tablet `18px`
  - mobile `16px`

**Anatomía de la tarjeta**
```text
li.resource.resource-card
  a / div.resource-card__media
    img.resource-card__image
    .resource-card__badge
  .resource-card__body
    .resource-card__eyebrow (opcional)
    h2.resource-card__title
    .resource-card__meta
      .resource-card__level-pill
      .resource-card__topic-pill (opcional)
```

**Media**
- Ratio recomendado: `4 / 3`
- Fondo fallback: `var(--ate-surface-card)`
- Radio superior `var(--ate-radius-xl)`
- Badge de tipo anclado arriba a la izquierda con margen interno de `12px`

**Badge de tipo**
- Reutiliza `.resource-type-badge`
- En grid usa modificador `.resource-type-badge--overlay`
- Fondo blanco o `rgba(255,255,255,0.94)` para garantizar legibilidad sobre imagen
- Texto e icono en `var(--ate-color-brand-blue-dark)`

**Body**
- Fondo `var(--ate-surface-canvas)`
- Borde lateral/inferior `1px solid var(--ate-hairline)`
- Radio inferior `var(--ate-radius-xl)`
- Padding `14px 16px 16px`

**Eyebrow**
- Uso recomendado para materia/área si existe (`schema:about`) o metadato editorial equivalente
- `12px`, uppercase, `letter-spacing: 1.4px`, `color: var(--ate-text-muted)`
- Si no hay valor, se omite sin dejar hueco

**Título**
- `font-size: 1.06rem`
- `font-weight: 800`
- `line-height: 1.28`
- `color: var(--ate-color-brand-blue-dark)`
- Clamp a 3 líneas

**Nivel educativo**
- `.resource-card__level-pill`
- Fondo `var(--ate-color-brand-blue-dark)`
- Texto `var(--ate-text-on-dark)`
- Radio `var(--ate-radius-pill)`
- `font-size: 12px`, `font-weight: 700`
- Debe ser el metadato persistente garantizado del pie de tarjeta

**Materia / área**
- `.resource-card__topic-pill` opcional
- Fondo `var(--ate-surface-yellow)`
- Texto `var(--ate-text-on-yellow)`
- Borde `1px solid color-mix(in srgb, var(--ate-color-brand-yellow) 75%, white)`
- Misma altura óptica que el pill de nivel

**Interacción**
- Hover tarjeta:
  - `transform: translateY(-4px)`
  - sombra `0 10px 24px rgba(12,44,132,0.12)`
  - borde ligeramente enfatizado
- Focus visible:
  - `outline: 2px solid var(--ate-color-brand-blue-mid)`
  - `outline-offset: 3px`

### Alternativas consideradas
- **Mantener masonry o grid genérico del tema base**: descartado. No comunica catálogo educativo ni jerarquiza bien el tipo y nivel.
- **Tarjetas más altas con descripción siempre visible**: descartado. Reduce densidad y penaliza exploración rápida.
- **Usar el mismo lenguaje exacto de `item-set/browse`**: descartado. Los recursos necesitan más densidad y menor peso editorial que las colecciones.

### Consecuencias
- El Desarrollador queda desbloqueado para rediseñar `item/browse.phtml` en grid alrededor del componente `.resource-card`.
- Debe reutilizar `.resource-type-badge` como base y exponer `lrmi:educationalLevel` como pill visible.
- La descripción larga del grid deja de ser prioritaria; si se mantiene, debe ser secundaria y opcional.

### Dependencias
- Requiere: D1, D2c, QA-002, QA-005.
- Desbloquea: backlog Ciclo 3 #2 (Browse grid).

---

## [2026-05-06] ACEPTADA — Browse de recursos: modo list compacto y coherente con grid

### Decisión
La vista `item/browse` en modo list se define como una fila compacta de catálogo, no como una tarjeta completamente distinta. Debe compartir componentes con el grid y priorizar escaneo horizontal: thumbnail, título, tipo de recurso y nivel educativo.

### Contexto
El modo list existe hoy como variación técnica del tema base. Si se mantiene sin rediseño, parecerá una vista heredada y romperá la coherencia del lote. El objetivo no es competir con el grid, sino ofrecer una lectura más densa para usuarios que quieran comparar títulos con menos protagonismo visual de la imagen.

### Especificación visual

**Objetivo visual**
- Vista más eficiente y compacta que el grid.
- Misma identidad visual que el grid.
- Mismos componentes nucleares: badge de tipo, título azul institucional, pills de metadatos.

**Fila**
- Clase base: `.resource-row`
- Wrapper puede seguir siendo `resource-list`
- Estructura:
```text
li.resource.resource-row
  .resource-row__media
  .resource-row__body
    .resource-row__header
      .resource-type-badge
      .resource-row__level-pill
    h2.resource-row__title
    .resource-row__eyebrow (opcional)
    .resource-row__summary (opcional, 2 líneas)
```

**Layout**
- Desktop (≥ 900px):
  - thumbnail fija a la izquierda
  - cuerpo flexible en el centro
  - metadatos en cabecera del cuerpo
- Tablet/mobile:
  - mini-card apilada
  - thumbnail arriba o a la izquierda según ancho disponible
  - los pills pasan debajo del título si no caben en la cabecera

**Media**
- ancho fijo desktop: `168px`
- ratio `16 / 10`
- radio `var(--ate-radius-lg)`
- fondo fallback `var(--ate-surface-card)`

**Contenedor**
- fondo `var(--ate-surface-canvas)`
- borde `1px solid var(--ate-hairline)`
- radio `var(--ate-radius-lg)`
- padding `12px`
- separación vertical entre filas `14px`
- hover: borde o sombra sutil, no tan teatral como en grid

**Badge de tipo**
- Mismo componente `.resource-type-badge`
- Sin overlay: se coloca inline en la cabecera de la fila
- Fondo `var(--ate-surface-card)`
- Texto/icono `var(--ate-color-brand-blue-mid)`

**Nivel**
- `.resource-row__level-pill`
- Mismo lenguaje que en grid, pero más contenido:
  - fondo `var(--ate-surface-soft)`
  - texto `var(--ate-color-brand-blue-dark)`
  - borde `1px solid var(--ate-hairline)`

**Título**
- `font-size: 1.05rem`
- `font-weight: 800`
- `color: var(--ate-color-brand-blue-dark)`
- Máximo 2 líneas

**Eyebrow / materia**
- Opcional, encima del título o debajo de la cabecera
- `12px`, uppercase, `letter-spacing: 1.2px`, `color: var(--ate-text-muted)`

**Resumen**
- Opcional
- `14px`, `color: var(--ate-text-body)`
- Máximo 2 líneas
- En mobile puede ocultarse si compromete claridad

**Interacción**
- La fila completa debe percibirse como clickable
- Hover:
  - borde `var(--ate-color-brand-blue-mid)` o sombra muy suave
  - no usar elevación fuerte
- Focus visible igual que en grid

**Responsive**
- ≥ 900px: fila horizontal completa
- 600–899px: thumbnail más pequeño y metadatos con wrap
- ≤ 599px: apilado vertical; primero media, luego cuerpo; metadatos debajo del título

### Alternativas consideradas
- **Lista puramente tabular**: descartada. Demasiado administrativa para un repositorio educativo.
- **Reutilizar la card del grid solo cambiando ancho**: descartado. El list debe optimizar comparación y densidad, no solo reflujo.
- **Ocultar la imagen en list**: descartado. La thumbnail sigue siendo un ancla visual útil para reconocimiento rápido.

### Consecuencias
- El Desarrollador queda desbloqueado para implementar el modo list como `.resource-row` coherente con `.resource-card`.
- Debe reutilizar `.resource-type-badge` y el mismo sistema de pills de nivel.
- El toggle grid/list pasa a tener dos vistas realmente diferenciadas pero hermanas.

### Dependencias
- Requiere: D1, D2c, QA-002.
- Desbloquea: backlog Ciclo 3 #6 (Browse list).

---

## [2026-05-07] ACEPTADA — Spec visual del bloque de cofinanciación `.project-funding` (backlog #8)

### Decisión
El bloque `.project-funding` se renderiza como una banda institucional discreta al pie de la ficha del recurso, **después de `view.show.after`**, con layout horizontal logo + texto en desktop y apilado en mobile. No compite con el contenido del recurso ni con los bloques de metadatos: actúa como acreditación legal, análogo a los bloques de cofinanciación que aparecen al final de materiales FEDER.

### Posición en `item/show`
- Ubicación: inmediatamente después de `<?php $this->trigger('view.show.after'); ?>`, antes del modal de admin.
- El bloque ocupa el ancho del contenedor de página (`max-width` del layout general), separado del cuerpo del recurso por un margen superior que lo sitúa claramente fuera del área de contenido.
- Se renderiza solo si `$projectItem !== null` (lógica PHP en el partial, no en `show.phtml`).

### Anatomía HTML

```html
<aside class="project-funding" aria-label="Proyecto cofinanciador">
    <div class="project-funding__logo-wrap">
        <img class="project-funding__logo" src="…" alt="«schema:name»">
    </div>
    <div class="project-funding__content">
        <span class="project-funding__name"><!-- schema:name --></span>
        <p class="project-funding__description"><!-- schema:description --></p>
        <a class="project-funding__link" href="…" target="_blank" rel="noopener">
            Más información
            <span class="material-symbols-outlined" aria-hidden="true">open_in_new</span>
        </a>
    </div>
</aside>
```

- El `<aside>` lleva `aria-label` para que lectores de pantalla lo identifiquen como región informativa.
- El `alt` de la imagen usa `schema:name` del proyecto; si no hay logo, el wrap no se renderiza.
- `schema:url` abre en nueva pestaña con `rel="noopener"` — es un enlace a ficha oficial externa.
- Si `schema:url` está vacío, el enlace no se renderiza (el partial PHP lo controla).

### Especificación visual

**Layout desktop (≥ 600px):**

```
┌────────────────────────────────────────────────────────┐
│ ▌ [LOGO]   Nombre del proyecto                         │
│            Cofinanciado por la Unión Europea — FEDER…  │
│            Más información ↗                           │
└────────────────────────────────────────────────────────┘
```

- Flex horizontal: logo a la izquierda, contenido de texto a la derecha.
- Acento visual: borde izquierdo de `4px solid var(--ate-color-brand-blue-mid)` — mismo patrón que el acento del título en `.audience-rail__title`.
- Logo: `max-height: 56px; max-width: 180px; object-fit: contain`. Sin distorsión de aspecto.
- Gap entre logo y texto: `20px`.

**Layout mobile (< 600px):**
- Apilado vertical: logo arriba (alineado a la izquierda), texto debajo.
- Logo: `max-height: 44px`.

**Tokens por elemento:**

| Elemento | Propiedad | Token | Valor |
|----------|-----------|-------|-------|
| `.project-funding` | `background` | `--ate-surface-soft` | `#F5F7FA` |
| `.project-funding` | `border` | `1px solid var(--ate-hairline-soft)` | `#EEF0F4` |
| `.project-funding` | `border-left` | `4px solid var(--ate-color-brand-blue-mid)` | `#0768AC` |
| `.project-funding` | `border-radius` | `var(--ate-radius-lg)` | `14px` |
| `.project-funding` | `padding` | `20px 24px` | — |
| `.project-funding` | `margin-top` | `40px` | — |
| `.project-funding__name` | `color` | `--ate-color-brand-blue-dark` | `#0C2C84` |
| `.project-funding__name` | `font-size` | `13px` | — |
| `.project-funding__name` | `font-weight` | `600` | — |
| `.project-funding__description` | `color` | `--ate-text-muted` | `#6B7280` |
| `.project-funding__description` | `font-size` | `13px` | — |
| `.project-funding__description` | `line-height` | `1.5` | — |
| `.project-funding__link` | `color` | `--ate-color-brand-blue-mid` | `#0768AC` |
| `.project-funding__link` | `font-size` | `12px` | — |
| `.project-funding__link` | `font-weight` | `600` | — |
| `.project-funding__link icon` | `font-size` | `14px` | — |

**Tipografía:** Inter en todos los elementos. Sin titulares grandes — el bloque es informativo, no editorial.

**Estado sin logo:**
- Si no hay logo resuelto, el `.project-funding__logo-wrap` no se renderiza.
- El `.project-funding__content` ocupa el ancho completo del bloque.

**Estado sin `schema:url`:**
- `.project-funding__link` no se renderiza. El partial PHP no genera el `<a>`.

**WCAG AA:**
- Name `#0C2C84` sobre `#F5F7FA`: ratio ~11.5:1 ✅
- Description `#6B7280` sobre `#F5F7FA`: ratio ~4.8:1 ✅ (texto pequeño, 13px — cumple AA para texto normal)
- Link `#0768AC` sobre `#F5F7FA`: ratio ~5.2:1 ✅

### Archivo Sass a crear
`asset/sass/components/item-show/_project-funding.scss`

Importar desde `asset/sass/components/_components.scss` en la sección de item-show.

### Alternativas consideradas

- **Banner ancho con imagen de fondo del logo**: descartado. Demasiado protagonismo para un elemento legal; además el logo puede ser muy variable en proporción.
- **Bloque dentro de la región de bloques del recurso (como `resourcePageBlocks`)**: descartado. El requisito es que aparezca en todos los REA con proyecto sin configuración por ítem; insertarlo como partial post-contenido es más predecible y no depende de la configuración de "Configure resource pages".
- **Posición al inicio de la ficha (antes del `item-body`)**: descartado. La cofinanciación es información complementaria-legal, no atributo editorial del recurso. Debe ir al pie para no competir con el contenido principal.
- **Usar `--ate-color-brand-yellow` como acento izquierdo**: evaluado pero descartado. El amarillo en bandas de borde estrecho no tiene contraste WCAG suficiente sobre fondo blanco/suave. El `--ate-color-brand-blue-mid` es el correcto para acentos de borde.

### Consecuencias
- El Desarrollador crea `project-funding.phtml` y `_project-funding.scss` siguiendo esta spec.
- Se integra en `show.phtml` después de `view.show.after`.
- El bloque `.project-funding` hereda la tipografía Inter del tema sin declaración adicional.
- No se requiere cambio en `config/theme.ini` — el vínculo es dato del ítem, no configuración del tema.

### Dependencias
- Requiere: decisión Arquitecto [2026-05-07] para el patrón PHP.
- Desbloquea: implementación del Desarrollador (backlog #8).

---

## [2026-05-08] ACEPTADA — Ajustes visuales header main-bar y footer

### Decisión
Dos cambios menores de identidad visual aplicados directamente al código:

**1. Main-bar del header: sin cambio**
- El fondo del `__main-bar` queda en blanco (`$color__white`, heredado de `.main-header`). El cambio a `--ate-text-on-dark-soft` fue aplicado y revertido en la misma sesión.

**2. Footer: borde superior eliminado**
- `border-top: 4px solid var(--ate-color-brand-yellow)` eliminado de `.main-footer`.
- El footer arranca directamente con el fondo oscuro (`$color__secondary`) sin línea de separación. La transición de contenido a footer es más suave.

### Archivos modificados
- `asset/sass/components/header/_header.scss` — `background-color` en `&__main-bar`
- `asset/sass/components/footer/_footer.scss` — eliminada línea `border-top`

### Dependencias
- Sin dependencias. Cambios de estilo aislados, no afectan estructura HTML ni PHP.

---

## [2026-05-07] ACEPTADA — Revisión visual `.project-funding`: sidebar compacto (backlog #8)

**Supera: spec inicial [2026-05-07] para `.project-funding`**

### Decisión
El bloque de cofinanciación se reubica en el **sidebar derecho**, debajo del bloque anclaje curricular, y adopta un lenguaje visual compacto de panel de sidebar — no de banda de pie de página.

### Posición
- Dentro de `<aside class="item-sidebar item-sidebar--right">`, después de `$rightSidebarBlockContent->getBlocks()`.
- Si el ítem tiene `$projectItem` pero no tiene otros bloques de sidebar, se crea igualmente el sidebar derecho (el grid `item-body--has-right` se activa).
- Se elimina la llamada autónoma tras `view.show.after`.

### Anatomía

```
┌──────────────────────────────────┐  ← border-top: 3px brand-blue-mid
│ PROYECTO COFINANCIADOR           │  ← eyebrow 10px uppercase, muted
│ ────────────────────────────── │  ← hairline divider
│        [  L O G O  ]            │  ← logo centrado, max-height 40px
│ ────────────────────────────── │  ← hairline divider (solo si hay logo)
│ Nombre del proyecto              │  ← 13px, weight 600, brand-blue-dark
│ Descripción legal cofinancia-   │  ← 12px, muted, line-clamp 4
│ ción FEDER 2021-2027...         │
│                    Más info ↗   │  ← 12px, weight 600, brand-blue-mid
└──────────────────────────────────┘
```

### Tokens

| Elemento | Propiedad | Token/Valor |
|----------|-----------|-------------|
| `.project-funding` | `background` | `var(--ate-surface-canvas)` |
| `.project-funding` | `border` | `1px solid var(--ate-hairline)` |
| `.project-funding` | `border-top` | `3px solid var(--ate-color-brand-blue-mid)` |
| `.project-funding` | `border-radius` | `var(--ate-radius-lg)` |
| `.project-funding` | `padding` | `16px 18px` |
| `.project-funding__eyebrow` | `color` | `var(--ate-text-muted)` |
| `.project-funding__eyebrow` | `font-size` | `10px` |
| `.project-funding__logo-area` | `text-align` | `center` |
| `.project-funding__logo` | `max-height` | `40px` |
| `.project-funding__logo` | `max-width` | `160px` |
| `.project-funding__name` | `color` | `var(--ate-color-brand-blue-dark)` |
| `.project-funding__name` | `font-size` | `13px; weight 600` |
| `.project-funding__description` | `color` | `var(--ate-text-muted)` |
| `.project-funding__description` | `font-size` | `12px` |
| `.project-funding__link` | `color` | `var(--ate-color-brand-blue-mid)` |
| `.project-funding__link` | `font-size` | `12px; weight 600` |
| `.project-funding__link` | `display` | `block; text-align: right` |

### Diferenciación respecto al anclaje curricular

| Atributo | Anclaje curricular | Project funding |
|----------|--------------------|-----------------|
| Fondo | `surface-soft` | `surface-canvas` (blanco) |
| Acento | `border-left` amarillo | `border-top` azul-mid |
| Propósito visual | Metadatos curriculares | Acreditación institucional |

### Consecuencias
- Desarrollador actualiza `_project-funding.scss` completo.
- Desarrollador mueve la llamada al partial en `show.phtml` al interior del sidebar derecho y actualiza la condición de `$bodyClass`.

### Dependencias
- Cierra: spec inicial `.project-funding` [2026-05-07].

---

## [2026-05-07] ACEPTADA — Sistema de color de audience-card: desacoplado de tema y WCAG AA garantizado (QA-013)

### Decisión
Todos los elementos descendientes de `.audience-card` (title, eyebrow, body, icon) y todos los estados de interacción (`:hover`, `:visited`, `:focus-visible`) tienen `color` declarado explícitamente con tokens `--ate-*` fijos dentro del bloque de cada variante. Ningún color depende de `--primary`, `--secondary`, `--accent` ni hereda de reglas globales de elemento.

### Diagnóstico real del problema

El problema no era el fondo de la card, sino la **cascada CSS sobre los elementos hijos**:

1. **`h3 { color: var(--ate-text-ink); }`** — regla global con especificidad (0,0,0,1). El `<h3 class="audience-card__title">` recibe `#0A0A0A` (negro) en lugar de heredar el blanco del padre. Resultado: negro sobre azul oscuro, ratio ~1:1. ❌
2. **`a:hover { color: var(--accent-dark); }`** — especificidad (0,0,1,1) > `.audience-card--teachers` (0,0,1,0). En hover, el color de toda la card cambia a `--accent-dark` configurable. ❌
3. **`a:visited { color: var(--accent); }`** — misma situación. ❌

### Solución

Color explícito en cada descendiente dentro del bloque de variante, con especificidad (0,0,2,0) que supera cualquier regla global de elemento o pseudo-clase simple:

```scss
.audience-card--teachers {
    // ...fondos y borde...
    &:hover, &:focus-visible, &:visited { color: var(--ate-text-on-dark); }
    .audience-card__eyebrow { color: var(--ate-text-on-dark); }
    .audience-card__title   { color: var(--ate-text-on-dark); }
    .audience-card__body    { color: var(--ate-text-on-dark); }
    .audience-card__icon    { color: var(--ate-text-on-dark); }
}
// idem para --students / --families con sus tokens
```

### Contraste verificado (valores reales de tokens)

| Variante | Fondo | Texto | Ratio | AA |
|----------|-------|-------|-------|----|
| Teachers | `#0C2C84` | `#FFFFFF` | ~10.7:1 | ✅ |
| Students (peor gradiente) | `#0768AC` | `#FFFFFF` | ~5.2:1 | ✅ |
| Families body | `#F5F7FA` | `#2C2C2C` | ~11.4:1 | ✅ |
| Families eyebrow | `#F5F7FA` | `#6B7280` | ~4.8:1 | ✅ |
| Families icon | `#F5F7FA` | `#0768AC` | ~5.2:1 | ✅ |

### Consecuencias
- `QA-013` pasa a estado Resuelto.
- Cualquier componente `.audience-card` futuro debe declarar sus colores en el bloque de variante, no en reglas de elemento ni depender de herencia.

### Dependencias
- Requiere: D1 (tokens `--ate-*`), decisión Orquestador [2026-05-07].
- Cierra: QA-013.

---

## [2026-05-11] ACEPTADA — Búsqueda mobile en header: segunda fila de la main-bar siempre visible (QA-015)

### Decisión

La búsqueda mobile se integra como **segunda fila de la main-bar**, siempre visible en pantallas < 1024px. No se usa lupa activable, ni drawer, ni overlay: la barra de búsqueda aparece directamente en el header sin acción del usuario, cumpliendo el requisito "siempre visible".

### Justificación

Este repositorio educativo tiene la búsqueda como acción primaria para los tres perfiles de audiencia. Una solución activada por icono (lupa, toggle) añade una fricción injustificada y convierte la búsqueda en una funcionalidad "descubrible", no visible. El menu-drawer está reservado para navegación estructural, no para herramientas de búsqueda. La segunda fila es el patrón correcto: siempre presente, ningún paso adicional, funciona sin JS.

### Anatomía del header mobile

```
┌────────────────────────────────────────┐  ← top-bar (80px, colapsa al scroll)
│  [Logo GobCan]                [user]   │
├────────────────────────────────────────┤  ← hr separador (1px)
│  [Site title]              [hamburger] │  ← main-bar fila 1 (68px)
│  [🔍  Buscar recursos…     ] [Buscar]  │  ← main-bar fila 2 (48px, NUEVA)
└─────────── borde amarillo ─────────────┘
```

En el estado `.header-scrolled` (top-bar colapsada):
```
│  [Site title]              [hamburger] │  ← sticky
│  [🔍  Buscar recursos…     ] [Buscar]  │  ← sticky, siempre visible
└─────────── borde amarillo ─────────────┘
```

### Especificación del componente `.main-header__search-area--mobile`

**Posición en el DOM:** dentro de `.main-header__main-bar`, después de `<nav>` y antes de `__search-area--compact`.

**Comportamiento CSS:**
- Visible en `< $lg` (< 1024px): `display: block; width: 100%;`
- Oculto en `≥ $lg`: `display: none;`
- En `.header-scrolled`: permanece visible (está dentro de la main-bar sticky, sin cambio).

**Formulario — reutiliza `common/search-form`**, mismo que `__search-area--top`. No requiere partial nuevo.

**Styling del wrapper `.main-header__search-area--mobile`:**

| Propiedad | Valor |
|-----------|-------|
| `display` | `block` (mobile) / `none` (desktop) |
| `width` | `100%` |
| `padding` | `0 0 10px` (espacio inferior antes del borde amarillo) |

**Styling del formulario heredado de `__search-form` (sin cambios en el partial):**

| Propiedad | Valor |
|-----------|-------|
| `form width` en mobile | `100%` (override del `width: 280px` de desktop) |
| `input height` | `40px` (igual que desktop) |
| `border` | `1.5px solid var(--ate-hairline)` |
| `border-radius` | `var(--ate-radius-pill)` |
| `font-size` | `14px` |
| `placeholder` | Mantener el texto actual del partial |
| `button` | Mantener icono lupa existente (Font Awesome `\f002`) |

**Ajuste de `$header-min-height`:**
Con la fila de búsqueda (40px input + 10px padding-bottom = 50px añadidos a la main-bar), el header mobile pasa a:
- top-bar: 80px + hr: 1px + main-bar fila1 (68px) + main-bar fila2 (50px) = **199px**

El Desarrollador debe actualizar `$header-min-height` de `149px` a `199px` en `_layout.scss` y verificar el efecto en `.menu-drawer { top }`.

### Contraste y accesibilidad

- El campo de búsqueda tiene `border: 1.5px solid var(--ate-hairline)` (`#DDE3EC`). Ratio sobre blanco: 1.5:1 — inferior al mínimo WCAG 3:1 para bordes de UI (criterio 1.4.11). **Compensar con un `background: var(--ate-surface-soft)` en el input mobile** para incrementar el contraste visual del campo sin cambiar el color del borde global.
- El placeholder `var(--ate-text-muted-light)` (`#9CA3AF`) sobre `--ate-surface-soft` (`#F5F7FA`): ratio 2.6:1. Los placeholders están exentos del criterio 1.4.3 (no son texto funcional), pero se recomienda `var(--ate-text-muted)` (`#6B7280`) → ratio 4.6:1 para mayor legibilidad.
- El botón de búsqueda es un `<button>` con texto visualmente oculto (font-size: 0) y pseudo-elemento FA — correcto para accesibilidad si el `<button>` tiene `aria-label` (verificar en el partial `common/search-form`).

### Variantes descartadas

- **Lupa toggle → overlay fullscreen**: requiere JS y convierte la búsqueda en funcionalidad oculta. Descartado.
- **Búsqueda dentro del drawer**: no es "siempre visible". Descartado.
- **Compactar site-title y poner búsqueda inline en fila 1**: reduce el site-title a un icono, rompe la identidad institucional en mobile. Descartado.
- **Ocultar top-bar siempre en mobile** para recuperar altura: la top-bar muestra el logo GobCan institucional. Descartado.

### Consecuencias

**Desarrollador — ficheros a modificar:**

| Fichero | Cambio |
|---------|--------|
| `view/common/header.phtml` | Añadir `<div class="main-header__search-area main-header__search-area--mobile">` con el partial de búsqueda, dentro de `.main-header__main-bar`, tras `<nav>` |
| `asset/sass/components/header/_header.scss` | Añadir bloque `&__search-area--mobile` con `display: block` en mobile, `display: none` en `≥ $lg`; override `form { width: 100%; }` e `input { background: var(--ate-surface-soft); }` |
| `asset/sass/abstracts/variables/_layout.scss` | Actualizar `$header-min-height` de `149px` a `199px` |

### Dependencias
- Requiere: QA-015 en análisis + decisión Orquestador [2026-05-11].
- Desbloquea: implementación del Desarrollador.
- Cierra: QA-015.
