# Arquitectura del tema

_Documento generado durante el proyecto. Se completa conforme el 
Arquitecto registra decisiones en `.project/decisions/architect.md`._

## Áreas a documentar

- Inventario del tema base: plantillas existentes y estructura de assets.
- Mapa de plantillas `.phtml`: cuáles se heredan, cuáles se sobreescriben, 
  cuáles se crean nuevas.
- Estrategia de integración con Advanced Search:
  - Plantillas del módulo sobreescritas o extendidas.
  - Configuración del índice (a nivel conceptual; los IDs internos 
    son configuración de la instancia).
  - Integración de la barra de búsqueda en el header.
- Mapa de hooks y eventos Omeka-S utilizados.
- Gestión de assets: carga, orden de dependencias.
- Jerarquía de plantillas del header.
- Mecanismo de release del tema.

---

## Notas vigentes

### `item-set/browse` — filtro de colecciones

- El filtro de colecciones se resuelve sobre metadatos del propio `item set`, no agregando valores desde los ítems hijos en tiempo de render.
- Orden de preferencia acordado para lectura (corregido en QA-006, 2026-05-06):
  - `Etapa`: `lrmi:educationalAlignment` (sin fallback; semánticamente distinto de nivel)
  - `Nivel`: `lrmi:educationalLevel` (preferido), fallback `lom:educationalLevel`
  - `Temática`: `schema:about` (preferido), fallback `dcterms:subject`
- Motivo arquitectónico: mantener browse barato de renderizar, estable y alineado con el modelo de metadatos del proyecto.
