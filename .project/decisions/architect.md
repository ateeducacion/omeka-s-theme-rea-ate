# Decisiones del Arquitecto

---

## [2026-05-01] CORREGIDA — Pipeline de release (backlog #6)

### Estado
ACEPTADA con corrección menor aplicada.

### Revisión realizada
Se revisó el pipeline `.github/workflows/release.yml` contra el `Makefile` y `config/theme.ini`.

**Hallazgos correctos (sin cambio necesario):**
- `config/theme.ini` contiene el campo `version = "0.3.0"` en la sección `[info]`. El `sed` del workflow lo actualiza correctamente en Ubuntu (GNU sed, sin espacio tras `-i`).
- El target `make package VERSION=x.y.z` existe y está completo: excluye `.git`, `.github`, `.project`, `node_modules`, `asset/sass`, `asset/css/*.map`, `package.json`, `package-lock.json`, `gulpfile.js`, `AGENTS.md`, `DESIGN.md`. Incluye todo lo necesario para producción (`asset/css`, `asset/js`, `asset/img`, `config`, `helper`, `language`, `view`, `composer.json`, `theme.jpg`).
- `npm run build` invoca `gulp css` que compila Sass → CSS en `asset/css/`. Correctamente situado antes del `make package`.
- `softprops/action-gh-release@v2` es la versión actual correcta.
- La doble actualización de `theme.ini` (paso workflow + Makefile interno) es redundante pero inofensiva en CI (entorno efímero).

**Gap encontrado y corregido:**
El job `build_and_upload` carecía del bloque `permissions: contents: write`. GitHub Actions usa permisos de token de solo lectura por defecto en repositorios de organizaciones con la política `Restrict permissions of GITHUB_TOKEN`. Sin ese bloque, `softprops/action-gh-release@v2` falla con 403 al intentar crear el release, aunque el ZIP ya esté generado.

**Corrección aplicada:**
```yaml
jobs:
  build_and_upload:
    runs-on: ubuntu-latest
    permissions:
      contents: write   # ← añadido
```

### Consecuencias
El pipeline cubre el ciclo completo: `git tag v0.4.0 && git push --tags` → checkout → compile Sass → `make package` (actualiza version en theme.ini, genera ZIP, excluye archivos de desarrollo) → GitHub Release con ZIP adjunto y release notes automáticas.

### Dependencias
- Desbloquea: entrega a producción mediante tag de versión.

---

## [2026-04-29] ACEPTADA

### Decisión
Bloque "Anclaje Curricular" implementado mediante el bloque `block` de BlockPlus,
sobreescribiendo su plantilla desde el tema.

### Contexto
Se necesita mostrar en la ficha de cada ítem las propiedades LRMI de alineación
curricular (`schema:educationalLevel`, `schema:about`, `schema:assesses`,
`schema:teaches`). El bloque debe:
- Aparecer automáticamente en todos los ítems del sitio.
- Ser posicionable por el gestor en "Configure resource pages".
- Mostrar solo las propiedades con valor en ese ítem.
- Distinguir valores literales de ítems vinculados (DefinedTerm LOMLOE).
- Ser configurable desde "Edit Theme Settings" sin tocar código.

BlockPlus está instalado y activo. Expone un Resource Page Block genérico
llamado `block` (clave de servicio: `block`) cuya plantilla puede sobreescribirse
en `view/common/resource-page-block-layout/block.phtml` desde el tema, sin
necesidad de registrar ningún servicio PHP propio.

### Alternativas consideradas

- **Resource Page Block propio** (`Module.php` + clase PHP + PSR-4 en
  `composer.json`): descartada. Los temas Omeka-S no inyectan en el service
  manager de Laminas; requiere `composer dump-autoload` en producción; añade
  complejidad innecesaria cuando BlockPlus ya ofrece el punto de extensión.
  Una implementación previa con este enfoque existe en el historial del
  repositorio y debe considerarse eliminada.

- **Override de `show.phtml`**: descartada. Hardcodea las propiedades y la
  región; no configurable por el gestor sin modificar código.

- **Módulo Omeka-S propio (`ModuloATE`)**: descartada para esta fase. Justificada
  solo si en el futuro se necesita reutilizar el bloque `block` de BlockPlus para
  otro propósito en el mismo sitio.

### Consecuencias

**Desarrollador — ficheros a crear:**

| Fichero | Acción |
|---------|--------|
| `view/common/resource-page-block-layout/block.phtml` | Crear — plantilla del bloque; lee `anclaje_div_class` y `anclaje_properties` de theme settings; renderiza propiedades con valor; distingue literales vs. recursos vinculados |

**Desarrollador — ficheros a modificar:**

| Fichero | Cambio |
|---------|--------|
| `config/theme.ini` | Añadir `element_groups.anclaje`, `elements.anclaje_div_class` (Text, default `anclaje-curricular`) y `elements.anclaje_properties` (Textarea, 6 filas, defaults con las 4 propiedades LRMI); añadir `resource_page_blocks.items.main[] = "block"` y `resource_page_blocks.items.right[] = "block"` |

**Desarrollador — no hay ficheros que eliminar** (la implementación anterior basada
en `Module.php` ya fue revertida del árbol de trabajo).

**Diseñador — sin cambios requeridos.** Los estilos del bloque ya existen en
`asset/sass/components/item-show/_item-show.scss` (selector `.values-group.div-class`)
y en la futura extracción a `_anclaje-curricular.scss`. La clase CSS del contenedor
es configurable vía theme setting; el valor por defecto `anclaje-curricular` debe
estar cubierto por el Sass del tema.

**Tradeoff conocido:** el bloque `block` de BlockPlus queda dedicado al Anclaje
Curricular en este tema. Si en el futuro se necesita usar `block` para un propósito
distinto en la misma instalación, la plantilla `block.phtml` deberá añadir lógica
de contexto (p. ej. leer una propiedad del ítem o un parámetro de la URL) para
distinguir los casos, o deberá crearse un módulo Omeka-S propio (`ModuloATE`) que
registre un bloque con clave diferente.

### Dependencias

- Desbloquea: Desarrollador para crear `block.phtml` y actualizar `theme.ini`.
- Sin bloqueos hacia Diseñador ni Orquestador.

---

## [2026-04-27] ACEPTADA

### Decisión
Inventario del tema base: plantillas, assets y convenciones existentes.

### Contexto
Antes de cualquier implementación es necesario conocer exactamente qué 
tiene el tema base para decidir qué se modifica, qué se extiende y qué 
se crea desde cero.

**Plantillas existentes (`view/`):**

| Plantilla | Propósito | Acción |
|-----------|-----------|--------|
| `layout/layout.phtml` | Layout maestro: head, body, CSS vars dinámicas | Modificar — añadir fuentes, ajustar CSS vars por defecto |
| `common/header.phtml` | Cabecera: top-bar + main-bar | Modificar — reorganizar search, ajustar estructura |
| `common/footer.phtml` | Footer configurable | Sin cambios (decisión cliente) |
| `common/banner.phtml` | Banner opcional de portada | Sin cambios por ahora |
| `common/menu-drawer.phtml` | Drawer móvil | Sin cambios |
| `search/search.phtml` | Página de resultados Advanced Search | Sin cambios — ya integra el módulo correctamente |
| `omeka/site/item/show.phtml` | Ficha de recurso | Revisar en fase posterior |
| `common/advanced-search/*.phtml` | Parciales de formulario native search | Sin cambios |

**Assets Sass (`asset/sass/`):**

| Directorio | Contenido | Acción |
|------------|-----------|--------|
| `abstracts/variables/_colors.scss` | Variables de color — apuntan a CSS vars `--primary` etc. | Modificar — añadir tokens ATE |
| `abstracts/variables/_typography.scss` | Fuentes y tamaños — actualmente Open Sans | Modificar — cambiar a Lato + Source Serif 4 |
| `abstracts/variables/_breakpoints.scss` | `$sm/$md/$lg/$xl/$xxl` | Sin cambios — breakpoints compatibles |
| `abstracts/variables/_layout.scss` | `$wrap-max-width: 1300px` | Sin cambios — suficiente |
| `components/header/_header.scss` | Estilos del header | Modificar — scroll behavior, reorganización |
| `components/advanced-search/_advanced-search.scss` | Estilos de facetas | Modificar — aplicar design tokens ATE |

**Config (`config/theme.ini`):**
- Versión Omeka-S: `^4.1.0` ✓ (producción es 4.2)
- Colores configurables desde admin: `primary_color`, `secondary_color`, `accent_color`
- Footer: ya tiene todas las opciones necesarias (logo, descripción, menú, copyright)

### Alternativas consideradas
N/A — entrada de inventario, no de decisión de diseño.

### Consecuencias
- El Desarrollador sabe exactamente qué modificar y qué dejar intacto.
- Las decisiones 2–5 parten de este inventario.

### Dependencias
- Desbloquea: decisiones 2, 3, 4, 5 del Arquitecto.
- Desbloquea: decisión de tokens CSS del Diseñador (tiene el inventario de vars existentes).

---

## [2026-04-27] ACEPTADA

### Decisión
Estructura del header: top-bar institucional + main-bar sticky con búsqueda siempre visible.

### Contexto
El requisito exige que la barra de búsqueda esté visible tanto en el 
estado inicial (ambas bandas visibles) como en el estado sticky (solo 
main-bar visible). El header actual tiene la búsqueda en `__top-bar`, 
que desaparece al hacer sticky.

**Solución adoptada:**

Mover el bloque `__search-form` (y el enlace "Advanced search") de 
`__top-bar` a `__main-bar`. La `__top-bar` queda solo con el logo 
institucional del GobCan.

**Comportamiento:**
- **Estado inicial (scroll = 0):** Ambas bandas visibles. Top-bar muestra 
  logo GobCan (~80px). Main-bar muestra logo del site + nav + búsqueda (68px).
- **Estado scrolled (scroll > umbral):** JS añade clase `header-scrolled` 
  al `<header>`. CSS aplica `max-height: 0; overflow: hidden` a `__top-bar` 
  con transición de 0.25s. Main-bar permanece fija con logo + nav + búsqueda.
- **Umbral de scroll:** altura de `__top-bar` (~80px). Se lee dinámicamente 
  con JS para no hardcodear el valor.

**Archivos a modificar:**
1. `view/common/header.phtml` — mover `__search-form` a `__main-bar`
2. `asset/sass/components/header/_header.scss` — CSS del comportamiento scroll
3. `asset/js/script.js` — añadir listener de scroll que añade/quita `.header-scrolled`

### Alternativas consideradas
- **Duplicar el search form** (una copia en cada barra): más complejo, dos 
  formularios en DOM. Descartado.
- **Header completamente fijo desde el inicio** (sin top-bar que colapsa): 
  pierde la banda institucional del GobCan visible al entrar. Descartado por 
  requisito de identidad institucional.
- **CSS puro con `position: sticky` por banda:** la top-bar con `scroll` 
  nativo requiere JS igualmente para la clase; CSS puro no puede detectar 
  scroll relativo de forma compatible. Descartado.

### Consecuencias
- El Desarrollador modifica `header.phtml` (mover bloque search) y 
  `_header.scss` (añadir lógica `.header-scrolled`).
- El Desarrollador añade lógica de scroll en `asset/js/script.js`.
- El Diseñador debe especificar el diseño visual de ambos estados.

### Dependencias
- Desbloquea: implementación del header por el Desarrollador.
- Requiere: decisión del Diseñador sobre estados visuales del header.

---

## [2026-04-27] ACEPTADA

### Decisión
Estrategia de integración con Advanced Search 3.4.60.

### Contexto
El módulo Advanced Search (Daniel-KM) está instalado en versión 3.4.60. 
El tema ya tiene `view/search/search.phtml` que integra correctamente el 
módulo. Las 5 facetas confirmadas se configuran desde el admin de Omeka-S 
en la configuración del Search Config — no en plantillas del tema.

**Estrategia:**

1. **Formulario de búsqueda en header:** Usar el formulario nativo de Omeka-S 
   (`common/search-form`) que ya existe en el tema. Este formulario se 
   configura en Omeka-S para que su `action` apunte a la página de búsqueda 
   avanzada del módulo. No se sobrescribe ninguna plantilla del módulo para 
   el header.

2. **Página de resultados:** `view/search/search.phtml` ya está en el tema 
   y funciona. Solo requiere ajustes de estilo (tokens ATE).

3. **Facetas:** Las 5 facetas confirmadas (`lrmi:EducationalLevel`, 
   `schema:about`, `lrmi:LearningResourceType`, `lrmi:timeRequired`, 
   `dcterms:relation`) se configuran en el admin de Advanced Search. 
   El tema solo aplica estilos al sidebar `aside.search-facets` y al 
   componente `search/facets-list`.

4. **Plantillas del módulo a sobrescribir (si necesario):** Solo si el 
   renderizado por defecto del módulo no se adapta al diseño. En ese caso 
   se crean en `view/search/` siguiendo la convención de Omeka-S para 
   override de plantillas de módulos.

**No se necesita:**
- Motor de búsqueda externo (Solr/ES): índice interno es suficiente.
- Override del formulario de Advanced Search para el header.

### Alternativas consideradas
- **Inyectar el formulario de Advanced Search directamente en el header** 
  via `$this->partial(...)`: requiere que el Search Config esté disponible 
  en el contexto del header. Más frágil. Descartado en favor del formulario 
  nativo de Omeka-S con redirección.
- **Crear una barra de búsqueda con JS + AJAX**: fuera de scope, complejidad 
  innecesaria. Descartado.

### Consecuencias
- La configuración de facetas es trabajo del administrador de Omeka-S 
  (no del desarrollador del tema).
- El Desarrollador solo estiliza el sidebar de facetas y la página de 
  resultados.
- El formulario de Omeka-S debe configurarse en admin para apuntar a la 
  URL de Advanced Search.

### Dependencias
- Requiere: configuración del Search Config en admin de Omeka-S (fuera del 
  desarrollo del tema).
- Desbloquea: implementación de estilos de search/facets.

---

## [2026-04-27] ACEPTADA

### Decisión
Convenciones de nombrado de plantillas, assets y CSS.

### Contexto
El tema base usa convenciones BEM para CSS y la estructura de directorios 
estándar de Omeka-S para plantillas. Es necesario definir qué convenciones 
se heredan y qué se añade.

**Convenciones adoptadas:**

**CSS / Sass:**
- Continuar BEM existente: `.main-header__*`, `.search-facets__*` etc.
- CSS custom properties del sistema ATE: prefijo `--ate-` para diferenciarlos 
  de las CSS vars del mecanismo de colores del tema (`--primary`, `--secondary`, 
  `--accent`). Ejemplo: `--ate-color-brand-yellow`, `--ate-font-heading`.
- Las vars `--primary`, `--secondary`, `--accent` se mantienen porque el 
  mecanismo de `layout.phtml` + `theme.ini` las gestiona. Sus valores por 
  defecto en `layout.phtml` se cambian a los colores ATE.

**Plantillas:**
- Overrides de Omeka-S core: `view/omeka/site/*/`
- Overrides de módulos: `view/{nombre-modulo}/{ruta}/` (ej: `view/search/`)
- Parciales propios del tema: `view/common/`
- Sin crear subdirectorios nuevos salvo que sea necesario.

**JavaScript:**
- Vanilla JS / módulos ES. Sin nuevas dependencias.
- Lógica nueva en `asset/js/script.js` (ya existe). Si crece mucho, 
  crear `asset/js/header.js` separado.

**Nuevos archivos Sass:**
- Nuevos componentes: `asset/sass/components/{nombre}/_nombre.scss`
- Importar desde `asset/sass/components/_components.scss`

### Alternativas consideradas
- **Prefijo `--freedom-` para CSS vars**: confunde con el tema original. 
  Descartado en favor de `--ate-`.
- **Crear `asset/js/header.js` separado desde el inicio**: prematuro si 
  la lógica de scroll es pequeña. Se crea si la lógica supera ~50 líneas.

### Consecuencias
- El Desarrollador conoce exactamente dónde crear cada archivo.
- Los nombres `--ate-*` no colisionan con el sistema existente.

### Dependencias
- Desbloquea: toda la implementación del Desarrollador.

---

## [2026-04-27] ACEPTADA

### Decisión
Mecanismo de release: GitHub Actions.

### Contexto
El cliente ha confirmado que habrá un pipeline de GitHub Actions. 
Es necesario definir qué empaqueta y qué excluye.

**Workflow adoptado:**

Trigger: push de tag `v*.*.*` (ej: `v0.4.0`).

Artefacto: ZIP del tema listo para subir a Omeka-S, nombrado 
`freedom-ate-{version}.zip`.

**Incluye:**
```
asset/css/          (compilado, no sass source)
asset/js/
asset/img/
config/
helper/
language/
view/
composer.json
theme.jpg
```

**Excluye:**
```
.project/
asset/sass/
node_modules/
package.json
package-lock.json
gulpfile.js
compile-translations.*
*.map
.git/
.gitignore
AGENTS.md
DESIGN.md
README.md  (opcional: puede incluirse)
```

**Pasos del workflow:**
1. Checkout
2. `npm ci && npm run build` (compila Sass → CSS)
3. Crear ZIP con las inclusiones definidas
4. Upload del ZIP como release asset en GitHub Releases

El archivo del workflow: `.github/workflows/release.yml`.

### Alternativas consideradas
- **Script local de release**: más simple pero no automatizado. Descartado.
- **Incluir las fuentes Sass en el ZIP**: no necesario para producción. 
  Descartado.

### Consecuencias
- El Desarrollador crea `.github/workflows/release.yml`.
- El CSS se compila en CI, no se commitea CSS compilado a mano.
- Los tags de versión son la única forma de hacer releases.

### Dependencias
- Desbloquea: automatización del ciclo de release.

---

## [2026-05-05] PROPUESTA

### Decisión
Filtro de `item-set/browse`: la fuente de verdad serán los metadatos del propio `item set`, no una agregación dinámica desde sus ítems. El tema debe alinearse con el modelo del proyecto usando propiedades preferentes `lrmi:` / `schema:` y mantener compatibilidad de lectura con los nombres usados en la implementación actual solo como fallback.

### Contexto
El hallazgo `QA-001` abierto por el Orquestador detecta que el filtro de colecciones no funciona en la instancia real. La causa no es solo un bug de interfaz: la implementación actual en `view/omeka/site/item-set/browse.phtml` asume que cada colección expone `dcterms:educationLevel`, `lom:educationalLevel` y `dcterms:subject`, mientras que el modelo de metadatos del proyecto documenta `lrmi:educationalLevel` y `schema:about` como propiedades de referencia para los recursos educativos.

Desde arquitectura, la decisión relevante no es solo qué propiedades leer, sino también dónde vive esa información. Para una página de browse de colecciones, agregar metadatos desde todos los ítems hijos en tiempo de render complica el template, añade coste de API por colección y hace el filtro dependiente del contenido interno en lugar de la curación editorial de la colección. En cambio, tratar el `item set` como entidad editorial con sus propios metadatos mantiene el browse de colecciones estable, predecible y barato de renderizar.

### Alternativas consideradas
- **Agregar valores desde los ítems de cada colección en tiempo de render**: descartado. Aumenta complejidad, coste de consultas y ambigüedad cuando una colección contiene ítems con taxonomías heterogéneas.
- **Mantener solo `dcterms:educationLevel`, `lom:educationalLevel` y `dcterms:subject`**: descartado. Consolida una desalineación con el modelo de metadatos documentado del proyecto y con otras vistas del tema.
- **Hacer el filtro totalmente configurable desde `theme.ini`**: descartado para este ciclo. Flexible, pero introduce complejidad administrativa innecesaria para un caso que puede resolverse con una convención arquitectónica clara.

### Consecuencias
- El `item set` pasa a ser la entidad fuente de verdad del filtro de colecciones.
- El Desarrollador debe actualizar `view/omeka/site/item-set/browse.phtml` para leer propiedades con este orden de preferencia:
  - `Etapa`: `lrmi:educationalLevel`, fallback `dcterms:educationLevel`.
  - `Nivel`: `lom:educationalLevel` solo como fallback legacy o si la instancia lo usa realmente; si no hay valores en ninguna colección, el select no debe renderizarse.
  - `Temática`: `schema:about`, fallback `dcterms:subject`.
- El filtrado client-side sigue siendo válido: solo cambia la extracción de valores y la visibilidad de selects vacíos.
- El Desarrollador debe ajustar también los `data-*` de cada tarjeta y la construcción de opciones para que usen la misma jerarquía de propiedades.
- La carga editorial mínima para que el filtro funcione queda explícita: las colecciones deben rellenar esos metadatos en el propio `item set`.
- Si el equipo quiere que el filtro refleje automáticamente taxonomías agregadas de los ítems, eso requerirá una decisión posterior distinta, probablemente fuera del template y más próxima a un módulo o proceso de sincronización.

### Dependencias
- Requiere: hallazgo `QA-001` documentado en `.project/docs/qa-findings.md`.
- Requiere: validación del Orquestador para pasar de `PROPUESTA` a `ACEPTADA`.
- Desbloquea: corrección del filtro de `item-set/browse` por el Desarrollador una vez validada.

---

## [2026-05-07] ACEPTADA — Patrón PHP para `project-funding.phtml` (backlog #8)

### Decisión
El bloque de visibilidad institucional se implementa como partial `view/common/project-funding.phtml`. La navegación `schema:isPartOf` → ítem Project y la resolución del logo se realizan en `item/show.phtml` antes de invocar el partial. El partial recibe el `$projectItem` ya resuelto y es pasivo: solo renderiza o no renderiza.

### Contexto
El requisito #8 exige mostrar en `item/show` un bloque con logo, texto legal y enlace del proyecto cofinanciador cuando el REA tiene `schema:isPartOf` apuntando a un ítem de clase `schema:Project`. El patrón debe:
- No añadir coste de API si no hay proyecto vinculado.
- Ser reutilizable desde otras plantillas futuras.
- Ser compatible con Omeka-S 4.2 sin registrar servicios PHP propios.
- Resolver el logo desde el modelo de datos documentado (`schema:logo` como media adjunta).

### Patrón adoptado

**1. Resolución en `item/show.phtml` (caller)**

```php
$projectItem = null;
$isPartOfValue = $item->value('schema:isPartOf');
if ($isPartOfValue && $isPartOfValue->type() === 'resource') {
    $projectItem = $isPartOfValue->valueResource(); // ItemRepresentation
}
echo $this->partial('common/project-funding', ['projectItem' => $projectItem]);
```

La comprobación `$value->type() === 'resource'` es necesaria: `schema:isPartOf` podría tener un valor URI o literal si el dato fue importado con inconsistencias; solo los valores de tipo `resource` tienen `valueResource()`.

**2. Lógica interna de `project-funding.phtml`**

```php
/** @var \Omeka\Api\Representation\ItemRepresentation|null $projectItem */
if (!isset($projectItem) || $projectItem === null) { return; }

$name        = $projectItem->value('schema:name');
$description = $projectItem->value('schema:description');
$url         = $projectItem->value('schema:url');

// Logo: schema:logo como recurso vinculado (Media) con fallback a primaryMedia
$logoUrl = null;
$logoValue = $projectItem->value('schema:logo');
if ($logoValue && $logoValue->type() === 'resource') {
    $logoMedia = $logoValue->valueResource();
    $logoUrl   = $logoMedia->thumbnailUrl('large') ?: $logoMedia->originalUrl();
}
if (!$logoUrl) {
    $primary = $projectItem->primaryMedia();
    if ($primary) {
        $logoUrl = $primary->thumbnailUrl('large') ?: $primary->originalUrl();
    }
}

if (!$name && !$logoUrl) { return; } // sin datos mínimos, no renderizar
```

**Resolución del logo — dos capas:**

| Capa | Condición | Método |
|------|-----------|--------|
| Principal | `schema:logo` es un valor de tipo `resource` que apunta a un Media | `$logoValue->valueResource()->thumbnailUrl('large')` |
| Fallback | No hay `schema:logo` vinculado o es URI/literal | `$projectItem->primaryMedia()->thumbnailUrl('large')` |

El fallback con `primaryMedia()` cubre el caso habitual en Omeka-S donde el logo se sube como adjunto del ítem sin propiedad vinculante explícita, evitando que el bloque quede en blanco por una diferencia de carga editorial.

**3. Estructura HTML mínima requerida (a completar por el Diseñador)**

```html
<aside class="project-funding">
    <!-- logo si $logoUrl -->
    <!-- $name como heading o texto -->
    <!-- $description como párrafo legal -->
    <!-- $url como enlace externo -->
</aside>
```

La clase raíz es `.project-funding`. El Diseñador decide la composición interna.

### Alternativas descartadas

- **Resolver `schema:isPartOf` dentro del partial**: el partial quedaría acoplado a `$item` (el REA), que no siempre estará disponible en futuros callers. Descartado.
- **Inyectar solo el ID del proyecto y llamar a `$api->read()` en el partial**: introduce una llamada de API dentro de un partial, invisible para el caller. Descartado.
- **Usar `$item->linkedResources('schema:isPartOf')` (si existiese)**: no existe como método público en `ItemRepresentation` en Omeka-S 4.2. El patrón `$value->valueResource()` es el único oficial.
- **Resolver logo solo por URI en `schema:logo`**: el modelo documenta media adjunta, no URI externa. Usar URI como único mecanismo obligaría al editor a gestionar URLs externas para un activo que ya está en el repositorio.

### Consecuencias

**Desarrollador — ficheros a crear:**

| Fichero | Acción |
|---------|--------|
| `view/common/project-funding.phtml` | Crear — partial con lógica de resolución de logo y renderizado condicional |

**Desarrollador — ficheros a modificar:**

| Fichero | Cambio |
|---------|--------|
| `view/omeka/site/item/show.phtml` | Añadir resolución de `schema:isPartOf` e invocación del partial antes del cierre de la zona de metadatos |

**Diseñador — spec visual requerida antes de implementar.**
El partial no se implementa hasta tener la decisión del Diseñador sobre layout, tokens y posición en `item/show`.

### Dependencias
- Desbloquea: decisión del Diseñador (spec visual del bloque `.project-funding`).
- Bloqueado por: decisión del Diseñador — Desarrollador no implementa hasta tenerla.
- Sin dependencias de módulos externos; usa únicamente la API de representación estándar de Omeka-S 4.2.
