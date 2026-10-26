const mediaShareScript = () => {

    const feedbackDelay = 2000;

    // Single polite live region so screen readers announce the copy result.
    const liveRegion = document.createElement('div');
    liveRegion.className = 'screen-reader-text';
    liveRegion.setAttribute('aria-live', 'polite');
    document.body.appendChild(liveRegion);

    // execCommand fallback for plain-HTTP sites, where navigator.clipboard is
    // not exposed.
    function legacyCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (e) {
            copied = false;
        }
        document.body.removeChild(textarea);
        return copied ? Promise.resolve() : Promise.reject();
    }

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).catch(() => legacyCopy(text));
        }
        return legacyCopy(text);
    }

    function showCopied(button) {
        const icon = button.querySelector('.material-symbols-outlined');
        const tooltip = button.querySelector('.media-action-tooltip');
        if (!button.dataset.icon) {
            button.dataset.icon = icon.textContent;
            button.dataset.tooltip = tooltip.textContent;
        }
        icon.textContent = 'check';
        tooltip.textContent = button.dataset.copiedMessage;
        button.classList.add('is-copied');
        liveRegion.textContent = button.dataset.copiedMessage;

        clearTimeout(button.copiedTimeout);
        button.copiedTimeout = setTimeout(() => {
            icon.textContent = button.dataset.icon;
            tooltip.textContent = button.dataset.tooltip;
            button.classList.remove('is-copied');
            liveRegion.textContent = '';
        }, feedbackDelay);
    }

    // Escape hides the tooltip under the pointer/focus until it leaves.
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        document.querySelectorAll('.media-action:hover, .media-action:focus-visible').forEach((action) => {
            action.classList.add('tooltip-dismissed');
        });
    });

    ['mouseout', 'focusout'].forEach((type) => {
        document.addEventListener(type, (event) => {
            const action = event.target.closest && event.target.closest('.media-action');
            if (action && !action.contains(event.relatedTarget)) {
                action.classList.remove('tooltip-dismissed');
            }
        });
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.media-copy');
        if (!button) {
            return;
        }
        const text = button.dataset.copyText;
        copyText(text)
            .then(() => showCopied(button))
            .catch(() => {
                // Last resort: let the user copy it by hand.
                const actions = button.closest('.media-actions');
                window.prompt(actions ? actions.dataset.copyFallbackMessage : '', text);
            });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mediaShareScript);
} else {
    mediaShareScript();
}
