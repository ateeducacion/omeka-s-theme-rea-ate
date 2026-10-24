# REA ATE — Tema Omeka S

<a href="https://ateeducacion.github.io/omeka-s-playground/?blueprint=https%3A%2F%2Fraw.githubusercontent.com%2Fateeducacion%2Fomeka-s-theme-rea-ate%2Frefs%2Fheads%2Fmaster%2Fblueprint.json">
  <img src="https://raw.githubusercontent.com/ateeducacion/omeka-s-theme-rea-ate/refs/heads/master/.github/assets/playground-preview-button.svg" alt="Try REA ATE in your browser" width="224">
</a><br>
<small><a href="https://ateeducacion.github.io/omeka-s-playground/?blueprint=https%3A%2F%2Fraw.githubusercontent.com%2Fateeducacion%2Fomeka-s-theme-rea-ate%2Frefs%2Fheads%2Fmaster%2Fblueprint.json">Try in your browser</a></small>

Tema Omeka S para el **Repositorio de Recursos Educativos Abiertos (REA)** del Canal REA ATE,
desarrollado por la **Área de Tecnología Educativa (ATE), Consejería de Educación, Formación Profesional, Actividad Física y Deportes (CEFPAD), Gobierno de Canarias**.

Este tema es una obra derivada de [Freedom S Theme](https://github.com/omeka-s-themes/freedom),
desarrollado por la Corporation for Digital Scholarship y publicado bajo
[GNU General Public License v3.0](LICENSE). Véase la sección [Atribución y licencia](#atribución-y-licencia).

---

## Requisitos

| Componente | Versión |
|------------|---------|
| [Omeka S](https://omeka.org/s/) | 4.2 o posterior dentro de la rama 4.x (`omeka_version_constraint = "^4.2.0"`) |
| [Advanced Search](https://github.com/Daniel-KM/Omeka-S-module-AdvancedSearch) (Daniel-KM) | 3.4.60 (las plantillas `view/search/` del tema parten de esta versión) |
| [Common](https://github.com/Daniel-KM/Omeka-S-module-Common) (Daniel-KM) | Dependencia de Advanced Search |

Módulos opcionales; si no están instalados, el tema prescinde de la parte correspondiente:

| Módulo | Uso en el tema |
|--------|----------------|
| [BlockPlus](https://github.com/Daniel-KM/Omeka-S-module-BlockPlus) | Bloque de página de recurso `block`, que el tema usa para el **Anclaje Curricular** de la ficha |
| [Numeric Data Types](https://github.com/omeka-s-modules/NumericDataTypes) | Plantillas propias para sus campos numéricos (duración, entero, fecha) y su filtro de búsqueda avanzada |
| Breadcrumbs | Migas de pan en la ficha del recurso y en la página de búsqueda, si el helper `breadcrumbs` existe |

---

## Instalación

1. Descarga `rea-ate-X.Y.Z.zip` de la [última versión publicada](https://github.com/ateeducacion/omeka-s-theme-rea-ate/releases/latest).
2. Descomprímelo en la carpeta `themes/` de Omeka S: el ZIP contiene un único directorio `rea-ate/`,
   de modo que el tema queda en `themes/rea-ate/`.
3. Selecciona el tema en el sitio y configúralo, según las
   [instrucciones del manual de usuario de Omeka S para temas](https://omeka.org/s/docs/user-manual/sites/site_theme/#install-themes).

El ZIP de la versión ya incluye el CSS compilado y no contiene las fuentes Sass, la configuración de
Node ni los ficheros de desarrollo. Las versiones se publican automáticamente al crear una etiqueta
`vX.Y.Z` o `X.Y.Z`.

Para desarrollo con Sass es necesario [Node.js](https://nodejs.org/) ≥ 20.19 (la versión que exige
`sass`; la integración continua usa Node 20):

```bash
npm ci
```

---

## Funcionalidades

### Búsqueda avanzada

- Formulario hero con chips multi-selección: nivel educativo, tipo de recurso y audiencia
- Subject picker y collections picker con pills eliminables
- Filtro de duración por rango numérico (`lrmi:timeRequired`)
- Integración con módulo Advanced Search (facetas, paginación, refine)
- **Facetas agrupadas** por `schema:DefinedTermSet`: los valores de las facetas listadas en el ajuste *Facets grouped by linked DefinedTermSet* (p. ej. `lrmi:educationalLevel`) se agrupan por etapa educativa (Primaria, ESO, Bachillerato…). Los grupos se descubren automáticamente a partir de los ítems de clase `schema:DefinedTermSet`, sin definirlos a mano; el orden se controla con `schema:position` en cada DefinedTermSet desde el propio Omeka S
- Panel de filtros activos como chips eliminables

### Resultados de búsqueda

- Tarjetas de recurso con nivel educativo, temática, tipo y duración
- Vista grid y lista conmutables
- Chip de duración en la barra de metadatos compactos de la vista lista
- **Anclaje curricular agrupado**: en los resultados de Advanced Search, cada materia
  (`schema:about`) se pinta como una píldora con los niveles (`lrmi:educationalLevel`) que le
  corresponden, en lugar de dos listas paralelas. La relación materia → nivel se lee del propio
  ítem materia, que debe llevar su nivel; cada nivel enlaza al filtro de búsqueda por ese nivel.
  Sólo se aplica si la propiedad de materia figura entre las propiedades de resultados de la
  configuración de búsqueda; si no, se usa el renderizado normal del módulo. Las propiedades se
  configuran en *Advanced Search Settings*.

### Página de recurso (`item/show`)

- Hero con título, colecciones y badge de tipo de recurso
- JSON-LD `schema:LearningResource` para SEO — duración emitida en formato ISO 8601 (`PT45M`)
- Bloque de anclaje curricular configurable por propiedades desde el panel de tema (requiere
  BlockPlus). Si el bloque incluye las propiedades de materia y nivel del anclaje, se muestra una
  tarjeta por materia con sus niveles y, plegados, los valores alineados que sean ítems enlazados
  (criterios, saberes…); si no, una lista plana de valores
- **Distintivo de licencia** a partir de `dcterms:license`: si el valor es la URI de CC0 1.0,
  CC BY 4.0, CC BY-SA 4.0, CC BY-NC 4.0 o CC BY-NC-SA 4.0, se muestra el distintivo oficial de
  Creative Commons (servido desde `asset/img/cc/`) en la barra lateral y en la lista de valores. Otra
  URI se muestra como valor normal de Omeka; un literal sin URI no aparece en la barra lateral ni en
  el JSON-LD (`license` usa la URI)
- **Bloque «Proyecto cofinanciador»** en la barra lateral derecha, a partir del primer valor de
  `schema:isPartOf` que enlace a un ítem, sea cual sea su tipo de dato (`resource`, `resource:item`,
  vocabulario personalizado…). Del ítem proyecto toma el nombre (`schema:name` o, en su defecto,
  `dcterms:title`), la descripción (`schema:description` o `dcterms:description`), el logotipo
  (`schema:logo` o su media principal) y el enlace «Más información» (`schema:url`). No se muestra si
  el proyecto no tiene nombre ni logotipo
- Acciones de administración inline: editar, añadir media, eliminar con confirmación

### Home

- Rail de audiencias (Profesorado / Alumnado / Familias) con URLs configurables desde el panel de tema

### Identidad visual

- Paleta oficial del Gobierno de Canarias (azul institucional `#0C2C84`, amarillo solar `#FFB300`)
- Sistema de tokens CSS `--ate-*` aplicados desde `layout.phtml`
- Barra de logos institucionales configurable (hasta 8 logos con enlace opcional)

---

## Configuración del tema

Los ajustes aparecen en el panel de tema del sitio con las etiquetas (en inglés o español) que
se indican, agrupados en el mismo orden que en `config/theme.ini`.

### General Settings

| Ajuste | Descripción | Por defecto |
|--------|-------------|-------------|
| Theme's Primary Color | Color primario del tema | `#0C2C84` |
| Theme's Secondary Color | Color secundario (fondos) | `#0C2C84` |
| Theme's accent Color | Color de énfasis (enlaces y acentos) | `#0768AC` |

### Header

| Ajuste | Descripción | Por defecto |
|--------|-------------|-------------|
| Header Menu | Mostrar el menú en la cabecera; si se desactiva, sólo se muestra el logotipo o el nombre | Activado |
| Header Layout | Logotipo y menú en línea (*Inline*) o centrados (*Centered*) | En línea |
| Top Navigation Depth | Niveles máximos en la navegación superior (0 o vacío = todos) | `0` |
| Logo | Logotipo horizontal (máx. 60 px de alto; recomendado: 210×60 px) | — |

### Banner

| Ajuste | Descripción |
|--------|-------------|
| Banner | Imagen de cabecera (recomendado: 1280×560 px) |
| Banner heading / Banner description | Título y descripción sobre el banner |
| Content position | Posición del contenido: izquierda, centro o derecha |
| Banner width | Dentro del contenedor o a sangre hasta el borde de la ventana |
| Banner height / Banner height for mobile devices | Altura máxima con unidad CSS (`200px`, `50vh`…), general y para pantallas pequeñas |
| Banner image vertical / horizontal position within the wrapper | Punto de anclaje de la imagen al recortarla con altura fija |

### Footer

| Ajuste | Descripción | Por defecto |
|--------|-------------|-------------|
| Footer Logo | Versión clara del logotipo (sobre fondo oscuro) | — |
| Footer Site description | HTML descriptivo (ver [Seguridad](#seguridad)) | Vacío |
| Footer Menu | Mostrar el menú en el pie | Desactivado |
| Footer Menu Depth | Profundidad del menú del pie | `1` |
| Footer Content | HTML libre en el pie (ver [Seguridad](#seguridad)) | Vacío |
| Footer Copyright | HTML de copyright (ver [Seguridad](#seguridad)) | «© Gobierno de Canarias» con enlaces a Aviso legal y Política de privacidad |

### Logos Bar

Hasta 8 logos institucionales mostrados sobre el footer: para cada posición, *Logo N* (asset) y
*Logo N — URL de enlace (opcional)*.

### Image Settings

*Add decorative border to*: borde decorativo opcional para Media y/o Assets.

### Resource Tags

*Show tags based on*: tags de recurso basados en Tipo de recurso (*Resource Type*) y/o Clase de
recurso (*Resource Class*).

### Home Audience Links

*Teachers / Students / Families card destination*: URLs de destino para las tarjetas del rail de
audiencias (Profesorado, Alumnado, Familias), que se muestra en la página de inicio del sitio. Por
defecto, `item`. Acepta rutas relativas al sitio (`item`, `item?fulltext_search=ciencias`), rutas
absolutas (`/…`) o URLs `http(s)`.

### Browse Settings

| Ajuste | Descripción |
|--------|-------------|
| Layout for Browse Pages | Grid, List, o conmutable con grid o lista por defecto (*Toggle*) |
| Truncate Body Property | Valor completo, 4 líneas con desvanecido o 4 líneas con puntos suspensivos |

### Bloque: Anclaje Curricular

Ajustes del bloque `block` de BlockPlus en la página de ítem.

| Ajuste | Descripción | Por defecto |
|--------|-------------|-------------|
| CSS class del contenedor | Clase del `<div>` que envuelve el bloque | `anclaje-curricular` |
| Propiedades del bloque de anclaje | Una propiedad por línea; el orden es el de visualización. Se ignoran las líneas vacías y las que empiezan por `#` | `schema:educationalLevel`, `schema:about`, `schema:assesses`, `schema:teaches` |

### Advanced Search Settings

| Ajuste | Descripción | Por defecto |
|--------|-------------|-------------|
| Facets grouped by linked DefinedTermSet | Una faceta por línea a agrupar (ej. `lrmi:educationalLevel`), con el mismo nombre de campo que en la configuración de Advanced Search. Los valores deben ser recursos enlazados (`schema:DefinedTerm`) | Vacío |
| Property linking a term to its group (DefinedTermSet) | Propiedad que enlaza `DefinedTerm` → `DefinedTermSet` | `schema:inDefinedTermSet` |
| Subject property for the curriculum anchor | Propiedad de materia del anclaje curricular; sus valores deben ser ítems enlazados con su propio nivel. Vacío desactiva el anclaje agrupado en los resultados | `schema:about` |
| Educational level property | Propiedad de nivel educativo, en el recurso y en cada ítem materia | `lrmi:educationalLevel` |

### Páginas de recurso

El tema declara las regiones *Left sidebar*, *Main* y *Right sidebar* para ítems, colecciones y
medios. Por defecto, la página de ítem muestra en *Main* los medios incrustados y el bloque `block`,
y en *Right sidebar* los valores, el bloque `block`, las colecciones, los recursos enlazados y la
lista de medios; la página de media muestra los valores y el medio.

---

## Metadatos LRMI soportados

| Propiedad | Uso en el tema |
|-----------|----------------|
| `lrmi:educationalLevel` | Nivel educativo (linked `DefinedTerm`); faceta agrupable, chips del formulario, anclaje curricular y JSON-LD |
| `lrmi:learningResourceType` | Tipo de recurso; badge con icono, chips del formulario y JSON-LD |
| `lrmi:educationalRole` | Audiencia destinataria; chips del formulario y JSON-LD |
| `lrmi:educationalUse` | Uso educativo; JSON-LD |
| `lrmi:timeRequired` | Duración en minutos (entero); filtro por rango, chip en tarjetas y JSON-LD |
| `lrmi:teaches` | Saberes básicos; plegable en resultados de Advanced Search e incluido en JSON-LD |
| `lrmi:assesses` | Competencias evaluadas; incluido en JSON-LD |
| `schema:about` | Temática / materia; eyebrow en tarjetas, selector de materias, anclaje curricular y `keywords` en JSON-LD |
| `schema:isPartOf` | Proyecto cofinanciador (primer valor enlazado a un ítem); bloque en la barra lateral de item/show y `isPartOf` en JSON-LD |
| `dcterms:license` | URI de la licencia; distintivo Creative Commons y `license` en JSON-LD |

---

## Seguridad

### HTML permitido en los ajustes del footer

Los ajustes `footer_site_info`, `footer_content` y `footer_copyright` aceptan HTML, pero el tema
lo reduce a una allowlist de prosa antes de imprimirlo (`helper/HtmlAllowlist.php`):

- **Se conservan:** `a` (con `href` `http`, `https` o `mailto`), `p`, `span`, `div`, `br`, `hr`,
  `strong`, `b`, `em`, `i`, `small`, `ul`, `ol`, `li`, `h2`–`h6`. Se admite `class` en todas
  (con sintaxis de clase CSS válida), `lang` en `a`, `p`, `span` y `div`, y en `a` además `title`,
  `target` (`_blank` o `_self`) y `rel` (`noopener`, `noreferrer`, `nofollow`, `external`, `me`). Un
  enlace con `target="_blank"` recibe siempre `rel="noopener noreferrer"`.
- **Se eliminan por completo:** `script`, `style`, `iframe`, `object`, `embed`, `form`, `input`,
  `button`, `select`, `textarea`, `link`, `meta`, `base`, `svg` y `math`, junto con su contenido.
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

Los helpers están cubiertos por la suite PHPUnit del proyecto
(`test/ReaAteTest/Helper/{SafeUrl,CssToken,HtmlAllowlist}Test.php`), junto con el resto de helpers
del tema. Véase [Pruebas y estilo de código](#pruebas-y-estilo-de-código).

---

## Desarrollo

Ejecuta `make help` para ver todos los objetivos del `Makefile`.

### Entorno local con Docker

```bash
make up      # en primer plano; make upd para segundo plano
make down    # detener y eliminar los contenedores
```

Levanta Omeka S 4.2 (imagen `erseco/alpine-omeka-s:4.2`) y MariaDB en <http://localhost:8080>, con
este repositorio montado como `themes/rea-ate`. En cada arranque se aplica el mismo
[`blueprint.json`](blueprint.json) que usa Omeka S Playground: crea el usuario `admin@example.com`
/ `password`, activa Common y Advanced Search y crea el sitio de demostración `demo` con este tema.
Además se instalan los módulos de desarrollo EasyAdmin, Adminer y Log.

`make clean` y `make fresh` eliminan los volúmenes, es decir, la base de datos y los ficheros
subidos: no los uses sobre una instancia con datos que quieras conservar.

### Compilar CSS

```bash
npm run build
```

Compila `asset/sass/*.scss` en `asset/css/`. Edita siempre las fuentes Sass, no el CSS generado.

### Vigilar cambios (watch)

```bash
npm start
```

### Traducciones

```bash
npm run compile-translations   # .po → .mo con Node (gettext-parser)
make i18n                      # extrae cadenas, actualiza .po, comprueba y compila .mo (requiere gettext)
```

El catálogo del tema es `language/es.po`.

### Pruebas y estilo de código

```bash
composer install
vendor/bin/phpunit -c test/phpunit.xml
vendor/bin/phpcs . --standard=PSR2 --ignore=vendor/,asset/,node_modules/,view/,language/,test/Stubs/ --extensions=php
```

`make test` y `make lint` ejecutan lo mismo, pero dependen de `deps-update`, que ejecuta
`composer update --with-dependencies` si falta `vendor/` o hay dependencias directas
desactualizadas, de modo que pueden modificar `composer.lock`.

### Empaquetar

```bash
make package VERSION=X.Y.Z
```

Genera `rea-ate-X.Y.Z.zip` con un único directorio `rea-ate/`. Cambia temporalmente la versión de
`config/theme.ini` y la restaura al terminar. Compila antes el CSS: el paquete no incluye las fuentes
Sass. La publicación de versiones la hace el flujo `.github/workflows/release.yml` al subir una
etiqueta.

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
│   ├── anclaje-curricular/ # bloque y tarjetas de anclaje curricular
│   ├── facets/             # panel de facetas
│   ├── footer/
│   ├── header/
│   ├── home/               # audience-rail
│   ├── item-show/
│   ├── linked-resources/
│   ├── resources/          # resource-catalog, resource-grid, resource-list, browse-controls
│   ├── search-results/     # resultados Advanced Search list-mode
│   └── …
├── generic/
└── utilities/
```

---

## Atribución y licencia

Este tema es una obra derivada de **[Freedom S Theme](https://github.com/omeka-s-themes/freedom)**,
desarrollado por la [Corporation for Digital Scholarship](https://digitalscholar.org/)
y publicado bajo [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

Las modificaciones realizadas sobre el tema original comprenden, entre otras:
- Sistema de tokens de diseño basado en la identidad corporativa del Gobierno de Canarias
- Integración con el módulo Advanced Search: formulario con chips, facetas agrupadas por DefinedTermSet y resultados con metadatos compactos
- Soporte de metadatos LRMI / Schema.org y emisión de JSON-LD `LearningResource`
- Componentes específicos: rail de audiencias, barra de logos institucionales, chip de duración, bloque de anclaje curricular
- Traducciones al español (es-ES)
- Correcciones de accesibilidad (skip link, atributos ARIA, `hidden` en modales)

Copyright © 2023-present Corporation for Digital Scholarship, Vienna, Virginia, USA.

Copyright © 2024–2026 Área de Tecnología Educativa (ATE), Consejería de Educación, Formación Profesional, Actividad Física y Deportes (CEFPAD), Gobierno de Canarias.

Distribuido bajo [GNU General Public License v3.0](LICENSE).
