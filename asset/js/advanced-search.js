// ---- Generic Advanced Search form (Omeka-S module) ----
// Only runs when the classic #property-queries section is present.
const advancedSearchScripts = () => {
    const propertyQueries = document.getElementById('property-queries');
    if (!propertyQueries) return;

    const addRowBtn = propertyQueries.querySelector('.add-value');
    let subFields;
    let removeRowBtns;

    const placeLabels = () => {
        setTimeout(() => {
            subFields = propertyQueries.querySelectorAll('.sub-field');
            subFields.forEach((subField) => {
                const prev = subField.previousElementSibling;
                if (prev.tagName == 'LABEL') {
                    prev.style.left = subField.offsetLeft + 'px';
                    prev.style.top = (subField.offsetTop - 19) + 'px';
                    prev.style.opacity = 1;
                }
            });

            removeRowBtns = propertyQueries.querySelectorAll('.remove-value');
            removeRowBtns.forEach((removeRowBtn) => {
                removeRowBtn.addEventListener('click', placeLabels);
            });
        }, 10);
    };

    placeLabels();
    addRowBtn.addEventListener('click', placeLabels);
    window.onresize = placeLabels;
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', advancedSearchScripts);
} else {
    advancedSearchScripts();
}

// ---- Educational search form (bs-* components, ciclo 6) ----
(function () {
    function initEduForm() {
        if (!document.querySelector('.bs-chip-group, .bs-hero-search')) return;

        // Multi-select chip groups: clicking a chip toggles it independently.
        document.querySelectorAll('.bs-chip-group').forEach(function (group) {
            group.addEventListener('click', function (e) {
                const chip = e.target.closest('.bs-chip');
                if (!chip) return;
                const selected = chip.classList.toggle('selected');
                chip.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
        });

        // "Más filtros" toggle.
        const moreBtn = document.getElementById('moreFiltersToggle');
        const morePanel = document.getElementById('moreFilters');
        if (moreBtn && morePanel) {
            moreBtn.addEventListener('click', function () {
                const open = morePanel.classList.toggle('open');
                moreBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        // Dropdown → pill: works for both subject (value = text) and collections (value = ID).
        document.querySelectorAll('select[data-pills-target]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                const val = sel.value.trim();
                if (!val) return;
                const pillsContainer = document.getElementById(sel.dataset.pillsTarget);
                if (!pillsContainer) return;

                // Avoid duplicate pills.
                const already = pillsContainer.querySelector('[data-value="' + CSS.escape(val) + '"]');
                if (already) { sel.value = ''; return; }

                // Use the option label as display text (handles IDs for collections).
                const label = sel.options[sel.selectedIndex].textContent.trim();

                // The label comes from an <option> whose text is catalogue-authored, so it
                // is inserted as a text node and never as markup.
                const pill = document.createElement('span');
                pill.className = 'bs-selected-pill';
                pill.dataset.value = val;
                pill.appendChild(document.createTextNode(label));

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'bs-selected-pill__remove';
                removeBtn.setAttribute('aria-label', 'Quitar ' + label);

                const removeIcon = document.createElement('span');
                removeIcon.className = 'material-symbols-outlined';
                removeIcon.setAttribute('aria-hidden', 'true');
                removeIcon.textContent = 'close';
                removeBtn.appendChild(removeIcon);
                pill.appendChild(removeBtn);

                removeBtn.addEventListener('click', function () {
                    pill.remove();
                });
                pillsContainer.appendChild(pill);
                sel.value = '';
            });
        });

        // Delegate pill removal for pills rendered server-side (existing query).
        document.querySelectorAll('.bs-selected-pills').forEach(function (container) {
            container.addEventListener('click', function (e) {
                const btn = e.target.closest('.bs-selected-pill__remove');
                if (!btn) return;
                btn.closest('.bs-selected-pill').remove();
            });
        });

        // Reset: also clear chip selections and pills.
        const resetBtn = document.getElementById('edu-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                document.querySelectorAll('.bs-chip').forEach(function (c) {
                    c.classList.remove('selected');
                    c.setAttribute('aria-pressed', 'false');
                });
                document.querySelectorAll('.bs-selected-pills').forEach(function (c) {
                    c.replaceChildren();
                });
                const collSel = document.getElementById('collection-add-select');
                if (collSel) collSel.value = '';
                const dMin = document.getElementById('duration-min');
                const dMax = document.getElementById('duration-max');
                if (dMin) dMin.value = '';
                if (dMax) dMax.value = '';
            });
        }

        // Before submit: one search condition per filter group (level, type, audience,
        // subject, duration). The values of a group are alternatives (OR) inside that
        // single condition, and conditions are joined with AND.
        //
        // Not one property row per value joined with "or": Omeka evaluates property
        // rows in sequence without parentheses, so "3º ESO or 4º ESO and Lengua"
        // reads as "3º ESO or (4º ESO and Lengua)" and returns 3º ESO resources of
        // any subject. Grouping needs a multi-value condition instead:
        // - types that accept a list in a property row (res, list): property[n] with
        //   text[] (AdvancedSearch matches any of them);
        // - other types (in = contains): AdvancedSearch filter[n] with val[]; a
        //   property row drops a list for these types and would match everything.
        const form = document.getElementById('advanced-search');
        if (!form) return;

        const LIST_TYPES = ['res', 'list'];

        form.addEventListener('submit', function () {
            const container = document.getElementById('bs-chip-inputs');
            if (!container) return;
            container.replaceChildren();

            let propertyIdx = 0;
            let filterIdx = 0;

            function addCondition(propId, type, values) {
                if (!propId || !values.length) return;
                if (LIST_TYPES.indexOf(type) !== -1) {
                    const stem = 'property[' + propertyIdx + ']';
                    if (propertyIdx > 0) addHidden(container, stem + '[joiner]', 'and');
                    addHidden(container, stem + '[property]', propId);
                    addHidden(container, stem + '[type]', type);
                    values.forEach(function (val) {
                        addHidden(container, stem + '[text][]', val);
                    });
                    propertyIdx++;
                } else {
                    const stem = 'filter[' + filterIdx + ']';
                    addHidden(container, stem + '[join]', 'and');
                    addHidden(container, stem + '[field]', propId);
                    addHidden(container, stem + '[type]', type);
                    values.forEach(function (val) {
                        addHidden(container, stem + '[val][]', val);
                    });
                    filterIdx++;
                }
            }

            // 1. Chip groups (multi-select chips for level, type, audience).
            document.querySelectorAll('.bs-chip-group[data-prop-id]').forEach(function (group) {
                const values = Array.prototype.map.call(
                    group.querySelectorAll('.bs-chip.selected'),
                    function (chip) { return chip.dataset.value || chip.textContent.trim(); }
                );
                addCondition(group.dataset.propId, group.dataset.propType || 'in', values);
            });

            // 2. Pill containers (subject multi-value select).
            document.querySelectorAll('.bs-selected-pills[data-prop-id]').forEach(function (pills) {
                const values = Array.prototype.map.call(
                    pills.querySelectorAll('.bs-selected-pill[data-value]'),
                    function (pill) { return pill.dataset.value; }
                );
                addCondition(pills.dataset.propId, pills.dataset.propType || 'in', values);
            });

            // 3. Collection pills → item_set_id[] inputs (already OR inside one argument).
            document.querySelectorAll('.bs-selected-pills[data-item-set-pills]').forEach(function (pills) {
                pills.querySelectorAll('.bs-selected-pill[data-value]').forEach(function (pill) {
                    addHidden(container, 'item_set_id[]', pill.dataset.value);
                });
            });

            // 4. Duration range: Omeka has no numeric range for literals, so the known
            //    values within [min, max] become one "is in list" condition. Known values
            //    come from a PHP data attribute; fallback = multiples of 5.
            const minInput = document.getElementById('duration-min');
            const maxInput = document.getElementById('duration-max');
            if (minInput || maxInput) {
                const durPropId = container.dataset.durPropId;
                const minVal = minInput && minInput.value !== '' ? parseInt(minInput.value, 10) : -Infinity;
                const maxVal = maxInput && maxInput.value !== '' ? parseInt(maxInput.value, 10) : Infinity;

                let allDurValues = null;
                try {
                    const raw = container.dataset.durValues;
                    allDurValues = raw && raw !== 'null' ? JSON.parse(raw) : null;
                } catch (e) { allDurValues = null; }

                // Fallback: multiples of 5 up to 240 min (covers typical edu resources)
                if (!allDurValues) {
                    allDurValues = [];
                    for (let m5 = 5; m5 <= 240; m5 += 5) allDurValues.push(m5);
                }

                if (durPropId && (isFinite(minVal) || isFinite(maxVal))) {
                    const matching = allDurValues.filter(function (v) {
                        return (isFinite(minVal) ? v >= minVal : true)
                            && (isFinite(maxVal) ? v <= maxVal : true);
                    }).map(String);
                    // A range with no known value must return nothing, not everything.
                    addCondition(durPropId, 'list', matching.length ? matching : ['__no_match__']);
                }
            }
        });

        function addHidden(container, name, value) {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = name;
            inp.value = value;
            container.appendChild(inp);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEduForm);
    } else {
        initEduForm();
    }
})();
