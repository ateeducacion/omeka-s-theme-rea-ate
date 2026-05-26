# Registro de hallazgos QA

_Documento operativo del proyecto. Fuente de trabajo para registrar incidencias detectadas en QA sobre la instancia real._

Última actualización: 2026-05-26 — Ciclo 6 cerrado. Búsqueda avanzada rediseñada. QA sobre instancia real pendiente (ciclo 7). Próximo ID: QA-034.

---

## Alcance actual

**Ciclo 5 — Cerrado:**

- `view/common/header.phtml`: enlace "Advanced search" apuntaba a `/item/search` (formulario nativo) en lugar de `/search` (módulo AdvancedSearch). — ✅ cerrado (QA-033)
- `view/omeka/site/item/show.phtml`: bloque JSON-LD Schema.org `LearningResource` inyectado en `<head>` vía `headScript()->appendScript()`. — ✅ validado en instancia real (0 errores, 0 advertencias en validator.schema.org)

**Ciclo 4 — Cerrado:**

- `item/show` linked-resources (desktop): sin filtro, sin acordeón, sin cabecera — ✅ validado
- Tarjeta linked-resource (desktop): thumbnail, título, descripción, pills, badge, placeholder — ✅ validado
- Regresión search results: sin cambios visuales con CSS compartida — ✅ validado
- QA-026: eyebrow/pill mostraban URL en lugar de título (recurso enlazado) — ✅ cerrado

**Ciclos anteriores — cerrados:**

- Header sticky: top-bar, main-bar, búsqueda y navegación.
- Search results: facetas, chips activos, contador y estados sin resultados.
- Item show: breadcrumb, badge de tipo de recurso, sidebars, panel `resource-link-info` y bloque de medios.
- Item-set browse: grid de colecciones, filtros client-side, contadores, fallback sin imagen y responsive básico.
- Accesibilidad visual: contrastes y focus states ajustados en ciclo 2.

---

## Severidad

| Severidad | Criterio |
|-----------|----------|
| Bloqueante | Impide una tarea principal, rompe navegación clave o invalida la release. |
| Alta | La funcionalidad existe pero falla de forma importante o genera una regresión visible. |
| Media | Problema real de UX, visual o responsive con workaround o impacto acotado. |
| Baja | Ajuste menor, inconsistencia visual o mejora no bloqueante. |

---

## Estados del hallazgo

| Estado | Significado |
|--------|-------------|
| Abierto | Detectado y pendiente de priorización o asignación. |
| En análisis | Validado, pendiente de decisión técnica o de diseño. |
| En curso | Asignado para corrección. |
| Resuelto | Corregido, pendiente de revalidación en QA. |
| Cerrado | Revalidado en instancia real. |
| Diferido | Problema confirmado que se pospone a un ciclo posterior. |
| Rechazado | No se corrige en este ciclo; debe indicarse el motivo. |

---

## Formato mínimo por hallazgo

Cada entrada debe incluir, como mínimo:

- `ID` secuencial (`QA-001`, `QA-002`, ...).
- `Severidad`.
- `Área`.
- `Hallazgo`.
- `Reproducción mínima` en 1-3 pasos concretos.
- `Estado`.
- `Responsable` si ya está asignado.

---

## Hallazgos

### QA-001 — El filtro de `item-set/browse` no produce resultados útiles en instancia real

- **Fecha:** 2026-05-05
- **Severidad:** Alta
- **Área:** Item-set browse
- **Hallazgo:** La barra de filtros de colecciones no funcionaba en la instancia real porque dependía de metadatos presentes en el propio `item set` y usaba propiedades (`dcterms:educationLevel`, `lom:educationalLevel`, `dcterms:subject`) que podían no coincidir con el modelo de datos real de la instancia.
- **Reproducción mínima:**
  1. Abrir la página de colecciones (`item-set/browse`) en la instancia real.
  2. Intentar filtrar por `Etapa`, `Nivel` o `Temática`.
  3. Ver que el filtrado no devuelve el comportamiento esperado o no ofrece opciones útiles porque las colecciones no tienen los metadatos esperados en esos campos.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución:**
El cierre definitivo del hallazgo queda normalizado con el mapeo final validado en el ciclo:
- `Etapa`: `lrmi:educationalAlignment`
- `Nivel`: `lrmi:educationalLevel` (preferido) o `lom:educationalLevel` (fallback)
- `Temática`: `schema:about` (preferido) o `dcterms:subject` (fallback)

Los filtros se alimentan desde metadatos del propio `item set`; si una dimensión no tiene datos reales, su `<select>` no se renderiza. La completitud de contenido sigue siendo responsabilidad del equipo de contenidos.

---

### QA-002 — El clip de tipo de recurso no es coherente entre search results e item show

- **Fecha:** 2026-05-05
- **Severidad:** Media
- **Área:** Search results / Item show
- **Hallazgo:** El renderizado de `lrmi:learningResourceType` en los resultados de búsqueda usaba un badge plano (`.lrt-badge`) sin iconografía ni el mismo tratamiento visual/textual que el badge de `item/show`, que sí aplicaba icono contextual, normalización de etiqueta y el componente específico (`.resource-type-badge`). El mismo tipo de recurso se percibía como dos componentes distintos.
- **Reproducción mínima:**
  1. Abrir un resultado de búsqueda que muestre `lrmi:learningResourceType`.
  2. Abrir la ficha del mismo recurso en `item/show`.
  3. Comparar ambos clips y ver que diferían en icono, casing/normalización y lenguaje visual.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución:**
El cierre definitivo unifica el badge `lrmi:learningResourceType` entre vistas:
- `item/show` mantiene el partial `common/resource-type-badge`.
- Search results usa `asset/js/advanced-search-list.js` para transformar el texto plano renderizado por AdvancedSearch en el mismo componente `.resource-type-badge`.
- PHP conserva un fallback defensivo en `resource-type-badge.phtml` y `resource-values.phtml` para contextos donde el valor sí llegue por partial.

---

### QA-003 — La barra de filtros de `item-set/browse` tiene demasiado peso visual y no está integrada en la cabecera

- **Fecha:** 2026-05-05
- **Severidad:** Media
- **Área:** Item-set browse
- **Hallazgo:** El bloque de filtros se percibía demasiado protagonista al entrar en `item-set/browse`. Se esperaba un tratamiento más discreto e integrado dentro de `item-set-browse-header`. Además, `item-set-browse-header__count` debía colocarse encima del grid de colecciones, alineado a la derecha, en lugar de competir con la cabecera principal.
- **Reproducción mínima:**
  1. Abrir la página `item-set/browse`.
  2. Observar la jerarquía visual inicial entre cabecera, contador y barra de filtros.
  3. Ver que el filtro destacaba demasiado y que el contador no ocupaba la posición esperada sobre `collections-grid`, a la derecha.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución:**
- Estructura HTML: el filtro ya estaba integrado dentro de `item-set-browse-header` y el contador en `item-set-browse-results-info` sobre el grid, alineado a la derecha mediante `justify-content: flex-end`.
- Peso visual del filtro reducido en `asset/sass/components/item-set-browse/_item-set-browse.scss`:
  - `.collection-filter-bar__label`: `font-weight` 700→500, `letter-spacing` 1.4px→0.4px, icono reducido a 14px con color `text-muted`.
  - `.collection-filter-select select`: `font-size` 13→12px, `font-weight` 600→500, `border` 1.5px→1px, `padding` reducido, color `text-body`.
  - Flecha chevron: borde 2px→1.5px con color `text-muted`.

---

### QA-004 — El contador del footer en `item-set/browse` muestra ítems en lugar de item sets

- **Fecha:** 2026-05-05
- **Severidad:** Media
- **Área:** Item-set browse
- **Hallazgo:** En el footer de `item-set/browse` aparece un contador asociado a colecciones / item sets, pero el valor mostrado corresponde al número de ítems y no al número de `item sets`, generando una etiqueta semánticamente incorrecta.
- **Reproducción mínima:**
  1. Abrir la página `item-set/browse`.
  2. Ir al footer o zona inferior donde aparece el contador de resultados.
  3. Ver que el texto o el contexto visual indica `item sets` / colecciones, pero la cifra corresponde a ítems.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución:**
En `view/omeka/site/item-set/browse.phtml`, se ha implementado una llamada al helper de paginación con parámetros explícitos para evitar la contaminación del estado global de la API.

Se calcula el total de resultados basándose en `count($visibleSets)` (colecciones no vacías) y se obtienen la página actual y el límite de forma manual. El HTML de la paginación se genera al inicio del archivo y se almacena en la variable `$pagination` para ser impreso al final, evitando así tanto el conteo incorrecto como errores de tipo (`TypeError`) al manipular objetos de respuesta en la vista.

---

### QA-005 — El clip de número de colecciones ocupa todo el ancho y no tiene separación visual con el grid

- **Fecha:** 2026-05-06
- **Severidad:** Media
- **Área:** Item-set browse
- **Hallazgo:** En `item-set/browse`, el elemento `item-set-browse-results-info` (que contiene el contador de colecciones) se expande al ancho completo de la pantalla y no tiene ninguna separación visual respecto al grid de colecciones que aparece inmediatamente debajo. Esto rompe la jerarquía visual esperada: el contador debería percibirse como una pequeña etiqueta alineada a la derecha, no como una banda de ancho completo.
- **Reproducción mínima:**
  1. Navegar a `/:site/item-set`.
  2. Observar la zona entre la cabecera de filtros y el grid de colecciones.
  3. Ver que el div del contador ocupa todo el ancho disponible y que el grid arranca sin margen o separador visibles.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución:**
Estilos actualizados en `asset/sass/components/item-set-browse/_item-set-browse.scss`:
- `.item-set-browse-results-info`: margen inferior aumentado de 12px a 24px para mayor separación del grid.
- `.item-set-browse-header__count`: transformado en un clip compacto mediante `padding: 4px 14px`, `background: var(--ate-surface-soft)`, `border: 1px solid var(--ate-hairline-soft)` y `border-radius: var(--ate-radius-pill)`. También se redujo ligeramente el tamaño de fuente a 12px.
Compilación de CSS ejecutada con éxito.

---

### QA-006 — El filtro `Etapa` usa la propiedad de `Nivel` y viceversa en `item-set/browse`

- **Fecha:** 2026-05-06
- **Severidad:** Alta
- **Área:** Item-set browse
- **Hallazgo:** El filtro etiquetado como `Etapa` en la barra de filtros de `item-set/browse` lee la propiedad `lrmi:educationalLevel`, que corresponde al concepto de **nivel educativo**, no al de etapa. El concepto `Etapa` debe asociarse a `lrmi:educationalAlignment`. Adicionalmente, el filtro `Nivel` usa `lom:educationalLevel` en lugar de `lrmi:educationalLevel`. El resultado es que los dos filtros muestran datos semánticamente incorrectos bajo sus respectivas etiquetas.
- **Reproducción mínima:**
  1. Navegar a `/:site/item-set`.
  2. Inspeccionar los valores que ofrece el select `Etapa`: contendrán valores de nivel educativo, no de etapa/alineación curricular.
  3. Confirmar en los metadatos de una colección que `lrmi:educationalLevel` almacena el nivel y que `lrmi:educationalAlignment` almacena la etapa.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución:**
Propiedades corregidas en `view/omeka/site/item-set/browse.phtml`:
- **Etapa:** Cambiado de `lrmi:educationalLevel` a `lrmi:educationalAlignment`. Se eliminó el fallback a `dcterms:educationLevel` por ser semánticamente incorrecto para "Etapa".
- **Nivel:** Cambiado para usar `lrmi:educationalLevel` como preferido y `lom:educationalLevel` como fallback.

Se han actualizado tanto los bloques de generación de opciones para los selects como la generación de atributos `data-etapa` y `data-nivel` en las tarjetas de colección.

---

### QA-007 — `Uncaught TypeError: Cannot read properties of null (reading 'classList')` en `item-set/browse`

- **Fecha:** 2026-05-06
- **Severidad:** Alta
- **Área:** Item-set browse
- **Hallazgo:** Error en consola al cargar `item-set/browse`. La función `applyFilters` del script de filtros itera `Object.values(filters)` y llama a `sel.classList.toggle(...)` sin comprobar si `sel` es `null`. Los tres selects (`#filter-etapa`, `#filter-nivel`, `#filter-tematica`) solo se renderizan en PHP si hay valores para esa dimensión de filtro. Si alguno no existe en el DOM, `getElementById` devuelve `null` y la llamada a `.classList` lanza el TypeError.
- **Reproducción mínima:**
  1. Navegar a `/:site/item-set` en una instancia donde al menos uno de los tres filtros no tenga datos (p. ej. sin colecciones con `lom:educationalLevel` informado).
  2. Abrir la consola del navegador.
  3. Ver el error: `Uncaught TypeError: Cannot read properties of null (reading 'classList') at applyFilters`.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución:**
Dos guardas de nulo añadidas en `applyFilters` (`view/omeka/site/item-set/_browse-filter-script.phtml`):

1. En el `forEach` que actualiza la clase `has-value`:
```js
Object.values(filters).forEach(function (sel) {
    if (!sel) return;
    sel.classList.toggle('has-value', !!sel.value);
    if (sel.value) anyActive = true;
});
```

2. En el `every` que evalúa cada tarjeta (segunda rotura, causa del crash al limpiar filtros):
```js
if (!sel || !sel.value) return true;
```

El error al limpiar (`clear` no restauraba todas las colecciones) era consecuencia del mismo null: el crash en `every` interrumpía el `forEach` de tarjetas antes de restaurar `card.style.display`, dejando tarjetas ocultas permanentemente hasta recargar.

**Nota sobre el origen de los datos:** el filtro lee los atributos `data-etapa`, `data-nivel` y `data-tematica` de cada `.collection-card`, que se generan en PHP a partir de los metadatos del **propio item-set**, no de los ítems contenidos en él.

---

### QA-008 — El clip de tipo de recurso ha desaparecido en search results

- **Fecha:** 2026-05-06
- **Severidad:** Alta
- **Área:** Search results
- **Hallazgo:** El badge `.resource-type-badge` de `lrmi:learningResourceType` no aparece en los resultados de búsqueda. La resolución de QA-002 eliminó el código JS que construía el badge en el cliente (`.lrt-badge`) y lo sustituyó por renderizado server-side en `resource-values.phtml` via el partial `common/resource-type-badge`. Sin embargo, el partial llama a `$resource->value($term)` sin comprobar si `$resource` está definido. En el contexto de search results, `$resource` puede no estar disponible o ser `null`; PHP falla silenciosamente y el partial no produce salida, haciendo que el badge desaparezca.
- **Reproducción mínima:**
  1. Navegar a la búsqueda y obtener resultados con ítems que tengan `lrmi:learningResourceType` informado.
  2. Comprobar que la columna de tipo de recurso en el card de cada resultado está vacía.
  3. Comparar con `item/show` del mismo recurso, donde el badge sí aparece.
- **Estado:** Cerrado
- **Responsable:** Sin asignar

**Resolución (revisada tras análisis del HTML real):**
El HTML inspeccionado en la instancia confirmó que AdvancedSearch **no pasa por `resource-values.phtml`**: renderiza los valores directamente como texto plano sin atributo `lang` en el `dd`. El partial PHP nunca se invoca para search results.

La solución definitiva es client-side, en `asset/js/advanced-search-list.js`:
- Se añaden `lrtNormalize`, `lrtIcon`, `buildLrtBadge` y `upgradeLrtProperty`, que replican la lógica del partial PHP (extracción de URI, camelCase→palabras, truncado a 35 chars, mapa de iconos).
- `processItem` llama a `upgradeLrtProperty` cuando detecta `data-term="lrmi:learningResourceType"`.
- El guard `if (vc.querySelector('.resource-type-badge')) return` evita doble proceso si en otro contexto el badge ya viene del servidor.

Cambios defensivos conservados en PHP:
- `resource-type-badge.phtml`: acepta `$resourceTypeValue` como alternativa a `$resource->value()`. Evita fatal error si `$resource` es null.
- `resource-values.phtml`: pasa `$value` como `resourceTypeValue` al partial. Útil si en el futuro AdvancedSearch adopta `resource-values.phtml`.

---

### QA-009 — Mejora de diseño del filtro en `item-set/browse`: chevron desalineado y proporciones del clip de texto

- **Fecha:** 2026-05-06
- **Severidad:** Media
- **Área:** Item-set browse
- **Hallazgo:** La barra de filtros de colecciones presenta dos problemas visuales:
  1. **Chevron desalineado**: el indicador desplegable (flecha `∨`) no está bien alineado verticalmente con el texto de la opción seleccionada dentro del `<select>`.
  2. **Proporciones del clip de texto**: la relación de aspecto del `<select>` (padding, altura, tamaño de fuente) no resulta visualmente equilibrada; el clip de texto necesita mejores proporciones para que el componente se perciba compacto y armonioso.
- **Reproducción mínima:**
  1. Navegar a `/:site/item-set`.
  2. Observar los selects `Nivel` y `Temática` en la barra de filtros.
  3. Ver que el icono chevron no queda visualmente centrado respecto al texto, y que las proporciones del clip son mejorables.
- **Estado:** Cerrado
- **Responsable:** Diseñador + Desarrollador

**Resolución:**
Decisión de diseño registrada en `.project/decisions/designer.md`.
Cambios implementados en `_item-set-browse.scss`:
- **Proporciones del Select:** `font-size` incrementado a 13px y padding ajustado a `6px 32px 6px 14px` para mejorar legibilidad y crear un clip más armonioso.
- **Alineación del Chevron:** Se ajustó la posición (`right: 12px`) y se corrigió el alineamiento vertical (`transform: translateY(-60%) rotate(45deg)`) para centrar visualmente la flecha respecto a la altura del texto.
Compilado correctamente.

---

### QA-010 — El selector de orden en `item/browse` ocupa demasiado espacio y rompe el header en varias filas

- **Fecha:** 2026-05-06
- **Severidad:** Media
- **Área:** Browse
- **Hallazgo:** El formulario de ordenación en `item/browse` ocupaba demasiado espacio horizontal y acababa rompiendo la composición del encabezado en varias filas. Visualmente competía con el título de página y con el resto de controles, en lugar de comportarse como un control secundario y discreto.
- **Reproducción mínima:**
  1. Navegar a `/:site/item`.
  2. Observar el header y el toolbar superior del catálogo.
  3. Ver que el selector de orden ocupa demasiado ancho y se distribuye en varias líneas, rompiendo la jerarquía visual.
- **Estado:** Cerrado
- **Responsable:** Desarrollador

**Resolución:**
Se ha recolocado el selector de orden en la misma barra del título `Recursos`, siguiendo el patrón de `item-set/browse`. Ajustes aplicados:
- `view/omeka/site/item/browse.phtml`: el `renderSortSelector('items')` sale del toolbar y pasa al bloque `.resource-browse-header__sort`.
- `asset/sass/components/resources/_browse-controls.scss`: el selector se compacta como control discreto, con altura fija alineada al header, sin romper en varias filas y con la etiqueta visualmente oculta para no cargar el layout.
- `asset/css/style.css`: recompilado con `npm run build`.

Pendiente de revalidación en instancia real.

---

### QA-011 — El chip de número de recursos en `item/browse` no sigue el patrón de `item-set/browse`

- **Fecha:** 2026-05-06
- **Severidad:** Baja
- **Área:** Browse
- **Hallazgo:** El chip con el número total de recursos en `item/browse` estaba colocado dentro del header principal, mientras que en `item-set/browse` ese metadato aparece en una banda independiente justo encima del grid. La inconsistencia rompe el patrón visual entre ambas vistas de catálogo.
- **Reproducción mínima:**
  1. Navegar a `/:site/item`.
  2. Comparar la posición del chip de recuento con la vista `/:site/item-set`.
  3. Ver que en `item/browse` el contador aparece dentro del header, en lugar de colocarse en su propia línea sobre el listado.
- **Estado:** Cerrado
- **Responsable:** Desarrollador

**Resolución:**
Se ha alineado `item/browse` con el patrón de `item-set/browse`:
- `view/omeka/site/item/browse.phtml`: el chip `.resource-browse-header__count` sale del header y pasa a un bloque nuevo `.resource-browse-results-info` justo encima del listado.
- `asset/sass/components/resources/_browse-controls.scss`: se ajusta el espaciado y el padding del clip para replicar la posición y tono visual del contador de colecciones.
- `asset/css/style.css`: recompilado con `npm run build`.

Pendiente de revalidación en instancia real.

---

### QA-012 — El chip de número de recursos en `item/browse` debe integrarse en la toolbar de catálogo

- **Fecha:** 2026-05-06
- **Severidad:** Baja
- **Área:** Browse
- **Hallazgo:** Tras recolocar el contador fuera del header principal, el chip de número de recursos seguía ocupando una banda propia encima del listado. Para esta vista concreta se pide integrarlo directamente dentro de `.resource-browse-toolbar`, junto al buscador y las acciones secundarias, para concentrar todos los controles y metadatos operativos en un único bloque.
- **Reproducción mínima:**
  1. Navegar a `/:site/item`.
  2. Observar la toolbar del catálogo y la franja independiente del contador sobre el listado.
  3. Ver que el chip de recuento no está integrado en la misma barra que el buscador y las acciones secundarias.
- **Estado:** Cerrado
- **Responsable:** Desarrollador

**Resolución:**
Se ha movido el contador al bloque `.resource-browse-toolbar`:
- `view/omeka/site/item/browse.phtml`: el chip sale de `.resource-browse-results-info` y pasa a `.resource-browse-toolbar__secondary` como `.resource-browse-toolbar__count`.
- `asset/sass/components/resources/_browse-controls.scss`: se añaden estilos específicos para `.resource-browse-toolbar__count` y se elimina la necesidad de la banda independiente anterior.
- `asset/css/style.css`: recompilado con `npm run build`.

Pendiente de revalidación en instancia real.

---

---

### QA-013 — Contraste no garantizado en tarjetas de audiencia (home)

- **Fecha:** 2026-05-07
- **Severidad:** Alta
- **Área:** Home / Accesibilidad
- **Hallazgo:** Las tarjetas `.audience-card--teachers` y `.audience-card--students` usan colores de fondo definidos con tokens ATE hardcoded (`--ate-color-brand-blue-dark`, gradiente con `--ate-color-brand-blue-mid`). Sin embargo, el color del texto dentro de las tarjetas podría depender o verse afectado por los colores configurables del tema en el admin de Omeka-S (`--primary`, `--secondary`, `--accent`), cuya combinación no está sometida a ninguna restricción de contraste. Además, el diseño actual no garantiza que una eventual reconfiguración de esos tokens por el administrador no rompa el contraste WCAG AA (4.5:1 para texto normal, 3:1 para texto grande).
- **Reproducción mínima:**
  1. Navegar a la home del sitio.
  2. Inspeccionar los valores de color aplicados sobre las tarjetas `.audience-card`.
  3. Comprobar con una herramienta WCAG (p.ej. Colour Contrast Analyser) si el texto supera la ratio 4.5:1 sobre cada fondo.
  4. Cambiar `primary_color` en la configuración del tema y recargar para verificar si el aspecto visual se degrada.
- **Estado:** Cerrado
- **Responsable:** Diseñador

**Resolución:**
Decisión del Diseñador [2026-05-07] registrada en `designer.md`. Los fondos de las tarjetas ya usaban exclusivamente tokens `--ate-*` hardcodeados — sin herencia de `--primary/--secondary/--accent`. Contrastes verificados: Teachers 10.7:1, Students (peor punto) 5.2:1, Families 11.4:1; todos pasan WCAG AA.

Único cambio de código: `opacity: 0.8` en `.audience-card__eyebrow` sustituido por colores explícitos por variante (`--ate-text-on-dark` para cards oscuras, `--ate-text-muted` para Familias), eliminando la dependencia de opacidad que impedía la auditoría WCAG automática.

Ficheros modificados: `asset/sass/components/home/_audience-rail.scss`, `asset/css/style.css`.

---

### QA-014 — Audience rail: columnas se adaptan a las tarjetas configuradas

- **Fecha:** 2026-05-07
- **Severidad:** Media
- **Área:** Home
- **Hallazgo:** La sección `.audience-rail` siempre renderiza las tres tarjetas (Profesorado, Alumnado, Familias) aunque el administrador deje en blanco el parámetro de URL de alguna de ellas. El comportamiento esperado es que una tarjeta con URL vacía no se renderice y la rejilla se adapte automáticamente a 1, 2 o 3 columnas según las tarjetas visibles.
- **Reproducción mínima:**
  1. En `Apariencia → Configurar tema`, dejar vacío el campo `Families card destination`.
  2. Recargar la home.
  3. Ver que la tarjeta de Familias sigue apareciendo (o que la rejilla queda desequilibrada si se suprime sin CSS adaptativo).
- **Estado:** Cerrado
- **Responsable:** Desarrollador

**Resolución:**
- `view/common/home-audience-rail.phtml`: `$resolveAudienceUrl` devuelve `null` cuando el setting está vacío (antes devolvía la URL por defecto). El array `$cards` se filtra con `array_filter` para excluir entradas con `url === null`. Si el array queda vacío, se sale del partial con `return` sin renderizar nada.
- `asset/sass/components/home/_audience-rail.scss`: `grid-template-columns` pasa de `repeat(2, minmax(0, 1fr))` a `repeat(auto-fit, minmax(260px, 1fr))`. Eliminado `grid-column: 1 / -1` de `.audience-card--families` para que fluya de forma natural en cualquier número de columnas.
- `asset/css/style.css`: recompilado con `npm run build`.

---

### QA-015 — No hay formulario de búsqueda accesible en el header en mobile (< 1024px)

- **Fecha:** 2026-05-11
- **Severidad:** Alta
- **Área:** Header / Mobile
- **Hallazgo:** Tanto `.main-header__search-area--top` como `.main-header__search-area--compact` están condicionadas a `@media (min-width: $lg)` (1024px). En cualquier pantalla inferior (todo el rango mobile y tablet < 1024px), ninguna área de búsqueda es visible en el header. El menu-drawer tampoco incluye formulario de búsqueda. El usuario solo podría alcanzar la búsqueda navegando manualmente a la URL `/item/search` si el menú principal contiene ese enlace, lo que contradice el requisito funcional "barra de búsqueda siempre visible en header".
- **Reproducción mínima:**
  1. Abrir DevTools → modo responsive → viewport 375px.
  2. Observar el header completo (top-bar + main-bar + drawer abierto).
  3. Verificar que no hay ningún input de búsqueda visible en ninguno de los tres elementos.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:** Decisión del Diseñador [2026-05-11] ACEPTADA: segunda fila de búsqueda siempre visible en la main-bar mobile (< 1024px). Implementación:
- `view/common/header.phtml`: añadido `<div class="main-header__search-area--mobile">` con `common/search-form` dentro de `.main-header__main-bar`, antes del área compact existente.
- `_header.scss`: bloque `&__search-area--mobile` con `display: block; width: 100%; padding-bottom: 10px` en mobile, `display: none` en ≥ 1024px. Override `form { width: 100% }` e `input { background: --ate-surface-soft; placeholder: --ate-text-muted }`.
- `_layout.scss`: `$header-min-height` 149px → 199px; `scroll-padding-top` con override de 160px en desktop.

**Bug detectado en revalidación [2026-05-12]:** la búsqueda se solapaba con el nombre del sitio porque `.main-header__main-bar` usaba `gap: 16px` sin `flex-wrap`, por lo que el área de búsqueda participaba como un flex-item más en la fila del título en lugar de saltar a una segunda fila.

**Corrección [2026-05-12]:** `_header.scss` — añadido `flex-wrap: wrap` a `__main-bar` y cambiado `gap: 16px` a `column-gap: 16px` (elimina el row-gap no deseado entre filas). Con `width: 100%` ya presente en `__search-area--mobile`, el área salta correctamente a la segunda fila. CSS recompilado.

---

### QA-016 — `audience-rail__grid` puede provocar scroll horizontal en viewports de 320px (WCAG 1.4.10)

- **Fecha:** 2026-05-11
- **Severidad:** Media
- **Área:** Home / Mobile / A11y
- **Hallazgo:** `grid-template-columns: repeat(auto-fit, minmax(260px, 1fr))` genera un track mínimo de 260px. En un viewport de 320px con el contenedor a `padding: 0 15px` y el rail a `padding: 20px 16px`, el ancho disponible para el grid es ~258px (< 260px mínimo). El grid fuerza el único track a 260px, causando 2px de desbordamiento horizontal. Viola WCAG 2.1 criterio 1.4.10 Reflow (sin scroll horizontal a 320px).
- **Reproducción mínima:**
  1. Abrir DevTools → responsive → 320px de ancho.
  2. Navegar a la home con al menos una audience-card visible.
  3. Verificar scroll horizontal o el grid desbordado.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:** `grid-template-columns` cambiado de `repeat(auto-fit, minmax(260px, 1fr))` a `repeat(auto-fit, minmax(min(260px, 100%), 1fr))` en `_audience-rail.scss`. Con `min(260px, 100%)`, el track nunca supera el ancho disponible del contenedor, eliminando el overflow a cualquier viewport. CSS compilado.

---

### QA-017 — `.resource-link-info__panel` de 260px puede desbordar el viewport en mobile

- **Fecha:** 2026-05-11
- **Severidad:** Baja
- **Área:** Item Show / Mobile
- **Hallazgo:** El panel expandido de `.resource-link-info` tiene `position: absolute; width: 260px; left: 0; top: 100%` sin ningún mecanismo de contención lateral. En viewports < 375px, o cuando el pill que lo contiene está posicionado en la mitad derecha del viewport, el panel puede quedar parcialmente fuera del área visible, requiriendo scroll horizontal.
- **Reproducción mínima:**
  1. Abrir `item/show` de un recurso con valores `dcterms:relation` en DevTools a 360px.
  2. Expandir el botón `+` de un pill de relación situado cerca del borde derecho.
  3. Verificar si el panel se desborda a la derecha del viewport.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución v1:** En `_item-show.scss`, `width: min(260px, calc(100vw - 30px))`. Insuficiente: el panel se contenía pero quedaba estrecho cuando el pill estaba en la zona derecha del viewport, porque `left: 0` (relativo al `dd.value` pill) colocaba el panel en x=pill_left y el viewport lo recortaba.

**Resolución v2 [2026-05-13]:** Corrección en `asset/js/resource-link-info.js`. Se añade `clampPanel()` que, tras abrir el panel (`wrapper.classList.add('expanded')`), mide `panel.getBoundingClientRect()` y desplaza `panel.style.left` el número de px exacto necesario para que el borde derecho del panel no supere `window.innerWidth - 15px`. Al cerrar (tanto el panel propio como los del acordeón), `panel.style.left` se resetea a `''`. El CSS vuelve a `width: 260px` fijo (el ajuste de posición lo hace JS, no el ancho).

---

### QA-018 — `scroll-padding-top` y `body padding-top` no reflejan la altura real del header en mobile

- **Fecha:** 2026-05-11
- **Severidad:** Baja
- **Área:** Header / Mobile
- **Hallazgo:** `$header-min-height: 133px` se usa para `body { padding-top: 133px }` y `scroll-padding-top: 133px`. La altura real del header en mobile es: `top-bar` (min-height 80px) + `hr` (~17px, incluye el `margin-top: 1em` heredado de normalize.css) + `main-bar` (min-height 68px) ≈ 165px. El desfase (~32px) no produce clipping visible de contenido porque `#main-content { padding-top: 2rem }` lo compensa, pero sí causa que los enlaces de ancla internos queden parcialmente tapados por el header al hacer scroll.
- **Reproducción mínima:**
  1. Crear un enlace de ancla a una sección en medio de una página larga.
  2. Activar el enlace en mobile (375px viewport).
  3. Verificar si el destino del scroll queda visible o tapado por el header.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:** Dos cambios aplicados:
1. `_header.scss`: añadido `margin-top: 0` al selector `.main-header hr` para neutralizar el `margin: 1em 0` heredado de normalize.css — el hr separador ya no empuja el main-bar hacia abajo.
2. `_layout.scss`: `$header-min-height` actualizado de `133px` a `149px` (top-bar 80px + hr 1px + main-bar 68px, border-box). `scroll-padding-top` y `body { padding-top }` ahora referencian la variable en lugar de valores literales, por lo que se actualizan solos.

---

### QA-019 — Las propiedades de metadata en search results no forman grupo visual cohesionado

- **Fecha:** 2026-05-18
- **Severidad:** Media
- **Área:** Search results
- **Hallazgo:** En los cards de resultados de búsqueda, las tres propiedades `lrmi:educationalLevel`, `schema:about` y `lrmi:learningResourceType` se renderizaban como flex-items independientes dentro del card. Al reducir el ancho de la tarjeta, cada propiedad podía saltar de línea de forma independiente, creando composiciones fragmentadas donde nivel y temática acababan en filas distintas mientras el tipo de recurso permanecía en la superior (o viceversa). El efecto resultante era visualmente ruidoso y sin jerarquía clara.
- **Reproducción mínima:**
  1. Navegar a los resultados de búsqueda con recursos que tengan `lrmi:educationalLevel`, `schema:about` y `lrmi:learningResourceType` informados.
  2. Reducir el ancho del viewport hasta que la tarjeta pierda espacio horizontal.
  3. Observar que las tres propiedades rompen en filas distintas de forma independiente.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
- `asset/js/advanced-search-list.js`: nueva función `groupMetaProperties()` que, tras procesar el item, mueve las tres propiedades a un `<div class="property-meta-group">` appended directamente al `<li>` del resultado. Al ser un único flex-item, las tres propiedades saltan de línea como bloque o permanecen en la fila del título como bloque.
- `asset/sass/components/search-results/_search-results-list.scss`: nuevos estilos para `.property-meta-group` (`order: 3; flex: 0 0 auto; margin-left: auto; display: flex; gap: 0.5rem 0.75rem`). Título ajustado a `flex: 1 1 0` para ceder el espacio sobrante correctamente.
- `LABEL_TO_TERM` en `advanced-search-list.js` ampliado con aliases en español para `lrmi:educationalLevel` (`nivel educativo`, `nivel`) y `schema:about` (`temática`, `tematica`, `tema`).

---

### QA-020 — El panel `resource-link-info` usaba `maxHeight` inline que rompía con contenido asíncrono

- **Fecha:** 2026-05-18
- **Severidad:** Baja
- **Área:** Item show
- **Hallazgo:** El script `resource-link-info.js` controlaba la apertura y cierre del panel acordeón mediante `panel.style.maxHeight = panel.scrollHeight + 'px'` (abrir) y `panel.style.maxHeight = '0'` (cerrar). El problema: el contenido del panel se carga de forma asíncrona vía `fetch`. Al abrir el panel, `scrollHeight` se mide antes de que llegue la respuesta de la API, por lo que el valor calculado era el del spinner de carga (`…`), no el del contenido real. Cuando la respuesta llegaba y se actualizaba `panel.innerHTML`, el panel tenía `maxHeight` congelado al valor anterior y el contenido extra quedaba cortado sin scroll.
- **Reproducción mínima:**
  1. Abrir `item/show` de un recurso con varios valores `dcterms:relation` que carguen descripciones largas.
  2. Expandir por primera vez un pill de relación.
  3. Observar que el panel puede quedar cortado al llegar la descripción, mostrando solo parte del texto.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
Eliminadas todas las manipulaciones de `maxHeight` inline en `asset/js/resource-link-info.js`. La apertura y cierre del panel queda delegada exclusivamente al CSS mediante la clase `.expanded` en el wrapper (regla `max-height: none` ya existente en el SCSS del panel). El contenido asíncrono ya no puede quedar cortado porque el panel no tiene `maxHeight` restrictivo impuesto por JS.

---

### QA-021 — En mobile, el panel `resource-link-info` desborda lateralmente cuando el pill está en la zona derecha (refinamiento QA-017)

- **Fecha:** 2026-05-18
- **Severidad:** Baja
- **Área:** Item show / Mobile
- **Hallazgo:** La resolución v2 de QA-017 añadía `clampPanel()` en JS para desplazar `panel.style.left` tras medir `getBoundingClientRect()`. Este enfoque funcionaba pero dependía de JavaScript para corregir posicionamiento, y podía producir un flash de desbordamiento visible antes de que el desplazamiento se aplicase. La causa raíz era que el containing block del panel era el `.dd.value` pill (`position: relative`), cuya posición relativa al viewport variaba. En mobile la solución CSS es más limpia: elevar el containing block a `.property.dcterms-relation`, que ocupa el ancho completo del contenedor de contenido.
- **Reproducción mínima:**
  1. Abrir `item/show` con valores `dcterms:relation` en DevTools a 360px.
  2. Expandir un pill situado en la mitad derecha del contenido.
  3. Observar si el panel se desborda a la derecha del viewport antes de que JS lo corrija.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
Cambios en `asset/sass/components/item-show/_item-show.scss`:
- `.property.dcterms-relation`: añadido `position: relative` en `@media (max-width: #{$lg - 1})`. En mobile esta propiedad se convierte en el containing block del panel absoluto.
- `.dd.value` pill: añadido `position: static` en el mismo breakpoint. Libera el containing block para que suba a `.property.dcterms-relation`.
- `.resource-link-info.expanded .resource-link-info__panel` en mobile: `width: min(260px, 100%)` con `left: 0; top: 100%` relativo al ancho del bloque `.property` (~ancho total del contenido). El panel nunca puede desbordarse.
- En desktop el comportamiento previo se conserva: `width: 260px`, containing block es el pill.
- `clampPanel()` eliminado de `resource-link-info.js` (ya no es necesario; la corrección es puramente CSS).

---

### QA-022 — Bloque `project-funding` tiene acento de borde superior en lugar de lateral

- **Fecha:** 2026-05-18
- **Severidad:** Baja
- **Área:** Item show
- **Hallazgo:** El bloque `.project-funding` en `item/show` mostraba `border-top: 3px solid var(--ate-color-brand-blue-mid)` como acento cromático. En el contexto visual de la ficha, este acento superior competía con la separación natural entre secciones y se confundía con un separador de sección en lugar de ser leído como el borde de acento del bloque. El patrón de acento lateral (`border-left`) es más habitual en tarjetas de información y se diferencia mejor del contexto.
- **Reproducción mínima:**
  1. Navegar a `item/show` de un recurso que tenga datos `schema:Project` (bloque de cofinanciación).
  2. Observar el bloque `.project-funding`.
  3. Ver que el acento de color aparece en el borde superior.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/sass/components/item-show/_project-funding.scss`: `border-top: 3px solid var(--ate-color-brand-blue-mid)` sustituido por `border-left: 3px solid var(--ate-color-brand-blue-mid)`. CSS recompilado.

---

### QA-023 — El título H1 de `item/browse` muestra siempre "Recursos" al navegar por una colección

- **Fecha:** 2026-05-18
- **Severidad:** Media
- **Área:** Browse
- **Hallazgo:** En `item/browse`, el encabezado H1 mostraba siempre el texto estático "Recursos" incluso cuando el listado estaba filtrado por un item set específico (URL del tipo `/:site/item?item_set_id=X`). En ese contexto, el subtítulo ya indicaba "Explora los recursos de esta colección", pero el título principal no reflejaba el nombre de la colección activa, lo que desorientaba al usuario respecto a qué colección estaba explorando.
- **Reproducción mínima:**
  1. Navegar a `/:site/item-set` y clicar en una colección.
  2. Observar el H1 del listado de recursos resultante.
  3. Ver que dice "Recursos" en lugar del nombre de la colección.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`view/omeka/site/item/browse.phtml`: el H1 es ahora dinámico — cuando `$itemSetShow` es verdadero (se está navegando dentro de un item set), muestra `$itemSet->displayTitle()`; en caso contrario, muestra el texto estático traducible "Recursos".

---

### QA-024 — Chips de `educationalLevel` y `schema:about` en search results no tienen diseño de pill

- **Fecha:** 2026-05-18
- **Severidad:** Media
- **Área:** Search results
- **Hallazgo:** Las propiedades `lrmi:educationalLevel` y `schema:about` en los cards de resultados se renderizaban como texto gris sin borde ni fondo, flotando libremente junto al badge de tipo de recurso. Sin contorno visual, los valores no se percibían como etiquetas/chips, y el texto no tenía contención clara: sin max-width ni truncado, valores largos podían desbordar o romper el layout del `.property-meta-group`.
- **Reproducción mínima:**
  1. Navegar a los resultados de búsqueda con recursos que tengan `lrmi:educationalLevel` y `schema:about`.
  2. Observar el grupo de metadata en la parte derecha de la tarjeta.
  3. Ver que nivel y temática aparecen como texto plano sin chip, sin contorno coherente con el badge de tipo de recurso.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/sass/components/search-results/_search-results-list.scss`:
- Los dos selectores se fusionan en una regla combinada.
- `dd` pasa a `display: inline-block` con `padding: 2px 8px`, `background: var(--ate-surface-soft)`, `border: 1px solid var(--ate-hairline)`, `border-radius: var(--ate-radius-pill)`, `font-size: 0.75rem`, `color: var(--ate-text-muted)` y `max-width: 18ch; overflow: hidden; text-overflow: ellipsis; white-space: nowrap`.
- En `@media (max-width: $md)` se añade reset explícito (`display: inline; padding: 0; background: none; border: none; ...`) para que en mobile los valores se muestren como texto inline junto a la etiqueta `dt`, sin chip.

---

### QA-025 — `resource-link-info` añade botón `+` en links cuyo icono está oculto por CSS

- **Fecha:** 2026-05-18
- **Severidad:** Baja
- **Área:** Search results / Item show
- **Hallazgo:** El script `resource-link-info.js` ya comprobaba que el enlace contuviese un `span.o-icon-items` antes de añadir el botón `+`. Sin embargo, no comprobaba si ese span era visible. En contextos donde el CSS oculta el icono (`display: none`), como en los chips de `lrmi:educationalLevel` y `schema:about` en search results, el botón `+` se inyectaba igualmente aunque el icono no fuera visible. El resultado era un `+` flotante sin contexto visual en el chip.
- **Reproducción mínima:**
  1. En search results, inspeccionar el HTML de un chip `lrmi:educationalLevel` que apunte a un recurso enlazado.
  2. Ver que el script añade `<button class="resource-link-info__btn">+</button>` aunque `.o-icon-items` tenga `display: none`.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/js/resource-link-info.js`: tras confirmar que `span.o-icon-items` existe, se añade `if (getComputedStyle(iconSpan).display === 'none') return`. El botón `+` solo se inyecta cuando el icono de recurso es realmente visible.

---

### QA-026 — `item/browse`: eyebrow y pill muestran URL en lugar del título cuando el valor es un recurso enlazado

- **Fecha:** 2026-05-21
- **Severidad:** Alta
- **Área:** Browse (grid y lista)
- **Hallazgo:** En `item/browse`, los campos `schema:about` (eyebrow) y `lrmi:educationalLevel` (pill de nivel) se renderizan con `(string) $value`. Cuando esos metadatos contienen un **valor de tipo recurso** (enlace a otro ítem Omeka-S), el cast devuelve la URL del ítem en lugar de su título — por ejemplo `http://localhost:8080/s/ceiplajares/item/24650` en vez de "1º ESO". El problema afecta tanto a la vista grid (`resource-card__eyebrow`, `resource-level-pill`) como a la vista lista (`resource-row__eyebrow`, `resource-row__level-pill`).
- **Reproducción mínima:**
  1. Crear o tener un ítem con `schema:about` o `lrmi:educationalLevel` apuntando a otro ítem Omeka-S (valor tipo recurso, no literal).
  2. Navegar a `/:site/item` en vista grid o lista.
  3. Ver que el eyebrow o el pill muestra la URL del ítem enlazado en lugar de su título.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`view/omeka/site/item/browse.phtml`: añadida función `$displayValue($value)` que comprueba `$value->type() === 'resource'` y llama a `$value->valueResource()->displayTitle(null, $valueLang)` cuando el valor es un recurso enlazado; si no, devuelve `(string) $value`. Los cuatro puntos de renderizado (`resource-card__eyebrow`, `resource-level-pill`, `resource-row__eyebrow`, `resource-row__level-pill`) usan ahora `$displayValue()` en lugar del cast directo.

---

### QA-027 — `linked-resources.phtml`: `(string)` cast muestra URL en edLevel y about cuando el valor es un recurso enlazado

- **Fecha:** 2026-05-21
- **Severidad:** Alta
- **Área:** Item show → bloque linked-resources
- **Hallazgo:** En `view/common/linked-resources.phtml`, los campos `lrmi:educationalLevel` (`$edLevel`) y `schema:about` (`$about`) se renderizan con `(string) $edLevel` y `(string) $about` directamente. Cuando esos metadatos contienen un **valor de tipo recurso** (enlace a otro ítem Omeka-S), el cast devuelve la URL del ítem en lugar de su título — el mismo bug que corrigió QA-026 en `browse.phtml`. La corrección de QA-026 no se propagó a `linked-resources.phtml`.
- **Reproducción mínima:**
  1. Abrir la ficha de un recurso (`item/show`) que tenga recursos enlazados en el bloque linked-resources.
  2. Que los ítems enlazados tengan `lrmi:educationalLevel` o `schema:about` apuntando a otro ítem Omeka-S (valor tipo recurso, no literal).
  3. Ver que el pill de nivel o el campo "About" dentro de la tarjeta linked-resource muestra la URL del ítem enlazado en lugar de su título.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`view/common/linked-resources.phtml`: añadida closure `$displayValue($value)` (idéntica a la de `browse.phtml`) que comprueba `$value->type() === 'resource'` y llama a `$value->valueResource()->displayTitle(null, $valueLang)` cuando el valor es un recurso enlazado; si no, devuelve `(string) $value`. Los dos puntos de renderizado (`lrmi:educationalLevel` y `schema:about`) usan ahora `$displayValue()` en lugar del cast directo.

---

### QA-028 — `item/show` linked-resources mobile: `property-meta-group` no ocupa el ancho completo de la tarjeta

- **Fecha:** 2026-05-21
- **Severidad:** Media
- **Área:** Item show → bloque linked-resources (375px, 320px)
- **Hallazgo:** En `asset/sass/components/search-results/_search-results-list.scss`, el bloque `@media (max-width: $md)` de la tarjeta compartida actualiza `.property-meta-group` con `order: 6`, `margin-left: 0` y `flex-wrap: wrap`, pero **no redefine `flex`**. El valor heredado del escritorio es `flex: 0 0 auto` (no crece, basis automático), por lo que en mobile el meta-group ocupa solo el ancho de su contenido, dejando espacio vacío a la derecha en la misma línea flex. En la tarjeta linked-resource los pills de nivel, temática y badge quedan alineados a la izquierda como un bloque compacto sin extenderse al ancho total de la tarjeta.
- **Reproducción mínima:**
  1. Abrir `item/show` con el bloque linked-resources en un dispositivo o emulación de 375px.
  2. Observar la fila del `property-meta-group` (nivel · sobre · badge tipo recurso) debajo del thumbnail+título.
  3. Ver que los pills no llegan al borde derecho de la tarjeta, con espacio en blanco a su derecha.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/sass/components/search-results/_search-results-list.scss`: añadido `flex: 0 0 100%` al bloque `@media (max-width: $md)` de `.property-meta-group`. El meta-group ocupa ahora el ancho completo de la tarjeta en mobile y los pills fluyen edge-to-edge con el wrapping interno ya existente.

---

### QA-029 — `item/browse` lista mobile ≤599px: imagen full-width con aspect-ratio 16:10 hace las filas excesivamente altas

- **Fecha:** 2026-05-21
- **Severidad:** Media
- **Área:** Browse lista (375px, 320px)
- **Hallazgo:** En `_resource-list.scss`, a `@media (max-width: 599px)` el `.resource-row` cambia a `flex-direction: column` y `.resource-row__media` pasa a `width: 100%; flex-basis: auto`. La `.resource-row__media-link` tiene `aspect-ratio: 16 / 10`, produciendo un bloque de imagen de ~234px de alto a 375px (200px a 320px). En vista lista mobile, cada fila empieza con una imagen de ancho completo del mismo peso visual que una tarjeta de grid, perdiendo la ventaja de densidad que justifica elegir la vista lista. A 375px con 10 resultados, el scroll es considerablemente mayor que en grid.
- **Reproducción mínima:**
  1. Navegar a `/:site/item?view=list` en un dispositivo o emulación de 375px.
  2. Observar la altura de cada fila de resultado.
  3. Ver que el bloque de imagen encabeza cada fila con ~234px de alto, similar a un card de grid.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/sass/components/resources/_resource-list.scss`: en `@media (max-width: 599px)` se eliminó `flex-direction: column` del `.resource-row` (sustituido por `gap: 12px; align-items: flex-start` para mantener el layout horizontal) y se redujo `.resource-row__media` a `flex: 0 0 80px; width: 80px`. El `.resource-row__media-link` recibe `aspect-ratio: 1` en ese breakpoint, produciendo un thumbnail cuadrado de 80×80px alineado a la izquierda del texto.

---

### QA-030 — `resource-card__eyebrow` y `resource-row__eyebrow` sin control de overflow en mobile

- **Fecha:** 2026-05-21
- **Severidad:** Baja
- **Área:** Browse grid y lista (375px, 320px)
- **Hallazgo:** `.resource-card__eyebrow` (`_resource-grid.scss`) y `.resource-row__eyebrow` (`_resource-list.scss`) no tienen ningún control de overflow: sin `overflow: hidden`, `text-overflow: ellipsis`, ni `max-width` ni `-webkit-line-clamp`. En mobile a 320px (área de contenido ~256–288px), un valor `schema:about` largo (p.ej. "Matemáticas y Ciencias de la Naturaleza") puede renderizarse en 2–3 líneas, expandiendo inconsistentemente la altura de las tarjetas. Por contraste, en search results el mismo valor `schema:about` se trunca a 18ch como pill.
- **Reproducción mínima:**
  1. Asegurarse de que haya ítems con un valor `schema:about` de al menos 25 caracteres.
  2. Navegar a `/:site/item` (grid o lista) en emulación de 320px.
  3. Observar el eyebrow encima del título: el texto largo ocupa más de una línea mientras ítems con valores cortos muestran una sola línea, creando alturas de tarjeta dispares.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/sass/components/resources/_resource-grid.scss` y `_resource-list.scss`: añadidos `overflow: hidden; white-space: nowrap; text-overflow: ellipsis` a `.resource-card__eyebrow` y `.resource-row__eyebrow` respectivamente. El eyebrow queda acotado a una sola línea con elipsis, garantizando alturas de tarjeta uniformes independientemente de la longitud del valor `schema:about`.

---

### QA-031 — `search/search.phtml` mobile: chips de `educationalLevel` y `about` desaparecen en ≤768px

- **Fecha:** 2026-05-21
- **Severidad:** Media
- **Área:** Search results — `search/search.phtml` (375px, 320px)
- **Hallazgo:** En mobile (≤768px), los pills de `lrmi:educationalLevel` y `schema:about` visibles en desktop desaparecen en los cards de resultados de búsqueda. La causa es el bloque de reset añadido en QA-024 en `_search-results-list.scss`: `display: inline; padding: 0; background: none; border: none; ...`. Ese reset fue diseñado para mostrar los valores como texto inline junto a la etiqueta `dt`, pero en la instancia real el resultado es que los chips desaparecen visualmente — el texto plano gris sobre fondo blanco no comunica la naturaleza de etiqueta/categoría que sí transmite el pill. Con el fix de QA-028 (`flex: 0 0 100%` en el meta-group mobile), los chips ya tienen espacio suficiente para fluir y no necesitan el reset.
- **Reproducción mínima:**
  1. Navegar a `/:site/search` en un dispositivo o emulación de 375px.
  2. Buscar cualquier término con resultados que tengan `lrmi:educationalLevel` y `schema:about` informados.
  3. Ver que en los cards de resultados, los pills de nivel y temática no aparecen: el texto plano gris es prácticamente invisible.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/sass/components/search-results/_search-results-list.scss`: el bloque de reset de QA-024 (`display: inline; padding: 0; background: none; border: none; ...`) se sustituye por dos reglas que:
1. Mantienen `dt { display: none }` para las propiedades chip (igual que desktop), anulando el `dt { display: inline }` genérico del meta-group mobile.
2. Restauran `dd { display: inline-block }` para las propiedades chip, anulando el `dd { display: inline }` genérico del meta-group mobile.

El resto del estilo pill (padding, background, border, border-radius, font-size, etc.) se conserva íntegramente desde el bloque desktop — no era necesario redefinirlo.

---

### QA-032 — Header mobile: toggle del menú se solapa con el nombre del sitio en estado no-scrolled

- **Fecha:** 2026-05-21
- **Severidad:** Media
- **Área:** Header mobile (375px, 320px)
- **Hallazgo:** En mobile (< `$lg`), el botón hamburguesa (`.main-navigation__toggle`) usa `margin: 20px auto`, lo que lo centra horizontalmente dentro del bloque `.main-navigation`. Ese bloque tiene `flex: 1 1 auto` y ocupa todo el espacio desde el borde derecho del site-title hasta el extremo derecho del main-bar. El resultado es que el toggle queda flotando en el centro de esa zona en lugar de anclarse al borde derecho del header, solapándose visualmente con el texto del nombre del sitio (especialmente cuando el nombre es largo o el viewport es estrecho). El solapamiento es más pronunciado en estado no-scrolled porque el site-name tiene su tamaño completo; al hacer scroll la fuente reduce a 14px y el solapamiento pasa desapercibido.
- **Reproducción mínima:**
  1. Abrir cualquier página del sitio en emulación de 375px.
  2. Sin hacer scroll, observar el header: el toggle hamburguesa aparece centrado en la mitad derecha del header, solapándose con el nombre del sitio.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`asset/sass/components/navigation/_navigation.scss`: `margin: 20px auto` → `margin: 20px 0 20px auto`. El `margin-left: auto` empuja el toggle al borde derecho del bloque `.main-navigation`, manteniéndolo siempre en el extremo derecho del header y fuera del área del site-title.

---

## Resumen rápido

---

### QA-033 — Header: enlace "Advanced search" apunta a `/item/search` en lugar de `/search`

- **Fecha:** 2026-05-21
- **Severidad:** Alta
- **Área:** Header global (`view/common/header.phtml`)
- **Hallazgo:** El enlace `.main-header__advanced-search` construía su `href` concatenando `$site->url() . '/item/search'`, que es el formulario de búsqueda nativo de Omeka-S. El módulo AdvancedSearch 3.4.60 expone su interfaz en `/search`, no en `/item/search`. El resultado era que el enlace "Búsqueda avanzada" llevaba a un formulario sin facetas ni opciones del módulo.
- **Reproducción mínima:**
  1. Abrir cualquier página del sitio.
  2. Hacer clic en el enlace "Búsqueda avanzada" del top-bar.
  3. El formulario que se carga es el nativo de Omeka-S (`/item/search`), sin facetas ni filtros del módulo AdvancedSearch.
- **Estado:** Cerrado
- **Responsable:** Developer

**Resolución:**
`view/common/header.phtml` línea 10: `$site->url() . '/item/search'` → `$site->url() . '/search'`.

---

## Resumen rápido

| Estado | Conteo |
|--------|--------|
| Abierto | 0 |
| En análisis | 0 |
| En curso | 0 |
| Resuelto | 0 |
| Cerrado | 33 |
| Diferido | 0 |
| Rechazado | 0 |
