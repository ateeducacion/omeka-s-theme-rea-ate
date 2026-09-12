# Agent guidance

REA ATE is an Omeka S theme for an educational-resource repository. Preserve
Omeka S compatibility declared in `config/theme.ini` (currently `^4.2.0`).

## Working conventions

- Write new guidance, skills, documentation and pull requests in English. Existing
  Spanish historical decisions and product translations retain their language.
- Branch names use English with `feature/` or `hotfix/`; never `codex/`.
- This repository's default branch is `master`. Base changes on its latest remote
  state; do not combine unrelated feature branches.
- Load only the skill and source relevant to the change. Existing implementation
  establishes current behavior; verify legacy `.project/` examples against it.
- `.project/agents/` and `.project/decisions/` preserve the established role and
  decision history. Use parallel roles for substantial independent work; routine
  edits do not require five roles or a new decision. Record durable decisions in
  the appropriate existing log without rewriting historical entries.

## Source and validation map

| Change | Source and relevant validation |
|---|---|
| View/helper behavior | `view/`, `helper/`; PHP syntax checks and affected `test/ReaAteTest/` tests |
| Styles | `asset/sass/`, `gulpfile.js`; `npm ci` then `npm run build`, inspect generated CSS |
| Browser behavior | `asset/js/` (plain JavaScript); verify affected page, keyboard and mobile behavior in Omeka S |
| Translation | `language/`; `make i18n` uses gettext and changes PO/MO files |
| Distribution | `Makefile` package target, `.gitattributes`, release workflow; inspect the produced ZIP |

`make test` and `make lint` depend on `deps-update`, which may update Composer
packages. For verification with installed dependencies use
`vendor/bin/phpunit -c test/phpunit.xml` and the PHPCS invocation in `Makefile`
directly. Report unavailable dependencies or runtime checks without claiming a pass.
Do not run `make clean` or `make fresh` on a valued database: they delete volumes.

## Theme constraints

- Keep template paths and variable contracts compatible with Omeka S and installed
  modules. Optional-module fallbacks must continue to work.
- Reuse `SafeUrl`, `CssToken`, and `HtmlAllowlist` where the current templates use
  them; output escaping and context-specific validation serve different purposes.
- Edit Sass sources and rebuild; do not implement fixes only in generated CSS.
  Preserve the `--ate-*` theme settings and the progressive-enhancement baseline.
- Resource values may be literals, URIs or linked resources. Keep stable vocabulary
  terms and facet query semantics rather than deriving behavior from display labels.
- Packaging must exclude `.project/`, `.agents/`, `.claude/` and `AGENTS.md`.
  `make package VERSION=X.Y.Z` creates a `rea-ate/` top-level directory. It temporarily
  changes `theme.ini`; verify restoration after a failure. Publishing needs a release request.

## Skills

| Task | Skill |
|---|---|
| Templates, helpers, styles, accessibility and translation | [rea-theme](.agents/skills/rea-theme/SKILL.md) |
| Metadata rendering, search facets and educational-resource badges | [rea-metadata-search](.agents/skills/rea-metadata-search/SKILL.md) |
| Build and ZIP release verification | [theme-release](.agents/skills/theme-release/SKILL.md) |
| Authoring/reviewing workflows | [github-actions-hardening](.agents/skills/github-actions-hardening/SKILL.md) |

Canonical skills live in `.agents/skills/`; `.claude/skills/` links to them.
The older `.project/skills/` files remain historical technical references, not
additional mandatory startup prompts. Consult only relevant material and verify
paths and examples against source.

Install external skills using `gh skills install OWNER/REPO PATH --dir .agents/skills`.
Keep vendored bodies and `metadata.github-*` provenance verbatim. Local skills have
no upstream metadata. Preview updates with
`gh skills update --dir .agents/skills --dry-run`.

The weekly/manual update workflow opens reviewable PRs, never merges them. GitHub
Actions must be allowed to create PRs; PRs created with `GITHUB_TOKEN` do not trigger
normal PR workflows automatically. Review skill diffs as behavior changes.
Maintainer preference overrides vendored pinning advice: use `actions/checkout@v7`,
`devantler-tech/actions/update-agent-skills@v13.3.3` (floating `v13` when available),
and `peter-evans/create-pull-request@v8`, with version tags rather than SHAs.
