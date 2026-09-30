#!/usr/bin/env bash
#
# Ejecuta el banco de pruebas XSS (asset/tests/xss-harness.html) en Chrome headless
# y devuelve 0 si todas las comprobaciones pasan.
#
# El banco carga los ficheros reales de asset/js/ y ejecuta las pruebas 1-4 del plan
# de verificación de seguridad. Se sirve desde la propia instancia de Omeka para que
# corra en el mismo origen (fetch y sessionStorage reales, sin restricciones de file://).
#
#   ./.project/tests/run-xss-harness.sh [base-url]
#
# Por defecto usa http://localhost:8080 (docker-compose.yml de este repo).

set -euo pipefail

BASE_URL="${1:-http://localhost:8080}"
URL="${BASE_URL}/themes/rea-ate/asset/tests/xss-harness.html"

CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"
if [ ! -x "$CHROME" ]; then
    for candidate in \
        /Applications/Chromium.app/Contents/MacOS/Chromium \
        "$(command -v google-chrome || true)" \
        "$(command -v chromium || true)" \
        "$(command -v chromium-browser || true)"
    do
        if [ -n "$candidate" ] && [ -x "$candidate" ]; then CHROME="$candidate"; break; fi
    done
fi

if [ ! -x "$CHROME" ]; then
    echo "No se encontró Chrome/Chromium. Indíquelo con la variable CHROME." >&2
    exit 2
fi

status="$(curl -s -o /dev/null -w '%{http_code}' "$URL" || true)"
if [ "$status" != "200" ]; then
    echo "El banco no es accesible en $URL (HTTP $status)." >&2
    echo "¿Está el contenedor levantado y el tema montado? docker compose up -d" >&2
    exit 2
fi

dom="$("$CHROME" --headless --disable-gpu --no-sandbox \
        --virtual-time-budget=6000 --dump-dom "$URL" 2>/dev/null)"

# El banco fija <title> a HARNESS-PASS o HARNESS-FAIL como marcador estable.
python3 - "$dom" <<'PY'
import html, re, sys

dom = sys.argv[1]

for state, body in re.findall(r'<li class="(ok|bad)">(.*?)</li>', dom, re.S):
    text = html.unescape(re.sub('<[^>]+>', '', body)).strip()
    print(('ok   ' if state == 'ok' else 'FAIL ') + text)

summary = re.search(r'<div id="summary"[^>]*>(.*?)</div>', dom, re.S)
if summary:
    print('\n' + html.unescape(re.sub('<[^>]+>', '', summary.group(1))).strip())

sys.exit(0 if 'HARNESS-PASS' in dom else 1)
PY
