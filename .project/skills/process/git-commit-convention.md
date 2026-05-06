# Convención de commits Git

## Propósito
Define el formato de mensajes de commit del proyecto REA ATE para mantener un historial legible y permitir la generación automatizada de changelogs por tipo y scope.

## Cuándo invocar esta skill
- Al escribir un mensaje de commit.
- Al revisar si un commit es demasiado grande o mezcla tipos.
- Al preparar las notas de una release.

## Referencia técnica

### Formato

```
type(scope): descripción corta en imperativo, presente, sin punto final
```

La descripción no supera 72 caracteres. Usa el imperativo ("add", "fix", "update"), no pasado ("added", "fixed").

### Tipos válidos

| Tipo | Cuándo usar |
|---|---|
| `feat` | Nueva funcionalidad (nueva vista, nuevo componente, nueva integración) |
| `fix` | Corrección de bug |
| `style` | Cambios puramente visuales en CSS/SASS que no afectan comportamiento |
| `refactor` | Reorganización de código sin cambio de funcionalidad |
| `docs` | Cambios en documentación (`.project/`, README, comentarios) |
| `test` | Añadir o corregir tests (si se incorporan al proyecto) |
| `chore` | Tareas de mantenimiento: dependencias, configuración de build, CI |
| `a11y` | Correcciones específicas de accesibilidad (subconjunto de `fix` o `style`) |

### Scopes del proyecto

| Scope | Qué cubre |
|---|---|
| `php` | Plantillas `.phtml`, helpers PHP, `theme.ini` |
| `sass` | Archivos SASS y CSS compilado |
| `js` | Archivos JavaScript |
| `metadata` | Mapeo de propiedades, vocabularios, datos estructurados |
| `search` | Integración con AdvancedSearch, resultados, chips, facetas |
| `item-show` | Vista de ficha de recurso |
| `item-set-browse` | Vista de galería de colecciones |
| `browse` | Vista de listado de ítems (grid y list) |
| `home` | Vista home y tarjetas de audiencia |
| `header` | Header sticky (top-bar + main-bar) |
| `footer` | Footer del tema |
| `media` | Renderizado de medias, SCORM, miniaturas |
| `a11y` | Accesibilidad y contrastes |
| `i18n` | Traducciones y localización |
| `deps` | Dependencias npm, Composer |
| `ci` | GitHub Actions, pipeline de release |
| `decisions` | Archivos de decisiones en `.project/decisions/` |

### Ejemplos reales del proyecto

```
feat(php): add LRMI metadata sidebar to item show template
fix(sass): correct contrast ratio on search result badges — QA-007
style(item-set-browse): reduce filter bar visual weight per QA-003
fix(js): add null guards in applyFilters for optional selects — QA-007
feat(ci): add GitHub Actions release pipeline for rea-ate theme
fix(metadata): remap Etapa filter to lrmi:educationalAlignment — QA-006
a11y(sass): fix days-ago-tag and filter-chip contrast for WCAG AA
refactor(php): extract resource-type-badge to shared partial
docs(decisions): architect decision on collection filter data source
chore(deps): update gulp-sass to latest compatible version
```

### Cuerpo del commit (cuándo usarlo)

Añadir cuerpo cuando el `why` no es obvio por el título:

```
fix(js): add null guards in applyFilters for optional selects

Selects only render when PHP finds values for that filter dimension.
If no items have `lrmi:educationalAlignment`, #filter-etapa is absent
from the DOM and getElementById returns null, crashing applyFilters.

Refs: QA-007, decisions/developer.md#2026-05-06
```

### Referencia a decisiones

Cuando el commit implementa una decisión registrada:
```
Refs: decisions/architect.md#2026-05-05
```

Cuando cierra un hallazgo de QA:
```
Closes: QA-006
```

### Cuándo dividir un commit

Un commit debe representar **una unidad lógica de cambio**. Dividir cuando:
- El commit mezcla un `feat` y un `fix` no relacionados.
- El scope abarca más de dos áreas distintas.
- La descripción necesita "y" para ser completa ("add X and fix Y").

Agrupar cuando:
- SASS compilado y template PHP son la misma funcionalidad (un solo `feat`).
- Varios archivos SCSS cambian juntos por la misma decisión de diseño.

## Errores comunes
- Usar pasado en la descripción ("fixed", "added") — usar imperativo presente.
- Commits con `chore: various fixes` — ser específico con scope y descripción.
- Incluir `asset/css/style.css` como si fuera un cambio significativo — es un artefacto compilado; mencionarlo en el cuerpo si procede, no en el título.
- Olvidar el scope cuando hay más de una área en el proyecto — el scope es obligatorio.
- Hacer un commit gigante con todo el trabajo de un ciclo — dividir en unidades lógicas para que `git log` sea útil.
