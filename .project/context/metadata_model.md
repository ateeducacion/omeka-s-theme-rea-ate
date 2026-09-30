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
| `dcterms:` | Dublin Core Terms | Título, descripción, licencia, relaciones |
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
| Nivel educativo | `lrmi:educationalLevel` | Ítem enlazado (clase Curso del grafo curricular) |
| Materia | `schema:about` | Ítem enlazado (clase Asignatura) |
| Licencia | `dcterms:license` | URI (vocabulario de cinco licencias Creative Commons) |
| Tiempo estimado | `lrmi:timeRequired` | Literal numérico |
| Áreas, programas o redes vinculadas | `dcterms:relation` | Ítem enlazado (clase Eje temático) |

> El tema accede a las propiedades por su **nombre cualificado** 
> (`prefix:term`), nunca por IDs internos de la instancia.

> **Fuente de verdad:** la plantilla REA de la instancia (id 3) y la decisión
> de arquitectura 0019 del módulo `omeka-s-OERManager`, que es quien escribe
> estos valores. `dcterms:rights` ya no se usa como licencia.

---

## Ítems vinculados

Los campos `lrmi:teaches` y `lrmi:assesses` apuntan a ítems internos 
de Omeka-S que representan elementos curriculares (saberes básicos, 
criterios de evaluación, etc.).

Sostienen además el anclaje curricular y temático tres campos que también 
son ítems enlazados:

| Campo | Clase del ítem de destino |
|-------|---------------------------|
| `lrmi:educationalLevel` | Curso |
| `schema:about` | Asignatura |
| `dcterms:relation` | Eje temático |

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
| `dcterms:license` | Confirmado: cinco URI de Creative Commons (ver abajo) |

`lrmi:educationalLevel`, `schema:about` y `dcterms:relation` ya no son 
vocabularios controlados de literales: son ítems enlazados (ver «Ítems 
vinculados»).

### Licencias admitidas (`dcterms:license`)

El valor es la **URI**, no una etiqueta. El tema reconoce estas cinco y 
muestra el distintivo oficial (`helper/LicenseBadge.php`):

| Licencia | URI |
|----------|-----|
| CC0 1.0 | `https://creativecommons.org/publicdomain/zero/1.0/` |
| CC BY 4.0 | `https://creativecommons.org/licenses/by/4.0/` |
| CC BY-SA 4.0 | `https://creativecommons.org/licenses/by-sa/4.0/` |
| CC BY-NC 4.0 | `https://creativecommons.org/licenses/by-nc/4.0/` |
| CC BY-NC-SA 4.0 | `https://creativecommons.org/licenses/by-nc-sa/4.0/` |

El JSON-LD emite esa URI en `license`. Un valor que no sea URI no se emite.

---

## Facetas de búsqueda

Las facetas activas en Advanced Search son **configuración de la 
instancia**, no del tema. El tema renderiza las facetas que el módulo 
le proporcione, sin asumir un conjunto fijo.

El subconjunto inicial de facetas se define durante el desarrollo, 
coordinado entre Arquitecto y cliente.
