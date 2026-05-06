# Anatomía del tema Omeka-S

## Propósito
Referencia de la estructura obligatoria de un tema Omeka-S, la jerarquía de resolución de plantillas y los view helpers del core para que Developer no tenga que inferir la convención de Omeka-S desde cero.

## Cuándo invocar esta skill
- Al crear o mover plantillas PHP.
- Al decidir dónde sobrescribir una plantilla de un módulo.
- Al necesitar un view helper del core (`thumbnail`, `breadcrumbs`, etc.).
- Al configurar `config/theme.ini`.

## Referencia técnica

### Estructura de directorios obligatoria

```
mi-tema/
├── config/
│   └── theme.ini          # metadatos y configuración del tema (OBLIGATORIO)
├── view/
│   ├── layout/
│   │   └── layout.phtml   # plantilla base HTML
│   ├── omeka/
│   │   └── site/
│   │       ├── item/
│   │       │   ├── browse.phtml
│   │       │   └── show.phtml
│   │       ├── item-set/
│   │       │   ├── browse.phtml
│   │       │   └── show.phtml
│   │       └── media/
│   │           └── show.phtml
│   └── common/            # partials y componentes reutilizables
├── asset/
│   ├── css/               # CSS compilado (no editar directamente)
│   ├── js/                # JavaScript
│   ├── sass/              # fuentes SASS
│   └── img/               # imágenes del tema
├── language/              # archivos de traducción .po/.mo
└── helper/                # view helpers PHP del tema
```

### `config/theme.ini` — campos obligatorios

```ini
[info]
name          = "REA ATE"
version       = "0.2.0"
author        = "ATE Canarias"
description   = "Tema para el repositorio de Recursos Educativos Abiertos de Canarias"
omeka_version_constraint = "^4.0"

; View helpers registrados por el tema
helpers[]     = "ResourceTags"
helpers[]     = "SlugifyValues"

; Configuración del tema (campos del panel de admin)
[config]
; Ejemplo de campo de texto
[config.element.footer_text]
type          = "Text"
options.label = "Texto del pie de página"

; Ejemplo de campo multimedia
[config.element.logo]
type          = "Asset"
options.label = "Logo del sitio"

; Bloques de página de recurso
[resource_page_blocks]
items.main[]  = "block"   ; activa el bloque de anclaje curricular en main
items.right[] = "block"
```

### Jerarquía de resolución de plantillas

Omeka-S busca la plantilla en este orden (el primero que existe gana):

1. **Tema activo** — `view/omeka/site/item/show.phtml`
2. **Módulo** — `modules/MiModulo/view/omeka/site/item/show.phtml`
3. **Core de Omeka-S** — `application/view/omeka/site/item/show.phtml`

Para sobrescribir una plantilla de módulo, copia la ruta exacta del módulo dentro de `view/` del tema. Ejemplo:
```
modules/AdvancedSearch/view/advanced-search/search/results.phtml
→ view/advanced-search/search/results.phtml  (en el tema)
```

### View helpers del core más usados

```php
// URL de un recurso en el sitio
$this->itemUrl($item)          // URL completa del item
$itemSet->siteUrl()            // URL de la colección (método del objeto)

// Thumbnails
$this->thumbnail($item, 'large')       // img HTML
$this->thumbnail($item, 'medium')
$this->thumbnail($item, 'square')
// Tamaños disponibles: large (800px), medium (200px), square (200×200)

// Breadcrumbs nativos
$this->breadcrumbs()           // devuelve HTML del breadcrumb

// Traducción
$this->translate('Buscar')
__('Buscar')                   // alias global

// Paginación
$this->pagination()            // HTML del paginador (usa estado global)

// Assets del tema
$this->assetUrl('css/style.css', 'rea-ate')   // URL a asset del tema
$this->assetUrl('js/app.js',    'rea-ate')

// Inyectar CSS/JS en el head desde una plantilla
$this->headScript()->appendFile($this->assetUrl('js/filtros.js', 'rea-ate'));
$this->headLink()->appendStylesheet($this->assetUrl('css/extra.css', 'rea-ate'));

// Bloques de página configurados en theme.ini
$this->blockContent('footer_text')

// Sitio actual
$site   = $this->currentSite();
$siteId = $site->id();
```

### Estructura de `view/layout/layout.phtml`

El layout envuelve todas las páginas. Partes clave:

```php
<!DOCTYPE html>
<html>
<head>
    <?= $this->headMeta() ?>
    <?= $this->headTitle() ?>
    <?= $this->headLink() ?>  <!-- CSS, canonicals -->
    <style>/* tokens CSS inline */</style>
    <?= $this->headScript() ?> <!-- JS deferido -->
</head>
<body>
    <?= $this->header() ?>    <!-- nav principal -->
    <main>
        <?= $this->content() ?>  <!-- contenido de la vista actual -->
    </main>
    <?= $this->footer() ?>
    <?= $this->inlineScript() ?>  <!-- JS al final del body -->
</body>
</html>
```

## Patrones frecuentes

```php
// Partial reutilizable
echo $this->partial('common/mi-componente', ['item' => $item]);

// Detectar si hay sidebar activo
$hasSidebar = (bool) $this->params()->fromRoute('sidebar');

// Detectar el módulo AdvancedSearch para breadcrumb
try {
    $searchUrl = $this->url('site/search', ['site-slug' => $site->slug()]);
} catch (\Exception $e) {
    $searchUrl = $this->url('site/resource', ['site-slug' => $site->slug(), 'controller' => 'item', 'action' => 'browse']);
}
```

## Errores comunes
- Poner lógica de negocio compleja en plantillas `.phtml` — moverla a un view helper en `helper/`.
- Editar `asset/css/style.css` directamente — es un archivo compilado, los cambios se pierden al compilar.
- No registrar los helpers en `theme.ini` con `helpers[]` — el helper no estará disponible en las plantillas.
- Usar rutas relativas en `assetUrl` — siempre pasar el nombre del tema como segundo argumento.

## Referencias externas
- Temas Omeka-S: https://omeka.org/s/docs/developer/themes/
- View helpers disponibles: https://omeka.org/s/docs/developer/themes/theme_api/
