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
        'learningresourcetype':   'lrmi:learningResourceType',
        'tipo de recurso educativo': 'lrmi:learningResourceType',
        'tipo de recurso':        'lrmi:learningResourceType',

        'educational level':      'lrmi:educationalLevel',
        'educationallevel':       'lrmi:educationalLevel',
        'nivel educativo':        'lrmi:educationalLevel',
        'nivel':                  'lrmi:educationalLevel',

        'about':                  'schema:about',
        'temática':               'schema:about',
        'tematica':               'schema:about',
        'tema':                   'schema:about',
        'subject':                'dcterms:subject',
        'materia':                'dcterms:subject',

        'time required':          'lrmi:timeRequired',
        'timerequired':           'lrmi:timeRequired',
        'duración':               'lrmi:timeRequired',
        'duracion':               'lrmi:timeRequired',
        'tiempo requerido':       'lrmi:timeRequired',
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

    function upgradeDurationProperty(property) {
        var dd = property.querySelector('dd.value .value-content');
        if (!dd) return;
        var mins = parseInt(dd.textContent.trim(), 10);
        if (!mins || mins <= 0) return;
        dd.textContent = '';
        var chip = document.createElement('span');
        chip.className = 'resource-duration-chip';
        var icon = document.createElement('span');
        icon.className = 'material-symbols-outlined';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = 'schedule';
        chip.appendChild(icon);
        chip.appendChild(document.createTextNode(' ' + mins + ' min'));
        dd.appendChild(chip);
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

    function groupMetaProperties(item) {
        if (item.querySelector('.property-meta-group')) return;

        var dl = item.querySelector('dl.properties');
        if (!dl) return;

        var terms = ['lrmi:educationalLevel', 'schema:about', 'lrmi:learningResourceType', 'lrmi:timeRequired'];
        var props = terms.map(function (t) {
            return dl.querySelector('.property[data-term="' + t + '"]');
        }).filter(Boolean);
        if (props.length === 0) return;

        // Insert directly into <li> so it is a first-class flex item,
        // avoiding any display:contents chain issues with dl.properties.
        var group = document.createElement('div');
        group.className = 'property-meta-group';
        item.appendChild(group);
        props.forEach(function (p) { group.appendChild(p); });
    }

    // ---- Plegado de listas largas de chips ----
    // Mejora progresiva: sin JS no se pliega nada y se ve todo. Solo se activa
    // cuando los chips desbordan la primera línea, así que una fila de tres
    // valores no gana un botón inútil.

    const COLLAPSIBLE_TERMS = ['lrmi:teaches', 'dcterms:relation'];
    let collapseSeq = 0;

    function undoCollapse(prop) {
        const values = prop.querySelector(':scope > .property__values');
        if (values) {
            while (values.firstChild) prop.insertBefore(values.firstChild, values);
            values.remove();
        }
        const toggle = prop.querySelector(':scope > .property__toggle');
        if (toggle) toggle.remove();
        prop.classList.remove('property--collapsible', 'property--collapsed');
        prop.style.removeProperty('--property-row-h');
        delete prop.dataset.collapseReady;
    }

    function setupCollapse(prop) {
        if (prop.dataset.collapseReady) return;

        const dds = Array.prototype.slice.call(prop.querySelectorAll(':scope > dd'));
        if (dds.length < 2) return;

        // Un chip que empieza más abajo que el primero es que ha saltado de línea.
        const firstTop = dds[0].offsetTop;
        const rowHeight = dds[0].offsetHeight;
        if (!dds.some(function (dd) { return dd.offsetTop > firstTop; })) return;

        prop.dataset.collapseReady = '1';

        const values = document.createElement('div');
        values.className = 'property__values';
        values.id = 'property-values-' + (++collapseSeq);
        dds.forEach(function (dd) { values.appendChild(dd); });
        prop.appendChild(values);

        // La altura de una fila se mide, no se asume: depende de la fuente
        // cargada y del tamaño de texto del usuario.
        prop.style.setProperty('--property-row-h', rowHeight + 'px');

        const dt = prop.querySelector(':scope > dt');
        const label = dt ? dt.textContent.trim() : '';
        const showAll = 'Ver todos (' + dds.length + ')';
        const showLess = 'Ver menos';

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'property__toggle';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-controls', values.id);

        const icon = document.createElement('span');
        icon.className = 'material-symbols-outlined';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = 'expand_more';

        const text = document.createTextNode(showAll);
        toggle.appendChild(text);
        toggle.appendChild(icon);

        // El nombre accesible empieza por el texto visible (WCAG 2.5.3) y le
        // antepone la zona, que en el DOM queda a la izquierda y fuera del botón.
        const nameFor = function (visible) {
            return label ? label + ': ' + visible : visible;
        };
        toggle.setAttribute('aria-label', nameFor(showAll));

        toggle.addEventListener('click', function () {
            const collapsed = prop.classList.toggle('property--collapsed');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            text.nodeValue = collapsed ? showAll : showLess;
            toggle.setAttribute('aria-label', nameFor(collapsed ? showAll : showLess));
            icon.textContent = collapsed ? 'expand_more' : 'expand_less';
        });

        prop.appendChild(toggle);
        prop.classList.add('property--collapsible', 'property--collapsed');
    }

    function scheduleCollapse(root) {
        // Medir antes de que la tipografía esté lista da alturas equivocadas.
        requestAnimationFrame(function () {
            (root || document).querySelectorAll(COLLAPSIBLE_TERMS.map(function (t) {
                return '.property[data-term="' + t + '"]';
            }).join(',')).forEach(setupCollapse);
        });
    }

    let resizeTimer = null;
    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function () {
            // Al cambiar el ancho cambia cuántos chips caben, así que se
            // deshace y se vuelve a medir en vez de conservar un estado viejo.
            document.querySelectorAll('.property--collapsible').forEach(undoCollapse);
            scheduleCollapse(document);
        }, 200);
    });

    function processItem(item) {
        const properties = item.querySelectorAll('dl.properties > .property');
        properties.forEach(function (prop) {
            // El anclaje curricular ya viene resuelto y maquetado desde el
            // servidor. Sus enlaces de nivel llevan lrmi:educationalLevel en el
            // href, así que sin esta guarda se etiquetaría como esa propiedad y
            // groupMetaProperties() lo arrastraría al grupo de la derecha.
            if (prop.classList.contains('property--curriculum-anchor')) {
                return;
            }
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

            if (prop.dataset.term === 'lrmi:timeRequired') {
                upgradeDurationProperty(prop);
            }

            if (prop.dataset.term === 'lrmi:teaches' || prop.dataset.term === 'dcterms:relation') {
                groupNoBreak(prop);
            }
        });

        groupMetaProperties(item);
        scheduleCollapse(item);
    }

    function processList(list) {
        list.querySelectorAll('li.resource.item').forEach(processItem);
    }

    function processAll(root) {
        (root || document).querySelectorAll(LIST_SELECTOR).forEach(processList);
    }

    function init() {
        processAll(document);

        // Inter y Material Symbols llegan de fuera: al aplicarse cambian el
        // ancho de los chips y con él cuántos caben en la primera línea. Se
        // vuelve a medir cuando la tipografía está lista.
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function () {
                document.querySelectorAll('.property--collapsible').forEach(undoCollapse);
                scheduleCollapse(document);
            });
        }

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

    // Facet URLs come from a data attribute, so they are a DOM sink: assigning an
    // unvalidated value to .href would honour "javascript:". Only same-origin
    // destinations are accepted.
    function safeHref(url) {
        if (!url) return '#';
        try {
            var resolved = new URL(url, window.location.href);
            if (resolved.origin !== window.location.origin) return '#';
            if (resolved.protocol !== 'http:' && resolved.protocol !== 'https:') return '#';
            return resolved.href;
        } catch (e) {
            return '#';
        }
    }

    function makeSpan(className, text) {
        var span = document.createElement('span');
        span.className = className;
        span.textContent = text;
        return span;
    }

    function buildFilterChips() {
        var bar = document.getElementById('active-filter-chips');
        if (!bar) return;

        bar.replaceChildren();
        var checked = document.querySelectorAll('.search-facets input[type="checkbox"]:checked');
        if (!checked.length) return;

        checked.forEach(function (cb) {
            var chip = document.createElement('a');
            chip.className = 'filter-chip';
            chip.href = safeHref(cb.dataset.url);

            // Group names and facet values are catalogue metadata: build them as text
            // nodes so markup inside a facet value can never be interpreted.
            var group = getGroupName(cb);
            if (group) {
                chip.appendChild(makeSpan('filter-chip__group', group + ':'));
                chip.appendChild(document.createTextNode(' '));
            }
            chip.appendChild(makeSpan('filter-chip__value', getValueText(cb)));

            var remove = makeSpan('filter-chip__remove', '✕');
            remove.setAttribute('aria-hidden', 'true');
            chip.appendChild(remove);

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
