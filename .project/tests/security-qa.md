# QA de seguridad — runbook de verificación

Plan de pruebas de las correcciones de la auditoría (rama `security/audit-fixes`).

> **Aviso**: la instancia Docker de desarrollo contiene un volcado realista (12.609 ítems,
> 4 sitios). Las pruebas 5–9 requieren poner valores maliciosos en ajustes del tema.
> Haga siempre copia del blob de ajustes antes y restáurelo después (ver
> [Inyectar payloads](#inyectar-payloads)). Las pruebas 1–4 y 10 no tocan configuración.

Datos de la instancia usados en los ejemplos:

| Dato | Valor |
|------|-------|
| Sitio | `ceiplajares` (site_id `1`) |
| Ítem con descripción | `2759` |
| Panel de ajustes del tema | `http://localhost:8080/admin/site/s/ceiplajares/theme` |
| Contenedor BD | `omeka-s-moduletemplate-mariadb-1` |

---

## A. Automatizadas — ejecutar ahora

Sin navegador y sin tocar la instancia:

```bash
php .project/tests/security-helpers-test.php    # SafeUrl, CssToken, HtmlAllowlist
node .project/tests/api-content-type-test.js    # guarda de Content-Type de la API
grep -rn "innerHTML\|insertAdjacentHTML" asset/js/ | grep -v minimasonry   # debe salir vacío
npm run build                                   # compila Sass sin errores
make package VERSION=0.0.0-test && unzip -l rea-ate-0.0.0-test.zip | grep -c "\.claude" ; rm -f rea-ate-0.0.0-test.zip   # debe dar 0
```

Cubren los puntos **11** y **12** del plan, más la lógica de los tres helpers.

---

## B. Requieren navegador

Abra la consola de DevTools en todas ellas: el criterio es **que no salte ningún `alert`
y que no aparezca ningún error de JS**.

### 1. XSS en descripción de ítem (C1 — crítico)

1. Admin → Items → Add. En `dcterms:description` ponga:
   `<img src=x onerror=alert('XSS')>`
2. Cree un segundo ítem con una propiedad de tipo *resource* (p. ej. `dcterms:relation`)
   que enlace al primero, y añádalo al sitio `ceiplajares`.
3. Abra el segundo ítem en el sitio público y pulse el botón `+` del enlace.

**Esperado:** se muestra el texto literal `<img src=x onerror=alert('XSS')>`. No salta alert.
En el inspector, el contenido del `<p>` debe ser un nodo `#text`, no un `<img>`.

> Si sale «Error loading info.», compruebe primero que la API responde:
> `curl -D - http://localhost:8080/api/items/2759 -o /dev/null`
> Debe devolver `200` y `Content-Type: application/ld+json`.

### 2. XSS en chips de faceta (A1)

1. Cree un ítem cuyo valor de una propiedad facetada (p. ej. `schema:about`) sea
   `<script>alert('facet')</script>`.
2. Vaya a la búsqueda avanzada del sitio y marque esa faceta.

**Esperado:** el chip muestra el texto con los `<script>` visibles como texto. No salta alert.

### 3. XSS en pills de búsqueda educativa (A2)

1. Cree una colección (item set) cuyo título contenga `<img src=x onerror=alert('pill')>`.
2. En el formulario de búsqueda educativa, selecciónela en el desplegable de colecciones.

**Esperado:** la pill muestra el título como texto plano y el `aria-label` del botón de
quitar es correcto. No salta alert.

### 4. href malicioso en chips (N3) — sin preparación

En cualquier página de resultados con facetas, pegue en la consola:

```js
const cb = document.querySelector('.search-facets input[type="checkbox"]');
cb.dataset.url = 'javascript:alert("href")';
cb.checked = true;
// Fuerza la reconstrucción de los chips
document.dispatchEvent(new Event('DOMContentLoaded'));
document.querySelectorAll('.filter-chip').forEach(c => console.log(c.getAttribute('href')));
```

**Esperado:** el `href` impreso es `#`, nunca `javascript:...`.

### 10. No regresión funcional

Recorra a mano, en escritorio y en móvil (DevTools responsive):

- Navegación de escritorio: submenús se abren con hover y con teclado.
- Drawer móvil: abrir, entrar en un submenú, botón «Anterior», botón «Cerrar».
- Toggle grid/lista en páginas de browse; la paginación conserva el modo.
- Banner con y sin heading/descripción.
- Footer: logo, menú, contenido y copyright; **los enlaces externos deben seguir
  abriéndose en pestaña nueva**.
- Toolbox de admin: borrar un ítem desde el modal (el token CSRF debe seguir siendo válido).

---

## C. Verificables desde la línea de comandos

Estas se comprueban en el HTML servido, sin navegador. Requieren inyectar un payload.

### Inyectar payloads

```bash
# 1. COPIA DE SEGURIDAD (imprescindible)
docker exec omeka-s-moduletemplate-mariadb-1 mariadb -uomeka -pomeka omeka \
  --skip-column-names --raw \
  -e "SELECT value FROM site_setting WHERE id='theme_settings_rea-ate' AND site_id=1;" \
  > /tmp/theme-settings-backup.json
wc -c /tmp/theme-settings-backup.json   # debe ser > 0 antes de continuar

# 2. Inyectar (ejemplo: banner_width)
docker exec omeka-s-moduletemplate-mariadb-1 mariadb -uomeka -pomeka omeka -e \
  "UPDATE site_setting
     SET value = JSON_SET(value, '\$.banner_width', '\"><img src=x onerror=alert(1)>')
   WHERE id='theme_settings_rea-ate' AND site_id=1;"

# 3. …ejecutar la comprobación…

# 4. RESTAURAR
docker exec -i omeka-s-moduletemplate-mariadb-1 mariadb -uomeka -pomeka omeka -e \
  "UPDATE site_setting SET value = LOAD_FILE('/dev/stdin')
   WHERE id='theme_settings_rea-ate' AND site_id=1;" < /tmp/theme-settings-backup.json
```

Los ajustes de tipo `Text` y `HtmlTextarea` (`banner_height`, `banner_height_mobile`,
`home_audience_*_url`, `anclaje_div_class`, `footer_content`, `footer_site_info`,
`footer_copyright`) se pueden poner **directamente desde el panel de ajustes del tema**,
que es más cómodo y reversible. Los de tipo `Select`, `Color` y `Url` (`banner_width`,
`banner_content_position`, `header_layout`, `primary_color`, `logos_bar_link_N`) sólo se
pueden forzar por BD, porque el formulario los valida — que es justamente la razón por la
que estos hallazgos son defensa en profundidad y no vulnerabilidades explotables por UI.

### 5. Breakout de CSS (A3)

Ponga `banner_height_mobile` = `100px;}</style><script>alert(1)</script>` (panel de ajustes).

```bash
curl -s http://localhost:8080/s/ceiplajares/item/2759 | grep -c '</style><script>'   # → 0
curl -s http://localhost:8080/s/ceiplajares/item/2759 | grep -o 'max-width:768px.\{0,80\}'
```

**Esperado:** `0` coincidencias del breakout. Al ser inválido, `CssToken::length()` cae al
fallback vacío y el bloque `@media` no se emite. Repita con `banner_height`.

### 6. Breakout de atributo (N1)

Requiere un banner configurado (si `banner` está vacío, la plantilla no renderiza nada).
Inyecte `banner_width` = `"><img src=x onerror=alert(1)>` por BD.

```bash
curl -s http://localhost:8080/s/ceiplajares/ | grep -o 'class="banner[^"]*"'
curl -s http://localhost:8080/s/ceiplajares/ | grep -c 'onerror=alert'   # → 0
```

**Esperado:** la clase queda en `banner` (el valor inválido se descarta por allowlist) y no
aparece ningún `<img onerror>`.

### 7. URL peligrosa (A5)

`logos_bar_link_1` = `javascript:alert(1)` (por BD), con `logos_bar_logo_1` ya configurado:

```bash
curl -s http://localhost:8080/s/ceiplajares/ | grep -o '<a href="javascript[^"]*"'   # vacío
curl -s http://localhost:8080/s/ceiplajares/ | grep -o '<span class="logos-bar__item">' | head -1
```

**Esperado:** ningún `href="javascript:`; el logo se renderiza dentro de un `<span>`.

Para el open redirect, ponga `home_audience_teachers_url` = `//example.org` (panel de
ajustes) y compruebe en la **portada** del sitio:

```bash
curl -s http://localhost:8080/s/ceiplajares/ | grep -o 'class="audience-card[^"]*" href="[^"]*"'
```

**Esperado:** ninguna tarjeta con `href="//example.org"`.

### 8. Catálogo de traducciones (N2)

Requiere editar `language/es.po`, recompilar (`npm run compile-translations`) y reiniciar.
Ponga como traducción de `Close` la cadena `</script><script>alert(1)</script>`.

```bash
curl -s http://localhost:8080/s/ceiplajares/ | grep -o 'const closeText = .\{0,80\}'
```

**Esperado:** la cadena aparece escapada con `<`, sin cerrar el bloque `<script>`.

### 9. HTML del footer (M1)

Ponga en `footer_content` (panel de ajustes) este bloque:

```html
<p>Texto <strong>legítimo</strong></p>
<script>alert('footer')</script>
<iframe src="https://example.org"></iframe>
<p onclick="alert('handler')">con handler</p>
<a href="javascript:alert('href')">enlace malo</a>
<a href="https://example.org" target="_blank">enlace bueno</a>
<div style="position:fixed;top:0">overlay</div>
```

```bash
P=$(curl -s http://localhost:8080/s/ceiplajares/item/2759)
for bad in "<script>alert('footer')" "<iframe" "onclick=" "href=\"javascript:" "style=\"position:fixed"; do
  printf '%-32s %s\n' "$bad" "$(grep -c -- "$bad" <<<"$P")"
done
grep -o '<a href="https://example.org"[^>]*>' <<<"$P"
```

**Esperado:** todos los recuentos a `0`, y el enlace bueno conservado con
`target="_blank" rel="noopener noreferrer"` (el purificador fuerza el `rel`).
El texto legítimo y el `<strong>` se conservan.
