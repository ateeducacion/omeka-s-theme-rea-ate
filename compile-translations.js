#!/usr/bin/env node

/**
 * Compile translations script for the REA ATE Omeka S theme
 * This script compiles all .po files in the language/ directory to .mo files
 * Uses gettext-parser (Node.js alternative to msgfmt)
 */

const fs = require('fs');
const path = require('path');
const gettextParser = require('gettext-parser');

// Color codes for output
const colors = {
    reset: '\x1b[0m',
    red: '\x1b[31m',
    green: '\x1b[32m',
    yellow: '\x1b[33m'
};

const languageDir = path.join(__dirname, 'language');

console.log('===================================');
console.log('Compiling Translation Files');
console.log('===================================');
console.log('');

// Check if language directory exists
if (!fs.existsSync(languageDir)) {
    console.error(`${colors.red}Error: Language directory not found: ${languageDir}${colors.reset}`);
    process.exit(1);
}

// Get all .po files
const poFiles = fs.readdirSync(languageDir)
    .filter(file => file.endsWith('.po'));

if (poFiles.length === 0) {
    console.log(`${colors.yellow}Warning: No .po files found in ${languageDir}${colors.reset}`);
    process.exit(0);
}

console.log(`Found ${poFiles.length} .po file(s) to compile`);
console.log('');

let successCount = 0;
let errorCount = 0;

// Process each .po file
poFiles.forEach(poFile => {
    const poPath = path.join(languageDir, poFile);
    const moFile = poFile.replace(/\.po$/, '.mo');
    const moPath = path.join(languageDir, moFile);
    const basename = path.basename(poFile, '.po');

    process.stdout.write(`Compiling ${poFile}... `);

    try {
        // Read .po file
        const poContent = fs.readFileSync(poPath, 'utf8');

        // Parse .po file
        const parsed = gettextParser.po.parse(poContent);

        // Compile to .mo binary format
        const moContent = gettextParser.mo.compile(parsed);

        // Write .mo file
        fs.writeFileSync(moPath, moContent);

        console.log(`${colors.green}✓ Success${colors.reset}`);
        successCount++;
    } catch (error) {
        console.log(`${colors.red}✗ Failed${colors.reset}`);
        console.error(`  Error: ${error.message}`);
        errorCount++;
    }
});

console.log('');
console.log('===================================');
console.log('Compilation Summary');
console.log('===================================');
console.log(`Successfully compiled: ${colors.green}${successCount}${colors.reset}`);
console.log(`Failed: ${colors.red}${errorCount}${colors.reset}`);
console.log('');

if (errorCount > 0) {
    console.error(`${colors.red}Some translations failed to compile. Please check the errors above.${colors.reset}`);
    process.exit(1);
} else {
    console.log(`${colors.green}All translations compiled successfully!${colors.reset}`);
    process.exit(0);
}
