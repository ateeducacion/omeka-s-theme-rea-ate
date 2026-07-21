# REA ATE — Tema Omeka S

<a href="https://ateeducacion.github.io/omeka-s-playground/?blueprint=https%3A%2F%2Fraw.githubusercontent.com%2Fateeducacion%2Fomeka-s-theme-rea-ate%2Frefs%2Fheads%2Fmaster%2Fblueprint.json">
  <img src="https://raw.githubusercontent.com/ateeducacion/omeka-s-theme-rea-ate/refs/heads/master/.github/assets/playground-preview-button.svg" alt="Try REA ATE in your browser" width="224">
</a><br>
<small><a href="https://ateeducacion.github.io/omeka-s-playground/?blueprint=https%3A%2F%2Fraw.githubusercontent.com%2Fateeducacion%2Fomeka-s-theme-rea-ate%2Frefs%2Fheads%2Fmaster%2Fblueprint.json">Try in your browser</a></small>

Tema Omeka S para el **Repositorio de Recursos Educativos Abiertos (REA)** del Canal REA ATE,
desarrollado por la **Área de Tecnología Educativa (ATE), Consejería de Educación, Formación Profesional, Actividad Física y Deportes (CEFPAD), Gobierno de Canarias**.

Este tema es una obra derivada de [Freedom S Theme](https://github.com/omeka-s-themes/freedom),
desarrollado por el Roy Rosenzweig Center for History and New Media (RRCHNM) y publicado bajo
[GNU General Public License v3.0](LICENSE). Véase la sección [Atribución y licencia](#atribución-y-licencia).

---

## Requisitos

| Componente | Versión mínima |
|------------|---------------|
| [Omeka S](https://omeka.org/s/) | 4.2 |
| [Advanced Search](https://github.com/Daniel-KM/Omeka-S-module-AdvancedSearch) (Daniel-KM) | 3.4.60 |

---

## Instalación

Para instalación básica, sigue las [instrucciones del manual de usuario de Omeka S para temas](https://omeka.org/s/docs/user-manual/sites/site_theme/#installing-themes).

Para desarrollo con Sass es necesario [Node.js](https://nodejs.org/) ≥ 16:

```bash
npm install
```

---

## Funcionalidades

### Búsqueda avanzada

- Formulario hero con chips multi-selección: nivel educativo, tipo de recurso y audiencia
- Subject picker y collections picker con pills eliminables
- Filtro de duración por rango numérico (`lrmi:timeRequired`)
- Integración con módulo Advanced Search (facetas, paginación, refine)
- **Facetas agrupadas automáticamente** por `schema:DefinedTermSet`: los valores de `lrmi:educationalLevel` se agrupan por etapa educativa (Primaria, ESO, Bachillerato…) sin configuración manual; el orden se controla con `schema:position` en cada DefinedTermSet desde el propio Omeka S
- Panel de filtros activos como chips eliminables

### Resultados de búsqueda

- Tarjetas de recurso con nivel educativo, temática, tipo y duración
- Vista grid y lista conmutables
- Chip de duración en la barra de metadatos compactos de la vista lista

### Página de recurso (`item/show`)

- Hero con título, colecciones y badge de tipo de recurso
- JSON-LD `schema:LearningResource` para SEO — duración emitida en formato ISO 8601 (`PT45M`)
- Bloque de anclaje curricular configurable por propiedades desde el panel de administración
- Acciones de administración inline: editar, añadir media, eliminar con confirmación

### Home

- Rail de audiencias (Profesorado / Alumnado / Familias) con URLs configurables desde el panel de tema

### Identidad visual

- Paleta oficial del Gobierno de Canarias (azul institucional `#0C2C84`, amarillo solar `#FFB300`)
- Sistema de tokens CSS `--ate-*` aplicados desde `layout.phtml`
- Barra de logos institucionales configurable (hasta 8 logos con enlace opcional)

---

## Configuración del tema

### General

| Ajuste | Descripción |
|--------|-------------|
| Primary Color | Color primario del tema |
| Secondary Color | Color secundario (fondos) |
| Accent Color | Color de énfasis (enlaces y acentos) |

### Header

| Ajuste | Descripción |
|--------|-------------|
| Header Menu | Mostrar / ocultar el menú en la cabecera |
| Header Layout | Logotipo e menú en línea o centrados |
| Top Navigation Depth | Niveles máximos en la navegación (0 = todos) |
| Logo | Logotipo horizontal (recomendado: 210×60 px) |

### Banner

Imagen de cabecera con opciones de título, descripción, posición del contenido, ancho y alto.

### Footer

| Ajuste | Descripción |
|--------|-------------|
| Footer Logo | Versión clara del logotipo (sobre fondo oscuro) |
| Footer Site description | Texto descriptivo |
| Footer Menu / Footer Menu Depth | Menú en el pie y profundidad |
| Footer Content | HTML libre en el pie |
| Footer Copyright | HTML de copyright |

### Logos Bar

Hasta 8 logos institucionales (asset + URL de enlace opcional) mostrados sobre el footer.

### Home Audience Links

URLs de destino para las tarjetas del rail de audiencias (Profesorado, Alumnado, Familias).
Acepta rutas relativas (`item`, `item?fulltext_search=ciencias`) o URLs absolutas.

### Browse Settings

| Ajuste | Descripción |
|--------|-------------|
| Layout for Browse Pages | Grid, Lista o Toggle (por defecto grid o lista) |
| Truncate Body Property | Truncar el cuerpo del recurso en el catálogo |

### Bloque: Anclaje Curricular

| Ajuste | Descripción |
|--------|-------------|
| CSS class del contenedor | Clase del `<div>` wrapper del bloque |
| Propiedades del bloque | Una propiedad por línea; el orden es el de visualización |

### Advanced Search Settings

| Ajuste | Descripción |
|--------|-------------|
| Facets grouped by linked DefinedTermSet | Una faceta por línea a agrupar (ej. `lrmi:educationalLevel`) |
| Property linking a term to its group | Propiedad que enlaza `DefinedTerm` → `DefinedTermSet` (por defecto `schema:inDefinedTermSet`) |

### Image Settings

Borde decorativo opcional para Media y/o Assets.

### Resource Tags

Tags de recurso basados en Tipo de recurso o Clase de recurso.

---

## Metadatos LRMI soportados

| Propiedad | Uso en el tema |
|-----------|----------------|
| `lrmi:educationalLevel` | Nivel educativo (linked `DefinedTerm`); faceta agrupable |
| `lrmi:learningResourceType` | Tipo de recurso; badge con icono |
| `lrmi:timeRequired` | Duración en minutos (entero); chip en tarjetas y JSON-LD |
| `lrmi:teaches` | Saberes básicos; mostrado en resultados avanzados |
| `lrmi:assesses` | Competencias evaluadas; incluido en JSON-LD |
| `schema:about` | Temática / materia; eyebrow en tarjetas |
| `schema:isPartOf` | Proyecto o colección padre; sidebar en item/show |

---

## Seguridad

### HTML permitido en los ajustes del footer

Los ajustes `footer_site_info`, `footer_content` y `footer_copyright` aceptan HTML, pero el tema
lo reduce a una allowlist de prosa antes de imprimirlo (`helper/HtmlAllowlist.php`):

- **Se conservan:** `a` (con `href` `http`, `https` o `mailto`), `p`, `span`, `div`, `br`, `hr`,
  `strong`, `b`, `em`, `i`, `small`, `ul`, `ol`, `li`, `h2`–`h6`, y los atributos `title`, `class`
  y `lang`.
- **Se eliminan por completo:** `script`, `style`, `iframe`, `object`, `embed`, `form`, `svg` y
  elementos de formulario, junto con su contenido.
- **Se descartan siempre:** los atributos `on*` (`onclick`, `onerror`, …), `style`, y cualquier
  `href` cuyo esquema no esté permitido (`javascript:`, `data:`, …).
- Las etiquetas no reconocidas se *desenvuelven*: se pierde la etiqueta, pero no el texto.

Si necesita incrustar un `iframe` o un script en el footer, hágalo desde un bloque de página o
desde un módulo, no desde estos ajustes.

Estos campos sólo deberían ser editados por `global_admin` o por editores del sitio: aunque el
purificador impide la ejecución de scripts, siguen permitiendo introducir enlaces y texto en todas
las páginas públicas del sitio.

### Validación de ajustes que llegan a CSS o a URLs

Los ajustes que se interpolan en CSS (colores, alturas del banner) o en atributos `href`/`src`
(enlaces de la barra de logos, tarjetas de audiencia) se validan contra su gramática en
`helper/CssToken.php` y `helper/SafeUrl.php`, con reserva a un valor por defecto seguro. Escapar no
basta en esos contextos: `escapeHtmlAttr()` no impide un `href="javascript:…"` ni que un valor CSS
cierre el bloque `<style>`.

### Pruebas de los helpers de seguridad

```bash
php .project/tests/security-helpers-test.php
```

---

## Desarrollo

### Compilar CSS

```bash
npm run build
```

### Vigilar cambios (watch)

```bash
npm start
```

### Compilar traducciones (`.po` → `.mo`)

```bash
npm run compile-translations
```

### Estructura Sass

```
asset/sass/
├── abstracts/
│   ├── mixins/
│   └── variables/          # tokens: breakpoints, colors, layout, typography
├── base/
│   ├── elements/           # buttons, fields, links, lists, media…
│   ├── layout/
│   └── typography/
├── components/
│   ├── advanced-search/    # formulario de búsqueda avanzada
│   ├── facets/             # panel de facetas
│   ├── footer/
│   ├── header/
│   ├── home/               # audience-rail
│   ├── item-show/
│   ├── linked-resources/
│   ├── resources/          # resource-card, resource-row, resource-catalog
│   ├── search-results/     # resultados Advanced Search list-mode
│   └── …
├── generic/
└── utilities/
```

---

## Atribución y licencia

Este tema es una obra derivada de **[Freedom S Theme](https://github.com/omeka-s-themes/freedom)**,
desarrollado por el [Roy Rosenzweig Center for History and New Media (RRCHNM)](https://rrchnm.org/)
y publicado bajo [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

Las modificaciones realizadas sobre el tema original comprenden, entre otras:
- Sistema de tokens de diseño basado en la identidad corporativa del Gobierno de Canarias
- Integración con el módulo Advanced Search: formulario con chips, facetas agrupadas por DefinedTermSet y resultados con metadatos compactos
- Soporte de metadatos LRMI / Schema.org y emisión de JSON-LD `LearningResource`
- Componentes específicos: rail de audiencias, barra de logos institucionales, chip de duración, bloque de anclaje curricular
- Traducciones al español (es-ES)
- Correcciones de accesibilidad (skip link, atributos ARIA, `hidden` en modales)

Copyright © 2024–2026 Área de Tecnología Educativa (ATE), Consejería de Educación, Formación Profesional, Actividad Física y Deportes (CEFPAD), Gobierno de Canarias.

Distribuido bajo [GNU General Public License v3.0](LICENSE).
