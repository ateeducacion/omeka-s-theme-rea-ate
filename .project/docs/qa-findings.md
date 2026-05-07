# Registro de hallazgos QA

_Documento operativo del ciclo 3. Fuente de trabajo para registrar incidencias detectadas en QA sobre la instancia real._

Última actualización: 2026-05-07

---

## Alcance actual

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
- **Estado:** Resuelto
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
- **Estado:** Resuelto
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
- **Estado:** Resuelto
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
- **Estado:** En análisis
- **Responsable:** Diseñador

**Decisión pendiente del Diseñador:**
Determinar si los fondos y colores de texto de las tarjetas de audiencia deben quedar completamente desacoplados de los colores configurables del tema (usando solo tokens `--ate-*` fijos), o si debe existir algún mecanismo de garantía de contraste cuando el administrador personaliza los colores del sitio. Ver decisión del Orquestador [2026-05-07].

---

## Resumen rápido

| Estado | Conteo |
|--------|--------|
| Abierto | 0 |
| En análisis | 1 |
| En curso | 0 |
| Resuelto | 3 |
| Cerrado | 9 |
| Diferido | 0 |
| Rechazado | 0 |
