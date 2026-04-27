// Enhances the AdvancedSearch results list (ul.search-result-list.list):
//  - Tags every .property div with data-term="vocab:localName" so the CSS
//    grid/flex layout can place it in the right "column".
//  - Converts lrmi:learningResourceType values into a coloured pill (lrt-badge),
//    with a deterministic pastel hue derived from the value text.
//  - Groups each metadata-search-link + sibling resource-link-info as a single
//    nowrap unit so the "+" info button never breaks away from its label.
//  - Uses a MutationObserver to keep working after AJAX facet reloads.

(function () {
    const LIST_SELECTOR = 'ul.search-result-list.list, ul.resource-list.search-result-list';
    const TERM_RE = /property(?:%5B|\[)0(?:%5D|\])(?:%5B|\[)property(?:%5D|\])=([^&]+)/i;

    // Fallback mapping for properties whose values are rendered as plain text
    // (no metadata-search-link) — we can't extract the term from an href in
    // that case, so we match the <dt> label instead. Keys must be lowercase.
    const LABEL_TO_TERM = {
        'learning resource type': 'lrmi:learningResourceType',
        'learningresourcetype': 'lrmi:learningResourceType',
        'tipo de recurso educativo': 'lrmi:learningResourceType',
        'tipo de recurso': 'lrmi:learningResourceType',
    };

    function extractTerm(href) {
        if (!href) return null;
        const m = href.match(TERM_RE);
        if (!m) return null;
        try {
            return decodeURIComponent(m[1]);
        } catch (e) {
            return m[1];
        }
    }

    function labelToTerm(property) {
        const dt = property.querySelector(':scope > dt');
        if (!dt) return null;
        const label = (dt.textContent || '').trim().toLowerCase();
        return LABEL_TO_TERM[label] || null;
    }

    function hueFromString(str) {
        let h = 0;
        for (let i = 0; i < str.length; i++) {
            h = ((h << 5) - h + str.charCodeAt(i)) | 0;
        }
        return Math.abs(h) % 360;
    }

    function decorateLearningResourceTypeBadge(property) {
        // When the value is rendered as a metadata-search-link, style the
        // link itself. When it's plain text inside .value-content (no link),
        // style the .value-content span directly.
        property.querySelectorAll('.value-content').forEach(function (vc) {
            const link = vc.querySelector('a.metadata-search-link');
            const target = link || vc;
            if (target.classList.contains('lrt-badge')) return;
            const text = (target.textContent || '').trim();
            if (!text) return;
            const hue = hueFromString(text.toLowerCase());
            target.classList.add('lrt-badge');
            target.style.setProperty('--lrt-bg', 'hsl(' + hue + ', 65%, 88%)');
            target.style.setProperty('--lrt-bg-hover', 'hsl(' + hue + ', 65%, 80%)');
        });
    }

    function groupNoBreak(property) {
        // Inside resource-linked values, each <dd> has a .value-content with
        // a metadata-search-link followed by a resource-link-info wrapper
        // (injected by resource-link-info.js). We mark the value-content as
        // "grouped" so CSS can apply white-space: nowrap without affecting
        // other areas of the page.
        property.querySelectorAll('dd.value.resource .value-content, dd.value.resource.items .value-content')
            .forEach(function (vc) {
                vc.classList.add('value-content--nobr');
            });
    }

    function processItem(item) {
        const properties = item.querySelectorAll('dl.properties > .property');
        properties.forEach(function (prop) {
            if (!prop.dataset.term) {
                const firstLink = prop.querySelector('a.metadata-search-link');
                let term = firstLink ? extractTerm(firstLink.getAttribute('href')) : null;
                if (!term) {
                    term = labelToTerm(prop);
                }
                if (term) {
                    prop.dataset.term = term;
                    const local = term.split(':').pop() || term;
                    prop.classList.add('property--' + local.toLowerCase());
                }
            }

            if (prop.dataset.term === 'lrmi:learningResourceType') {
                decorateLearningResourceTypeBadge(prop);
            }

            if (prop.dataset.term === 'lrmi:teaches' || prop.dataset.term === 'dcterms:relation') {
                groupNoBreak(prop);
            }
        });
    }

    function processList(list) {
        list.querySelectorAll('li.resource.item').forEach(processItem);
    }

    function processAll(root) {
        (root || document).querySelectorAll(LIST_SELECTOR).forEach(processList);
    }

    function init() {
        processAll(document);

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== Node.ELEMENT_NODE) return;
                    if (node.matches && node.matches('li.resource.item')) {
                        processItem(node);
                        return;
                    }
                    if (node.matches && node.matches(LIST_SELECTOR)) {
                        processList(node);
                        return;
                    }
                    if (node.querySelectorAll) {
                        processAll(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
