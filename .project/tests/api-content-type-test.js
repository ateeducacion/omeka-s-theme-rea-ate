/**
 * Regression test for the API response guard in asset/js/resource-link-info.js.
 *
 * Omeka S serves its REST API as JSON-LD:
 *   $ curl -D - http://localhost:8080/api/items/2759
 *   HTTP/1.1 200 OK
 *   Content-Type: application/ld+json
 *
 * A guard that only accepts "application/json" therefore rejects every real response
 * and the info panel shows "Error loading info." for every item. This test pins the
 * accepted media types so that regression cannot come back.
 *
 * Run from the theme root:
 *   node .project/tests/api-content-type-test.js
 */

const fs = require('fs');
const path = require('path');
const vm = require('vm');

const scriptPath = path.join(__dirname, '..', '..', 'asset', 'js', 'resource-link-info.js');
const source = fs.readFileSync(scriptPath, 'utf8');

// The script registers a DOMContentLoaded listener at load time. Stubbing readyState as
// "loading" means the listener is registered and never fired, so the module-level
// declarations become available without touching the DOM.
const sandbox = {
    document: {
        readyState: 'loading',
        addEventListener() {},
    },
};

vm.createContext(sandbox);
vm.runInContext(source, sandbox, { filename: scriptPath });

const isJsonMediaType = sandbox.isJsonMediaType;

if (typeof isJsonMediaType !== 'function') {
    console.error('FAIL isJsonMediaType is not exposed by resource-link-info.js');
    process.exit(1);
}

const cases = [
    // What Omeka S 4.2 actually returns. This is the case the original guard failed.
    ['application/ld+json', true],
    ['application/ld+json; charset=utf-8', true],
    ['application/json', true],
    ['application/json; charset=utf-8', true],
    ['APPLICATION/LD+JSON', true],
    ['  application/ld+json  ', true],
    // Anything that is not JSON must still be rejected: that is the point of the guard.
    ['text/html', false],
    ['text/html; charset=utf-8', false],
    ['application/xml', false],
    ['text/plain', false],
    ['', false],
    [null, false],
    [undefined, false],
    // Must not be fooled by a media type that merely mentions json.
    ['text/html+json-ish', false],
];

let pass = 0;
let fail = 0;

cases.forEach(([input, expected]) => {
    const actual = isJsonMediaType(input);
    if (actual === expected) {
        pass++;
        console.log('ok   ' + JSON.stringify(input) + ' -> ' + actual);
    } else {
        fail++;
        console.log('FAIL ' + JSON.stringify(input) + ' -> ' + actual + ' | want: ' + expected);
    }
});

console.log('\n' + pass + ' passed, ' + fail + ' failed');
process.exit(fail === 0 ? 0 : 1);
