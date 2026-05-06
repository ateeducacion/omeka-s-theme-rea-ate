# Renderizado de medias en Omeka-S

## Propósito
Referencia de los tipos de media soportados por Omeka-S, cómo renderizarlos desde plantillas PHP y cómo el tema REA ATE los presenta en el grid de medias de `item/show`.

## Cuándo invocar esta skill
- Al modificar `media-embeds.phtml` o `media-list.phtml`.
- Al añadir soporte para un nuevo tipo de media.
- Al implementar un fallback cuando no hay thumbnail.
- Al gestionar la descarga de archivos adjuntos.

## Referencia técnica

### Tipos de media en Omeka-S

| Tipo (`$media->ingester()`) | Renderer | Descripción |
|---|---|---|
| `upload` | Varía por MIME | Archivo subido (imagen, PDF, audio, vídeo) |
| `url` | `html` | URL externa embebida como iframe |
| `iiif-image` | `iiif-image` | Imagen IIIF con visor |
| `youtube` | `youtube` | Vídeo de YouTube embebido |
| `html` | `html` | HTML arbitrario (para SCORM, iframes custom) |
| `oembed` | `oembed` | Contenido oEmbed (Vimeo, SoundCloud…) |

En REA ATE el tipo más crítico además de `upload` es el **SCORM** (módulo LearningObject), que usa el ingester `LearningObject` y renderer también `LearningObject`.

```php
// Detectar SCORM
if ($media->ingester() === 'LearningObject' || $media->renderer() === 'LearningObject') {
    // renderizar con .scorm-card
}
```

### Renderizar cada tipo desde PHP

```php
// Renderizado automático (delega al renderer del media)
echo $media->render(); // HTML generado por Omeka-S

// Thumbnail
echo $media->thumbnailUrl('large');    // URL de imagen grande (800px)
echo $media->thumbnailUrl('medium');   // URL de imagen media (200px)
echo $media->thumbnailUrl('square');   // URL de imagen cuadrada (200×200)

// URL original del archivo
echo $media->originalUrl();

// URL de descarga (fuerza Content-Disposition: attachment)
echo $media->downloadUrl();

// Tipo MIME
echo $media->mediaType(); // 'image/jpeg', 'video/mp4', 'application/pdf'…

// Título del media (puede ser el nombre del archivo)
echo $media->displayTitle();

// Detectar si es imagen
$isImage = str_starts_with($media->mediaType(), 'image/');
```

### Thumbnails — tamaños disponibles y fallback

| Alias | Dimensiones | Uso |
|---|---|---|
| `large` | 800px ancho máx | Detalle de ítem, galería |
| `medium` | 200px ancho máx | Cards de resultado, list view |
| `square` | 200×200px recortado | Grid de colecciones |

Fallback cuando no hay thumbnail:
```php
$thumb = $item->primaryMedia()?->thumbnailUrl('large');
if (!$thumb) {
    // Icono genérico según tipo de recurso
    $icon = 'article'; // Material Symbol
}
?>
<?php if ($thumb): ?>
    <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($item->displayTitle()) ?>">
<?php else: ?>
    <div class="no-thumbnail">
        <span class="material-symbols-outlined" aria-hidden="true"><?= $icon ?></span>
    </div>
<?php endif; ?>
```

### Grid de medias en item/show (implementación actual)

El archivo `view/common/resource-page-block-layout/media-embeds.phtml` renderiza el grid unificado de medias. Implementación actual:
- Grid CSS con `grid-template-columns: repeat(2, 1fr)` por defecto.
- `iframe`/SCORM ocupan `grid-column: span 2` (ancho completo).
- El título del archivo (`displayTitle()`) está oculto — solo se muestra el contenido.
- SCORM envuelto en `.scorm-card` con cabecera azul y botones de launch/download.

```php
foreach ($item->media() as $media):
    $isScorm = $media->ingester() === 'LearningObject';
    $cssClass = $isScorm ? 'media-scorm' : 'media-item';
?>
<div class="<?= $cssClass ?>">
    <?= $media->render() ?>
</div>
<?php endforeach; ?>
```

### Media list en sidebar (media-list.phtml)

Muestra miniaturas en el sidebar izquierdo de `item/show`. Usa `linkRaw()` con miniatura en lugar de `linkPretty()` para evitar el nombre de archivo como texto del enlace.

```php
foreach ($item->media() as $media):
    echo $media->linkRaw(
        $this->thumbnail($media, 'square', ['alt' => '']),
        ['class' => 'media-thumb-link']
    );
endforeach;
```

### Lightbox sin librerías pesadas

Patrón implementado (si aplica): cada imagen en el grid tiene `data-lightbox-src` con la URL `large`. Un pequeño script JS escucha clicks en imágenes y abre un overlay con la imagen original.

```js
document.querySelectorAll('[data-lightbox-src]').forEach(function (img) {
    img.addEventListener('click', function () {
        openLightbox(this.dataset.lightboxSrc, this.alt);
    });
});
```

## Patrones frecuentes

```php
// Acceder al primer media del ítem
$primaryMedia = $item->primaryMedia();
$thumbUrl = $primaryMedia?->thumbnailUrl('medium');

// Listar solo imágenes
foreach ($item->media() as $media) {
    if (!str_starts_with($media->mediaType(), 'image/')) continue;
    // procesar imagen
}

// Descarga de archivo protegida
<a href="<?= $media->downloadUrl() ?>" download>
    Descargar (<?= strtoupper(pathinfo($media->source(), PATHINFO_EXTENSION)) ?>)
</a>
```

## Errores comunes
- Usar `$media->source()` para mostrar la URL pública del archivo — `source()` devuelve el nombre/ruta original del archivo en el servidor, no la URL pública. Usar `originalUrl()` o `thumbnailUrl()`.
- Llamar `$media->render()` para imágenes y esperar solo un `<img>` — algunos renderers devuelven HTML más complejo (figure + figcaption).
- No poner `aria-hidden="true"` en thumbnails dentro de tarjetas que ya tienen texto de título — duplicaría la info para lectores de pantalla.
- Usar `displayTitle()` para imágenes subidas — suele ser el nombre del archivo (ej. `IMG_4523.jpg`). Usar el `displayTitle()` del ítem padre si necesitas un alt descriptivo.

## Referencias externas
- Omeka-S Media: https://omeka.org/s/docs/user-manual/content/media/
- Módulo LearningObject (SCORM): https://github.com/Daniel-KM/Omeka-S-module-LearningObject
