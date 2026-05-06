# Accesibilidad WCAG 2.1 AA

## Propósito
Referencia de los criterios WCAG 2.1 nivel AA más relevantes para REA ATE, para que Designer y Developer puedan verificar que los componentes cumplen el mínimo exigido sin consultar la especificación completa.

## Cuándo invocar esta skill
- Al diseñar o implementar un componente interactivo (filtros, modales, chips).
- Al elegir colores o contrastes de texto.
- Al implementar navegación por teclado o ARIA.
- Al revisar un hallazgo de accesibilidad en QA.

## Referencia técnica

### Criterios WCAG 2.1 AA prioritarios para REA ATE

| Criterio | Nivel | Descripción | Aplica a |
|---|---|---|---|
| 1.1.1 Non-text Content | A | Imágenes con alt descriptivo | Thumbnails, icons decorativos |
| 1.3.1 Info and Relationships | A | Semántica HTML correcta (headings, listas, labels) | Todas las plantillas |
| 1.4.1 Use of Color | A | No transmitir info solo por color | Badges de tipo de recurso |
| 1.4.3 Contrast (Minimum) | AA | **Texto normal: 4.5:1. Texto grande: 3:1** | Toda la tipografía |
| 1.4.4 Resize Text | AA | Texto redimensionable al 200% sin pérdida | Layout responsive |
| 1.4.10 Reflow | AA | Sin scroll horizontal en 320px | Mobile |
| 1.4.11 Non-text Contrast | AA | Contraste 3:1 en bordes de inputs y iconos UI | Selects de filtro, botones |
| 2.1.1 Keyboard | A | Toda funcionalidad accesible por teclado | Filtros, chips, modales |
| 2.4.3 Focus Order | A | Orden de foco lógico | Componentes dinámicos |
| 2.4.7 Focus Visible | AA | Foco visible en todos los elementos interactivos | CSS `:focus-visible` |
| 3.1.1 Language of Page | A | `lang` en `<html>` | `layout.phtml` |
| 4.1.2 Name, Role, Value | A | ARIA correcto en componentes custom | Selects, chips, modales |

### Requisitos de contraste

```
Texto normal (< 18px regular o < 14px bold):  ratio mínimo 4.5:1
Texto grande (≥ 18px regular o ≥ 14px bold):  ratio mínimo 3:1
Componentes UI (bordes de input, iconos):      ratio mínimo 3:1
```

Paleta ATE y sus ratios aprobados (sobre blanco `#FFFFFF`):
| Color | Hex | Ratio sobre blanco | Uso |
|---|---|---|---|
| brand-blue-dark | `#0C2C84` | 12.6:1 ✅ | Texto, botones |
| brand-blue-mid | `#0768AC` | 5.5:1 ✅ | Links, activos |
| text-body | `#2C2C2C` | 14.1:1 ✅ | Texto corrido |
| text-muted | `#6B7280` | 4.6:1 ✅ | Metadatos (borderline AA) |
| brand-yellow | `#FFB300` | 2.5:1 ❌ | Solo decorativo, no texto |

**Regla crítica:** El amarillo `#FFB300` NO puede usarse como color de texto sobre fondo blanco. Solo como borde, fondo de superficie o decoración.

### Estructura de encabezados semánticos

```html
<!-- layout.phtml -->
<h1><!-- Título del sitio (solo en home) --></h1>

<!-- En item/show.phtml -->
<h1>Título del recurso</h1>
    <h2>Medios del recurso</h2>
    <h2>Metadatos</h2>
        <h3>Anclaje curricular</h3>

<!-- En item/browse.phtml -->
<h1>Recursos educativos</h1>
    <!-- Cada card usa <h2> para el título del ítem -->
    <h2>Título del ítem</h2>
```

No saltar niveles de heading (de `h1` a `h3` directamente). Un `h1` por página.

### ARIA para componentes dinámicos

```html
<!-- Barra de búsqueda -->
<form role="search" aria-label="Buscar recursos">
    <input type="search" aria-label="Términos de búsqueda">
    <button type="submit">Buscar</button>
</form>

<!-- Filtros de colecciones -->
<fieldset>
    <legend>Filtrar colecciones</legend>
    <label for="filter-nivel">Nivel educativo</label>
    <select id="filter-nivel" name="nivel" aria-controls="collections-grid">
        <option value="">Todos los niveles</option>
    </select>
</fieldset>

<!-- Chips de filtro activos -->
<ul role="list" aria-label="Filtros activos">
    <li>
        <span>Primaria</span>
        <button aria-label="Eliminar filtro Primaria">×</button>
    </li>
</ul>

<!-- Estado de resultados dinámicos -->
<div aria-live="polite" aria-atomic="true">
    <p>Mostrando 12 colecciones</p>
</div>
```

### Navegación por teclado

- Todos los elementos interactivos accesibles con `Tab`.
- `Escape` cierra modales y paneles desplegables.
- `Enter` y `Space` activan botones y selects.
- El foco nunca queda atrapado fuera de un modal abierto (focus trap).

```css
/* Focus visible — no quitar sin alternativa */
:focus-visible {
    outline: 2px solid var(--ate-color-brand-blue-mid);
    outline-offset: 2px;
}

/* Nunca hacer esto */
* { outline: none; } /* peligroso */
```

### Iconos decorativos vs informativos

```html
<!-- Icono decorativo (Material Symbols) — ocultar de lectores de pantalla -->
<span class="material-symbols-outlined" aria-hidden="true">school</span>

<!-- Icono informativo sin texto visible — necesita label -->
<button aria-label="Eliminar filtro">
    <span class="material-symbols-outlined" aria-hidden="true">close</span>
</button>
```

### Alternativas de texto para imágenes

```php
// Thumbnail de un ítem
$alt = $item->displayTitle() ?: 'Imagen del recurso';
echo $this->thumbnail($item, 'large', ['alt' => htmlspecialchars($alt)]);

// Imagen decorativa
<img src="..." alt=""> <!-- alt vacío para decorativas -->
```

## Herramientas de testing

| Herramienta | Qué verifica | Cómo usar |
|---|---|---|
| axe DevTools (extensión) | Errores automáticos WCAG | Inspeccionar → axe → Analyze |
| Lighthouse (Chrome DevTools) | Accesibilidad + rendimiento | F12 → Lighthouse → Accessibility |
| NVDA (Windows) + Firefox | Lectura por pantalla real | Prueba manual de flujo |
| WebAIM Contrast Checker | Ratio de contraste de colores | Introducir hex de fg y bg |
| Teclado solo (sin ratón) | Flujo de navegación por Tab | Navegar toda la página |

## Errores comunes
- Usar `color: var(--ate-color-brand-yellow)` sobre fondo blanco para texto — no pasa AA.
- Quitar `outline` sin alternativa visual de foco — criterio 2.4.7.
- `<div>` clickable sin `role="button"` y `tabindex="0"` — inaccesible por teclado.
- `aria-label` en un elemento que ya tiene texto visible — redundante y confuso, usar `aria-label` solo cuando no hay texto visible.
- `aria-live="assertive"` para mensajes no urgentes — interrumpe la lectura activa; usar `polite`.

## Referencias externas
- WCAG 2.1: https://www.w3.org/TR/WCAG21/
- WAI-ARIA Authoring Practices: https://www.w3.org/WAI/ARIA/apg/
- WebAIM Contrast Checker: https://webaim.org/resources/contrastchecker/
