# Integración con Advanced Search

_Documento generado durante el proyecto. Se completa cuando el 
Arquitecto define la estrategia de integración._

## Módulo de referencia

**Advanced Search** para Omeka-S  
Repositorio: https://github.com/Daniel-KM/Omeka-S-module-AdvancedSearch

## Áreas a documentar

- Versión del módulo utilizada en producción.
- Subconjunto de facetas activas seleccionadas.
- Tipo de adaptador de búsqueda (interno / Solr / otro).
- Plantillas del módulo sobreescritas en el tema.
- Configuración de la barra de búsqueda en el header.
- Comportamiento de la búsqueda en distintos estados del header.

## Notas

- Los identificadores internos de propiedades de la instancia (IDs) son 
  configuración de la instalación de producción y **no se documentan en 
  el código del tema** ni en este repositorio. El tema accede a las 
  propiedades por su nombre cualificado (`prefix:term`).
- Las facetas activas son configuración de la instancia Omeka-S, no del 
  tema. El tema debe renderizar las facetas que el módulo le proporcione.
