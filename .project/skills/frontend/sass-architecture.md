# Arquitectura SASS del tema

## Propósito
Referencia de la estructura 7-1 adaptada, los tokens de diseño como variables SASS y las convenciones BEM del tema REA ATE para que Developer y Designer trabajen en la misma capa CSS sin conflictos.

## Cuándo invocar esta skill
- Al crear un archivo SASS nuevo o un componente.
- Al definir o modificar un token de diseño.
- Al nombrar clases CSS de un nuevo componente.
- Al configurar la compilación de SASS.

## Referencia técnica

### Estructura 7-1 del tema

```
asset/sass/
├── abstracts/             # Sin output CSS propio
│   ├── _variables.scss    # Variables Sass ($font__body, $color__link…)
│   ├── _mixins.scss       # Mixins reutilizables
│   └── _functions.scss    # Funciones Sass
│
├── base/                  # Estilos globales sin clase
│   ├── _reset.scss
│   └── _typography.scss
│
├── components/            # Un subdirectorio por componente
│   ├── item-show/
│   │   └── _item-show.scss
│   ├── search-results/
│   │   └── _search-results-page.scss
│   ├── item-set-browse/
│   │   └── _item-set-browse.scss
│   ├── anclaje-curricular/
│   │   └── _anclaje-curricular.scss
│   ├── media-embeds/
│   │   └── media-embeds.scss
│   ├── resource-link-info/
│   │   └── _resource-link-info.scss
│   └── _components.scss   # @forward de todos los componentes
│
├── layout/                # Estructura de página
│   ├── _header.scss
│   ├── _footer.scss
│   ├── _sidebar.scss
│   └── _regions.scss
│
├── pages/                 # Estilos página-específicos
│   └── _home.scss
│
├── vendors/               # CSS de terceros con modificaciones mínimas
│   └── _omeka-core.scss   # Overrides sobre el tema base Freedom
│
└── style.scss             # Punto de entrada: @use de todo lo anterior
```

### Tokens de diseño

Los tokens visuales viven como CSS custom properties en `layout.phtml` (cargadas en `:root`). En SASS **no se definen valores hardcoded**; se usan las variables mediante `var()`:

```scss
// CORRECTO — usar token ATE
.resource-type-badge {
    background-color: var(--ate-color-brand-blue-dark);
    color: var(--ate-text-on-dark);
    border-radius: var(--ate-radius-pill);
}

// INCORRECTO — valor hardcoded
.resource-type-badge {
    background-color: #0C2C84; // no hacer esto
}
```

Las variables Sass en `abstracts/_variables.scss` son para **valores del sistema de compilación** (breakpoints, z-indexes, etc.), no para colores ni tipografía:

```scss
// abstracts/_variables.scss
$breakpoint-mobile: 600px;
$breakpoint-tablet: 1024px;
$z-header: 1040;
$z-modal: 2000;

// Tipografía — apuntan a las CSS vars de layout.phtml
$font__body:     var(--ate-font-body);
$font__headings: var(--ate-font-heading);
```

### Convención BEM

```scss
// Bloque
.collection-card { }

// Elemento
.collection-card__title { }
.collection-card__image { }
.collection-card__meta { }

// Modificador
.collection-card--featured { }
.collection-card--empty { }

// Estado con clase JS
.collection-card.is-hidden { display: none; }
.collection-filter-select.has-value { /* select con valor activo */ }
```

Reglas BEM del proyecto:
- Prefijos de bloque: descriptivos, en kebab-case (`item-set-browse`, `resource-type-badge`).
- Modificadores estructurales en SASS (`--featured`), estados dinámicos con `.is-*` o `has-*`.
- **No** anidar más de 2 niveles en SASS para mantener especificidad baja.

### Compilación

```bash
# Desarrollo (watch + source maps)
npm run start
# o
gulp css

# Producción (minificado, sin source maps)
npm run build

# Resultado
asset/css/style.css        # CSS compilado
asset/css/style.css.map    # Source map (desarrollo)
```

El archivo `style.scss` es el único punto de entrada de la compilación. Todo el SASS debe estar importado desde ahí (directamente o via `_components.scss`).

### Añadir un componente nuevo

1. Crear directorio y archivo: `asset/sass/components/mi-componente/_mi-componente.scss`
2. Añadir `@use 'mi-componente/mi-componente'` en `asset/sass/components/_components.scss`
3. Verificar que `style.scss` importa `_components.scss` (ya debe estarlo)
4. Compilar y verificar

### Qué NO poner en SASS
- Lógica de datos (filtros de PHP, llamadas a API).
- Strings de texto visible para el usuario (usar PHP + `__()` para i18n).
- Valores de color o tipografía hardcoded — usar `var(--ate-*)`.
- Bloques `:root { }` con redefinición de tokens — los tokens se definen en `layout.phtml`.

## Patrones frecuentes

```scss
// Responsive con breakpoints del proyecto
@media (max-width: 1024px) { /* tablet */ }
@media (max-width: 600px)  { /* mobile */ }

// Grid responsivo estándar de colecciones
.collections-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;

    @media (max-width: 1024px) { grid-template-columns: repeat(2, 1fr); }
    @media (max-width: 600px)  { grid-template-columns: 1fr; }
}

// Truncado de texto
.card__title {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
```

## Errores comunes
- Editar `asset/css/style.css` directamente — se sobrescribe en la siguiente compilación.
- Crear un `_variables.scss` con colores hardcoded — rompe el sistema de tokens.
- Añadir `!important` por defecto — solo cuando hay inline styles de módulos de Omeka-S que no se pueden evitar (p. ej. botones SCORM).
- Olvidar el `@use` en `_components.scss` — el archivo SASS no se compila aunque exista.

## Referencias externas
- Guía 7-1: https://sass-guidelin.es/#the-7-1-pattern
- BEM methodology: https://getbem.com/
