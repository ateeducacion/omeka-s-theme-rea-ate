# Skill: Theme Creator (transversal)

## Cuándo usar este skill
Cuando se trabaja con la estructura de un tema Omeka-S: crear, modificar, 
extender plantillas, organizar assets, configurar el archivo del tema.

## Estructura estándar de un tema Omeka-S

```
config/
└── theme.ini                  # Configuración del tema (nombre, versión, autor)
view/
├── layout/
│   └── layout.phtml           # Layout principal
├── common/                    # Parciales reutilizables
└── omeka/
    └── site/                  # Plantillas de páginas (index, item, browse...)
asset/
├── css/
├── js/
└── img/
```

## Reglas para extender un tema base

- **No modificar archivos del tema base sin necesidad.** Preferir extender 
  o sobreescribir desde plantillas propias.
- Cuando se sobreescriba una plantilla del tema base, mantener la misma 
  ruta y nombre dentro de `view/` para que Omeka-S la resuelva.
- Las plantillas nuevas específicas del proyecto van en `view/common/` o 
  en la ruta correspondiente al elemento de Omeka-S que renderizan.
- Los assets nuevos se añaden sin sustituir los existentes salvo decisión 
  explícita del Arquitecto.

## Plantillas `.phtml`: bloque de comentario inicial

Toda plantilla creada o modificada significativamente en el proyecto 
debe llevar al inicio un bloque de comentario PHP con:

```php
<?php
/**
 * Plantilla: [nombre y propósito]
 * Rol responsable: [Arquitecto / Desarrollador]
 * Fecha: [YYYY-MM-DD]
 * Decisión origen: [ruta a decisión en .project/decisions/ + fecha]
 */
?>
```

## Configuración del tema (`config/theme.ini`)

Mínimo requerido por Omeka-S:
- `name`: nombre del tema
- `description`: descripción breve
- `author`: autor o entidad
- `theme_link`: URL del repositorio

Cualquier campo adicional (versión, configuraciones de tema, etc.) lo 
decide el Arquitecto.

## Releases del tema

Las releases empaquetan únicamente el contenido del tema Omeka-S, 
excluyendo `.project/` y archivos de coordinación.

El mecanismo concreto (script, GitHub Action, archivo manual) lo decide 
el Arquitecto y se registra en una decisión.
