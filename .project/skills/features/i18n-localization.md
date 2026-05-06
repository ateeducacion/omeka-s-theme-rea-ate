# Internacionalización y localización

## Propósito
Referencia del sistema de traducción de Omeka-S (gettext) y cómo REA ATE gestiona los textos traducibles en plantillas PHP, para que Developer no hardcodee strings de UI en castellano sin pasar por el sistema i18n.

## Cuándo invocar esta skill
- Al añadir texto estático visible para el usuario en una plantilla.
- Al generar o actualizar el archivo `.pot` de traducciones.
- Al revisar si un string ya existe como traducción o debe añadirse.
- Al configurar el idioma del tema en Omeka-S.

## Referencia técnica

### Cómo funciona gettext en Omeka-S

Omeka-S usa gettext para traducciones. El flujo:
1. El código usa `__('Texto en inglés')` o `$this->translate('Texto en inglés')`.
2. Se extrae el `.pot` (template) con una herramienta de extracción.
3. Los traductores generan archivos `.po` (por idioma).
4. Se compilan a `.mo` (binario que PHP lee en runtime).

**Idioma base**: los strings en el código fuente están en **inglés** (convención Omeka-S). Las traducciones al español están en `language/es_ES.po`.

### Directorio de traducciones

```
language/
├── template.pot    # Template de strings extraídos (fuente)
├── es_ES.po        # Traducciones español de España
└── es_ES.mo        # Binario compilado (generado desde .po)
```

### Uso en plantillas PHP

```php
// Función global (alias de translate())
echo __('Search resources');         // → "Buscar recursos" en es_ES
echo __('Filter by educational level');

// View helper de Zend/Laminas
echo $this->translate('Collections');

// Con sprintf para interpolación
echo sprintf(__('%d resources found'), $total);

// En atributos HTML
<button aria-label="<?= $this->escapeHtmlAttr(__('Clear filters')) ?>">
```

**Regla del proyecto**: todo string de UI visible para el usuario debe pasar por `__()`. No hardcodear "Buscar", "Colecciones", etc. directamente en la plantilla.

### Idiomas objetivo de REA ATE

| Idioma | Código | Estado |
|---|---|---|
| Español (España) | `es_ES` | Principal — todas las cadenas traducidas |
| Español (Canarias) | — | No diferenciado, usa `es_ES` |
| Inglés | `en_US` | Strings base en el código fuente |

No se prevén más idiomas en este ciclo.

### Generar o actualizar el `.pot`

```bash
# Desde la raíz del tema, usando xgettext
find . -name "*.phtml" -o -name "*.php" | \
    xargs xgettext --language=PHP \
    --keyword=__ --keyword=translate \
    --from-code=UTF-8 \
    --output=language/template.pot

# Alternativa con compile-translations.js del proyecto
node compile-translations.js
# o
bash compile-translations.sh
```

### Actualizar la traducción `.po`

```bash
# Fusionar el .pot actualizado con la traducción existente
msgmerge --update language/es_ES.po language/template.pot

# Editar es_ES.po con Poedit u otro editor
# Compilar .po a .mo
msgfmt language/es_ES.po -o language/es_ES.mo
```

### Gestión de plurales

```php
// Para strings pluralizables
echo $this->translatePlural('%d colección', '%d colecciones', $count, $count);
// o usando ngettext
echo ngettext('%d collection', '%d collections', $count);
```

### Detectar el idioma activo en PHP

```php
$locale = $this->i18n()->getLocale(); // 'es_ES', 'en_US'…
$lang   = substr($locale, 0, 2);      // 'es', 'en'…
```

### Atributo `lang` en el HTML

El layout debe incluir el atributo `lang` en `<html>` (criterio WCAG 3.1.1):

```php
// En layout.phtml
<html lang="<?= $this->i18n()->getLocale() ?>">
```

## Patrones frecuentes

```php
// String con variable numérica
sprintf(__('Showing %d of %d resources'), $showing, $total)

// Cadena ya definida como constante PHP para reusar
define('STR_FILTER_ALL', __('All'));
```

## Errores comunes
- Hardcodear strings en castellano (`echo "Buscar"`) — no son traducibles y generan inconsistencias si el sitio cambia de idioma.
- Poner strings en variables antes de llamar a `__()` — xgettext no los extrae al `.pot`. Siempre el literal directamente en `__('literal')`.
- Olvidar compilar `.mo` después de actualizar `.po` — Omeka-S lee el `.mo`, si está desactualizado las nuevas traducciones no aparecen.
- Usar `$this->translate()` en helpers PHP fuera del contexto de vista — usar el servicio `$this->getServiceLocator()->get('MvcTranslator')`.

## Referencias externas
- Omeka-S i18n: https://omeka.org/s/docs/developer/internationalization/
- Poedit (editor .po): https://poedit.net/
- gettext manual: https://www.gnu.org/software/gettext/manual/
