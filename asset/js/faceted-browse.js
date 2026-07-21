const showMoreFacets = (button) => {
    const facets = document.getElementById('facets');
    facets.classList.toggle('show-all');

    if (button.textContent === 'Show more') {
        button.textContent = 'Show less';
    } else {
        button.textContent = 'Show more';
    }
};
