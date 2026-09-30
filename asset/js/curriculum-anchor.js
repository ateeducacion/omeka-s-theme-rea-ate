// Tooltips of the grouped curriculum anchor (view/common/curriculum-anchor-card.phtml).
// CSS shows them on :hover / :focus-within; this adds the WCAG 1.4.13 "dismissible"
// part: Escape hides the open tooltip without moving focus or the pointer. The
// tooltip comes back once the pointer leaves the code or focus moves elsewhere.

(function () {
    const ITEM = '.curriculum-card__code-item';
    const DISMISSED = 'curriculum-card__code-item--dismissed';

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        const open = document.querySelectorAll(ITEM + ':hover, ' + ITEM + ':focus-within');
        open.forEach(function (item) {
            if (!item.classList.contains(DISMISSED)) {
                item.classList.add(DISMISSED);
                event.preventDefault();
            }
        });
    });

    function restore(event) {
        const item = event.target.closest && event.target.closest(ITEM);
        if (!item) return;
        // focusout/mouseleave fire before the new target is known: only restore when
        // the pointer and the focus have both left this item.
        requestAnimationFrame(function () {
            if (!item.matches(':hover') && !item.contains(document.activeElement)) {
                item.classList.remove(DISMISSED);
            }
        });
    }

    document.addEventListener('focusout', restore);
    document.addEventListener('mouseout', restore);
})();
