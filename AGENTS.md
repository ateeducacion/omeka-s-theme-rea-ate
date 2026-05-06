# Sistema multiagente del proyecto

Este proyecto opera con cinco roles que colaboran de forma asíncrona.
Cada rol tiene responsabilidades delimitadas y se comunica a través de
los archivos de decisiones en `.project/decisions/`.

La documentación de coordinación del proyecto vive en `.project/`.
Este directorio **no forma parte de las releases del tema**.

---

## Estructura de `.project/`

```
.project/
├── agents/          # Prompts de sistema por rol
├── context/         # Requisitos y modelo de metadatos del proyecto
├── decisions/       # Diario de decisiones por agente
├── docs/            # Documentación técnica del proyecto
└── skills/          # Procedimientos técnicos reutilizables por dominio
    ├── omeka-s-core/
    ├── metadata/
    ├── frontend/
    ├── features/
    └── process/
```

### Distinción conceptual

| Concepto | Dónde vive | Qué es |
|----------|-----------|--------|
| **Agente** | `.project/agents/` | Prompt de sistema: rol, responsabilidades, protocolo, skills que invoca |
| **Skill** | `.project/skills/` | Procedimiento técnico sin estado, reutilizable por cualquier agente |
| **Decisión** | `.project/decisions/` | Historial de decisiones tomadas, con contexto y alternativas descartadas |

---

## Roles y archivos de agente

| Rol | Prompt de sistema | Diario de decisiones |
|-----|-------------------|----------------------|
| Orquestador | `.project/agents/orchestrator.md` | `.project/decisions/orchestrator.md` |
| Arquitecto | `.project/agents/architect.md` | `.project/decisions/architect.md` |
| Diseñador | `.project/agents/designer.md` | `.project/decisions/designer.md` |
| Desarrollador | `.project/agents/developer.md` | `.project/decisions/developer.md` |
| QA | `.project/agents/qa.md` | `.project/docs/qa-findings.md` |

---

## Skills disponibles

| Dominio | Skill | Descripción |
|---------|-------|-------------|
| omeka-s-core | `omeka-api.md` | API REST y PHP de Omeka-S |
| omeka-s-core | `omeka-theme-anatomy.md` | Estructura del tema, helpers y resolución de plantillas |
| omeka-s-core | `omeka-hooks-events.md` | Sistema de eventos e inyección de assets |
| metadata | `lrmi-schema.md` | Propiedades LRMI y su mapeo en REA ATE |
| metadata | `dublin-core-mapping.md` | Elementos DC/DC Terms y campos obligatorios |
| metadata | `schema-org-rea.md` | Tipos Schema.org y JSON-LD para SEO |
| frontend | `sass-architecture.md` | Estructura 7-1, tokens, BEM y compilación |
| frontend | `js-progressive-enhancement.md` | Patrones JS, registro de scripts en Omeka-S |
| frontend | `a11y-wcag.md` | Criterios WCAG 2.1 AA, contraste, ARIA |
| features | `advanced-search.md` | Módulo AdvancedSearch: búsqueda, facetas, resultados |
| features | `media-rendering.md` | Tipos de media, thumbnails, SCORM |
| features | `i18n-localization.md` | Gettext, archivos .po/.mo, strings traducibles |
| process | `qa-checklist.md` | Checklist de revisión funcional, a11y y release |
| process | `decision-log-protocol.md` | Formato y reglas del diario de decisiones |
| process | `git-commit-convention.md` | Tipos, scopes y ejemplos de commits |

---

## Flujo de trabajo

```
Orquestador lee estado → identifica próximas decisiones necesarias
       ↓
Arquitecto y Diseñador proponen decisiones en paralelo
       ↓
Orquestador valida y marca decisiones como ACEPTADAS
       ↓
Desarrollador implementa basándose en decisiones ACEPTADAS
       ↓
QA verifica en instancia real y registra hallazgos
       ↓
Orquestador actualiza estado del proyecto
```

---

## Cómo iniciar una sesión con un agente

Patrón de prompt para activar un agente con el contexto mínimo:

```
[Pega el contenido de .project/agents/<rol>.md]

---

Contexto del proyecto:
[Pega .project/context/requirements.md]

Decisiones previas tuyas:
[Pega .project/decisions/<rol>.md — últimas entradas relevantes]

Skills relevantes para esta tarea:
[Pega el contenido de las skills necesarias]

---

Tarea: [descripción concreta]
```

El Orquestador carga todos los archivos de `agents/` para coordinar entre roles.

---

## Releases del tema

Las releases se generan sobre la raíz del repositorio excluyendo
`.project/` y los archivos de coordinación. Solo se empaqueta el
contenido del tema Omeka-S.

El pipeline de release está operativo en GitHub Actions (tag `v*.*.*`).
Ver `.project/decisions/architect.md` para el procedimiento exacto.

---

## Convenciones generales

| Aspecto | Convención |
|---------|-----------|
| Idioma del código | Inglés (variables, clases CSS, funciones) |
| Idioma documentación | Español (es-ES) |
| Versión mínima Omeka-S | 4.2 |
| JavaScript | Vanilla JS, sin bundler |
| CSS | Sistema de tokens `--ate-*` en `layout.phtml` |
| Commits | `type(scope): descripción` — ver `skills/process/git-commit-convention.md` |
