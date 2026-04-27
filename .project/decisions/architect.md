# Decisiones del Arquitecto

_Sin entradas todavía. Antes de registrar la primera decisión, el 
Arquitecto debe leer:_

- _`.project/skills/architect.md`_
- _`.project/skills/theme-creator.md`_
- _`.project/skills/omeka-integration.md`_
- _`.project/skills/decision-logging.md`_
- _`.project/context/requirements.md`_
- _`.project/context/metadata_model.md`_
- _`.project/decisions/orchestrator.md`_

_Como primer paso, el Arquitecto debe analizar el tema base existente: 
qué plantillas tiene, cómo organiza sus assets, qué convenciones sigue, 
y qué hay que extender o sobreescribir para cumplir los requisitos._

## Áreas de decisión a abordar

1. **Análisis del tema base**: inventario de plantillas y assets, 
   convenciones existentes, qué se hereda y qué se sobreescribe.
2. **Estrategia de integración con Advanced Search**: plantillas del 
   módulo a sobreescribir, configuración del índice, integración de la 
   barra de búsqueda en el header.
3. **Estructura de plantillas para el header**: separación entre banda 
   institucional y menú flotante, persistencia de la barra de búsqueda 
   en modo sticky.
4. **Convenciones de plantillas y assets**: nombrado, organización, 
   prefijo CSS, gestión de dependencias.
5. **Subconjunto de campos para facetas**: en coordinación con el cliente, 
   a partir del modelo de metadatos.
6. **Mecanismo de release del tema**: empaquetado, exclusiones, 
   automatización.
