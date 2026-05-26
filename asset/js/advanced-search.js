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
                var chip = e.target.closest('.bs-chip');
                if (!chip) return;
                chip.classList.toggle('selected');
            });
        });

        // "Más filtros" toggle.
        var moreBtn = document.getElementById('moreFiltersToggle');
        var morePanel = document.getElementById('moreFilters');
        if (moreBtn && morePanel) {
            moreBtn.addEventListener('click', function () {
                var open = morePanel.classList.toggle('open');
                moreBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        // Dropdown → pill: works for both subject (value = text) and collections (value = ID).
        document.querySelectorAll('select[data-pills-target]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                var val = sel.value.trim();
                if (!val) return;
                var pillsContainer = document.getElementById(sel.dataset.pillsTarget);
                if (!pillsContainer) return;

                // Avoid duplicate pills.
                var already = pillsContainer.querySelector('[data-value="' + CSS.escape(val) + '"]');
                if (already) { sel.value = ''; return; }

                // Use the option label as display text (handles IDs for collections).
                var label = sel.options[sel.selectedIndex].textContent.trim();

                var pill = document.createElement('span');
                pill.className = 'bs-selected-pill';
                pill.dataset.value = val;
                pill.innerHTML =
                    label +
                    '<button type="button" class="bs-selected-pill__remove" aria-label="Quitar ' + label + '">' +
                    '<span class="material-symbols-outlined" aria-hidden="true">close</span>' +
                    '</button>';
                pill.querySelector('.bs-selected-pill__remove').addEventListener('click', function () {
                    pill.remove();
                });
                pillsContainer.appendChild(pill);
                sel.value = '';
            });
        });

        // Delegate pill removal for pills rendered server-side (existing query).
        document.querySelectorAll('.bs-selected-pills').forEach(function (container) {
            container.addEventListener('click', function (e) {
                var btn = e.target.closest('.bs-selected-pill__remove');
                if (!btn) return;
                btn.closest('.bs-selected-pill').remove();
            });
        });

        // Reset: also clear chip selections and pills.
        var resetBtn = document.getElementById('edu-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                document.querySelectorAll('.bs-chip').forEach(function (c) {
                    c.classList.remove('selected');
                });
                document.querySelectorAll('.bs-selected-pills').forEach(function (c) {
                    c.innerHTML = '';
                });
                var collSel = document.getElementById('collection-add-select');
                if (collSel) collSel.value = '';
                var dMin = document.getElementById('duration-min');
                var dMax = document.getElementById('duration-max');
                if (dMin) dMin.value = '';
                if (dMax) dMax.value = '';
            });
        }

        // Before submit: build property[n][...] inputs from all chip groups AND pill containers.
        // Chips + pills use consecutive indices 0..N.
        // Number inputs use static high indices (50, 51) set in PHP — disable when empty.
        var form = document.getElementById('advanced-search');
        if (!form) return;

        form.addEventListener('submit', function () {
            var container = document.getElementById('bs-chip-inputs');
            if (container) {
                container.innerHTML = '';
                var idx = 0;

                // 1. Chip groups (multi-select chips for level, type, audience).
                document.querySelectorAll('.bs-chip-group[data-prop-id]').forEach(function (group) {
                    var propId   = group.dataset.propId;
                    var propType = group.dataset.propType || 'in';
                    var selected = group.querySelectorAll('.bs-chip.selected');

                    selected.forEach(function (chip, i) {
                        var val = chip.dataset.value || chip.textContent.trim();
                        addHidden(container, 'property[' + idx + '][property]', propId);
                        addHidden(container, 'property[' + idx + '][type]',     propType);
                        addHidden(container, 'property[' + idx + '][text]',     val);
                        if (i > 0) {
                            addHidden(container, 'property[' + idx + '][joiner]', 'or');
                        }
                        idx++;
                    });
                });

                // 2. Pill containers (subject multi-value select → property OR conditions).
                document.querySelectorAll('.bs-selected-pills[data-prop-id]').forEach(function (pills) {
                    var propId   = pills.dataset.propId;
                    var propType = pills.dataset.propType || 'in';
                    var items    = pills.querySelectorAll('.bs-selected-pill[data-value]');

                    items.forEach(function (pill, i) {
                        var val = pill.dataset.value;
                        addHidden(container, 'property[' + idx + '][property]', propId);
                        addHidden(container, 'property[' + idx + '][type]',     propType);
                        addHidden(container, 'property[' + idx + '][text]',     val);
                        if (i > 0) {
                            addHidden(container, 'property[' + idx + '][joiner]', 'or');
                        }
                        idx++;
                    });
                });

                // 3. Collection pills → item_set_id[] inputs.
                document.querySelectorAll('.bs-selected-pills[data-item-set-pills]').forEach(function (pills) {
                    pills.querySelectorAll('.bs-selected-pill[data-value]').forEach(function (pill) {
                        addHidden(container, 'item_set_id[]', pill.dataset.value);
                    });
                });
            }

            // 3. Duration range: Omeka only supports eq/in/sw/ew for property queries.
            //    Build OR eq conditions for each known value within [min, max].
            //    Known values come from PHP data attribute; fallback = multiples of 5.
            var chipContainer = document.getElementById('bs-chip-inputs');
            var minInput = document.getElementById('duration-min');
            var maxInput = document.getElementById('duration-max');
            if (chipContainer && (minInput || maxInput)) {
                var durPropId = chipContainer.dataset.durPropId;
                var minVal = minInput && minInput.value !== '' ? parseInt(minInput.value, 10) : -Infinity;
                var maxVal = maxInput && maxInput.value !== '' ? parseInt(maxInput.value, 10) : Infinity;

                var allDurValues = null;
                try {
                    var raw = chipContainer.dataset.durValues;
                    allDurValues = raw && raw !== 'null' ? JSON.parse(raw) : null;
                } catch (e) { allDurValues = null; }

                // Fallback: multiples of 5 up to 240 min (covers typical edu resources)
                if (!allDurValues) {
                    allDurValues = [];
                    for (var m5 = 5; m5 <= 240; m5 += 5) allDurValues.push(m5);
                }

                if (isFinite(minVal) || isFinite(maxVal)) {
                    var matching = allDurValues.filter(function (v) {
                        return (isFinite(minVal) ? v >= minVal : true)
                            && (isFinite(maxVal) ? v <= maxVal : true);
                    });

                    if (matching.length === 0 && durPropId) {
                        // Range entered but no values match — force zero results
                        addHidden(container, 'property[' + idx + '][property]', durPropId);
                        addHidden(container, 'property[' + idx + '][type]', 'eq');
                        addHidden(container, 'property[' + idx + '][text]', '__no_match__');
                        idx++;
                    } else {
                        matching.forEach(function (val, i) {
                            addHidden(container, 'property[' + idx + '][property]', durPropId);
                            addHidden(container, 'property[' + idx + '][type]', 'eq');
                            addHidden(container, 'property[' + idx + '][text]', String(val));
                            if (i > 0) addHidden(container, 'property[' + idx + '][joiner]', 'or');
                            idx++;
                        });
                    }

                }
            }
        });

        function addHidden(container, name, value) {
            var inp = document.createElement('input');
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
