# JavaScript con progressive enhancement

## Propósito
Referencia de los principios y patrones de JavaScript que aplica el proyecto REA ATE, donde toda funcionalidad crítica debe ser accesible sin JS y el JS solo añade mejoras de experiencia.

## Cuándo invocar esta skill
- Al escribir cualquier script nuevo para el tema.
- Al decidir si una funcionalidad requiere JS o puede hacerse en PHP/CSS.
- Al registrar un script en Omeka-S desde una plantilla.
- Al pasar datos de PHP a JavaScript.

## Referencia técnica

### Principio base
La página es **funcional sin JS**. Los filtros de colecciones, por ejemplo, deben poder funcionar como formularios HTML estándar si JS no carga. JS añade filtrado client-side, animaciones y comportamiento dinámico, pero no debe ser el único camino para completar una tarea principal.

### Patrón de inicialización

```js
// Todos los scripts del tema usan DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    initFiltros();
    initLrtBadges();
});

function initFiltros() {
    const filterBar = document.getElementById('collection-filter-bar');
    if (!filterBar) return; // guard: salir si el elemento no existe en esta página
    // ...
}
```

Regla: **siempre** poner un guard al inicio de cada función que busque elementos del DOM. Si el elemento no existe, retornar silenciosamente.

### Registrar scripts en Omeka-S

**Desde la plantilla donde se usa (preferido):**
```php
// En la plantilla .phtml que necesita el script
$this->headScript()->appendFile(
    $this->assetUrl('js/item-set-browse.js', 'rea-ate'),
    'text/javascript',
    ['defer' => true]
);
```

**Desde layout.phtml (scripts globales):**
```php
$this->headScript()->appendFile(
    $this->assetUrl('js/global.js', 'rea-ate'),
    'text/javascript',
    ['defer' => true]
);
```

Usar siempre `defer` (o `async` si el script es independiente del DOM) para no bloquear el renderizado.

### Módulos ES6 vs scripts globales

Omeka-S no tiene bundler por defecto. El tema usa **scripts globales simples** (no módulos ES6 con `import/export`) para evitar dependencias de build para JS.

```js
// INCORRECTO para este proyecto (requeriría bundler)
import { normalizeLrt } from './utils.js';

// CORRECTO — funciones en el scope global del archivo o IIFE
(function () {
    function normalizeLrt(label) { /* ... */ }
    // uso interno al archivo
})();
```

Si en el futuro se introduce un bundler (esbuild, rollup), actualizar esta skill.

### Pasar datos de PHP a JavaScript

Usar atributos `data-*` en el HTML generado por PHP. Nunca usar `<script>var x = <?= json_encode($phpVar) ?></script>` directamente en la plantilla si puede evitarse.

```php
// En la plantilla PHP
<div id="collections-grid"
     data-site-id="<?= $this->currentSite()->id() ?>"
     data-base-url="<?= $this->url('site/resource', ['site-slug' => $site->slug(), 'controller' => 'item-set', 'action' => 'browse']) ?>">
```

```js
// En el script JS
const grid    = document.getElementById('collections-grid');
const siteId  = grid?.dataset.siteId;
const baseUrl = grid?.dataset.baseUrl;
```

### Alternativas nativas a jQuery

| jQuery | Nativo ES6+ |
|---|---|
| `$(document).ready(fn)` | `document.addEventListener('DOMContentLoaded', fn)` |
| `$('.clase')` | `document.querySelectorAll('.clase')` |
| `$el.addClass('x')` | `el.classList.add('x')` |
| `$el.toggleClass('x')` | `el.classList.toggle('x')` |
| `$el.on('click', fn)` | `el.addEventListener('click', fn)` |
| `$.ajax(url)` | `fetch(url).then(r => r.json())` |
| `$el.closest('.x')` | `el.closest('.x')` |
| `$el.find('.x')` | `el.querySelectorAll('.x')` |
| `$el.attr('data-x')` | `el.dataset.x` |

### Cuándo usar `data-*` para pasar datos PHP a JS
- Valores de configuración que cambian por ítem o por página (IDs, URLs).
- Metadatos necesarios para el filtrado client-side (slugs de etapa, nivel, temática en las tarjetas de colecciones).
- **No** para grandes volúmenes de datos (listas completas de resultados) — en ese caso usar llamadas fetch a la API de Omeka-S.

## Patrones frecuentes

```js
// Guard pattern (todos los scripts empiezan así)
const container = document.querySelector('.mi-componente');
if (!container) return;

// Delegación de eventos (eficiente para listas dinámicas)
document.addEventListener('click', function (e) {
    if (e.target.matches('.filter-chip__remove')) {
        removeChip(e.target.closest('.filter-chip'));
    }
});

// Debounce para inputs de búsqueda
function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}
const onSearch = debounce(applyFilters, 150);
```

## Errores comunes
- Usar `document.getElementById()` sin comprobar null — si el elemento no está en la página actual, el script lanza TypeError al llamar `.addEventListener` sobre null.
- Escribir `<script>` inline en las plantillas para lógica reutilizable — mover a archivos `.js` en `asset/js/` y registrar con `headScript()`.
- Añadir `<script src="...">` directamente en HTML sin pasar por `headScript()` — Omeka-S no puede deduplicar ni gestionar el orden de carga.
- Usar `innerHTML` con datos no escapados — riesgo de XSS. Usar `textContent` para texto o `createElement` para HTML dinámico.

## Referencias externas
- MDN Web Docs — JavaScript: https://developer.mozilla.org/es/docs/Web/JavaScript
- Progressive Enhancement: https://developer.mozilla.org/en-US/docs/Glossary/Progressive_Enhancement
