---
name: rea-metadata-search
description: Change REA ATE educational metadata, resource badges, linked values or AdvancedSearch presentation.
---
# REA metadata and search

Start at the affected partial in `view/common/` and its caller. Search overrides
also live below `view/search/`; inspect the real path before relying on legacy
`.project/skills/features/advanced-search.md` examples.

- `view/common/resource-values.phtml` and `resource-type-badge.phtml` render item
  values. `asset/js/advanced-search-list.js` enhances search-result markup;
  changing one representation does not automatically update the other.
- Keep `lrmi:learningResourceType`, `lrmi:educationalLevel` and other configured
  terms intact. A label can come from a literal, URI or linked resource. Preserve
  links and escaping rather than flattening every value into untrusted HTML.
- Grouped facets use `advancedsearch_grouped_facets` and
  `advancedsearch_termset_property` settings in `config/theme.ini`; the latter
  defaults to `schema:inDefinedTermSet`. Do not hardcode translated group labels
  or item IDs. Maintain filter values, active chips, counts and pagination together.
- Preserve the existing fallback if AdvancedSearch is unavailable. Avoid adding
  per-result API requests to rendering or deriving item-set filters by fetching
  every child item.

Consult `.project/context/metadata_model.md` for repository vocabulary intent and
`.project/docs/advanced-search.md` for integration history. Verify their examples
against current templates and the installed module version.

Check an empty value, multiple values, a linked value and active facets on the
changed page; verify badges in both item and search views when shared behavior changes.
