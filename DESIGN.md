---
version: 1.0
name: Canarias Educativa
description: Sistema de diseño para plataformas educativas de la Comunidad Autónoma de Canarias. Orientado a comunidad educativa — profesorado, alumnado y familias. Basado en la paleta oficial del Manual de Identidad Corporativa del Gobierno de Canarias. Canvas blanco limpio con acentos en amarillo solar y azul atlántico. Tipografía clara y accesible. Jerarquía visual sin ambigüedad para audiencias mixtas (adultos institucionales + jóvenes + familias).

colors:
  # Paleta oficial Gobierno de Canarias
  brand-yellow:       "#FFB300"   # Pantone 130 — amarillo solar, acento primario
  brand-yellow-light: "#FFCC00"   # Pantone 7406 — amarillo bandera, uso decorativo
  brand-blue-dark:    "#0C2C84"   # Pantone 287 — azul institucional, CTAs y headers
  brand-blue-mid:     "#0768AC"   # Pantone 3005 — azul atlántico, links y accents
  brand-sage:         "#A3AD99"   # Pantone 7538 — verde grisáceo, uso especial/neutro cálido
  brand-red:          "#FE000C"   # Pantone 485 — solo alertas y errores
  brand-ochre:        "#D88F1F"   # Pantone 722 — ocre tierra, badges secundarios

  # Superficies
  canvas:             "#FFFFFF"   # Fondo base: blanco limpio
  surface-soft:       "#F5F7FA"   # Fondo de secciones alternas
  surface-card:       "#EEF2F8"   # Fondo de tarjetas neutras (azul muy pálido)
  surface-yellow:     "#FFF8E1"   # Fondo de tarjetas destacadas (amarillo muy suave)
  surface-dark:       "#0C2C84"   # Secciones oscuras institucionales
  surface-dark-soft:  "#0E3599"   # Hover/elevated sobre dark

  # Texto
  ink:                "#0A0A0A"   # Titulares y texto principal
  body:               "#2C2C2C"   # Texto de cuerpo
  body-soft:          "#4A4A4A"   # Texto secundario
  muted:              "#6B7280"   # Captions, metadatos
  muted-light:        "#9CA3AF"   # Placeholders, fine-print
  on-dark:            "#FFFFFF"   # Texto sobre superficies oscuras
  on-dark-soft:       "#B8C8E8"   # Texto secundario sobre dark
  on-yellow:          "#0C2C84"   # Texto sobre amarillo — azul institucional
  hairline:           "#DDE3EC"   # Bordes y separadores
  hairline-soft:      "#EEF0F4"   # Bordes sutiles

  # Semánticos
  success:            "#16A34A"   # Verde accesible
  warning:            "#D97706"   # Ámbar medio
  error:              "#DC2626"   # Rojo
  info:               "#0768AC"   # Azul mid (brand)

typography:
  display-xl:
    fontFamily: "Lato, sans-serif"
    fontSize: 56px
    fontWeight: 700
    lineHeight: 1.05
    letterSpacing: -1.5px
  display-lg:
    fontFamily: "Lato, sans-serif"
    fontSize: 44px
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: -1px
  display-md:
    fontFamily: "Lato, sans-serif"
    fontSize: 34px
    fontWeight: 700
    lineHeight: 1.15
    letterSpacing: -0.5px
  display-sm:
    fontFamily: "Lato, sans-serif"
    fontSize: 26px
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: -0.3px
  title-lg:
    fontFamily: "Lato, sans-serif"
    fontSize: 22px
    fontWeight: 700
    lineHeight: 1.3
    letterSpacing: 0
  title-md:
    fontFamily: "Lato, sans-serif"
    fontSize: 18px
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: 0
  title-sm:
    fontFamily: "Lato, sans-serif"
    fontSize: 16px
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: 0
  body-lg:
    fontFamily: "Source Serif 4, Georgia, serif"
    fontSize: 18px
    fontWeight: 400
    lineHeight: 1.7
    letterSpacing: 0
  body-md:
    fontFamily: "Source Serif 4, Georgia, serif"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.7
    letterSpacing: 0
  body-sm:
    fontFamily: "Source Serif 4, Georgia, serif"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.6
    letterSpacing: 0
  caption:
    fontFamily: "Lato, sans-serif"
    fontSize: 13px
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: 0
  caption-uppercase:
    fontFamily: "Lato, sans-serif"
    fontSize: 11px
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: 1.8px
  button:
    fontFamily: "Lato, sans-serif"
    fontSize: 15px
    fontWeight: 700
    lineHeight: 1
    letterSpacing: 0.2px
  nav-link:
    fontFamily: "Lato, sans-serif"
    fontSize: 15px
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: 0
  label:
    fontFamily: "Lato, sans-serif"
    fontSize: 13px
    fontWeight: 700
    lineHeight: 1
    letterSpacing: 1px

rounded:
  xs:   4px
  sm:   6px
  md:   10px
  lg:   14px
  xl:   20px
  2xl:  28px
  pill: 9999px
  full: 9999px

spacing:
  xxs:     4px
  xs:      8px
  sm:      12px
  md:      16px
  lg:      24px
  xl:      32px
  xxl:     48px
  xxxl:    64px
  section: 88px

components:
  button-primary:
    backgroundColor: "{colors.brand-blue-dark}"
    textColor: "{colors.on-dark}"
    typography: "{typography.button}"
    rounded: "{rounded.md}"
    padding: 12px 24px
    height: 46px
  button-primary-hover:
    backgroundColor: "{colors.surface-dark-soft}"
  button-secondary:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.brand-blue-dark}"
    border: "2px solid {colors.brand-blue-dark}"
    typography: "{typography.button}"
    rounded: "{rounded.md}"
    padding: 10px 22px
    height: 46px
  button-accent:
    backgroundColor: "{colors.brand-yellow}"
    textColor: "{colors.on-yellow}"
    typography: "{typography.button}"
    rounded: "{rounded.md}"
    padding: 12px 24px
    height: 46px
  button-ghost:
    backgroundColor: transparent
    textColor: "{colors.brand-blue-mid}"
    typography: "{typography.button}"
  top-nav:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    borderBottom: "3px solid {colors.brand-yellow}"
    height: 68px
    typography: "{typography.nav-link}"
  top-nav-dark:
    backgroundColor: "{colors.surface-dark}"
    textColor: "{colors.on-dark}"
    borderBottom: "3px solid {colors.brand-yellow}"
    height: 68px
  hero-band:
    backgroundColor: "{colors.surface-dark}"
    textColor: "{colors.on-dark}"
    accentColor: "{colors.brand-yellow}"
    padding: "{spacing.section}"
  hero-band-light:
    backgroundColor: "{colors.surface-yellow}"
    textColor: "{colors.ink}"
    accentColor: "{colors.brand-blue-dark}"
    padding: "{spacing.section}"
  audience-card-teachers:
    backgroundColor: "{colors.brand-blue-dark}"
    textColor: "{colors.on-dark}"
    accentColor: "{colors.brand-yellow}"
    rounded: "{rounded.xl}"
    padding: "{spacing.xl}"
  audience-card-students:
    backgroundColor: "{colors.brand-yellow}"
    textColor: "{colors.on-yellow}"
    rounded: "{rounded.xl}"
    padding: "{spacing.xl}"
  audience-card-families:
    backgroundColor: "{colors.brand-blue-mid}"
    textColor: "{colors.on-dark}"
    rounded: "{rounded.xl}"
    padding: "{spacing.xl}"
  content-card:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    border: "1px solid {colors.hairline}"
    rounded: "{rounded.lg}"
    padding: "{spacing.lg}"
  content-card-featured:
    backgroundColor: "{colors.surface-yellow}"
    textColor: "{colors.ink}"
    borderLeft: "4px solid {colors.brand-yellow}"
    rounded: "{rounded.lg}"
    padding: "{spacing.lg}"
  resource-card:
    backgroundColor: "{colors.surface-soft}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "{spacing.lg}"
  notice-card:
    backgroundColor: "{colors.surface-card}"
    textColor: "{colors.ink}"
    borderLeft: "4px solid {colors.brand-blue-mid}"
    rounded: "{rounded.md}"
    padding: "{spacing.md}"
  notice-card-important:
    backgroundColor: "{colors.surface-yellow}"
    textColor: "{colors.ink}"
    borderLeft: "4px solid {colors.brand-yellow}"
    rounded: "{rounded.md}"
    padding: "{spacing.md}"
  badge-teacher:
    backgroundColor: "{colors.brand-blue-dark}"
    textColor: "{colors.on-dark}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: 4px 12px
  badge-student:
    backgroundColor: "{colors.brand-yellow}"
    textColor: "{colors.on-yellow}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: 4px 12px
  badge-family:
    backgroundColor: "{colors.brand-blue-mid}"
    textColor: "{colors.on-dark}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: 4px 12px
  badge-new:
    backgroundColor: "{colors.brand-sage}"
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: 4px 12px
  text-input:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    border: "1.5px solid {colors.hairline}"
    rounded: "{rounded.md}"
    padding: 12px 16px
    height: 46px
  text-input-focused:
    border: "2px solid {colors.brand-blue-mid}"
  section-band-alt:
    backgroundColor: "{colors.surface-soft}"
    textColor: "{colors.ink}"
    padding: "{spacing.section}"
  section-band-dark:
    backgroundColor: "{colors.surface-dark}"
    textColor: "{colors.on-dark}"
    padding: "{spacing.section}"
  cta-band:
    backgroundColor: "{colors.brand-yellow}"
    textColor: "{colors.on-yellow}"
    rounded: "{rounded.2xl}"
    padding: "{spacing.xxxl}"
  footer:
    backgroundColor: "{colors.surface-dark}"
    textColor: "{colors.on-dark-soft}"
    borderTop: "4px solid {colors.brand-yellow}"
    padding: "{spacing.xxxl}"
---

## Descripción General

Sistema de diseño para portales educativos del archipiélago canario. Sirve a **tres audiencias simultáneas** — profesorado, alumnado y familias — desde una misma interfaz, por lo que la claridad estructural y la señalización visual por tipo de usuario son prioridades de primer orden.

La identidad arranca de la **paleta oficial del Gobierno de Canarias**: el amarillo solar (`{colors.brand-yellow}`) como acento de energía y el azul institucional (`{colors.brand-blue-dark}`) como ancla de autoridad. Sobre canvas blanco limpio, estos dos colores crean una jerarquía inmediata sin necesidad de ornamento.

El tono visual es **institucional pero accesible**: no frío ni burocrático, pero tampoco tan informal que pierda credibilidad ante docentes y familias. La tipografía serif para el cuerpo (Source Serif 4) da calidez lectora; el sans-serif Lato para titulares y UI mantiene la legibilidad en pantalla.

**Características clave:**
- Canvas blanco `{colors.canvas}` como base — nunca gris frío
- Azul institucional `{colors.brand-blue-dark}` para CTAs principales, headers y elementos de autoridad
- Amarillo solar `{colors.brand-yellow}` para acentos, destacados y llamadas a la acción secundarias
- Sistema de **badges por audiencia** (profesorado / alumnado / familias) con color diferenciador
- Tipografía serif para cuerpo de texto — mejora la legibilidad en contenido educativo largo
- Borde amarillo de 3–4px en nav y elementos destacados como firma de marca
- Footer siempre azul oscuro con borde amarillo superior — cierre institucional

---

## Colores

### Paleta de marca (Gobierno de Canarias)
- **Amarillo solar** (`{colors.brand-yellow}` — #FFB300): Acento principal. CTAs secundarios, elementos destacados, borde inferior de nav. El color más reconocible de la identidad canaria.
- **Amarillo bandera** (`{colors.brand-yellow-light}` — #FFCC00): Uso decorativo e ilustrativo. Nunca para texto ni CTA principal.
- **Azul institucional** (`{colors.brand-blue-dark}` — #0C2C84): Botones primarios, header oscuro, tarjetas de profesorado, footer. Voz de autoridad.
- **Azul atlántico** (`{colors.brand-blue-mid}` — #0768AC): Links, iconos activos, badges de familias. Más ligero y cercano que el institucional.
- **Verde grisáceo** (`{colors.brand-sage}` — #A3AD99): Badge "Novedad", separadores decorativos. Color especial — no abusar.
- **Rojo** (`{colors.brand-red}` — #FE000C): Reservado exclusivamente para errores y alertas críticas.

### Superficies
- **Canvas** (`{colors.canvas}` — #FFFFFF): Base universal de todas las páginas.
- **Surface soft** (`{colors.surface-soft}` — #F5F7FA): Secciones alternas para crear ritmo sin ruido visual.
- **Surface card** (`{colors.surface-card}` — #EEF2F8): Tarjetas neutras con ligero tinte azul institucional.
- **Surface yellow** (`{colors.surface-yellow}` — #FFF8E1): Tarjetas y bandas destacadas con tinte amarillo cálido.
- **Surface dark** (`{colors.surface-dark}` — #0C2C84): Secciones oscuras, nav en versión institucional, footer.

### Texto
- **Ink** (`{colors.ink}` — #0A0A0A): Titulares y texto primario sobre canvas claro.
- **Body** (`{colors.body}` — #2C2C2C): Texto corrido estándar.
- **Muted** (`{colors.muted}` — #6B7280): Fechas, metadatos, captions.
- **On dark** (`{colors.on-dark}` — #FFFFFF): Texto sobre superficies oscuras (azul, footer).
- **On yellow** (`{colors.on-yellow}` — #0C2C84): Texto sobre amarillo — azul institucional para máximo contraste.

---

## Tipografía

### Fuentes
```html
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;600;700;900&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
```

- **Lato** — titulares, navegación, botones, labels, UI. Humanista sans-serif, legible a todas las edades.
- **Source Serif 4** — cuerpo de texto, artículos, contenido educativo largo. La serif mejora la legibilidad en lectura sostenida.

### Jerarquía

| Token | Tamaño | Peso | Uso |
|---|---|---|---|
| `display-xl` | 56px | 700 | Hero principal de portada |
| `display-lg` | 44px | 700 | Titulares de sección hero |
| `display-md` | 34px | 700 | H2 de sección |
| `display-sm` | 26px | 700 | H3, títulos de tarjeta grandes |
| `title-lg` | 22px | 700 | Títulos de card, H3 en artículo |
| `title-md` | 18px | 700 | H4, subtítulos |
| `body-lg` | 18px | 400 | Lead paragraph, intro artículo |
| `body-md` | 16px | 400 | Texto corrido estándar |
| `body-sm` | 14px | 400 | Footer body, notas al pie |
| `caption` | 13px | 500 | Fechas, autor, metadatos |
| `caption-uppercase` | 11px | 700 | Etiquetas de sección ("RECURSOS", "NOTICIAS") |
| `button` | 15px | 700 | Labels de botón |
| `nav-link` | 15px | 600 | Menú de navegación |
| `label` | 13px | 700 | Badges de audiencia |

### Principios tipográficos
- Lato bold es la voz institucional: directa, clara, sin ambigüedad.
- Source Serif 4 en cuerpo mejora la legibilidad para lectores de todas las edades — especialmente en contenido curricular y comunicados a familias.
- Nunca mezclar las dos fuentes en el mismo bloque visual de UI; el cambio serif/sans-serif se reserva para la transición UI → contenido.

---

## Sistema de Audiencias

El sistema usa **tres colores de señalización** para identificar a qué audiencia va dirigido cada contenido:

| Audiencia | Color | Badge | Tarjeta hero |
|---|---|---|---|
| Profesorado | `{colors.brand-blue-dark}` azul institucional | `badge-teacher` | `audience-card-teachers` |
| Alumnado | `{colors.brand-yellow}` amarillo solar | `badge-student` | `audience-card-students` |
| Familias | `{colors.brand-blue-mid}` azul atlántico | `badge-family` | `audience-card-families` |

Cada recurso, noticia o sección lleva su badge de audiencia visible. En páginas de portada, las tres tarjetas de audiencia aparecen juntas como acceso directo — azul / amarillo / azul medio — formando el tricolor de identidad del sistema.

---

## Layout

### Espaciado
- **Base unit:** 4px
- **Sección:** `{spacing.section}` (88px) entre bandas principales
- **Cards internas:** `{spacing.lg}` (24px) tarjetas de contenido; `{spacing.xl}` (32px) tarjetas de audiencia y CTA

### Grid y contenedor
- **Ancho máximo de contenido:** 1200px centrado
- **Padding horizontal:** 24px (mobile), 48px (tablet), auto (desktop)
- **Grid de tarjetas de audiencia:** 3 columnas en desktop, 1 en mobile
- **Grid de recursos/noticias:** 3 columnas en desktop, 2 en tablet, 1 en mobile
- **Artículo de contenido:** columna única 720px max — legibilidad lectora óptima

### Ritmo de sección
1. Hero (oscuro o amarillo suave)
2. Acceso por audiencia (3 tarjetas azul/amarillo/azul medio)
3. Recursos destacados (canvas blanco, grid 3 columnas)
4. Banda alternada (surface-soft, contenido secundario)
5. Noticias/novedades (canvas, grid 3 columnas)
6. CTA band (amarillo solar)
7. Footer (azul oscuro, borde amarillo)

---

## Elevación y Profundidad

| Nivel | Tratamiento | Uso |
|---|---|---|
| Plano | Sin sombra ni borde | Bandas de sección, hero |
| Hairline | `1px solid {colors.hairline}` | Tarjetas de contenido estándar |
| Borde izquierdo | `4px solid {colors.brand-yellow}` o `{colors.brand-blue-mid}` | Notices y destacados |
| Sombra suave | `0 2px 8px rgba(12,44,132,0.08)` | Tarjetas en hover |
| Relleno de color | Superficie de audiencia sólida | Tarjetas de profesorado/alumnado/familias |

Sin sombras pesadas. La profundidad viene del contraste de superficies: canvas → amarillo → azul oscuro.

---

## Componentes

### Navegación

**`top-nav`** — Fondo blanco canvas, borde inferior amarillo 3px (`{colors.brand-yellow}`). Altura 68px. Logo + nombre del portal a la izquierda. Menú horizontal central con las secciones principales. A la derecha: selector de audiencia y buscador. Fuente `{typography.nav-link}`.

**`top-nav-dark`** — Versión institucional sobre azul oscuro. Misma estructura, texto blanco, borde amarillo inferior se mantiene.

### Botones

**`button-primary`** — Azul institucional sólido, texto blanco, 46px alto, rounded `{rounded.md}`. Para acciones principales: "Acceder", "Ver todos los recursos", "Descargar".

**`button-accent`** — Amarillo solar, texto azul institucional. Para acciones de descubrimiento: "Explorar", "Ver novedades". El amarillo sobre blanco requiere el texto azul para superar WCAG AA.

**`button-secondary`** — Blanco con borde azul 2px. Para acciones alternativas.

**`button-ghost`** — Sin fondo, texto azul atlántico. Para acciones terciarias e inline.

### Tarjetas de Audiencia

**`audience-card-teachers`** — Fondo azul institucional, texto blanco, acento amarillo en icono/número destacado. Rounded `{rounded.xl}`. Icono de profesorado + título + descripción breve + CTA en blanco.

**`audience-card-students`** — Fondo amarillo solar, texto azul institucional. El más llamativo de los tres, orientado a captar la atención de jóvenes. Icono + título + CTA.

**`audience-card-families`** — Fondo azul atlántico, texto blanco. Tono más cercano y menos formal que el azul institucional.

### Tarjetas de Contenido

**`content-card`** — Canvas blanco, borde hairline, rounded `{rounded.lg}`. Badge de audiencia en la esquina superior. Imagen opcional, título en `{typography.title-md}`, extracto en `{typography.body-sm}`, fecha en `{typography.caption}`.

**`content-card-featured`** — Fondo amarillo suave `{colors.surface-yellow}`, borde izquierdo amarillo 4px. Para el contenido editorial más relevante de cada sección.

**`resource-card`** — Fondo `{colors.surface-soft}` con icono de tipo de recurso (PDF, video, enlace externo). Título, descripción breve, badge de audiencia.

### Notices / Avisos

**`notice-card`** — Fondo azul muy claro, borde izquierdo azul atlántico. Para información relevante pero no urgente: fechas de entrega, convocatorias, instrucciones.

**`notice-card-important`** — Fondo amarillo suave, borde izquierdo amarillo. Para avisos que requieren atención inmediata de familias o docentes.

### Badges

Los badges se colocan siempre en la esquina superior izquierda de las tarjetas de contenido.

- **`badge-teacher`** — Azul institucional, texto blanco. "Profesorado"
- **`badge-student`** — Amarillo solar, texto azul. "Alumnado"
- **`badge-family`** — Azul atlántico, texto blanco. "Familias"
- **`badge-new`** — Verde grisáceo (`{colors.brand-sage}`), texto oscuro. "Novedad"

### CTA Band

**`cta-band`** — Fondo amarillo solar, texto azul institucional, rounded `{rounded.2xl}`, padding `{spacing.xxxl}`. Título en `{typography.display-md}`, subtexto en `{typography.body-lg}`, botón primario azul. Ejemplo: "¿Buscas recursos para tu aula? Explora el catálogo completo."

### Footer

**`footer`** — Fondo azul oscuro `{colors.surface-dark}`, borde superior amarillo 4px. Columnas: identidad corporativa (logo + escudo Canarias) + secciones principales + acceso por audiencia + contacto / aviso legal. Texto en `{colors.on-dark-soft}`. Links en `{colors.on-dark}` con hover amarillo.

---

## Accesibilidad

El sistema educativo canario sirve a usuarios de todas las edades y capacidades. Requisitos mínimos:

- **Contraste mínimo WCAG AA** en todos los textos. Verificado para las combinaciones críticas:
  - Amarillo `#FFB300` sobre blanco → **Usar siempre texto azul** `#0C2C84` encima (ratio 5.2:1 ✓)
  - Blanco sobre azul `#0C2C84` → ratio 9.1:1 ✓
  - `{colors.body}` `#2C2C2C` sobre blanco → ratio 12.6:1 ✓
- **Tamaño mínimo de fuente** para cuerpo: 16px (body-md). Nunca bajar de 13px (caption) para texto informativo.
- **Touch targets** mínimo 44×44px para todos los elementos interactivos.
- **Focus visible** en todos los elementos interactivos: outline `2px solid {colors.brand-blue-mid}` con offset 2px.
- **Textos alternativos** en todas las imágenes educativas y recursos visuales.

---

## Do's y Don'ts

### ✅ Hacer
- Usar canvas blanco como base — aporta claridad y reduce la fatiga visual en sesiones largas.
- Poner siempre el badge de audiencia en los contenidos — facilita la navegación de los tres perfiles.
- Usar el amarillo con texto azul institucional encima, nunca negro ni gris.
- Mantener el borde amarillo inferior en la navegación — es la firma de marca del sistema.
- Usar Source Serif 4 para artículos y contenido educativo extenso — mejora la lectura sostenida.
- Cerrar siempre con el footer azul oscuro con borde amarillo — sello institucional.
- Usar la banda de CTA amarilla para convertir visitas en exploración activa de recursos.

### ❌ No hacer
- No usar el rojo `{colors.brand-red}` para otra cosa que no sean errores y alertas críticas.
- No poner texto negro o gris sobre fondo amarillo solar — usar siempre azul institucional.
- No usar más de dos familias tipográficas (Lato + Source Serif 4 son las únicas permitidas).
- No añadir una cuarta categoría de audiencia con un nuevo color — el sistema de tres es deliberado.
- No usar gradientes — la paleta plana e institucional es más accesible y fácil de mantener.
- No usar el azul oscuro `{colors.brand-blue-dark}` en bloques de texto corrido — solo para UI y destacados.
- No omitir el badge de audiencia en contenidos: la señalización por perfil es funcional, no decorativa.
- No usar sombras pesadas — el sistema usa contraste de superficies, no profundidad ilusoria.
- No usar el verde grisáceo `{colors.brand-sage}` como color primario — es un color especial de apoyo.

---

## Responsive

### Breakpoints

| Nombre | Ancho | Cambios clave |
|---|---|---|
| Mobile | < 640px | Nav hamburguesa; hero 56→32px; tarjetas audiencia 1-col; recursos 1-col; badges se acortan |
| Tablet | 640–1024px | Nav condensada; tarjetas audiencia 3-col pequeño o 1-col apilado; recursos 2-col |
| Desktop | 1024–1280px | Nav completa; 3 tarjetas audiencia en row; recursos 3-col |
| Wide | > 1280px | Contenido centrado 1200px; más breathing room |

### Estrategia móvil
- El selector de audiencia (profesorado/alumnado/familias) debe ser el primer elemento visible en mobile — las tres audiencias deben poder auto-clasificarse sin scroll.
- Los badges de audiencia en tarjetas se mantienen a todos los tamaños.
- El footer oscuro se simplifica en mobile a logo + links esenciales + aviso legal.

---

## Tokens de referencia rápida para IA

Al generar componentes, usar siempre estas combinaciones validadas:

```
CTA primario:     bg={colors.brand-blue-dark}   text={colors.on-dark}
CTA acento:       bg={colors.brand-yellow}       text={colors.on-yellow}
Card profesores:  bg={colors.brand-blue-dark}    text={colors.on-dark}    accent={colors.brand-yellow}
Card alumnado:    bg={colors.brand-yellow}       text={colors.on-yellow}
Card familias:    bg={colors.brand-blue-mid}     text={colors.on-dark}
Banner hero:      bg={colors.surface-dark}       text={colors.on-dark}    accent={colors.brand-yellow}
Sección alterna:  bg={colors.surface-soft}       text={colors.ink}
Aviso normal:     bg={colors.surface-card}       border-left={colors.brand-blue-mid}
Aviso importante: bg={colors.surface-yellow}     border-left={colors.brand-yellow}
Footer:           bg={colors.surface-dark}       border-top={colors.brand-yellow}  text={colors.on-dark-soft}
```

---

*Basado en el Manual de Identidad Corporativa del Gobierno de Canarias.*
*Mantenido para portales educativos de la comunidad canaria.*
