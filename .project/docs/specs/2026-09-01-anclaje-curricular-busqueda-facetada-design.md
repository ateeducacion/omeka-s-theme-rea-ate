# Anclaje curricular en la búsqueda facetada

**Fecha:** 2026-09-01
**Rama:** `feature/anclaje-curricular-search`
**Estado:** diseño aprobado, pendiente de plan de implementación

---

## 1. Problema

En los resultados de la búsqueda facetada (`/s/<sitio>/rea`), el bloque
`.property-meta-group` pinta `lrmi:educationalLevel` y `schema:about` como dos
tiras de píldoras independientes, en el orden en que llegan. Como un recurso
tiene varios niveles y varias materias, y las materias se repiten por nombre, la
ficha resulta ilegible.

Medido sobre la instancia de desarrollo (12.609 ítems, sitio `ceiplajares`,
19 recursos de clase `lrmi:LearningResource`):

| Ítem | Niveles | Materias | Píldoras | Repeticiones en `about` |
|---|---|---|---|---|
| 4676 Cuerpos geométricos | 4 | 8 | **12** | Matemáticas ×2, Física y Química ×2 |
| 4674 Figuras Planas | 5 | 5 | 10 | Matemáticas ×4 |
| 40422 Guaydil | 5 | 5 | 10 | Conocimiento del Medio ×4 |
| 40427 Alonso Quesada | 4 | 6 | 10 | Lengua Castellana ×3 |
| 3181 Partes de la célula | 3 | 3 | 6 | Conocimiento del Medio ×3 |

**10 de 19 recursos** tienen títulos repetidos en `about`. En total, **111
píldoras** en una página de 15 resultados.

Tres fallos concretos:

1. **Repetición.** Cuatro píldoras «Matemáticas» idénticas en la misma tarjeta.
2. **Desconexión.** Las dos listas son paralelas y no se puede saber qué nivel
   corresponde a qué materia. No es deducible por posición: el ítem 4676 tiene
   4 niveles y 8 materias.
3. **Truncado.** El `max-width: 18ch` de
   `asset/sass/components/search-results/_search-results-list.scss` deja
   «Matemáticas aplic…» y tres «Conocimiento del …» idénticos. En *Cuerpos
   geométricos* las píldoras además desbordan el borde de la tarjeta.

## 2. Modelo de datos

La relación materia→nivel **ya existe en los datos** y no hay que inferirla.
Cada valor de `schema:about` apunta a un ítem *Asignatura*:

```
Ítem 24676  "Matemáticas"
  dcterms:type            → "Asignatura"
  dcterms:identifier      → "asig:1SMAT", "MAT"
  schema:about (literal)  → "Matemáticas"
  lrmi:educationalLevel   → ítem 24561 "1º ESO"     ← el enlace que buscamos
  dcterms:isPartOf        → ítem 5055 "ESO"

Ítem 24561  "1º ESO"
  dcterms:type            → "Curso"
  schema:inDefinedTermSet → ítem 5055 "ESO"          ← etapa, para ordenar
```

Verificaciones hechas contra la instancia:

- **Los 57 enlaces de materia de los 19 recursos tienen exactamente 1
  `lrmi:educationalLevel`.** La correspondencia materia→nivel es una función
  total; no hay materias con dos niveles ni sin nivel.
- **Título y código de asignatura son biyectivos** (`Matemáticas`↔`MAT`,
  `Matemáticas I`↔`MTI`, …): cero colisiones. Agrupar por título normalizado es
  seguro y no requiere leer `dcterms:identifier`.
- Las etapas (`schema:DefinedTermSet`) llevan `schema:position`:
  Infantil=1, Primaria=2, ESO=3, Bachillerato=4.
- El tipo del valor llega unas veces como `resource` y otras como
  `resource:item`. Hay que aceptar ambos, como ya hace el template del módulo.

Es la misma jerarquía que explota `view/search/facets-list.phtml` para agrupar
las facetas por `DefinedTermSet`.

## 3. Decisiones

| # | Decisión | Alternativas descartadas |
|---|---|---|
| D1 | **Agrupar por materia**, con la etapa implícita en el nombre del nivel («1º Primaria» ya dice la etapa). | Agrupar por etapa › materia › niveles: fiel al currículo pero genera hasta 4 bloques por tarjeta y repite información que el nivel ya lleva. Agrupar por nivel: repetiría `about`, que es justo lo que hay que evitar. |
| D2 | **La materia es rótulo, cada nivel es enlace.** El nivel filtra por `lrmi:educationalLevel`, que es el `metadata-search-link` que el módulo ya genera. | Materia también enlace: exigiría construir URLs con `joiner=or` sobre N ids de asignatura y validarlas contra AdvancedSearch. Solo la materia enlace: perdería el filtrado por nivel que hoy funciona. |
| D3 | **Píldora compuesta de dos zonas**, la derecha partida en un segmento clicable por nivel. | Fila con rejilla materia/niveles: ocupa más alto. Columna a la derecha del título: mantiene el sitio destacado pero obliga a seguir abreviando los nombres largos. |
| D4 | **Intersección con los niveles declarados por el recurso** (ver §4.3). | Mostrar todos los niveles de la asignatura: produce enlaces muertos. |
| D5 | **La fila no lleva rótulo** (2026-09-02, a petición del cliente; antes «Currículo»). El partial omite el `<dt>` cuando el rótulo está vacío, pero la fila conserva la sangría de las demás zonas para que las tres compartan margen izquierdo. | «Currículo» y «Anclaje curricular»: el segundo ata mejor con el bloque de la ficha, pero son 18 caracteres en una columna de 8,5 rem y parte la línea. Se descarta cualquier rótulo: las píldoras se identifican solas. |
| D6 | Los rótulos de zona (`SABERES BÁSICOS`, `RELACIÓN`) ocupan una **columna propia de 8,5 rem**, fuera del flujo, y los chips se sangran ese ancho. | Rótulo en su propia línea con los chips debajo: gana ancho útil pero añade una línea por bloque. Dejarlo como estaba: al envolver, la segunda fila de chips volvía al margen izquierdo y rompía la columna. |

## 4. Algoritmo

### 4.1 Construcción de grupos

1. Recorrer los valores de `schema:about` en orden.
   - **Enlazado**: etiqueta = `displayTitle()` del ítem asignatura;
     niveles candidatos = sus valores de `lrmi:educationalLevel`.
   - **Literal**: etiqueta = la cadena; sin niveles candidatos.
2. Agrupar por etiqueta normalizada: `trim` + colapso de espacios internos +
   `mb_strtolower`. Se conserva la primera forma vista para pintar.

### 4.2 Orden

- Niveles dentro de un grupo: por `(schema:position` de la etapa del curso,
  id del curso`)`. La etapa se alcanza por el `schema:inDefinedTermSet` del
  curso, reutilizando el theme setting **`advancedsearch_termset_property`**
  que ya existe para las facetas agrupadas.
- Grupos: por la clave de su primer nivel **ya ordenado**; a igualdad,
  alfabético por etiqueta normalizada. Los grupos que se quedan sin ningún
  nivel tras la intersección de §4.3 van al final.

Hoy los valores salen en orden de ID, que produce secuencias como
«1º ESO · 2º ESO · 1º Primaria».

### 4.3 Intersección (D4)

De los niveles candidatos de cada asignatura **solo sobreviven los que el
recurso declara en su propio `lrmi:educationalLevel`**.

Sin esta regla, el ítem 5051 *Acebiño* mostraría «Biología y Geología › 4º ESO»
—porque esa asignatura es de 4º ESO— pero el recurso solo declara
1º Bachillerato: al pulsar el enlace, *Acebiño* no aparecería en los
resultados. La intersección garantiza que **todo enlace pintado devuelve al
menos este recurso**.

Corolario: el conjunto de niveles mostrados es exactamente el conjunto de
niveles declarados por el recurso. No se añade ni se oculta información.

### 4.4 Niveles huérfanos

Los niveles declarados que ninguna materia reclama van a una fila final con
rótulo **«Otros niveles»**, visualmente diferenciada.

**Excepción:** si hay exactamente **un** grupo, **sin** niveles, y sobran
huérfanos, los huérfanos se adjuntan a ese grupo. Cubre los ítems 37129 y
37132, donde `about` es un literal suelto sin ítem detrás y el nivel viene del
recurso; separarlos daría «Matemáticas (sin nivel)» + «Otros niveles: 2º ESO».
Con dos o más grupos, o dos o más huérfanos, no se puede desambiguar y van
separados.

### 4.5 Resultado medido

Aplicando las reglas a los 19 recursos: **111 píldoras → 35 filas** (−68 %),
cero materias repetidas, cero enlaces muertos.

| Ítem | Antes | Después | Render |
|---|---|---|---|
| 3181 | 6 | 1 | Conocimiento del Medio › 3º, 4º, 5º Primaria |
| 4674 | 10 | 2 | Matemáticas › 1º, 2º Primaria, 1º, 2º ESO · Matemáticas I › 1º Bach. |
| 4676 | 12 | 6 | Matemáticas › 3º Primaria, 2º ESO · … |
| 5047 | 3 | 2 | Física › 2º Bach. · **Otros niveles** › 2º ESO |
| 5051 | 3 | 2 | Biología, Geología y CC.AA. › 1º Bach. · Biología y Geología › — |
| 37129 | 2 | 1 | Matemáticas › 2º ESO *(regla §4.4)* |

## 5. Arquitectura

### 5.1 Ficheros

| Fichero | Acción |
|---|---|
| `helper/CurriculumAnchor.php` | **Crear.** Resolución y agrupación. |
| `config/theme.ini` | **Modificar.** `helpers[] = "CurriculumAnchor"` + settings de §5.3. |
| `view/search/results.phtml` | **Crear** (override del módulo AdvancedSearch). |
| `view/common/curriculum-anchor.phtml` | **Crear.** Renderizado de las píldoras. |
| `asset/sass/components/search-results/_search-results-list.scss` | **Modificar.** Sustituir el bloque `.property[data-term="lrmi:educationalLevel"], .property[data-term="schema:about"]`. |
| `asset/js/advanced-search-list.js` | **Modificar.** `groupMetaProperties()` deja de mover esas dos propiedades. |
| `test/ReaAteTest/Helper/CurriculumAnchorTest.php` | **Crear.** Ver §8. |

`view/search/results.phtml` es una copia del template del módulo
(`modules/AdvancedSearch/view/search/results.phtml`) con una única
intervención: dentro del bucle `foreach ($properties as $property)`, los
términos de materia y nivel se saltan y, en su lugar, se emite una vez el
partial `common/curriculum-anchor`. El resto de propiedades
(`lrmi:learningResourceType`, `lrmi:teaches`, `dcterms:relation`,
`lrmi:timeRequired`) sigue con el renderizado original.

El tema ya usa este patrón de override con `view/search/facets-list.phtml`.

### 5.2 Estructura del helper

Dos capas, separadas deliberadamente:

- **Capa pura** — recibe arrays planos (materias con sus niveles candidatos,
  niveles declarados, posiciones de etapa) y devuelve las filas ordenadas.
  Sin dependencias de Omeka. Es la que se prueba en PHPUnit.
- **Capa de acceso** — extrae los valores de la `ItemRepresentation`, lanza las
  consultas por lote y llama a la capa pura.

Registro por `helpers[]` en `theme.ini` y clase en `helper/`, siguiendo el
patrón de `SafeUrl`, `CssToken` y `HtmlAllowlist`.

### 5.3 Configuración

Dos settings nuevos en la sección de tema, con el mismo estilo que los
`advancedsearch_*` existentes:

| Setting | Defecto | Uso |
|---|---|---|
| `curriculum_anchor_subject_property` | `schema:about` | Propiedad de materia en el recurso. |
| `curriculum_anchor_level_property` | `lrmi:educationalLevel` | Propiedad de nivel, **tanto en el recurso como en el ítem asignatura** (los datos usan el mismo término en ambos). |

La ordenación por etapa **reutiliza `advancedsearch_termset_property`**
(`schema:inDefinedTermSet`), que ya existe. No se añade setting para eso.

Cadena vacía en cualquiera de los dos desactiva el componente y el template cae
al renderizado normal del módulo.

### 5.4 Coste

Tres consultas por página de resultados, todas por ID indexado:

1. Ítems asignatura referenciados por los `about` de la página.
2. Ítems curso referenciados (para su `schema:inDefinedTermSet`).
3. Los ~5 `DefinedTermSet` (para su `schema:position`).

Frente a las ~80 consultas que saldrían resolviendo
`$value->valueResource()->value(...)` ítem a ítem. Es el mismo patrón de lotes
de `facets-list.phtml`.

### 5.5 Degradación

El helper devuelve `null` —y `results.phtml` renderiza las dos propiedades como
hoy— en dos casos: **cualquiera de los dos settings vacío**, o **el recurso no
tiene ningún valor de materia**. El tema tiene que seguir siendo instalable en
sitios sin este modelo de datos.

Un recurso **con materias pero sin niveles declarados no es un caso de
degradación**: sus materias se pintan como píldoras simples, sin zona derecha,
exactamente igual que el grupo sin niveles de §6. Devolver `null` ahí sería
incoherente con esa regla y, además, peor: la ruta de degradación renderiza las
propiedades crudas, que se apoyan en el bloque de estilos de píldora heredado.

Por eso el bloque SCSS existente de `.property[data-term="lrmi:educationalLevel"]`
y `.property[data-term="schema:about"]` **se conserva**: es la ruta de
degradación, no CSS muerto. Solo se le retira el `max-width: 18ch`, que es el
truncado defectuoso. Convive sin solaparse con el componente nuevo, que se
selecciona por clase (`.property--curriculum-anchor`) y no lleva `data-term`.

## 6. Markup y estilo

Sin `data-term` en el `div` exterior: `groupMetaProperties()` en
`asset/js/advanced-search-list.js` selecciona estrictamente por
`[data-term=…]`, así que devolver ese atributo arrastraría el bloque al grupo
de píldoras junto al título y rompería el layout de fila completa.

```html
<div class="property property--curriculum-anchor">
  <!-- sin <dt>: el partial lo omite cuando el rótulo está vacío (D5) -->
  <dd class="curriculum-anchor__group">
    <span class="curriculum-anchor__subject">Matemáticas</span>
    <a class="curriculum-anchor__level" href="…">1º Primaria</a>
    <a class="curriculum-anchor__level" href="…">1º ESO</a>
  </dd>
  <dd class="curriculum-anchor__group curriculum-anchor__group--orphan">
    <span class="curriculum-anchor__subject">Otros niveles</span>
    <a class="curriculum-anchor__level" href="…">2º ESO</a>
  </dd>
</div>
```

- El `<dd>` es la píldora compuesta: `display: inline-flex`, borde y radio
  `pill`. La zona de materia va sobre `--ate-surface-soft`; cada nivel es un
  `<a>` separado por `border-left`, con su propio `:hover`.
- **Sin `max-width` en la materia.** Si no cabe, la píldora envuelve el nombre
  en dos líneas (`flex-wrap` + `white-space: normal`) en lugar de truncarlo.
- Los `href` de nivel son los que ya genera el módulo; no se construyen URLs
  nuevas.
- Un grupo que se queda sin ningún nivel tras la intersección (ítem 5051) se
  pinta como píldora simple, solo con la zona de materia y sin separador ni
  zona derecha. No se emite ningún marcador de «sin nivel».
- Móvil (`< $md`): las píldoras apilan una por línea y el rótulo pasa arriba.
  Los segmentos mantienen área táctil suficiente.
- El `.property-meta-group` de la derecha del título se queda con tipo de
  recurso y duración, que son de valor único y sí caben.

## 7. Alcance

**Dentro:** resultados de la búsqueda facetada.

**Fuera:** `view/common/linked-resources.phtml` (comparte el nombre de clase
`.property-meta-group` y quedará divergiendo a propósito), el bloque de anclaje
curricular de la ficha del ítem, y `item/browse`. Si el componente cuaja, se
portan después reutilizando el helper.

## 8. Pruebas

**PHPUnit** sobre la capa pura, un caso por regla y por caso borde real:

| Caso | Ítem de referencia | Qué verifica |
|---|---|---|
| Agrupación simple | 3181 | 3 materias idénticas → 1 fila, 3 niveles |
| Deduplicación con dos etiquetas | 4674 | «Matemáticas» ×4 + «Matemáticas I» → 2 filas |
| Muchas materias | 4676 | 12 valores → 6 filas, sin pérdida |
| Nivel huérfano | 5047 | fila «Otros niveles» con 2º ESO |
| Intersección | 5051 | «Biología y Geología» sin nivel; no aparece 4º ESO |
| Literal + huérfano único | 37129 | regla §4.4: una sola fila |
| Orden por etapa | 4674 | Primaria antes que ESO antes que Bachillerato |
| Degradación | — | sin materias enlazadas → `null` |

**Manual**, contra la instancia Docker: comprobar en `/s/ceiplajares/rea` que
ningún enlace de nivel devuelve 0 resultados, y que la tarjeta de *Cuerpos
geométricos* ya no desborda.

## 9. Dependencias y riesgos

- **La suite PHPUnit (`test/`) vive hoy en la rama `security/audit-fixes`, no
  en `master`.** Esta rama sale de `master`, así que o se rebasa sobre la de
  seguridad una vez fusionada, o hay que crear el andamiaje de `test/` aquí.
  Decidir antes de implementar.
- **Override de template del módulo.** `view/search/results.phtml` queda atado
  a la versión de AdvancedSearch. Mismo riesgo que ya se asumió con
  `facets-list.phtml`. Conviene anotar en cabecera la versión del módulo desde
  la que se copió.
- **`results.properties` es configuración de la instancia**, no del tema. Si un
  administrador quita `schema:about` de la lista, el componente no se pinta.
  Documentarlo.
- El modelo de datos verificado es el del volcado de desarrollo. Antes de
  producción conviene reejecutar las comprobaciones de §2 contra el catálogo
  real, que tiene muchos más recursos.
