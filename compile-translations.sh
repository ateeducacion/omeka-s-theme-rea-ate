#!/bin/bash

# Compile translations script for the REA ATE Omeka S theme
# This script compiles all .po files in the language/ directory to .mo files

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LANGUAGE_DIR="$SCRIPT_DIR/language"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "==================================="
echo "Compiling Translation Files"
echo "==================================="
echo ""

# Check if msgfmt is available
if ! command -v msgfmt &> /dev/null; then
    echo -e "${RED}Error: msgfmt command not found.${NC}"
    echo "Please install gettext package:"
    echo "  - macOS: brew install gettext && brew link gettext --force"
    echo "  - Ubuntu/Debian: sudo apt-get install gettext"
    echo "  - Fedora/RHEL: sudo yum install gettext"
    echo ""
    echo "Alternatively, use npm to compile translations:"
    echo "  npm install"
    echo "  npm run compile-translations"
    exit 1
fi

# Check if language directory exists
if [ ! -d "$LANGUAGE_DIR" ]; then
    echo -e "${RED}Error: Language directory not found: $LANGUAGE_DIR${NC}"
    exit 1
fi

# Count .po files
PO_COUNT=$(find "$LANGUAGE_DIR" -maxdepth 1 -name "*.po" -type f | wc -l | tr -d ' ')

if [ "$PO_COUNT" -eq 0 ]; then
    echo -e "${YELLOW}Warning: No .po files found in $LANGUAGE_DIR${NC}"
    exit 0
fi

echo "Found $PO_COUNT .po file(s) to compile"
echo ""

# Initialize counters
SUCCESS_COUNT=0
ERROR_COUNT=0

# Loop through all .po files
for PO_FILE in "$LANGUAGE_DIR"/*.po; do
    # Skip if no .po files found (glob didn't match)
    [ -e "$PO_FILE" ] || continue

    # Get filename without extension
    BASENAME=$(basename "$PO_FILE" .po)
    MO_FILE="$LANGUAGE_DIR/$BASENAME.mo"

    echo -n "Compiling $BASENAME.po... "

    # Compile .po to .mo
    if msgfmt -o "$MO_FILE" "$PO_FILE" 2>&1; then
        echo -e "${GREEN}✓ Success${NC}"
        SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
    else
        echo -e "${RED}✗ Failed${NC}"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
done

echo ""
echo "==================================="
echo "Compilation Summary"
echo "==================================="
echo -e "Successfully compiled: ${GREEN}$SUCCESS_COUNT${NC}"
echo -e "Failed: ${RED}$ERROR_COUNT${NC}"
echo ""

if [ "$ERROR_COUNT" -gt 0 ]; then
    echo -e "${RED}Some translations failed to compile. Please check the errors above.${NC}"
    exit 1
else
    echo -e "${GREEN}All translations compiled successfully!${NC}"
    exit 0
fi
