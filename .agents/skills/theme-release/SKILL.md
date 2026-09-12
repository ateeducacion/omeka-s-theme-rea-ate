---
name: theme-release
description: Verify REA ATE theme build and release ZIP contents when packaging or release configuration changes.
---
# Theme release verification

Read `Makefile` and `.github/workflows/release.yml` before packaging. The workflow
runs on version tags; verification alone does not authorize tagging or publishing.

1. Use the requested version and inspect `config/theme.ini`. Build styles with
   `npm ci` and `npm run build`; the release does not ship Sass sources.
2. Run relevant helper tests and gettext checks for changed code/catalogs. Avoid
   dependency upgrades hidden behind Makefile validation prerequisites.
3. Run `make package VERSION=X.Y.Z` in an isolated staging copy when only auditing.
   Inspect the ZIP: one `rea-ate/` root, theme config, view/helper assets and compiled
   CSS present; coordination and agent files absent.
4. Check both rsync exclusions in `Makefile` and export exclusions in
   `.gitattributes`; the release ZIP and GitHub source archive use different paths.
5. Confirm `config/theme.ini` is restored and no generated release ZIP is staged.

Report checks actually run, missing tools, and package contents. Do not label a
release ready if required build or runtime validation remains unverified.
