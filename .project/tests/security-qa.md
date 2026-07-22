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

## B. Pruebas 1–4: automatizadas con el banco XSS

Las cuatro pruebas de XSS en JavaScript están cubiertas por
`asset/tests/xss-harness.html`, que carga los ficheros **reales** de `asset/js/`, monta los
fixtures DOM que cada script espera, inyecta los payloads y comprueba el resultado.
Intercepta `window.alert`, así que un fallo de defensa se detecta solo: no hay que vigilar
la pantalla.

```bash
./.project/tests/run-xss-harness.sh              # Chrome headless, sale 0 si todo pasa
./.project/tests/run-xss-harness.sh http://otro-host:8080
```

O ábralo en el navegador para inspeccionarlo a mano:

    http://localhost:8080/themes/rea-ate/asset/tests/xss-harness.html

El banco se sirve desde la propia instancia de Omeka para correr en el mismo origen
(`fetch` y `sessionStorage` reales). Está excluido del ZIP de release.

**Control negativo** (comprobar que el banco de verdad detecta fallos): reintroduzca el
sink vulnerable en `advanced-search-list.js` sustituyendo la construcción de chips por
`chip.innerHTML = ...` y `chip.href = cb.dataset.url || '#'`. El banco debe pasar a
`HARNESS-FAIL` con 5 fallos, incluido `alerts: facet` — es decir, el XSS se ejecuta de
verdad. Restaure después con `git checkout asset/js/advanced-search-list.js`.

### Cobertura del banco

| Prueba | Qué verifica |
|--------|--------------|
| 1 (C1) | La descripción de la API se pinta como texto; no hay nodos inyectados; no sale «Error loading info.» |
| 2 (A1) | El valor y el nombre de grupo de la faceta no se interpretan como markup |
| 3 (A2) | La etiqueta de colección y el `aria-label` de la pill son texto plano |
| 4 (N3) | Un `data-url="javascript:..."` se neutraliza a `href="#"` |
| global | Ningún `alert()` llegó a ejecutarse |

### Verificación end-to-end en la aplicación real (opcional)

El banco prueba el JavaScript de forma aislada con un `fetch` interceptado. Para la
comprobación completa contra datos reales, cree en el admin un ítem con
`dcterms:description` = `<img src=x onerror=alert('XSS')>`, enlácelo desde otro ítem con
una propiedad de tipo *resource* y pulse el botón `+` en el sitio público. Debe verse el
texto literal y ningún alert.

---

## B-bis. Prueba 10: requiere navegador y persona

Es la única que no se puede automatizar de forma útil. Criterio en todas: **que no salte
ningún `alert` y que no haya errores en la consola de DevTools**.

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
