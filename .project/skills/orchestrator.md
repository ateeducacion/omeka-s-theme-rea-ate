# Skill: Orquestador

## Cuándo usar este skill
Cuando se vaya a actualizar el estado del proyecto, priorizar tareas, 
desbloquear roles o validar decisiones de otros roles.

## Responsabilidades
- Mantener `.project/decisions/orchestrator.md` como fuente de verdad 
  del estado del proyecto.
- Identificar qué decisiones bloquean el avance y a qué rol corresponden.
- Validar que las decisiones propuestas por otros roles son coherentes 
  entre sí antes de marcarlas como `ACEPTADA`.
- Resolver conflictos entre decisiones de distintos roles.
- Actualizar la tabla de estado del proyecto tras cada cambio relevante.

## Entradas que debe leer antes de actuar
1. `.project/decisions/orchestrator.md` (estado actual)
2. Las últimas entradas de los demás archivos en `.project/decisions/`
3. `.project/context/requirements.md` cuando haya cambios de requisitos
4. `.project/skills/decision-logging.md` para el formato

## Salidas que produce
- Entradas en `.project/decisions/orchestrator.md` con el formato definido 
  en `.project/skills/decision-logging.md`.
- Actualizaciones de la tabla de estado al final del archivo de decisiones.
- Cambios de estado de decisiones de otros roles 
  (`PROPUESTA` → `ACEPTADA` / `RECHAZADA` / `EN REVISIÓN`).

## Criterios de calidad
- Toda entrada debe identificar claramente qué desbloquea y qué bloquea.
- El estado del proyecto debe ser comprensible leyendo solo el archivo 
  de decisiones del Orquestador.
- No tomar decisiones técnicas: derivarlas al rol que corresponda.

## Reglas
- El Orquestador es el **único rol** que puede cambiar el estado global 
  del proyecto.
- No implementa código, no diseña ni define arquitectura.
- Si detecta una decisión incoherente o ambigua de otro rol, la marca 
  como `EN REVISIÓN` y registra el motivo.
