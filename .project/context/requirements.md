# Requisitos del proyecto

_Documento vivo. Se actualiza conforme se confirman o refinan requisitos._

Última actualización: 2026-04-27

---

## Requisitos funcionales

### Búsqueda facetada
- Las búsquedas facetadas se implementan mediante el módulo 
  **Advanced Search** de Omeka-S (no el buscador nativo).
- La barra de búsqueda avanzada debe estar **siempre visible en la 
  cabecera**, incluso cuando el menú principal está en modo flotante 
  solapando la cabecera institucional.
- El subconjunto exacto de facetas activas se define durante el 
  desarrollo, en coordinación entre Arquitecto y cliente, a partir 
  del modelo de metadatos documentado en `metadata_model.md`.

### Recursos educativos
- El repositorio aloja Recursos Educativos Abiertos descritos con un 
  modelo de metadatos basado en LRMI / Schema.org.
- Ver `metadata_model.md` para el modelo de referencia.

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

### Footer
- Footer institucional. Contenido y estructura a definir con el cliente.

---

## Requisitos técnicos

- Tema PHP para Omeka-S.
- El tema parte de un tema Omeka-S existente en este repositorio.
- Integración con módulo **Advanced Search** 
  (ref: Daniel-KM/Omeka-S-module-AdvancedSearch).
- Compatible con los vocabularios activos en la instancia Omeka-S 
  (ver `metadata_model.md`).
- Sistema de diseño basado en tokens CSS, definido por el Diseñador.
- JavaScript: enfoque preferente vanilla JS / módulos ES.

---

## Requisitos pendientes de confirmar

| Requisito | Estado | Responsable |
|-----------|--------|-------------|
| Identidad visual institucional | ⏳ Pendiente | Cliente |
| Contenido y estructura del footer | ⏳ Pendiente | Cliente |
| Facetas exactas de Advanced Search | ⏳ Pendiente | Arquitecto + Cliente |
| Versión exacta de Omeka-S en producción | ⏳ Pendiente | Cliente |
| Versión del módulo Advanced Search | ⏳ Pendiente | Cliente |
| Mecanismo de release del tema | ⏳ Pendiente | Arquitecto |
