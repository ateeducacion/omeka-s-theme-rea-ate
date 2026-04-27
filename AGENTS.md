# Sistema multiagente del proyecto

Este proyecto opera con cuatro roles que colaboran de forma asíncrona.
Cada rol tiene responsabilidades delimitadas y se comunica a través de
los archivos de decisiones en `.project/decisions/`.

La documentación de coordinación del proyecto vive en `.project/`.
Este directorio **no forma parte de las releases del tema**.

---

## Roles y skills

Cada rol tiene un skill asociado en `.project/skills/` que define con 
detalle sus responsabilidades, entradas, salidas esperadas y criterios 
de calidad. **Antes de actuar, cada rol debe leer su propio skill.**

| Rol | Skill | Decisiones |
|-----|-------|------------|
| Orquestador | `.project/skills/orchestrator.md` | `.project/decisions/orchestrator.md` |
| Arquitecto | `.project/skills/architect.md` | `.project/decisions/architect.md` |
| Desarrollador | `.project/skills/developer.md` | `.project/decisions/developer.md` |
| Diseñador | `.project/skills/designer.md` | `.project/decisions/designer.md` |

## Skills transversales

Skills que cualquier rol puede consultar según el tipo de tarea:

| Skill | Cuándo se consulta |
|-------|--------------------|
| `.project/skills/theme-creator.md` | Trabajar con la estructura de un tema Omeka-S |
| `.project/skills/omeka-integration.md` | Integración con Omeka-S y sus módulos |
| `.project/skills/decision-logging.md` | Registrar decisiones en `.project/decisions/` |

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
Orquestador actualiza estado del proyecto
```

---

## Releases del tema

Las releases se generan sobre la raíz del repositorio excluyendo 
`.project/` y los archivos de coordinación. Solo se empaqueta el 
contenido del tema Omeka-S.

El procedimiento exacto de release se define durante el desarrollo 
(GitHub Actions, script local, etc.) y se registra en una decisión 
del Arquitecto.

---

## Convenciones generales

| Aspecto | Convención |
|---------|-----------|
| Idioma del código | Inglés (variables, clases CSS, funciones, comentarios técnicos) |
| Idioma documentación | Español (es-ES) |
| Versión mínima Omeka-S | A confirmar al inicio del proyecto |
| JavaScript | Vanilla JS / módulos ES preferentemente |
| CSS | Sistema de tokens definido por el Diseñador |

Las convenciones específicas de prefijos CSS, estructura de plantillas y 
gestión de assets se acuerdan en las primeras decisiones del Arquitecto 
y el Diseñador.
