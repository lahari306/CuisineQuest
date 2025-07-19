// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add active class to current navigation item
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (currentLocation.includes(linkPath) && linkPath !== 'index.php') {
            link.classList.add('active');
        } else if (currentLocation.endsWith('/') || currentLocation.endsWith('index.php')) {
            document.querySelector('.navbar-nav .nav-link[href="index.php"]').classList.add('active');
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Show search suggestions when typing in search bar
    const searchInput = document.querySelector('input[name="keyword"]');
    const searchSuggestions = document.getElementById('search-suggestions');
    
    if (searchInput && searchSuggestions) {
        searchInput.addEventListener('input', debounce(function() {
            const keyword = searchInput.value.trim();
            if (keyword.length > 2) {
                fetchSearchSuggestions(keyword);
            } else {
                searchSuggestions.style.display = 'none';
            }
        }, 300));
    }
    
    // Toggle between grid and list view - UPDATED FOR ALL PAGES
    initializeViewToggle();
    
    // Load more button functionality
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadMoreResults();
        });
    }
});

// Initialize view toggle functionality
function initializeViewToggle() {
    const gridViewBtn = document.querySelector('.grid-view-btn');
    const listViewBtn = document.querySelector('.list-view-btn');
    const resultsContainer = document.querySelector('.results-container');
    
    if (gridViewBtn && listViewBtn && resultsContainer) {
        // Set initial state - grid view is default
        resultsContainer.classList.add('grid-view');
        
        // Apply saved preference if it exists
        const preferredView = localStorage.getItem('preferredView') || 'grid';
        if (preferredView === 'list') {
            resultsContainer.classList.remove('grid-view');
            resultsContainer.classList.add('list-view');
            gridViewBtn.classList.remove('active');
            listViewBtn.classList.add('active');
        } else {
            resultsContainer.classList.add('grid-view');
            resultsContainer.classList.remove('list-view');
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        }
        
        // Set up event listeners
        gridViewBtn.addEventListener('click', function() {
            resultsContainer.classList.remove('list-view');
            resultsContainer.classList.add('grid-view');
            listViewBtn.classList.remove('active');
            gridViewBtn.classList.add('active');
            localStorage.setItem('preferredView', 'grid');
        });
        
        listViewBtn.addEventListener('click', function() {
            resultsContainer.classList.remove('grid-view');
            resultsContainer.classList.add('list-view');
            gridViewBtn.classList.remove('active');
            listViewBtn.classList.add('active');
            localStorage.setItem('preferredView', 'list');
        });
    }
}

// Helper function to throttle API calls
function debounce(func, delay) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// Fetch search suggestions using AJAX
function fetchSearchSuggestions(keyword) {
    const searchSuggestions = document.getElementById('search-suggestions');
    
    fetch(`api/search_suggestions.php?keyword=${encodeURIComponent(keyword)}`)
        .then(response => response.json())
        .then(data => {
            if (data.suggestions && data.suggestions.length > 0) {
                let html = '<div class="list-group">';
                data.suggestions.forEach(suggestion => {
                    html += `<a href="search.php?keyword=${encodeURIComponent(suggestion)}" class="list-group-item list-group-item-action">${suggestion}</a>`;
                });
                html += '</div>';
                searchSuggestions.innerHTML = html;
                searchSuggestions.style.display = 'block';
            } else {
                searchSuggestions.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching search suggestions:', error);
            searchSuggestions.style.display = 'none';
        });
}

// Load more results
function loadMoreResults() {
    const loadMoreBtn = document.getElementById('load-more-btn');
    const resultsContainer = document.querySelector('.results-container .row');
    const currentPage = parseInt(loadMoreBtn.dataset.page) || 1;
    const nextPage = currentPage + 1;
    const keyword = loadMoreBtn.dataset.keyword || '';
    const searchType = loadMoreBtn.dataset.searchType || 'name';
    
    loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    loadMoreBtn.disabled = true;
    
    fetch(`api/load_more.php?page=${nextPage}&keyword=${encodeURIComponent(keyword)}&type=${searchType}`)
        .then(response => response.json())
        .then(data => {
            if (data.results && data.results.length > 0) {
                let html = '';
                data.results.forEach(meal => {
                    html += `
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card h-100">
                                <img src="${meal.thumbnail}" class="card-img-top" alt="${meal.name}" onerror="imgError(this)">
                                <div class="card-body">
                                    <h5 class="card-title">${meal.name}</h5>
                                    <p class="card-text small">
                                        <span class="badge bg-primary">${meal.category}</span>
                                        <span class="badge bg-secondary">${meal.area}</span>
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <a href="meal.php?id=${meal.id}" class="btn btn-outline-primary btn-sm w-100">View Recipe</a>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                resultsContainer.innerHTML += html;
                loadMoreBtn.dataset.page = nextPage;
                
                if (data.hasMore) {
                    loadMoreBtn.innerHTML = 'Load More';
                    loadMoreBtn.disabled = false;
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            } else {
                loadMoreBtn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading more results:', error);
            loadMoreBtn.innerHTML = 'Load More';
            loadMoreBtn.disabled = false;
        });
}

// Update popular searches in footer
function updatePopularSearches() {
    const popularSearchesContainer = document.getElementById('popular-searches');
    
    if (popularSearchesContainer) {
        fetch('api/popular_searches.php')
            .then(response => response.json())
            .then(data => {
                if (data.searches && data.searches.length > 0) {
                    let html = '';
                    data.searches.forEach(search => {
                        html += `<a href="search.php?keyword=${encodeURIComponent(search.keyword)}" class="popular-keyword">${search.keyword}</a>`;
                    });
                    popularSearchesContainer.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error fetching popular searches:', error);
            });
    }
}

// Call updatePopularSearches when page loads
document.addEventListener('DOMContentLoaded', updatePopularSearches); 