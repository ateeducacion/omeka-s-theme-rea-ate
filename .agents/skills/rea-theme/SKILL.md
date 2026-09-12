---
name: rea-theme
description: Change REA ATE templates, view helpers, theme styles or browser behavior with Omeka S compatibility.
---
# REA theme changes

Trace the affected template and helper before editing. Core page overrides live
under `view/omeka/site/`; shared partials under `view/common/`; layout and assets
are registered from `view/layout/layout.phtml`. Theme helper registrations and
settings belong to `config/theme.ini`, implementations to `helper/`.

- Preserve helper escaping boundaries: `SafeUrl` validates URL contexts,
  `CssToken` constrains style settings and `HtmlAllowlist` constrains allowed markup.
  Extend their corresponding `test/ReaAteTest/Helper/` tests when behavior changes.
- `gulpfile.js` compiles top-level `asset/sass/*.scss` into `asset/css/`. Change
  source partials, run `npm run build` with dependencies installed, and review the
  output; there is no JavaScript bundler to rebuild.
- Keep `--ate-*` settings and responsive layout contracts. Check focus visibility,
  keyboard navigation, expanded state and usable content without JavaScript when
  changing menus or accordions. Test the actual affected page in Omeka S.
- Use existing translation helpers for new user-visible strings and the Makefile
  gettext commands when changing catalogs. Do not translate metadata values as UI strings.

For deeper design history consult `.project/docs/design-system.md` only when
needed; old `.project/skills/frontend/` examples are references, not proof of
current file layout. Finish with affected syntax/helper tests and, for visual
changes, a browser check. Report any checks that need an unavailable live instance.
