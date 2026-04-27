# Modelo de metadatos — REA

Modelo de referencia del proyecto basado en LRMI / Schema.org sobre 
Omeka-S. Este documento describe los campos y vocabularios esperados; 
los valores concretos y la configuración de la instancia (IDs internos, 
facetas activas, vocabularios controlados específicos) se definen 
durante el desarrollo y la puesta en producción.

---

## Vocabularios de referencia

| Prefijo | Vocabulario | Uso principal |
|---------|-------------|---------------|
| `dcterms:` | Dublin Core Terms | Título, descripción, derechos, relaciones |
| `lrmi:` | LRMI (via Schema.org) | Campos educativos específicos |
| `schema:` | Schema.org | Campos semánticos generales |

El conjunto exacto de vocabularios cargados en la instancia Omeka-S y 
sus prefijos canónicos se confirman al inicio del proyecto.

---

## Campos de referencia del recurso educativo

| Campo | Propiedad | Tipo de valor esperado |
|-------|-----------|------------------------|
| Título | `dcterms:title` | Literal |
| Descripción | `dcterms:description` | Literal |
| Tipo de recurso | `lrmi:learningResourceType` | Literal (vocabulario controlado) |
| Saberes básicos | `lrmi:teaches` | Ítem vinculado |
| Criterios de evaluación | `lrmi:assesses` | Ítem vinculado |
| Nivel educativo | `lrmi:educationalLevel` | Literal (vocabulario controlado) |
| Materia | `schema:about` | Literal |
| Derechos / Licencia | `dcterms:rights` | Literal (vocabulario controlado) |
| Tiempo estimado | `lrmi:timeRequired` | Literal numérico |
| Áreas, programas o redes vinculadas | `dcterms:relation` | Literal (vocabulario controlado) |

> El tema accede a las propiedades por su **nombre cualificado** 
> (`prefix:term`), nunca por IDs internos de la instancia.

---

## Ítems vinculados

Los campos `lrmi:teaches` y `lrmi:assesses` apuntan a ítems internos 
de Omeka-S que representan elementos curriculares (saberes básicos, 
criterios de evaluación, etc.).

El tema debe ser capaz de renderizar estos vínculos mostrando el título 
del ítem referenciado, sin asumir una estructura interna específica.

---

## Vocabularios controlados

Algunos campos del modelo usan vocabularios controlados configurados en 
la instancia Omeka-S (Custom Vocab u otros mecanismos). El tema **no 
debe codificar** los valores de estos vocabularios: debe leerlos del 
recurso y renderizarlos genéricamente.

Los vocabularios controlados conocidos se documentan aquí conforme se 
confirman durante el desarrollo:

| Campo | Estado del vocabulario controlado |
|-------|-----------------------------------|
| `lrmi:learningResourceType` | A confirmar |
| `lrmi:educationalLevel` | A confirmar |
| `dcterms:rights` | A confirmar |
| `dcterms:relation` | A confirmar |

---

## Facetas de búsqueda

Las facetas activas en Advanced Search son **configuración de la 
instancia**, no del tema. El tema renderiza las facetas que el módulo 
le proporcione, sin asumir un conjunto fijo.

El subconjunto inicial de facetas se define durante el desarrollo, 
coordinado entre Arquitecto y cliente.
