# Makefile to facilitate theme packaging and translations

# Define SED_INPLACE based on the operating system
ifeq ($(shell uname), Darwin)
  SED_INPLACE = sed -i ''
else
  SED_INPLACE = sed -i
endif

# Detect the operating system
ifeq ($(OS),Windows_NT)
    # We are on Windows
    ifdef MSYSTEM
        # MSYSTEM is defined, we are in MinGW or MSYS
        SYSTEM_OS := unix
    else ifdef CYGWIN
        # CYGWIN is defined, we are in Cygwin
        SYSTEM_OS := unix
    else
        # Not in MinGW or Cygwin
        SYSTEM_OS := windows

    endif
else
    # Not Windows, assuming Unix
    SYSTEM_OS := unix
endif

# Generate the freedom-ate-X.X.X.zip package
package:
	@if [ -z "$(VERSION)" ]; then \
		echo "Error: VERSION not specified. Use 'make package VERSION=1.2.3'"; \
		exit 1; \
	fi
	@ORIGINAL_VERSION=$$(grep -E '^[[:space:]]*version' config/theme.ini | sed -E 's/^[[:space:]]*version[[:space:]]*=[[:space:]]*"([^"]*)"/\1/'); \
	echo "Updating version to $(VERSION) in theme.ini..."; \
	$(SED_INPLACE) 's/^\([[:space:]]*version[[:space:]]*=[[:space:]]*\).*$$/\1"$(VERSION)"/' config/theme.ini; \
	echo "Creating directory structure..."; \
	rm -rf tmpzip && mkdir -p tmpzip/freedom-ate; \
	echo "Copying theme files..."; \
	rsync -av \
		--exclude='tmpzip' \
		--exclude='.git' \
		--exclude='.github' \
		--exclude='.project' \
		--exclude='*.zip' \
		--exclude='node_modules' \
		--exclude='.DS_Store' \
		--exclude='asset/sass' \
		--exclude='asset/css/*.map' \
		--exclude='package.json' \
		--exclude='package-lock.json' \
		--exclude='gulpfile.js' \
		--exclude='compile-translations.js' \
		--exclude='compile-translations.sh' \
		--exclude='Makefile' \
		--exclude='AGENTS.md' \
		--exclude='DESIGN.md' \
		./ tmpzip/freedom-ate/; \
	echo "Creating ZIP archive: freedom-ate-$(VERSION).zip..."; \
	cd tmpzip && zip -qr ../freedom-ate-$(VERSION).zip freedom-ate && cd ..; \
	rm -rf tmpzip; \
	echo "Restoring version to $${ORIGINAL_VERSION} in theme.ini..."; \
	$(SED_INPLACE) "s/^\([[:space:]]*version[[:space:]]*=[[:space:]]*\).*$$/\1\"$${ORIGINAL_VERSION}\"/" config/theme.ini; \
	echo "Package created: freedom-ate-$(VERSION).zip"

# Generate .pot template from translate() and // @translate
generate-pot:
	@mkdir -p language
	@echo "Extracting strings marked with // @translate..."
	@php vendor/zerocrates/extract-tagged-strings/extract-tagged-strings.php > language/tagged.pot || true
	@echo "Attempting to extract translate() calls with xgettext (if available)..."
	@if command -v xgettext >/dev/null 2>&1; then \
	  find . -path ./vendor -prune -o \( -name '*.php' -o -name '*.phtml' \) -print > language/.files.list; \
	  xgettext \
	      --language=PHP \
	      --from-code=utf-8 \
	      --keyword=translate \
	      --keyword=translatePlural:1,2 \
	      --files-from=language/.files.list \
	      --output=language/xgettext.pot || true; \
	  rm -f language/.files.list; \
	else \
	  echo "xgettext not found, skipping translate() extraction"; \
	fi
	@echo "Building language/template.pot..."
	@if command -v msgcat >/dev/null 2>&1; then \
	  if [ -f language/xgettext.pot ] && [ -s language/xgettext.pot ]; then \
	    msgcat language/xgettext.pot language/tagged.pot --use-first -o language/template.pot; \
	  else \
	    cp language/tagged.pot language/template.pot; \
	  fi; \
	else \
	  echo "msgcat not found, copying tagged.pot as template.pot"; \
	  cp language/tagged.pot language/template.pot; \
	fi
	@rm -f language/xgettext.pot language/tagged.pot
	@echo "Generated language/template.pot"

# Update all .po files from .pot template
update-po:
	@echo "Updating translation files..."
	@find language -name "*.po" | while read po; do \
		echo "Updating $$po..."; \
		msgmerge --update --backup=off "$$po" language/template.pot; \
	done

# Check for untranslated strings
check-untranslated:
	@echo "Checking untranslated strings..."
	@find language -name "*.po" | while read po; do \
		echo "\n$$po:"; \
		msgattrib --untranslated "$$po" | if grep -q msgid; then \
			echo "Warning: Untranslated strings found!"; exit 1; \
		else \
			echo "All strings translated!"; \
		fi \
	done

# Compile all .po files in the language directory into .mo
compile-mo:
	@echo "Compiling .po files into .mo..."
	@find language -name '*.po' | while read po; do \
		mo=$${po%.po}.mo; \
		msgfmt "$$po" -o "$$mo"; \
		echo "Compiled $$po -> $$mo"; \
	done

# Full i18n workflow: pot -> po -> mo
i18n: generate-pot update-po check-untranslated compile-mo

# Display help with available commands
help:
	@echo ""
	@echo "Usage: make <command>"
	@echo ""
	@echo "Packaging:"
	@echo "  package           - Generate a .zip package of the theme with version tag"
	@echo "                      Usage: make package VERSION=1.2.3"
	@echo ""
	@echo "Translations (i18n):"
	@echo "  generate-pot      - Extract translatable strings to template.pot"
	@echo "  update-po         - Update .po files from template.pot"
	@echo "  check-untranslated- Check for untranslated strings in .po files"
	@echo "  compile-mo        - Compile .mo files from .po files"
	@echo "  i18n              - Run full translation workflow (generate, update, check, compile)"
	@echo ""
	@echo "Dependency management:"
	@echo "  deps-update       - Run composer update --with-dependencies (checks composer availability)"
	@echo "  deps-outdated     - Show outdated direct dependencies"
	@echo ""
	@echo "Other:"
	@echo "  help              - Show this help message"
	@echo ""

# Set help as the default goal if no target is specified
.DEFAULT_GOAL := help

# Dependency management
.PHONY: check-composer
check-composer:
	@command -v composer >/dev/null 2>&1 || (echo "Error: composer not found in PATH. Please install Composer and try again." && exit 1)

.PHONY: deps-update
deps-update: check-composer
	@# If vendor is missing, install/update regardless of outdated status
	@if [ ! -d vendor ]; then \
	  echo "vendor/ not found. Running composer update to install dependencies..."; \
	  composer update --with-dependencies --no-interaction; \
	  exit 0; \
	fi
	@echo "Checking for outdated direct dependencies..."
	@OUTDATED=$$(composer outdated --direct --no-interaction 2>/dev/null || true); \
	if [ -z "$$OUTDATED" ] || echo "$$OUTDATED" | grep -qi "No outdated packages"; then \
	  echo "No direct dependencies outdated. Skipping composer update."; \
	else \
	  echo "Outdated direct dependencies found:"; \
	  echo "$$OUTDATED"; \
	  echo "Running composer update --with-dependencies..."; \
	  composer update --with-dependencies --no-interaction; \
	fi

.PHONY: deps-outdated
deps-outdated: check-composer
	composer outdated --direct --no-interaction
