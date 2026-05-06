# QA Checklist

## Propósito
Checklist estructurada para revisar implementaciones del tema REA ATE antes de marcar un ítem del backlog como cerrado, usada por el agente QA y el Desarrollador para asegurar calidad mínima.

## Cuándo invocar esta skill
- Antes de declarar un ítem del backlog como completado.
- Al iniciar una ronda de QA sobre instancia real.
- Al revisar un hallazgo de regresión.
- Al preparar la release de una nueva versión.

## Referencia técnica

### Escala de severidad

| Severidad | Criterio |
|---|---|
| Crítica | Bloquea una tarea principal del usuario, rompe la navegación clave o invalida la release. |
| Alta | La funcionalidad existe pero falla de forma importante o genera una regresión visible. |
| Media | Problema real de UX, visual o responsive con workaround o impacto acotado. |
| Baja | Ajuste menor, inconsistencia visual o mejora no bloqueante. |

### Formato de reporte de hallazgo

```
ID: QA-NNN
Fecha: YYYY-MM-DD
Severidad: [Crítica | Alta | Media | Baja]
Área: [Header | Search | Item Show | Item Set Browse | Browse | Home | A11y | Mobile | Release]
Hallazgo: Descripción del problema en una frase.
Reproducción:
  1. Paso 1
  2. Paso 2
  3. Resultado observado vs. resultado esperado
Estado: [Abierto | En análisis | En curso | Resuelto | Cerrado | Diferido | Rechazado]
Responsable: [architect | designer | developer | —]
```

---

## Checklist de revisión funcional

### Header y navegación
- [ ] La top-bar se oculta al hacer scroll y la main-bar se vuelve sticky correctamente.
- [ ] La barra de búsqueda es visible y funcional en todas las páginas.
- [ ] El menú de navegación está completo y los enlaces llevan al destino correcto.
- [ ] El logo del sitio enlaza a la home.
- [ ] En mobile (< 768px), el menú es accesible (hamburger o equivalente).

### Search results
- [ ] Los resultados se muestran al ejecutar una búsqueda.
- [ ] Las facetas filtran correctamente por nivel, tipo y temática.
- [ ] Los chips de filtro activos aparecen cuando hay una faceta seleccionada.
- [ ] Los chips desaparecen (sin hueco) cuando no hay filtros activos.
- [ ] El contador de resultados actualiza al activar/desactivar facetas.
- [ ] El mensaje "sin resultados" aparece correctamente cuando no hay matches.
- [ ] El badge de `lrmi:learningResourceType` aparece en los cards de resultado.
- [ ] La paginación funciona y muestra el número correcto de páginas.

### Item Show (ficha de recurso)
- [ ] El breadcrumb apunta a la búsqueda facetada (no al browse genérico).
- [ ] El badge de tipo de recurso (eyebrow) aparece con icono y etiqueta normalizada.
- [ ] El título usa tipografía correcta y tamaño clamp.
- [ ] La etiqueta de tiempo (days-ago) calcula la distancia desde `dcterms:date`.
- [ ] El sidebar derecho (anclaje curricular) muestra nivel, etapa y competencias.
- [ ] Los pills de relaciones (`dcterms:relation`) funcionan y el panel de info se abre correctamente.
- [ ] El bloque de medias renderiza imágenes, vídeos y SCORM según su tipo.
- [ ] La vista con sidebar izquierdo activo no rompe el layout del grid.

### Item-set browse (colecciones)
- [ ] Las colecciones vacías (sin ítems públicos en el sitio) no aparecen en el grid.
- [ ] Los selects de filtro se pueblan con valores reales de los metadatos.
- [ ] El filtrado client-side funciona sin recargar la página.
- [ ] El estado vacío (0 colecciones tras filtrar) muestra el mensaje de empty state.
- [ ] El contador de colecciones actualiza al filtrar.
- [ ] El botón "Limpiar" aparece solo cuando hay un filtro activo.
- [ ] El grid responde al cambio de tamaño de pantalla (3 → 2 → 1 columna).

### Accesibilidad
- [ ] Contraste de texto normal ≥ 4.5:1 (verificar con WebAIM Contrast Checker).
- [ ] Contraste de texto grande ≥ 3:1.
- [ ] Contraste de bordes de inputs UI ≥ 3:1.
- [ ] Todos los elementos interactivos son alcanzables con Tab.
- [ ] El foco es visible en todos los elementos interactivos.
- [ ] Las imágenes tienen atributo `alt` descriptivo (o vacío si son decorativas).
- [ ] Los iconos decorativos (Material Symbols) tienen `aria-hidden="true"`.
- [ ] La jerarquía de headings es correcta (un `h1` por página, sin saltar niveles).
- [ ] El `<html>` tiene atributo `lang`.

### Rendimiento
- [ ] No hay doble carga de las mismas fuentes o scripts.
- [ ] Los assets (CSS, JS, fuentes) tienen `defer` o `async` donde corresponde.
- [ ] Las imágenes en el grid de colecciones cargan con lazy loading (`loading="lazy"`).
- [ ] `npm run build` (o `gulp css`) ejecuta sin errores.

### Metadatos y datos estructurados
- [ ] `dcterms:title` y `dcterms:description` aparecen en todas las vistas que los requieren.
- [ ] `lrmi:learningResourceType` aparece con el componente correcto (badge con icono).
- [ ] `lrmi:educationalLevel` aparece en el sidebar de item show.
- [ ] El JSON-LD (si está implementado) pasa la validación en https://validator.schema.org/.

### Release (antes de cada tag)
- [ ] `npm run build` limpio (sin errores ni warnings críticos).
- [ ] `git status` limpio (sin cambios pendientes).
- [ ] `CHANGELOG.md` o notas de release actualizadas.
- [ ] El tag sigue el formato `vX.Y.Z` (semver).
- [ ] El GitHub Action de release ejecuta correctamente y genera el artefacto `.zip`.
- [ ] El `.zip` excluye `.project/`, `node_modules/`, `.git/` y archivos de desarrollo.

## Patrones frecuentes

```bash
# Verificar contraste desde línea de comandos (requiere npm i -g wcag-contrast)
# Alternativa: usar la extensión axe DevTools en el navegador

# Verificar que el build es limpio
npm run build 2>&1 | grep -E "error|warning"

# Listar archivos que entrarían en el release zip
git ls-files --exclude-standard | grep -v '^\.project'
```

## Errores comunes
- Marcar un ítem como "Resuelto" sin revalidar en instancia real — "resuelto" implica verificación en staging/producción, no solo en local.
- No asignar severidad al hallazgo — impide priorizar correctamente.
- Registrar la corrección en el ticket sin actualizar `docs/qa-findings.md` — el registro de hallazgos es la fuente de verdad del QA.
