# Tema Omeka S «REA ATE» — resumen técnico

> Visión de conjunto del tema: para qué sirve, qué sobrescribe, cómo funcionan sus piezas
> y qué deuda arrastra. Punto de entrada para quien llega al proyecto, y contexto para
> asistentes de IA.
>
> Redactado el **2026-09-02** contra la rama `feature/anclaje-curricular-search`
> (22 commits sobre `master`) y verificado contra la instancia Docker de desarrollo
> (`http://localhost:8080`, sitio `ceiplajares`, volcado realista de 12.609 ítems).
> Donde algo depende de configuración de instancia y no del tema, se indica expresamente.
>
> **Al actualizarlo**, revisar sobre todo la §2 (estado de las ramas) y la §9 (deuda
> técnica), que son las que caducan antes.

---

## 1. Propósito

Tema para el **Repositorio de Recursos Educativos Abiertos (REA)** del Canal REA ATE,
desarrollado por el **Área de Tecnología Educativa (ATE)** de la Consejería de Educación,
Formación Profesional, Actividad Física y Deportes del **Gobierno de Canarias**.

| | |
|---|---|
| Nombre / versión | `REA ATE`, v0.11.0 (`config/theme.ini`) |
| Base | Obra derivada de [Freedom S Theme](https://github.com/omeka-s-themes/freedom) (RRCHNM) |
| Licencia del tema | GPL-3.0-or-later (heredada de Freedom) |
| Omeka S | `^4.2.0` |
| Dependencia dura | Módulo **AdvancedSearch** 3.4.60 (Daniel-KM) + su dependencia `Common` |
| Módulos adicionales en uso | BlockPlus, AdvancedResourceTemplate, D3Graph, Mapping (opcional), Selection / ContactUs (opcionales) |
| i18n | `language/es.po`, `es.mo`, `template.pot`; `has_translations = true` |

El tema no es genérico: asume un **modelo de metadatos LRMI/Schema.org** concreto
(`.project/context/metadata_model.md`) y una jerarquía curricular LOMLOE modelada como
ítems enlazados. Accede a las propiedades **siempre por nombre cualificado**
(`prefix:term`), nunca por ID interno de la instancia.

### Modelo de datos curricular (clave para entenderlo todo)

```
Recurso (lrmi:LearningResource)
  ├── schema:about            → ítem «Asignatura»
  ├── lrmi:educationalLevel   → ítem «Curso»
  ├── lrmi:teaches            → ítems «Saberes básicos»
  ├── lrmi:assesses           → ítems «Criterios de evaluación»
  └── schema:isPartOf         → ítem «Proyecto» (financiación)

Ítem «Asignatura» (schema:DefinedTerm)
  ├── dcterms:type          = "Asignatura"
  ├── dcterms:identifier    = "asig:1SMAT", "MAT"   ← código estable
  ├── lrmi:educationalLevel → ítem «Curso»          ← relación materia→nivel
  └── dcterms:isPartOf      → ítem «Etapa»

Ítem «Curso» (schema:DefinedTerm)
  ├── dcterms:type            = "Curso"
  └── schema:inDefinedTermSet → ítem «Etapa» (schema:DefinedTermSet)

Ítem «Etapa» (schema:DefinedTermSet)
  └── schema:position         = 1 Infantil · 2 Primaria · 3 ESO · 4 Bachillerato
```

En el volcado de desarrollo: **19 recursos** de clase `lrmi:LearningResource` frente a
**12.542 ítems** de clase `schema:DefinedTerm` (el vocabulario curricular).

---

## 2. Estado del repositorio

| Rama | Estado |
|---|---|
| `master` | Rama de integración. **La copia local está 37 commits por detrás de `origin/master`.** |
| `security/audit-fixes` | Auditoría OWASP ASVS 4.0 L2 / ENS. Fases 0-1 cerradas y verificadas (12/12 pruebas QA). **Sin fusionar.** Aporta los helpers `SafeUrl`, `CssToken`, `HtmlAllowlist`, la suite PHPUnit (`test/`), `composer.json` con `require-dev`, `docker-compose.yml` y `blueprint.json`. |
| `feature/anclaje-curricular-search` | Anclaje curricular agrupado en la búsqueda facetada. 22 commits. Revisión final limpia. **Sin fusionar.** |

**Consecuencia práctica:** varios ficheros (`docker-compose.yml`, `composer.lock`,
`blueprint.json`, todo `test/`) **solo existen en `security/audit-fixes`**. Los tests
unitarios no pueden ejecutarse desde otra rama sin ese andamiaje.

---

## 3. Plantillas que sobrescribe

47 plantillas. Agrupadas por origen:

### Layout y núcleo del sitio
| Fichero | Qué hace |
|---|---|
| `view/layout/layout.phtml` | Layout global. Inyecta tokens CSS de los colores configurables, `skip link`, `role="main"`, Inter y Material Symbols desde Google Fonts. |
| `view/omeka/site/item/show.phtml` | Ficha de recurso (§5). |
| `view/omeka/site/item/browse.phtml` | Listado de ítems en rejilla/lista con filtros. |
| `view/omeka/site/item/search.phtml` | Formulario de búsqueda avanzada propio del tema. |
| `view/omeka/site/item-set/browse.phtml` | Galería de colecciones con filtros por nivel/materia. |
| `view/omeka/site/item-set/_browse-filter-script.phtml` | JS de esos filtros (**script inline**). |
| `view/omeka/site/index/search.phtml` | Resultados de la búsqueda nativa de Omeka. |
| `view/omeka/site/media/browse.phtml`, `media/show.phtml` | Vistas de media. |
| `view/omeka/site/page/browse.phtml` | Listado de páginas. |

### Parciales comunes
`header.phtml` · `footer.phtml` · `banner.phtml` · `logos-bar.phtml` · `menu-drawer.phtml`
(*inline*) · `user-bar.phtml` (*inline*) · `pagination.phtml` · `resource-values.phtml` ·
`resource-type-badge.phtml` · `linked-resources.phtml` · `project-funding.phtml` ·
`home-audience-rail.phtml` · `curriculum-anchor.phtml` · `numeric-integer.phtml` ·
`numeric-duration.phtml` · `numeric-timestamp.phtml` · `numeric-data-types-advanced-search.phtml`

### Bloques de página (BlockPlus / Omeka)
`block-layout/`: `asset` · `browse-preview` · `collecting-block-one` · `file` ·
`item-showcase` · `item-with-metadata` · `mapping-block-popup-content` · `page-title`

### Bloques de página de recurso
`resource-page-block-layout/`: `block` (**el anclaje curricular de la ficha**) ·
`linked-resources` · `media-embeds` · `media-list` · `media-render`

### Búsqueda avanzada (formularios)
`common/advanced-search/`: `has-media` · `ids` · `item-sets` (*inline*) · `properties` ·
`resource-class` · `sort`

### Módulo AdvancedSearch
| Fichero | Riesgo |
|---|---|
| `view/search/search.phtml` | Página de búsqueda facetada completa. |
| `view/search/facets-list.phtml` | Facetas agrupadas por `DefinedTermSet`. |
| `view/search/results.phtml` | **Copia literal de AdvancedSearch 3.4.60** con 4 hunks aditivos. Al actualizar el módulo hay que rebasar contra 3.4.60. |

### View helpers propios (`helper/`, namespace `OmekaTheme\Helper`)
`ResourceTags` · `ShadeColor` · `ContrastColor` · `CanEditInCurrentSite` · `SlugifyValues` ·
`CurriculumAnchor`
*(en `security/audit-fixes` se suman `SafeUrl`, `CssToken`, `HtmlAllowlist`)*

> **Trampa conocida:** Omeka registra los helpers del tema con la cadena literal del
> `theme.ini` (`application/src/Mvc/MvcListeners.php:372`) y Laminas ServiceManager v3 no
> normaliza nombres. Hay que invocarlos respetando mayúsculas: `$this->CurriculumAnchor(…)`,
> nunca `$this->curriculumAnchor(…)`, que lanza `ServiceNotFoundException`.

---

## 4. Portada y bloques

La portada real (`/s/ceiplajares/page/home`) **no está compuesta por bloques del tema**:
es un **único bloque `html`** con el hero escrito a mano en el editor de Omeka. Esto
significa que buena parte del aspecto de la home vive **en la base de datos, no en el
repositorio** — un cambio de portada no se ve en el diff del tema.

Bloques configurados en el sitio de ejemplo:

| Página | Bloques |
|---|---|
| `home` | `html` (hero manual) |
| `recursos` | `pageTitle` + `showcase` (tarjetas a las páginas de nivel) |
| `1-eso` … `4-eso` | `pageTitle` + `browsePreview` (query preconstruida por nivel) |
| `recursos-educativos-as` | `searchingForm` → **incrusta la búsqueda facetada** (`search_config` 3) |
| `lomloe`, `relacion` | `d3Graph` (visualización de grafo del currículo) |

Componentes del tema que sí participan de la portada:

- **`home-audience-rail.phtml`** — carril de tarjetas por audiencia (profesorado, alumnado,
  familias). Los tres destinos son ajustes de tema (`home_audience_*_url`).
- **`banner.phtml`** — banner con imagen, encabezado, descripción, posición y alto
  configurables (`banner_*`).
- **`logos-bar.phtml`** — barra de logos institucionales.
- **`header.phtml` / `menu-drawer.phtml`** — cabecera *sticky* de dos niveles y menú lateral.

---

## 5. Campos que muestra la ficha del recurso

`view/omeka/site/item/show.phtml` (242 líneas). Estructura:

### 5.1 Cabecera (`.item-hero`)
- **Badge de tipo de recurso** (`resource-type-badge.phtml`) — icono Material Symbols
  derivado de `lrmi:learningResourceType` mediante un mapa de palabras clave
  (vídeo→`smart_display`, cuestionario→`quiz`, etc.).
- **Píldoras de colección** — un enlace por `itemSet`.
- **Título** (`dcterms:title`).
- **Antigüedad** — «hace N días» calculada desde `$item->created()`.
- **Caja de administración** — editar / añadir media / borrar, visible solo si hay usuario
  y `CanEditInCurrentSite`.

### 5.2 Cuerpo
Tres regiones (izquierda / principal / derecha) pobladas por los *resource page blocks* de
Omeka. La región principal incluye el **bloque de anclaje curricular**
(`resource-page-block-layout/block.phtml`), que renderiza las propiedades listadas en el
ajuste de tema `anclaje_properties`. En la instancia de ejemplo:

```
lrmi:educationalLevel · schema:about · lrmi:assesses · lrmi:teaches
dcterms:rights · lrmi:educationalUse · lrmi:educationalRole · lrmi:timeRequired
```

La barra derecha añade **`project-funding.phtml`**, que resuelve `schema:isPartOf` y muestra
el proyecto financiador con su logo.

### 5.3 JSON-LD `schema:LearningResource`
Se inyecta en `headScript` como `application/ld+json`. Mapeo completo:

| Schema.org | Propiedad Omeka |
|---|---|
| `name` | `dcterms:title` |
| `description` | `dcterms:description` (con `strip_tags`) |
| `url` | URL del ítem en el sitio |
| `inLanguage` | `dcterms:language` |
| **`license`** | **`dcterms:rights`** |
| `datePublished` | `dcterms:date` |
| `timeRequired` | `lrmi:timeRequired` → `PT{n}M` |
| `learningResourceType` | `lrmi:learningResourceType` |
| `educationalLevel` | `lrmi:educationalLevel` |
| `educationalUse` | `lrmi:educationalUse` |
| `educationalRole` | `lrmi:educationalRole` |
| `teaches` | `lrmi:teaches` (escalar si es uno, array si varios) |
| `assesses` | `lrmi:assesses` |
| `about` | `dcterms:relation` |
| `keywords` | `dcterms:subject` + `schema:about`, unidos por coma |
| `creator` | `dcterms:creator` → `Person` |
| `publisher` | `dcterms:publisher` → `Organization` |
| `isPartOf` | `schema:isPartOf` → `LearningResource` anidado |
| `thumbnailUrl` | Media principal, tamaño `medium` |

### 5.4 Modal de borrado
Incrusta un **token CSRF de administración** en `data-csrf-token`. En la rama actual está
condicionado a `$user && $showToolboxForUser`. La auditoría de seguridad identificó que un
alcance mayor de este token convertía cualquier XSS del tema en borrado de ítems; el commit
`1372ffb` de `security/audit-fixes` reduce ese alcance. **Es la razón por la que el escapado
en este tema no es cosmético.**

### 5.5 SCORM
`resource-page-block-layout/media-embeds.phtml` incrusta paquetes SCORM en iframe. El
*sandbox* del iframe figura como pendiente en la Fase 4 de la auditoría.

---

## 6. Búsqueda facetada

Es la pieza más elaborada del tema. Vive sobre el módulo **AdvancedSearch**.

### 6.1 Puntos de entrada
- Página propia del módulo: `/s/ceiplajares/rea` (`search_config` slug `rea`, id 3).
- Incrustada en `/s/ceiplajares/page/recursos-educativos-as` mediante el bloque
  `searchingForm`.

### 6.2 Configuración (de instancia, no del tema)
```
results.properties       = lrmi:educationalLevel, schema:about,
                           lrmi:learningResourceType, lrmi:teaches,
                           dcterms:relation, lrmi:timeRequired
results.grid_list_mode   = list_only
pagination_per_page      = 15
facet.mode               = js
```

### 6.3 Facetas agrupadas por etapa — `view/search/facets-list.phtml`

Las facetas cuyos valores son `schema:DefinedTerm` enlazados se agrupan en bloques
`<details>/<summary>` por el `schema:DefinedTermSet` al que pertenecen (ESO, Primaria…).

- **Autodescubrimiento**, sin IDs codificados: 1 consulta para localizar los
  `DefinedTermSet` (~5) + N consultas para sus hijos, todas sobre columnas indexadas.
- **Orden de grupos** por `schema:position` del `DefinedTermSet`; a falta de él, por ID.
- Los valores sin `DefinedTermSet` caen en `.facet-group-ungrouped` al final.
- Ajustes: `advancedsearch_grouped_facets` (una faceta por línea),
  `advancedsearch_termset_property` (por defecto `schema:inDefinedTermSet`).

> **Bug crítico documentado:** `AdvancedSearch\View\Helper\AbstractFacet::__invoke()` usa
> `static $facetsData = []`. Llamar `$facetElements($name, …)` dos veces para el mismo campo
> devuelve los datos de la primera llamada. La solución adoptada es invocarlo **una sola vez**
> con `$asData = true` y renderizar cada subgrupo con `$partial()` directamente.

### 6.4 Anclaje curricular en los resultados — `view/search/results.phtml` + `CurriculumAnchor`

Sustituye las dos tiras paralelas de píldoras (`schema:about` y `lrmi:educationalLevel`) por
**una píldora compuesta por materia**, con sus niveles anidados como segmentos enlazables.

Efecto medido sobre el catálogo: **111 píldoras → 35 filas** (−68 %), sin materias repetidas.

Reglas del algoritmo (`helper/CurriculumAnchor.php`, método puro `groupRows()`):

1. **Agrupación** por etiqueta de materia normalizada (`trim` + colapso de espacios +
   `mb_strtolower`). Título y código de asignatura son biyectivos en los datos, así que
   agrupar por título es seguro.
2. **Intersección**: de los niveles que declara cada asignatura solo sobreviven los que el
   recurso también declara. Es lo que garantiza que **ningún enlace lleve a un resultado
   vacío** — verificado empíricamente: las 14 URLs de nivel de la página 1 devuelven entre
   1 y 7 resultados.
3. **Huérfanos**: los niveles declarados que ninguna materia reclama van a una fila final
   «Otros niveles».
4. **Excepción**: un único grupo sin niveles absorbe los huérfanos (caso del `schema:about`
   literal suelto).
5. **Orden**: niveles por `(posición de etapa, id de curso)`; grupos por su primer nivel y,
   a igualdad, alfabético.

**Rendimiento:** `primeResources($resources)` recorre todos los recursos de la página y
lanza **3 consultas por lote** (asignaturas, cursos, etapas) antes del bucle. Sin ese
*priming* serían 3 × 15 = 45. Es una optimización, nunca una precondición: `__invoke()`
devuelve lo mismo sin ella.

**Degradación:** si cualquiera de los dos ajustes está vacío o el recurso no tiene materias,
el helper devuelve `null` y la plantilla cae al renderizado nativo del módulo. Los bloques
SCSS de `.property[data-term="lrmi:educationalLevel"]` y `schema:about` **se conservan
deliberadamente** para esa ruta: no son CSS muerto.

> Los ajustes `curriculum_anchor_subject_property` y `curriculum_anchor_level_property`
> resuelven su valor por defecto **en código**, no solo en `theme.ini`: `attributes.value`
> es el valor por defecto del *formulario*, y `themeSetting()` devuelve `null` para una clave
> nunca guardada. Sin ese default en código la función quedaba apagada en cualquier sitio
> cuyo admin no hubiera reguardado la configuración.

### 6.5 Capa JavaScript — `asset/js/advanced-search-list.js`
- Etiqueta cada `.property` con `data-term` deducido del `metadata-search-link` o del `<dt>`.
- Convierte `lrmi:learningResourceType` en badge con icono y `lrmi:timeRequired` en chip
  de duración.
- Agrupa tipo de recurso y duración en `.property-meta-group` a la derecha del título.
- **Guarda obligatoria:** ignora `.property--curriculum-anchor` *antes* de la inferencia de
  `data-term`. Sin ella, como los enlaces de nivel llevan `lrmi:educationalLevel` en el
  `href`, el bloque acabaría arrastrado a la tira de píldoras del título.
- **Plegado de listas largas de chips** (`lrmi:teaches`, `dcterms:relation`): cuando los
  valores desbordan la primera línea, envuelve los `<dd>` en `.property__values`, limita su
  alto a una fila —medida sobre un chip real y publicada como `--property-row-h`, porque
  depende de la fuente cargada y del tamaño de texto del usuario— y añade un botón
  «Ver todos (N)» / «Ver menos» con `aria-expanded` y `aria-controls`. Es **mejora
  progresiva**: sin JavaScript no se pliega nada. Se recalcula tras `document.fonts.ready`
  y al redimensionar, deshaciendo y volviendo a medir en vez de conservar un estado viejo.
- Barra de chips de filtros activos, con validación de URL de mismo origen.
- Preserva la posición de scroll al marcar una faceta (`sessionStorage`).

---

## 7. Tratamiento de la licencia

**Estado: mínimo y mejorable.** No hay componente dedicado.

- `dcterms:rights` se mapea a `license` en el **JSON-LD** de la ficha (§5.3). Es el único
  tratamiento semántico.
- Visualmente se renderiza como **un valor más** del bloque de anclaje curricular, porque
  `dcterms:rights` está incluido en el ajuste `anclaje_properties` de la instancia. No hay
  badge de Creative Commons, ni icono, ni enlace a la licencia, ni normalización de la URL.
- El modelo de metadatos lo declara como «Literal (vocabulario controlado)», pero
  `.project/context/metadata_model.md` marca ese vocabulario como **«A confirmar»**.
- La licencia **del propio tema** es GPL-3.0-or-later, heredada de Freedom S Theme, con la
  atribución al RRCHNM en el README.

**Hueco identificado:** para un repositorio de recursos *abiertos*, la licencia de cada
recurso no tiene tratamiento visual propio. Un componente que reconociera las URLs de
Creative Commons y pintara el badge oficial con su enlace sería una mejora evidente y de
bajo coste.

---

## 8. Estado de accesibilidad

**Objetivo declarado: WCAG 2.1 AA** (`.project/skills/frontend/a11y-wcag.md`, con tabla de
criterios prioritarios). Existe un scope de commit `a11y` propio.

### Implementado
- *Skip link* a `#main-content` y `role="main"` en el layout.
- `lang` en `<html>` desde la configuración de locale del sitio.
- `aria-label` en los botones de la caja de administración de la ficha.
- `aria-label` «materia — nivel» en cada segmento de nivel del anclaje curricular, para que
  la navegación por lista de enlaces no oiga niveles sueltos sin contexto.
- `aria-hidden` en iconos decorativos: **32 usos** repartidos por las plantillas.
- `:focus-visible`: **15 reglas** en 8 componentes SCSS (carril de audiencia, resultados de
  búsqueda, rejilla y lista de recursos, galería de colecciones, controles de listado,
  financiación del proyecto).
- Área táctil de 24×24 px en los segmentos de nivel del anclaje (WCAG 2.2 SC 2.5.8),
  medida durante la revisión de esa función.

### Hallazgos abiertos en QA (`.project/docs/qa-findings.md`, 34 entradas)
| ID | Severidad | Problema |
|---|---|---|
| **QA-013** | Alta | Contraste no garantizado en las tarjetas de audiencia de la home: el color de texto depende de los colores configurables del tema, sin ninguna restricción de contraste. Una reconfiguración por el administrador puede romper el 4.5:1. |
| **QA-015** | Alta | No hay formulario de búsqueda accesible en la cabecera por debajo de 1024 px. |
| **QA-010** | Media | El selector de orden en `item/browse` rompe la cabecera en varias filas. |

### Riesgos y carencias
- **Sin pruebas automatizadas de accesibilidad.** Todo se verifica a mano.
- **Desbordamiento horizontal en móvil** en componentes preexistentes (chips de
  `dcterms:relation`, buscador de la cabecera), afectando a SC 1.4.10 Reflow.
- **Google Fonts externo**: Inter y Material Symbols se cargan desde
  `fonts.googleapis.com`. Es a la vez un problema de RGPD y un riesgo de rendimiento; el
  *self-hosting* está planificado en la Fase 2 de la auditoría.
- **Cuatro scripts inline** (`user-bar`, `menu-drawer`, `advanced-search/item-sets`,
  `item-set/_browse-filter-script`) bloquean la adopción de una CSP estricta.

> ⚠️ **Aviso metodológico.** Para verificar anchos móviles, `--window-size` de Chrome
> headless **no sirve**: el navegador fija el viewport de maquetación en un mínimo de
> 500 px pero escribe el PNG al ancho pedido, de modo que produce recortes que parecen
> desbordamientos reales. Hay que usar un iframe dimensionado o CDP
> `Emulation.setDeviceMetricsOverride`. Un falso positivo por esta causa ya estuvo a punto
> de provocar la «corrección» de CSS que funcionaba.

---

## 9. Deuda técnica y riesgos abiertos

| Riesgo | Detalle |
|---|---|
| Copia de plantilla de módulo | `view/search/results.phtml` está anclado a AdvancedSearch **3.4.60**. Son 4 hunks aditivos sin líneas *upstream* modificadas, así que el rebase es mecánico, pero hay que hacerlo en cada actualización. |
| Caché estático de `AbstractFacet` | Ver §6.3. Cualquier retoque de las facetas agrupadas debe respetar la regla de una sola llamada por campo. |
| Portada en base de datos | El hero de la home es un bloque `html` manual: no está versionado. |
| `results.properties` es de instancia | Si un administrador quita `schema:about` de las propiedades mostradas, el anclaje curricular deja de pintarse. |
| msgids en español | `Otros niveles` y otras cadenas nuevas son msgids en español dentro de un catálogo cuyos msgids son ingleses. Funciona en `es`, pero cualquier otro locale vería español sin traducir. |
| Cadenas del JS sin traducir | «Ver todos (N)», «Ver menos» y «Limpiar todo» están **codificadas en español dentro de `advanced-search-list.js`**, sin pasar por `translate()`. Para internacionalizarlas habría que inyectarlas desde PHP como `data-*`. |
| Sin caché negativa | `CurriculumAnchor::fetchItems()` vuelve a pedir un ID que nunca resuelve en cada llamada. |
| Fases 2-4 de seguridad pendientes | CSP, *self-hosting* de fuentes, cabeceras de seguridad, *expression injection* en `release.yml`, action de terceros anclada a `@main`, sandbox de iframes SCORM, endurecimiento de la instancia. |

---

## 10. Entorno de desarrollo

```bash
docker compose up -d          # Omeka S + MariaDB, tema montado en vivo
npx gulp css                  # compila SASS → asset/css/style.css (versionado)
./vendor/bin/phpunit -c test/phpunit.xml    # solo desde security/audit-fixes
```

| Recurso | URL |
|---|---|
| Sitio | `http://localhost:8080/s/ceiplajares/page/home` |
| Búsqueda facetada | `http://localhost:8080/s/ceiplajares/rea` |
| Búsqueda incrustada | `http://localhost:8080/s/ceiplajares/page/recursos-educativos-as` |
| Ficha de ejemplo | `http://localhost:8080/s/ceiplajares/item/3181` |
| Ajustes del tema | `http://localhost:8080/admin/site/s/ceiplajares/theme` |

Los cambios en PHP y plantillas se ven al recargar; los de SASS requieren `npx gulp css`.

### Convenciones del proyecto (`.project/`)
- `context/` — requisitos y modelo de metadatos
- `docs/` — arquitectura, sistema de diseño, hallazgos de QA, specs y planes
- `decisions/` — diarios de decisiones por rol, con formato fijo
- `skills/` — referencias técnicas (Omeka, metadatos, frontend, proceso)
- Commits: `type(scope): descripción en imperativo`, **máximo 72 caracteres**, sin punto
  final. Scopes: `php`, `sass`, `js`, `metadata`, `search`, `item-show`, `item-set-browse`,
  `browse`, `home`, `header`, `footer`, `media`, `a11y`, `i18n`, `deps`, `ci`, `decisions`.
