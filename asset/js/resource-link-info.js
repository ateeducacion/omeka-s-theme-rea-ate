// Guards against parsing a response that is not JSON at all (a proxy error page, a
// login redirect). Omeka S serves its API as JSON-LD, so the check must accept the
// whole "+json" structured-suffix family, not just application/json.
function isJsonMediaType(contentType) {
    const mediaType = String(contentType || '').split(';')[0].trim().toLowerCase();
    // RFC 6839 structured syntax suffix: application/json and application/<x>+json.
    return /^application\/([\w.+-]+\+)?json$/.test(mediaType);
}

const resourceLinkInfoScript = () => {

    // Adds a '+' button next to each .resource-link that fetches and shows
    // the linked resource's description as an inline accordion panel.
    // Uses MutationObserver to also handle links injected dynamically (e.g. faceted-browse AJAX).

    // Base path for the API, rendered by layout.phtml so the theme keeps working when
    // Omeka is installed in a subdirectory. Falls back to the root-relative path.
    const apiBase = document.body.dataset.apiItemsBase || '/api/items';

    // Item descriptions are catalogue metadata: they are authored content and must never
    // be treated as markup. Everything below builds DOM nodes and assigns textContent.
    const REQUEST_TIMEOUT_MS = 8000;

    function renderMessage(panel, text, className) {
        const p = document.createElement('p');
        p.className = className;
        p.textContent = text;
        panel.replaceChildren(p);
    }

    function processLink(link) {
        // Skip if already processed
        if (link.closest('.resource-link-info')) return;

        // Only process links that have a *visible* o-icon-items span.
        // CSS may hide the icon in certain contexts (e.g. search-results metadata chips);
        // adding the '+' button there would be invisible noise.
        const iconSpan = link.querySelector('span.o-icon-items');
        if (!iconSpan) return;
        if (getComputedStyle(iconSpan).display === 'none') return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Extract item ID from URL paths like /s/site-slug/item/28963
        const match = href.match(/\/item(?:s)?\/(\d+)/);
        if (!match) return;

        const itemId = match[1];
        const wrapper = document.createElement('span');
        wrapper.className = 'resource-link-info';

        // Move the link inside the wrapper
        link.parentNode.insertBefore(wrapper, link);
        wrapper.appendChild(link);

        // Create the toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'resource-link-info__btn';
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.setAttribute('aria-label', 'Show resource info');
        toggleBtn.textContent = '+';
        wrapper.appendChild(toggleBtn);

        // Create the accordion panel
        const panel = document.createElement('div');
        panel.className = 'resource-link-info__panel';
        renderMessage(panel, '…', 'resource-link-info__loading');
        wrapper.appendChild(panel);

        let loaded = false;

        toggleBtn.addEventListener('click', function () {
            const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.textContent = '+';
                wrapper.classList.remove('expanded');
            } else {
                // Accordion: close any other open panel in the same property group
                const group = wrapper.closest('.property');
                if (group) {
                    group.querySelectorAll('.resource-link-info.expanded').forEach(function (other) {
                        if (other === wrapper) return;
                        const otherBtn = other.querySelector('.resource-link-info__btn');
                        otherBtn.setAttribute('aria-expanded', 'false');
                        otherBtn.textContent = '+';
                        other.classList.remove('expanded');
                    });
                }

                toggleBtn.setAttribute('aria-expanded', 'true');
                toggleBtn.textContent = '−';
                wrapper.classList.add('expanded');

                if (!loaded) {
                    loaded = true;

                    const controller = new AbortController();
                    const timeout = setTimeout(function () { controller.abort(); }, REQUEST_TIMEOUT_MS);

                    fetch(apiBase + '/' + encodeURIComponent(itemId), {
                        headers: { 'Accept': 'application/ld+json, application/json' },
                        signal: controller.signal
                    })
                        .then(function (res) {
                            if (!res.ok) {
                                throw new Error('HTTP ' + res.status);
                            }
                            if (!isJsonMediaType(res.headers.get('Content-Type'))) {
                                throw new Error('Unexpected content type');
                            }
                            return res.json();
                        })
                        .then(function (data) {
                            const descriptions = data['dcterms:description'];
                            if (!descriptions || !descriptions.length) {
                                renderMessage(panel, '—', 'resource-link-info__empty');
                                return;
                            }

                            const fragment = document.createDocumentFragment();
                            descriptions.forEach(function (desc) {
                                const p = document.createElement('p');
                                p.textContent = desc['@value'] || '';
                                fragment.appendChild(p);
                            });
                            panel.replaceChildren(fragment);
                        })
                        .catch(function () {
                            renderMessage(panel, 'Error loading info.', 'resource-link-info__error');
                        })
                        .finally(function () {
                            clearTimeout(timeout);
                        });
                }
            }
        });
    }

    function processLinks(root) {
        root.querySelectorAll('a.resource-link').forEach(processLink);
    }

    // Process links present on initial page load
    processLinks(document);

    // Watch for dynamically injected content (e.g. faceted-browse AJAX results)
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType !== Node.ELEMENT_NODE) return;
                // Check if the node itself is a resource-link
                if (node.matches('a.resource-link')) {
                    processLink(node);
                }
                // Check descendants
                processLinks(node);
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', resourceLinkInfoScript);
} else {
    resourceLinkInfoScript();
}
