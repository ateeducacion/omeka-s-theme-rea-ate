const freedomScripts = () => {

    const body = document.body;
    const mainHeader = document.querySelector('.main-header');
    const mainHeaderTopBar = document.querySelector('.main-header__top-bar');
    const mainHeaderMainBar = document.querySelector('.main-header__main-bar');
    const menuDrawer = document.getElementById('menu-drawer');
    const userBar = document.getElementById('user-bar');

    // Scrolling Events

    let lastKnownScrollPosition = 0;
    let ticking = false;
    let scrollDirection = 'up';

    function onScroll(scrollPos) {
        if (scrollPos > 60 && scrollDirection === 'down') {
            mainHeader.classList.add('header-scrolled');
        } else if (scrollPos === 0) {
            mainHeader.classList.remove('header-scrolled');
        }
        if (menuDrawer) {
            menuDrawer.style.top = mainHeader.offsetHeight + 'px';
            menuDrawer.style.height = 'calc(100% - ' + mainHeader.offsetHeight + 'px)';
        }
    }

    document.addEventListener('scroll', (event) => {
        scrollDirection = Math.max(lastKnownScrollPosition, window.scrollY) == lastKnownScrollPosition ? 'up': 'down';
        lastKnownScrollPosition = window.scrollY;

        if (!ticking) {
            window.requestAnimationFrame(() => {
                onScroll(lastKnownScrollPosition);
                ticking = false;
            });

            ticking = true;
        }
    });

    // Resize Events

    let userBarHeight = 0;
    let timeout = false;
    const delay = 250;

    onResize();

    function onResize() {
        getUserBarHeight();
        refreshBodyPaddingTop();
        onScroll(lastKnownScrollPosition);
    }

    window.addEventListener('resize', function() {
        clearTimeout(timeout);
        timeout = setTimeout(onResize, delay);
    });

    function refreshBodyPaddingTop() {
        body.style.paddingTop = mainHeader.offsetHeight + 'px';
        document.documentElement.style.scrollPaddingTop = (mainHeaderMainBar.offsetHeight + 20) + 'px';
    }

    if (window.ResizeObserver) {
        new ResizeObserver(refreshBodyPaddingTop).observe(mainHeader);
    }

    function getUserBarHeight() {
        if (userBar) {
            userBarHeight = userBar.offsetHeight;
        }
    }

    // Annotations tooltip position

    const annotationBtns = document.querySelectorAll('.annotation-btn');

    annotationBtns.forEach((annotationBtn) => {
        const annotationTooltip = annotationBtn.querySelector('.annotation-tooltip');
        const annotationTooltipWrapper = annotationTooltip.querySelector('.annotation-tooltip__wrapper');

        const eventList = ['click', 'mouseover'];
        eventList.forEach((event) => {
            annotationBtn.addEventListener(event, setAnnotationTooltipPos);
        });

        function setAnnotationTooltipPos() {
            const annotationBtnOffset = annotationBtn.getBoundingClientRect();
            const { top, left } = annotationBtnOffset;
            const distanceToRightEdge = window.innerWidth - (left + annotationBtn.offsetWidth);
            
            if (distanceToRightEdge < (annotationTooltipWrapper.offsetWidth + 15)) {
                annotationTooltip.style.left = (distanceToRightEdge - annotationTooltipWrapper.offsetWidth - 15) + 'px';
            } else {
                annotationTooltip.style.left = '0px';
            }

            if ((top - mainHeader.offsetHeight - mainHeader.offsetTop) < (annotationTooltipWrapper.offsetHeight + 15)) {
                annotationTooltip.style.bottom = (- annotationTooltipWrapper.offsetHeight - 20) + 'px';
                annotationTooltipWrapper.classList.add('below-button');
            } else {
                annotationTooltip.style.bottom = '10px';
                annotationTooltipWrapper.classList.remove('below-button');

                if (annotationTooltip.style.left == '0px') {
                    annotationTooltip.style.bottom = '5px';
                }
            }
        }
    });

    // Delete confirmation modal
    const deleteModal = document.getElementById('delete-confirm-modal');
    if (deleteModal) {
        const titleSpan = document.getElementById('delete-item-title');
        const csrfToken = deleteModal.getAttribute('data-csrf-token');
        const adminBasePath = deleteModal.getAttribute('data-admin-base-path');
        let currentItemId = null;

        document.querySelectorAll('.o-icon-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                currentItemId = this.getAttribute('data-item-id');
                titleSpan.textContent = this.getAttribute('data-item-title');
                deleteModal.style.display = 'flex';
            });
        });

        document.getElementById('delete-cancel').addEventListener('click', function() {
            deleteModal.style.display = 'none';
            currentItemId = null;
        });

        deleteModal.querySelector('.delete-confirm-modal__overlay').addEventListener('click', function() {
            deleteModal.style.display = 'none';
            currentItemId = null;
        });

        document.getElementById('delete-confirm').addEventListener('click', function() {
            if (!currentItemId) return;
            const deleteUrl = adminBasePath + '/item/' + currentItemId + '/delete';
            const params = new URLSearchParams();
            params.append('confirmform_csrf', csrfToken);
            fetch(deleteUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: params.toString()
            }).then(function(response) {
                if (response.ok) {
                    window.location.reload();
                } else {
                    deleteModal.style.display = 'none';
                }
            }).catch(function() {
                deleteModal.style.display = 'none';
            });
        });
    }

    // Add class to properties with dcterms:relation field-term
    const properties = document.querySelectorAll('.property');

    properties.forEach((property) => {
        const fieldTerm = property.querySelector('dt .field-term');
        if (fieldTerm && fieldTerm.textContent.includes('dcterms:relation')) {
            property.classList.add('dcterms-relation');
        }
    });

    // Collapsible facet groups
    document.querySelectorAll('.search-facets li.facet').forEach(function(facet) {
        const heading = facet.querySelector('h4');
        if (!heading) return;

        const key = 'ate-facet-' + heading.textContent.trim();
        if (sessionStorage.getItem(key) === '1') {
            facet.classList.add('facet-collapsed');
        }

        heading.addEventListener('click', function() {
            facet.classList.toggle('facet-collapsed');
            sessionStorage.setItem(key, facet.classList.contains('facet-collapsed') ? '1' : '0');
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', freedomScripts);
} else {
    freedomScripts();
}
