// Enhances the AdvancedSearch results list (ul.search-result-list.list):
//  - Tags every .property div with data-term="vocab:localName" so the CSS
//    grid/flex layout can place it in the right "column".
//  - Groups each metadata-search-link + sibling resource-link-info as a single
//    nowrap unit so the "+" info button never breaks away from its label.
//  - Uses a MutationObserver to keep working after AJAX facet reloads.
// Note: lrmi:learningResourceType is rendered server-side as .resource-type-badge
//  via common/resource-type-badge.phtml — no client-side transformation needed.

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

    // ---- lrmi:learningResourceType badge (client-side) ----
    // AdvancedSearch renders property values as plain text, bypassing
    // resource-values.phtml. We replicate the PHP badge logic here so search
    // results stay consistent with item/show.

    var LRT_ICON_MAP = [
        ['vídeo',        'smart_display'],
        ['video',        'smart_display'],
        ['audio',        'headphones'],
        ['podcast',      'headphones'],
        ['documento',    'article'],
        ['document',     'article'],
        ['interactiv',   'touch_app'],
        ['juego',        'sports_esports'],
        ['game',         'sports_esports'],
        ['cuestionario', 'quiz'],
        ['quiz',         'quiz'],
        ['evaluaci',     'quiz'],
        ['assessment',   'quiz'],
        ['presentaci',   'slideshow'],
        ['presentation', 'slideshow'],
        ['lección',      'menu_book'],
        ['lesson',       'menu_book'],
        ['lectur',       'menu_book'],
        ['unidad',       'folder_open'],
        ['unit',         'folder_open'],
        ['simulaci',     'model_training'],
        ['simulation',   'model_training'],
        ['actividad',    'assignment'],
        ['activity',     'assignment'],
    ];

    function lrtNormalize(raw) {
        var m = raw.match(/[/#]([^/#]+)\/?$/);
        var label = m ? m[1] : raw;
        label = label.replace(/([a-z])([A-Z])/g, '$1 $2');
        return label.charAt(0).toUpperCase() + label.slice(1);
    }

    function lrtIcon(lower) {
        for (var i = 0; i < LRT_ICON_MAP.length; i++) {
            if (lower.indexOf(LRT_ICON_MAP[i][0]) !== -1) return LRT_ICON_MAP[i][1];
        }
        return 'school';
    }

    function buildLrtBadge(rawText) {
        var label   = lrtNormalize(rawText.trim());
        var MAX     = 35;
        var clipped = label.length > MAX;
        var display = clipped ? label.substring(0, 34) + '…' : label;
        var icon    = lrtIcon(label.toLowerCase());

        var badge = document.createElement('span');
        badge.className = 'resource-type-badge';
        if (clipped) badge.title = label;

        var iconEl = document.createElement('span');
        iconEl.className = 'material-symbols-outlined';
        iconEl.setAttribute('aria-hidden', 'true');
        iconEl.textContent = icon;

        badge.appendChild(iconEl);
        badge.appendChild(document.createTextNode(' ' + display));
        return badge;
    }

    function upgradeLrtProperty(property) {
        property.querySelectorAll('dd.value .value-content').forEach(function (vc) {
            if (vc.querySelector('.resource-type-badge')) return; // already a badge
            var raw = vc.textContent.trim();
            if (!raw) return;
            vc.textContent = '';
            vc.appendChild(buildLrtBadge(raw));
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
                upgradeLrtProperty(prop);
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

// ---- Scroll position preservation on facet selection ----
// Facet checkboxes trigger a full page reload via data-url.
// We save scrollY before the redirect and restore it on load.
(function () {
    // Restore scroll position if set by a previous facet click
    var savedY = sessionStorage.getItem('rea-scroll-y');
    if (savedY !== null) {
        sessionStorage.removeItem('rea-scroll-y');
        var targetY = parseInt(savedY, 10);
        if (document.readyState === 'complete') {
            window.scrollTo(0, targetY);
        } else {
            window.addEventListener('load', function () {
                requestAnimationFrame(function () { window.scrollTo(0, targetY); });
            });
        }
    }

    // Save scroll before checkbox-triggered navigation
    document.addEventListener('change', function (e) {
        var cb = e.target;
        if (cb.type === 'checkbox' && cb.closest('.search-facets') && cb.dataset.url) {
            sessionStorage.setItem('rea-scroll-y', window.scrollY);
        }
    });
})();

// ---- Active filter chips bar ----
(function () {
    function getGroupName(cb) {
        var facetLi = cb.closest('li.facet');
        if (!facetLi) return '';
        var h4 = facetLi.querySelector('h4');
        return h4 ? h4.textContent.trim() : '';
    }

    function getValueText(cb) {
        var li = cb.closest('li.facet-item');
        if (!li) return cb.value;
        var span = li.querySelector('label span');
        return span ? span.textContent.replace(/\(\d+\)\s*$/, '').trim() : cb.value;
    }

    function buildFilterChips() {
        var bar = document.getElementById('active-filter-chips');
        if (!bar) return;

        bar.innerHTML = '';
        var checked = document.querySelectorAll('.search-facets input[type="checkbox"]:checked');
        if (!checked.length) return;

        checked.forEach(function (cb) {
            var chip = document.createElement('a');
            chip.className = 'filter-chip';
            chip.href = cb.dataset.url || '#';
            var group = getGroupName(cb);
            var value = getValueText(cb);
            chip.innerHTML =
                (group ? '<span class="filter-chip__group">' + group + ':</span> ' : '') +
                '<span class="filter-chip__value">' + value + '</span>' +
                '<span class="filter-chip__remove" aria-hidden="true">✕</span>';
            bar.appendChild(chip);
        });

        if (checked.length > 1) {
            var params = new URLSearchParams(window.location.search);
            var toDelete = [];
            params.forEach(function (v, k) {
                if (/^facet(\[|%5B)/i.test(k)) toDelete.push(k);
            });
            toDelete.forEach(function (k) { params.delete(k); });
            var qs = params.toString();
            var clearUrl = window.location.pathname + (qs ? '?' + qs : '');

            var clearBtn = document.createElement('a');
            clearBtn.className = 'chips-clear-all';
            clearBtn.href = clearUrl;
            clearBtn.textContent = 'Limpiar todo';
            bar.appendChild(clearBtn);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildFilterChips);
    } else {
        buildFilterChips();
    }
})();
