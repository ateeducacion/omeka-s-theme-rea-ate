# Code review, October 2026 (backlog item #10)

Scope: `helper/*.php`, `view/**/*.phtml`, `asset/js/*.js`, `asset/sass/**`,
`config/theme.ini`, `language/`, `test/`. Base: `origin/master` at `5d08d16`.
Reviewed against Omeka S 4.3.0-alpha, AdvancedSearch 3.4.60, BlockPlus 3.4.44 and
CustomVocab 2.0.2 as installed in the local instance (read-only GET requests and
read-only inspection of module sources).

Line numbers refer to the base commit. Status is **fixed** (fixed in branch `hotfix/code-review`, with the commit) or
**proposed** (left as a documented proposal; it needs a design decision, is larger
than a low-risk fix, or would touch lines the security branch already changed).
Every finding was first recorded as open in commit `0289ca5`.

Security findings are listed separately at the end and are deferred to the security
review (#9).

## Summary

| ID | Severity | Category | Short description | Status |
|---|---|---|---|---|
| CR-01 | high | Performance | Item browse count query loads every matching item | fixed |
| CR-02 | medium | Correctness | Item-set browse overrides pagination with the current page's count | fixed |
| CR-03 | medium | Correctness | Advanced search counts and lists item sets of every site | fixed |
| CR-04 | medium | Correctness | `resource-values.phtml` detects linked values by data-type name | fixed |
| CR-05 | medium | Correctness | Curriculum block detects aligned linked values by data-type name | fixed |
| CR-06 | medium | Correctness | Resource-type badge shows the URL tail for a linked value | fixed |
| CR-07 | low | Correctness | JSON-LD `creator`/`publisher` names become URLs for linked values | fixed |
| CR-08 | medium | i18n | Linked-resources labels render in English on the Spanish site | fixed |
| CR-09 | medium | Correctness | Layout toggle derives `view` from the translated `aria-label` | fixed |
| CR-10 | medium | Accessibility | Menu toggle is labelled "Cerrar" while it opens the menu | fixed |
| CR-11 | low | Accessibility | Advanced-search chips expose no selected state | fixed |
| CR-12 | low | Accessibility | `aria-role` attribute and empty `lang=""` in `resource-values.phtml` | fixed |
| CR-13 | low | Accessibility | Media download link has an unlabelled icon | fixed |
| CR-14 | low | Performance | `CurriculumAnchor` re-queries IDs the API did not return | fixed |
| CR-15 | low | Correctness | Facet group sort subtracts integers that may overflow | fixed |
| CR-16 | medium | Correctness | Curriculum block defaults do not match the data model | fixed |
| CR-17 | low | Compatibility | `truncate_body_property` help text uses the wrong key | fixed |
| CR-18 | low | Correctness | `truncate_body_property` has no effect on item browse | proposed |
| CR-19 | low | Maintainability | Upstream attribution still credits RRCHNM | fixed |
| CR-20 | low | Maintainability | Stray compiled CSS in `asset/sass/` | fixed |
| CR-21 | low | Maintainability | Stale project name in `compile-translations.js`; duplicate rsync exclude | fixed |
| CR-22 | low | Maintainability | `accordion.js` and `faceted-browse.js` are never loaded | proposed |
| CR-23 | medium | Maintainability | Five copies of the value-label closure, all wrong for URI values | proposed |
| CR-24 | medium | Performance | Grouped facets run 1 + N unbounded queries per search page | proposed |
| CR-25 | low | Correctness | Grouped facets key DefinedTerms by title, so homonyms collide | proposed |
| CR-26 | medium | Performance | Item-set browse runs one count query per item set | proposed |
| CR-27 | medium | Performance | Advanced search hydrates up to 500 items to list durations | proposed |
| CR-28 | low | Performance | Linked-resources partial reads four values per linked resource | proposed |
| CR-29 | medium | Correctness | Literal educational levels disappear from search results | proposed |
| CR-30 | low | Correctness | `results.phtml` keeps upstream type-name detection | proposed |
| CR-31 | medium | Accessibility | Delete-confirmation modal has no dialog semantics or focus handling | proposed |
| CR-32 | low | i18n | Hardcoded English and Spanish strings in JavaScript | proposed |
| CR-33 | low | Accessibility | Facet heading toggle in `script.js` is mouse-only | proposed |
| CR-34 | low | Accessibility | Layout toggle uses `disabled` to show the current view | proposed |
| CR-35 | low | i18n | Spanish source msgids are absent from `template.pot` | proposed |
| CR-36 | low | Compatibility | Missing vocabulary properties raise warnings in advanced search | proposed |
| CR-37 | low | Maintainability | Unused `$labelMap`; blocks rendered twice in item-set view | proposed |
| CR-38 | low | Test coverage | Helper tests cover only pure functions | proposed |
| CR-39 | medium | Correctness | Item JSON-LD is wrapped in `//<!--`, so it is not valid JSON | proposed |
| CR-40 | low | Correctness | Undefined `$linkType` in the file block's error message | fixed |
| CR-41 | medium | Correctness | File/showcase blocks call `displayTitle()` on a null media | proposed |
| CR-42 | low | Correctness | Showcase block closes unopened elements when an item is missing | proposed |

## Findings

### CR-01 — Item browse count query loads every matching item (high, performance)

`view/omeka/site/item/browse.phtml:55-59`. The toolbar count runs
`$api->search('items', $countQuery)` without `limit`. Omeka's `limitQuery()` sets
no maximum when `limit` is absent, so the API hydrates a representation of every
matching item just to read `getTotalResults()`.
Scenario: a site with 12,000 items opens `/item` → the page builds 12,000 item
representations in addition to the 25 it shows. The count also diverges from the
listing: it forces `is_public=true` (a logged-in editor sees fewer than listed) and
ignores `site_attachments_only` when `browse_attached_items` is enabled.
Fix: read the total that the controller already gave the paginator
(`$this->pagination()->getPaginator()->getTotalCount()`); no extra query.
Status: **fixed** in `8c111fe`.

### CR-02 — Item-set browse overrides pagination (medium, correctness)

`view/omeka/site/item-set/browse.phtml:24-29`. The template rebuilds pagination with
`totalCount = count($visibleSets)` (non-empty sets on the current page only) and a
hardcoded `perPage = 15`. The comment says this avoids "pollution" from
`$api->search()`, but API searches do not touch the paginator service.
Scenario (local instance, read-only): `/ceiplajares/item-set?page=2&per_page=2`
renders `1–1 de 1` with no pager, and `?page=1&per_page=2` renders "0 resultados";
with more item sets than the site's per-page setting, later pages are unreachable.
Fix: keep the controller's paginator. Empty sets stay hidden on each page.
Status: **fixed** in `8c111fe`.

### CR-03 — Advanced search shows other sites' data (medium, correctness)

`view/omeka/site/item/search.phtml:57-59` and `:66-68`. The "recursos disponibles"
count and the collection selector query `items`/`item_sets` without `site_id`.
Scenario (local instance): `ceiplajares` has 69 learning resources and 15 item sets;
the badge shows 71 and the selector offers all 54 item sets of the installation.
Choosing another site's collection always returns zero results.
Fix: add `site_id` of the current site to both queries.
Status: **fixed** in `e5416c8`.

### CR-04 — `resource-values.phtml` uses the data-type name (medium, correctness)

`view/common/resource-values.phtml:58-64`. Linked values are detected with
`strpos($valueType, 'resource')`, the rule PR #53/#54 removed elsewhere. A
`customvocab:N` value linked to an item gets no `resource items` classes, and a
value whose type contains "resource" but has no linked resource would call
`resourceName()` on `null` (fatal error).
Fix: detect with `$value->valueResource()`, as in the other templates.
Status: **fixed** in `4519638`.

### CR-05 — Curriculum block uses the data-type name (medium, correctness)

`view/common/resource-page-block-layout/block.phtml:83` and `:116-118`. Aligned terms
(`lrmi:assesses`, `lrmi:teaches`) are selected for grouping only when their type
contains "resource". Scenario: criteria catalogued through a CustomVocab of items
(`customvocab:N`) stay in the flat list instead of their subject card, although
`CurriculumAnchor::alignment()` (which uses `valueResource()`) would group them. The
flat-list `dd` class has the same null risk as CR-04.
Fix: use `valueResource()` in both places.
Status: **fixed** in `7d33975`.

### CR-06 — Resource-type badge on linked values (medium, correctness)

`view/common/resource-type-badge.phtml:17-20`. The label starts from
`(string) $resourceTypeValue`. For a linked value Omeka's
`AbstractResource::toString()` and `CustomVocab::toString()` return the linked
resource URL, so the badge shows its last path segment.
Scenario: `lrmi:learningResourceType` linked to item 40433 → badge "40433".
Fix: use the linked resource's `displayTitle()`; literals and URIs keep the current
behaviour.
Status: **fixed** in `07905ac`.

### CR-07 — JSON-LD creator/publisher (low, correctness)

`view/omeka/site/item/show.phtml:121-124`. `creator` and `publisher` use
`(string) $value`; a linked `dcterms:creator` produces
`{"@type":"Person","name":"http://…/items/123"}`.
Fix: use the existing `$_resolve` closure.
Status: **fixed** in `07905ac`.

### CR-08 — English labels in linked resources (medium, i18n)

`view/common/linked-resources.phtml:70,76,82`. `Educational Level`, `About` and
`Learning Resource Type` are in no catalogue (theme, core or modules).
Scenario (local instance): `/ceiplajares/item/40433` shows `<dt>Educational Level</dt>`
on a Spanish site.
Fix: the template now uses the Spanish msgids the theme already uses for these labels ("Nivel educativo", "Materia", "Tipo de recurso"), so no catalogue change is needed. Adding an "About" msgid to the global text domain would also have retranslated "About" for core and modules.
Status: **fixed** in `e2cc462`.

### CR-09 — Layout toggle depends on translations (medium, correctness)

`asset/js/browse.js:38`. The `view` parameter is the lowercased `aria-label`
(`$translate('Grid')`/`$translate('List')`). The server accepts only `list`
(`item/browse.phtml:41-52`, `media/browse.phtml`). Scenario: in any locale that
translates "List" (French "Liste"), choosing the list view reloads with
`view=liste` and renders the grid. It works in Spanish only because core has no
Spanish "List" string.
Fix: derive the view from the button's `grid`/`list` class.
Status: **fixed** in `3f7dab0`.

### CR-10 — Menu toggle accessible name (medium, accessibility)

`view/common/header.phtml:39-44`. The hamburger toggle has
`aria-labelledby="menu-backer"`, the drawer's close link whose text is "Cerrar"
(or the previous menu name). Screen readers announce the closed menu opener as
"Cerrar, botón, contraído".
Fix: give the toggle its own `aria-label`.
Status: **fixed** in `3f7dab0`.

### CR-11 — Chip selection state (low, accessibility)

`view/omeka/site/item/search.phtml` chip groups and `asset/js/advanced-search.js:47-53`.
Chips are toggle buttons whose state is only a `selected` class; assistive
technology cannot tell which levels/types are selected.
Fix: render and toggle `aria-pressed`.
Status: **fixed** in `3f7dab0`.

### CR-12 — Invalid ARIA and empty `lang` (low, accessibility)

`view/common/resource-values.phtml:69,83,87`. `aria-role` is not an attribute (it is
ignored), and every value without a language gets `lang=""`, which marks the value
as "language unknown" instead of inheriting the page language.
Fix: `role="img"` (as `search/results.phtml` does) and emit `lang` only when set.
Status: **fixed** in `4519638`.

### CR-13 — Download link (low, accessibility)

`view/common/resource-page-block-layout/media-embeds.phtml:40-42`. The icon-only link
relies on `title` (untranslated "Download") and exposes the ligature text
"download" to screen readers.
Fix: translated `aria-label`, `aria-hidden` on the icon.
Status: **fixed** in `3f7dab0`.

### CR-14 — CurriculumAnchor negative cache (low, performance)

`helper/CurriculumAnchor.php:325-348`. `fetchItems()` caches only items the API
returned. IDs that come back empty (a private subject for an anonymous visitor, a
level linked to an item set) are queried again on every later call, so the
three-query budget in `results.phtml` grows by one query per result that references
them. With all IDs visible the budget holds: `primeResources()` makes exactly three
batch queries (subjects, levels, termsets) and each `__invoke()` is served from the
cache. On the item page `alignment()` adds one query per aligned term.
Fix: remember misses in the instance cache; add a test with a counting API double.
Status: **fixed** in `bbd74c4, 2119c4d`.

### CR-15 — Facet group sort (low, correctness)

`view/search/facets-list.phtml:239-243`. `uksort` returns `$posA - $posB` with
`PHP_INT_MAX` as the missing-position value; a negative `schema:position` overflows
to a float and PHP 8.1+ emits an implicit-conversion deprecation with an unreliable
order.
Fix: spaceship operator.
Status: **fixed** in `07905ac`.

### CR-16 — Curriculum block defaults (medium, correctness)

`config/theme.ini:400` and `view/common/resource-page-block-layout/block.phtml:13`.
The default `anclaje_properties` lists `schema:educationalLevel`, `schema:assesses`
and `schema:teaches`, but the data model (`.project/context/metadata_model.md`,
decision in `.project/decisions/architect.md`: "las 4 propiedades LRMI") and the
local data (item 40435) use `lrmi:educationalLevel`, `lrmi:assesses` and
`lrmi:teaches`; `curriculum_anchor_level_property` defaults to
`lrmi:educationalLevel`. With the defaults the grouped card never renders. In
addition, as with the anchor terms, `themeSetting()` returns `null` until the theme
form is saved, so on such a site the block renders nothing at all.
Fix: change the form default to the LRMI terms (only affects forms that were never
saved; stored values win) and fall back to the same list in code when the setting
was never saved. An explicitly saved empty value still disables the block.
Also found while fixing: INI does not expand "\n" inside a quoted value, so the old one-line default was a single string with literal backslash-n; a site that saved the form without editing it stored that string and the block showed nothing. The default is now a multi-line quoted value (as `footer_copyright` already is), and `block.phtml` also splits on a literal "\n" so such stored values work. Sites with a saved list keep it.
Status: **fixed** in `7d33975`.

### CR-17 — `truncate_body_property` help text (low, compatibility)

`config/theme.ini:373` uses `elements.truncate_body_property.info`; Laminas reads
help text from `options.info`, so it is never shown.
Status: **fixed** in `7d33975`.

### CR-18 — `truncate_body_property` on item browse (low, correctness)

The setting is read only by `media/browse.phtml:17` and
`block-layout/browse-preview.phtml:14`. `item/browse.phtml` always clamps the summary
to two lines in CSS (`_resource-grid.scss:152-161`, `_resource-list.scss:153`), so the
label "How to handle long values … on the browse page" does not hold for items.
Proposal: either apply the `fadeout`/`ellipsis`/`full` classes in `item/browse.phtml`
(needs a design check of the cards) or reword the label to say it applies to media
and browse-preview blocks.
Status: **proposed**.

### CR-19 — Upstream attribution (low, maintainability)

`config/theme.ini:5` and `package.json:4` credit Freedom S Theme to "(RRCHNM)"; the
README (PR #57) credits the Corporation for Digital Scholarship.
Status: **fixed** in `22b3381`.

### CR-20 — Stray compiled CSS (low, maintainability)

`asset/sass/style.css` and `asset/sass/style.css.map` are a compiled copy of the
parent theme from commit `92b0db9`. Gulp writes to `asset/css/`, the layout loads
`css/style.css`, the package excludes `asset/sass`, and only a historical note in
`.project/decisions/developer.md` mentions the file. They confuse searches (every
grep of a class finds stale rules).
Status: **fixed** in `22b3381`.

### CR-21 — Stale names (low, maintainability)

`compile-translations.js:4` (and `compile-translations.sh:3`, `language/es.po:1-3`)
still say "Omeka S Theme Freedom ATE". `Makefile:112,116` excludes `.claude` twice in
the package rsync (harmless).
Also updated `compile-translations.sh:3`; the `es.po` header was left as is to avoid touching the catalogue.
Status: **fixed** in `22b3381`.

### CR-22 — Unloaded scripts (low, maintainability)

`asset/js/accordion.js` and `asset/js/faceted-browse.js` are not referenced by any
template. `accordion.js:38,46` also reads the undefined globals `expandAllText` and
`collapseAllText`, so it would throw if it were loaded, and `faceted-browse.js` reads
an element `#facets` that no template renders.
Proposal: delete both (and the `.accordion__*` / `.resources-linked__expand-collapse-btn`
rules) after confirming no site page block relies on them.
Status: **proposed**.

### CR-23 — Duplicated value-label closures (medium, maintainability)

`$displayValue`/`$_resolve` closures are repeated in `item/browse.phtml:101`,
`index/search.phtml:25`, `common/linked-resources.phtml:15`, `item/show.phtml:41` and,
inline, in `CurriculumAnchor` (lines 65, 158, 174). All fall back to
`(string) $value`, which for a URI value is the URI, not its label. Scenario: a
`schema:about` URI value labelled "Matemáticas" shows
`http://…/asignatura/matematicas` on cards and in the anchor.
Proposal: one helper, e.g. `ValueLabel($value, $lang)`, returning the linked
resource's title, else the URI label (`$value->value()`), else the URI or literal,
with PHPUnit tests; replace the closures in one change.
Status: **proposed**.

### CR-24 — Grouped facets query count (medium, performance)

`view/search/facets-list.phtml:80-111`. When grouped facets are configured, each
search page runs one query for all `schema:DefinedTermSet` items and one more per
term set for its `schema:DefinedTerm` children, without `limit`, hydrating every
term (1 + N queries, hundreds of representations).
Proposal: one query for terms with the termset property (`type=ex`), mapping each
child through `value($termsetProperty)->valueResource()`, or cache the map per
request/site (it changes only when the vocabulary is edited).
Status: **proposed**.

### CR-25 — Homonym DefinedTerms (low, correctness)

`view/search/facets-list.phtml:105`. The map is keyed by `displayTitle()`. Two terms
with the same title in different sets (for example "1º" in two stages) collapse
into the last set read, and the facet value is grouped under the wrong stage.
Proposal: key by resource ID when the facet value is an ID; keep the title only as
fallback. Needs checking which value AdvancedSearch emits for each facet type.
Status: **proposed**.

### CR-26 — Item-set browse count queries (medium, performance)

`view/omeka/site/item-set/browse.phtml:12-22`. One `items` count query per item set
on the page (up to the per-page setting) to hide empty sets.
Proposal: use `ItemSetRepresentation::itemCount()` if site filtering is not
required, or one grouped query in a helper; decide whether hiding empty sets should
move to the site's item pool instead.
Status: **proposed**.

### CR-27 — Duration values (medium, performance)

`view/omeka/site/item/search.phtml:111-132`. Each advanced-search page load hydrates
up to 500 items (all sites) only to collect distinct `lrmi:timeRequired` integers;
beyond 500 items the list is silently incomplete.
Proposal: a helper with a DBAL `SELECT DISTINCT value` on the property (scoped to the
site), or the AdvancedSearch numeric/range facet already available in the module.
Status: **proposed**.

### CR-28 — Linked-resources values (low, performance)

`view/common/linked-resources.phtml:37-45`. For each linked resource (25 per page) the
partial reads description, level, subject and type values; each first access loads
that resource's value collection (about one query per card).
Proposal: acceptable at 25 per page; revisit if the per-page value grows.
Status: **proposed**.

### CR-29 — Literal levels hidden in results (medium, correctness)

`view/search/results.phtml:132-136` with `CurriculumAnchor::__invoke()` lines 47-53.
When the anchor renders, both anchor terms are skipped in the property list, but the
anchor keeps only linked levels. Scenario: a resource with a linked subject and a
literal `lrmi:educationalLevel` "2º ESO" → the level appears nowhere in its result
card.
Proposal: skip the level term only when all its values are linked, or render the
remaining literal values after the anchor. Needs a product decision on the layout.
Status: **proposed**.

### CR-30 — Upstream detection in `results.phtml` (low, correctness)

`view/search/results.phtml:145-147` keeps AdvancedSearch's type-name detection. It is
an unchanged upstream line in a file that is rebased against the module, so it is
left alone to keep the diff minimal; noted for the next rebase.
Status: **proposed**.

### CR-31 — Delete modal (medium, accessibility)

`item/show.phtml:253-265`, `item/browse.phtml:407-419`, `asset/js/script.js:114-163`.
The modal has no `role="dialog"`/`aria-modal`/label, focus is not moved into it or
returned to the trigger, Escape does not close it and a failed delete closes it
without a message.
Proposal: dialog semantics plus focus management (or a native `<dialog>`), and an
error message on failure. Touches the CSRF-token flow, so coordinate with #9.
Status: **proposed**.

### CR-32 — Hardcoded strings in JavaScript (low, i18n)

`asset/js/resource-link-info.js:62,133` ("Show resource info", "Error loading
info."), `asset/js/navigation.js:91` ("show submenu for …"),
`asset/js/advanced-search.js:91` ("Quitar "), `asset/js/advanced-search-list.js:241`.
English strings are read out on the Spanish site and the Spanish ones cannot be
translated.
Proposal: pass translated strings through `data-*` attributes rendered by the
templates (the layout is owned by #9; coordinate).
Status: **proposed**.

### CR-33 — Facet heading toggle (low, accessibility)

`asset/js/script.js:187-200`. Facets without a configured state get a click handler
on their `<h4>`; it is not focusable and has no expanded state. `sessionStorage` is
used without `try/catch`.
Proposal: use AdvancedSearch's own expand/collapse button (`state` option) or render
a button inside the heading.
Status: **proposed**.

### CR-34 — Layout toggle state (low, accessibility)

`item/browse.phtml:202-203`, `media/browse.phtml:46-47`, `block-layout/browse-preview.phtml:47-48`.
The current view is marked by disabling its button, which removes it from the tab
order and hides the state. Proposal: `aria-pressed`.
Status: **proposed**.

### CR-35 — Spanish msgids not in the template (low, i18n)

About 100 Spanish source strings (for example `Buscar recursos`, `Proyecto
cofinanciador`, `Otros niveles`) are in no catalogue. They display correctly in
Spanish, but no other locale can translate them, and `make i18n` has evidently not
been run since they were added.
Proposal: run `make i18n` in its own change and review the PO diff.
Status: **proposed**.

### CR-36 — Missing vocabulary properties (low, compatibility)

`view/omeka/site/item/search.phtml:234,257,297,376` index `$eduProps[...]` directly;
on an installation without the LRMI vocabulary each raises an "Undefined array key"
warning and renders an empty `data-prop-id`.
Proposal: hide the filter row when its property is unavailable.
Status: **proposed**.

### CR-37 — Dead code and double rendering (low, maintainability)

`common/linked-resources.phtml:21-25` defines `$labelMap` and never uses it.
`item/browse.phtml:109-143` calls `getBlocks()` twice per region (once in the
condition, once to print), rendering the item-set's resource-page blocks twice.
`--facet-color` (`_facets.scss:68,170`) is read but never set (it has a fallback).
`$labelMap` removed in e2cc462; the double `getBlocks()` and `--facet-color` remain proposals.
Status: **proposed**.

### CR-38 — Test coverage (low, test coverage)

Tests cover `groupRows()`, `attachAligned()` and the pure helpers. Nothing exercises
`CurriculumAnchor::__invoke()`/`primeResources()` query batching, `anchorTerms()`
null-versus-empty handling, `ResourceTags` or `CanEditInCurrentSite` (whose
`catch (\Exception)` does not catch the `Error` thrown when there is no current site).
`test/ReaAteTest/Helper/CurriculumAnchorQueryBudgetTest.php` now covers the three-query budget, the negative cache and `anchorTerms()` null-versus-empty (fails with 8 and 7 queries against the old helper). `ResourceTags` and `CanEditInCurrentSite` remain uncovered.
Status: **proposed**.

### CR-39 — JSON-LD wrapper (medium, correctness)

Reported by #9, confirmed. `view/omeka/site/item/show.phtml:130-133`. Laminas
HeadScript wraps inline content in `//<!--` … `//-->` unless the item has the
`noescape` attribute (laminas-view `HeadScript.php:292,456`). Scenario (local
instance, `/ceiplajares/item/40435`): the `application/ld+json` block starts with
`//<!--`, so JSON-LD parsers reject it and the Schema.org `LearningResource` is lost.
Proposal: pass `['noescape' => true]` as the third argument of `appendScript()`.
Not applied here: it changes the same statement whose `json_encode` flags the
security branch (`hotfix/security-review`) edits, and the two hunks would conflict.
Apply it as a one-line follow-up after that branch merges.
Status: **proposed**.

### CR-40 — Undefined `$linkType` (low, correctness)

Reported by #9, confirmed. `view/common/block-layout/file.phtml:31` formats the
exception with `$linkType`, which is never defined (core's template has the same
slip); an invalid link option raises a warning and an empty type in the message.
Fix: use `$link`.
Status: **fixed** in `c3b360d`.

### CR-41 — Null media in file/showcase blocks (medium, correctness)

Reported by #9, confirmed. `view/common/block-layout/file.phtml:44-45` and
`item-showcase.phtml:29-30`: `$media = $attachment->media() ?: $item->primaryMedia()`
may be `null` (an attached item without media), but the "file name" title option
calls `$media->displayTitle()` unconditionally. Scenario: a showcase block with
title option "file_name" and an item without media → fatal error, page breaks.
Proposal: guard the `elseif` with `&& $media`. Not applied here because the security
branch rewrites the adjacent title line in both files; apply after it merges.
Status: **proposed**.

### CR-42 — Showcase markup when an item is missing (low, correctness)

`view/common/block-layout/item-showcase.phtml:11-42`: when an attachment has no item
(deleted or private) but has a caption, the caption branch prints two closing
`</div>` for elements that were never opened, breaking the page structure.
Proposal: move the caption block inside the `if ($item)` branch.
Status: **proposed**.

## Security (deferred to #9)

Not fixed here; listed for the security review.

- `view/common/block-layout/item-showcase.phtml:30` and `view/common/block-layout/file.phtml:45`
  print `$media->displayTitle()` without escaping (stored XSS through a media title).
- `view/common/resource-values.phtml:71` prints `$valueLang` without escaping.
- Translated strings printed raw into attributes: `item/browse.phtml:202-203`,
  `media/browse.phtml:46-47`, `search/facets-list.phtml:202`, `common/pagination.phtml:28`,
  `item/search.phtml:151-161` (translation catalogues are trusted, so low).
- `view/omeka/site/item/browse.phtml:41,62` reads `$_GET`/`$_SERVER['REQUEST_URI']`
  directly; output is escaped, noted for consistency only.

## Verification

- `vendor/bin/phpunit -c test/phpunit.xml`: OK, 110 tests, 144 assertions (106 tests on
  the base commit).
- PHPCS (`Makefile:80` invocation): no errors.
- `php -l` on every edited template; `node --check` on the edited scripts.
- `npm run build`: finished; the generated `asset/css/style.css` is unchanged (no Sass
  source was edited).
- `git merge-tree --write-tree HEAD hotfix/security-review`: merges without conflicts.
- Scenarios for CR-02, CR-03, CR-08, CR-39 and the query counts were reproduced on the
  local instance with GET requests against `master`.

Note: `theme.ini` and `package.json` still say version `0.13.0` while `0.13.1` is
released; the release workflow sets the version at tag time, so it was left alone.

## Not verified

- Omeka S 4.2.x specifically (the local instance runs 4.3.0-alpha).
- Behaviour with AdvancedSearch absent, and screen-reader output (reasoned from
  markup, not tested with assistive technology).
- The local instance mounts `master`, so fixes in this branch were not rendered there.
