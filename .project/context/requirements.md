# Requisitos del proyecto

_Documento vivo. Se actualiza conforme se confirman o refinan requisitos._

Última actualización: 2026-05-07

---

## Requisitos funcionales

### Búsqueda facetada
- Las búsquedas facetadas se implementan mediante el módulo 
  **Advanced Search** de Omeka-S (no el buscador nativo).
  - Versión confirmada: **3.4.60** (Daniel-KM/Omeka-S-module-AdvancedSearch)
- La barra de búsqueda avanzada debe estar **siempre visible en la 
  cabecera**, incluso cuando el menú principal está en modo flotante 
  solapando la cabecera institucional.
- **Facetas activas confirmadas** (2026-04-27):
  - `lrmi:EducationalLevel` — Nivel educativo
  - `schema:about` — Materia / área temática
  - `lrmi:LearningResourceType` — Tipo de recurso educativo
  - `lrmi:timeRequired` — Tiempo estimado
  - `dcterms:relation` — Relación / recurso relacionado
- **Motor de búsqueda**: índice interno de Advanced Search (sin Solr ni Elasticsearch por ahora).

### Recursos educativos
- El repositorio aloja Recursos Educativos Abiertos descritos con un 
  modelo de metadatos basado en LRMI / Schema.org.
- Ver `metadata_model.md` para el modelo de referencia.

### Bloque de visibilidad institucional (cofinanciación de proyectos)

Añadido: 2026-05-07.

Cada ítem REA puede estar vinculado a un ítem de tipo `schema:Project` mediante la propiedad `schema:isPartOf`. Si el vínculo existe, la página de detalle del REA (`item/show`) debe mostrar un **bloque de visibilidad institucional** con los datos del proyecto.

**Modelo de datos:**
- `schema:isPartOf` en el ítem REA → enlaza a un ítem Omeka-S de tipo `schema:Project`
- El ítem Project contiene:
  - `schema:name` → nombre oficial del proyecto
  - `schema:description` → texto legal de cofinanciación (ej: "Cofinanciado por la Unión Europea — FEDER 2021-2027")
  - `schema:logo` → recurso media (imagen) adjunto al ítem Project
  - `schema:url` → enlace a la ficha oficial del programa
- Un REA pertenece a un proyecto como máximo. Algunos REA no tienen proyecto.

**Requisito funcional:**
- Si el REA tiene `schema:isPartOf` apuntando a un `schema:Project`, mostrar el bloque con logo, texto legal y enlace.
- Si no hay proyecto vinculado, el bloque no se muestra.

**Requisitos técnicos:**
- Implementar como partial reutilizable `view/common/project-funding.phtml`
- Recuperar el ítem Project navegando `schema:isPartOf` desde el ítem REA vía la API de Omeka-S
- El logo se obtiene como media adjunta al ítem Project (`schema:logo`)
- El bloque debe ser estilizable de forma independiente (clase raíz `.project-funding`)
- Invocable desde `item/show.phtml` y cualquier otra plantilla futura

---

## Requisitos de layout

### Header
- Dos secciones diferenciadas:
  1. **Banda institucional superior**: logotipo, nombre de la institución, 
     enlaces institucionales.
  2. **Menú principal**: flotante (sticky), solapa la banda institucional 
     al hacer scroll.
- La barra de búsqueda avanzada permanece visible y accesible en ambos 
  estados.
- El **menú principal** se gestiona desde la navegación de Omeka-S. 
  El tema no debe interferir en su estructura ni administración — 
  solo renderizarlo correctamente.

### Audiencias
- Tres perfiles definidos: **Profesorado, Alumnado, Familias**.
  - Prioridad de desarrollo: Profesorado y Alumnado son los principales.
  - Familias como tercer perfil, menor prioridad.

### Footer
- Footer **configurable mediante las opciones del tema** — se mantiene 
  la estructura por defecto del tema base.
- Logo: se usa el logotipo del Gobierno de Canarias ya presente en el 
  repositorio. No requiere cambios.

---

## Requisitos técnicos

- Tema PHP para **Omeka-S 4.2** (versión confirmada en producción).
- El tema parte de un tema Omeka-S existente en este repositorio.
- Integración con módulo **Advanced Search 3.4.60** 
  (ref: Daniel-KM/Omeka-S-module-AdvancedSearch).
- Compatible con los vocabularios activos en la instancia Omeka-S 
  (ver `metadata_model.md`).
- Sistema de diseño basado en tokens CSS, definido por el Diseñador.
- JavaScript: enfoque preferente vanilla JS / módulos ES.
- **Release**: pipeline de GitHub Actions (a definir por el Arquitecto).

---

## Requisitos pendientes de confirmar

| Requisito | Estado | Responsable |
|-----------|--------|-------------|
| Identidad visual institucional | ✅ Confirmada — ver DESIGN.md | — |
| Contenido y estructura del footer | ✅ Confirmado — opciones del tema, logo GobCan existente | — |
| Facetas exactas de Advanced Search | ✅ Confirmadas — 5 campos LRMI/Schema | — |
| Versión exacta de Omeka-S en producción | ✅ 4.2 | — |
| Versión del módulo Advanced Search | ✅ 3.4.60 | — |
| Mecanismo de release del tema | ✅ Operativo — GitHub Actions + Makefile | Arquitecto |
| Audiencias y prioridades | ✅ Profesorado y Alumnado (principales), Familias (secundario) | — |
