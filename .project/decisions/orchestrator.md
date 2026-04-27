# Decisiones del Orquestador

---

## [2026-04-27] ACEPTADA

### Decisión
Inicio del proyecto. Estado inicial registrado.

### Contexto
El repositorio del tema Omeka-S ha sido inicializado con el sistema 
multiagente en `.project/`. El tema parte de un tema Omeka-S existente 
que ya está en el repositorio. Los requisitos iniciales están documentados 
en `.project/context/requirements.md` y el modelo de metadatos de 
referencia en `.project/context/metadata_model.md`.

### Alternativas consideradas
N/A — entrada de estado inicial.

### Consecuencias
- El Arquitecto debe iniciar con un análisis del tema base existente y 
  proponer la estrategia de integración con Advanced Search y la 
  estructura de plantillas del header.
- El Diseñador debe iniciar proponiendo el sistema de tokens CSS y el 
  comportamiento del header en sus distintos estados.
- El Desarrollador queda bloqueado hasta que Arquitecto y Diseñador 
  registren sus primeras decisiones `ACEPTADA`.

### Dependencias
- Desbloquea: primera ronda de decisiones de Arquitecto y Diseñador 
  en paralelo.
- Bloquea: toda implementación del Desarrollador.

---

## Estado actual del proyecto

| Aspecto | Estado |
|---------|--------|
| Fase | INICIO |
| Tema base | Existente en el repositorio — pendiente de análisis por el Arquitecto |
| Decisiones Arquitecto | ⏳ Pendientes |
| Decisiones Diseñador | ⏳ Pendientes |
| Implementación | 🔒 Bloqueada |

## Requisitos pendientes de aclaración

| Requisito | Responsable |
|-----------|-------------|
| Análisis del tema base: plantillas disponibles y estructura de assets | Arquitecto |
| Identidad visual institucional | Cliente |
| Contenido y estructura del footer | Cliente |
| Subconjunto de facetas activas en Advanced Search | Arquitecto + Cliente |
| Versión exacta de Omeka-S en producción | Cliente |
| Versión del módulo Advanced Search en uso | Cliente |
| Mecanismo de release del tema | Arquitecto |
