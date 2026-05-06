# Hooks y eventos de Omeka-S

## Propósito
Referencia del sistema de eventos de Omeka-S para inyectar comportamiento en puntos específicos del ciclo de vida sin modificar el core, usado principalmente por Architect y Developer cuando el tema necesita extender funcionalidad sin un módulo completo.

## Cuándo invocar esta skill
- Al necesitar ejecutar código en un punto concreto del ciclo de renderizado.
- Al inyectar CSS o JS solo en páginas específicas (item show, search, etc.).
- Al querer interceptar operaciones de API (post-read, pre-search).
- Al registrar un listener desde el tema sin crear un módulo.

## Referencia técnica

### Registro de listeners desde el tema

Los temas Omeka-S **no tienen `Module.php`**. Para escuchar eventos desde un tema, se usa el archivo `Module.php` dentro de un módulo auxiliar. Si solo se necesita inyectar assets en páginas específicas, el enfoque más simple es condicional en la plantilla:

```php
// En view/layout/layout.phtml o en la propia plantilla de la vista
$route = $this->params()->fromRoute('action');
$controller = $this->params()->fromRoute('controller');

if ($controller === 'item' && $route === 'show') {
    $this->headScript()->appendFile($this->assetUrl('js/item-show.js', 'rea-ate'));
}
```

### Eventos principales del ciclo de vistas

| Evento | Cuándo se dispara | Uso típico |
|--------|-------------------|------------|
| `view.layout.head` | Antes de cerrar `<head>` | Inyectar CSS/JS globales |
| `view.browse.before` | Antes del listado de recursos | Añadir controles de filtro |
| `view.browse.after` | Después del listado | Añadir paginación custom |
| `view.show.before` | Antes del contenido del recurso | Añadir breadcrumb custom |
| `view.show.after` | Después del contenido | Añadir recursos relacionados |
| `view.search.before` | Antes de resultados de búsqueda | Insertar barra de filtros |
| `view.search.after` | Después de resultados | Añadir paginación |

En plantillas `.phtml`, estos triggers ya están invocados por el core:

```php
// El core llama esto en item/show.phtml
echo $this->trigger('view.show.before', ['resource' => $item], true);
// ... contenido del ítem ...
echo $this->trigger('view.show.after', ['resource' => $item], true);
```

Si el tema sobrescribe la plantilla, debe conservar estas llamadas para que los módulos puedan inyectar su contenido.

### Eventos de API

```php
// En Module.php de un módulo auxiliar
public function attachListeners(SharedEventManagerInterface $sharedEvents)
{
    // Después de leer un ítem (para modificar la representación)
    $sharedEvents->attach(
        'Omeka\Api\Adapter\ItemAdapter',
        'api.read.post',
        [$this, 'onItemReadPost']
    );

    // Antes de una búsqueda (para modificar los parámetros)
    $sharedEvents->attach(
        'Omeka\Api\Adapter\ItemAdapter',
        'api.search.pre',
        [$this, 'onItemSearchPre']
    );
}
```

### Diferencia: event listeners vs view helpers

| | Event listener | View helper |
|---|---|---|
| Registro | `Module.php` con `attachListeners` | `helper/` + `theme.ini` |
| Invocación | Automática (Omeka lo llama) | Manual (`$this->MiHelper()`) |
| Estado | Sin estado propio | Puede tener estado |
| Uso típico | Interceptar operaciones del sistema | Lógica reutilizable en plantillas |

### Inyectar CSS/JS solo en páginas específicas

**Desde la plantilla (preferido para el tema):**
```php
// En view/omeka/site/item/show.phtml
$this->headScript()->appendFile(
    $this->assetUrl('js/item-show.js', 'rea-ate'),
    'text/javascript',
    ['defer' => true]
);
```

**Desde layout.phtml con detección de ruta:**
```php
$routeName = $this->params()->fromRoute('__CONTROLLER__');
if (in_array($routeName, ['item', 'item-set'])) {
    $this->headLink()->appendStylesheet(
        $this->assetUrl('css/resource-pages.css', 'rea-ate')
    );
}
```

### Override de bloques de página (BlockPlus / core)

Los `resource_page_blocks` permiten que el gestor del sitio arrastre bloques a las regiones de las páginas de recurso. Para que el tema registre un bloque:

```ini
; En config/theme.ini
[resource_page_blocks]
items.main[]  = "block"
items.right[] = "block"
```

La plantilla del bloque se coloca en:
```
view/common/resource-page-block-layout/block.phtml
```

## Patrones frecuentes

```php
// Conservar triggers de módulos al sobrescribir show.phtml
echo $this->trigger('view.show.before', ['resource' => $item], true);
// ... HTML del tema ...
echo $this->trigger('view.show.after', ['resource' => $item], true);

// Trigger específico de media
echo $this->trigger('view.show.before', ['resource' => $media], true);
```

## Errores comunes
- Sobrescribir `item/show.phtml` y olvidar los `trigger()` — módulos como BlockPlus dejan de inyectar su contenido.
- Intentar registrar listeners en el tema sin `Module.php` — no funciona; usar condicionales en la plantilla.
- Usar `headScript()->prependFile()` en lugar de `appendFile()` para scripts propios — puede causar orden de carga incorrecto.

## Referencias externas
- Eventos de Omeka-S: https://omeka.org/s/docs/developer/events/
- Resource page blocks: https://omeka.org/s/docs/developer/themes/theme_api/#resource-page-blocks
