# Skill: Omeka Integration (transversal)

## Cuándo usar este skill
Cuando se vaya a integrar el tema con Omeka-S o cualquiera de sus módulos: 
acceder a recursos, propiedades, vocabularios, facetas, índices de búsqueda.

## Conceptos base de Omeka-S relevantes para el tema

- **Resource**: cualquier ítem, item set, media o site page de Omeka-S.
- **Property**: una propiedad RDF de algún vocabulario (ej. `dcterms:title`).
- **Vocabulary**: conjunto de propiedades (Dublin Core, Schema.org, LRMI...).
- **Resource Template**: plantilla que define qué propiedades usa un tipo 
  de recurso.
- **Resource Class**: clase RDF del recurso (ej. `schema:LearningResource`).

## Acceso a propiedades en plantillas

Las propiedades se acceden por su nombre cualificado (`prefix:term`), 
**no por su `property_id`**. Los IDs internos cambian entre instalaciones 
y no deben aparecer en el código del tema.

```php
// Correcto: por nombre cualificado
$resource->value('dcterms:title');
$resource->value('lrmi:educationalLevel');

// Incorrecto: por ID interno
$resource->value(1); // ❌ no portable
```

## Vocabularios usados en el proyecto

Los vocabularios concretos en uso y su estado en la instancia se 
documentan en `.project/context/metadata_model.md`. El tema debe 
funcionar con los nombres cualificados estándar de cada vocabulario.

## Integración con módulos

El módulo principal de búsqueda es **Advanced Search** 
(Daniel-KM/Omeka-S-module-AdvancedSearch). Su integración:
- La estrategia concreta la define el Arquitecto.
- Las plantillas del módulo pueden sobreescribirse desde el tema 
  manteniendo la ruta de `view/` que el módulo expone.
- La configuración del índice y las facetas activas se gestiona en la 
  administración de Omeka-S, no en el código del tema.

## Reglas

- El tema **no debe asumir** ningún `property_id` ni configuración 
  específica de la instancia (estos pueden cambiar en producción).
- El tema **no debe asumir** un conjunto fijo de facetas: las facetas 
  son configuración de la instancia, no del tema.
- Cualquier valor controlado (vocabularios) referenciado en el tema 
  debe documentarse en `.project/context/metadata_model.md`.
