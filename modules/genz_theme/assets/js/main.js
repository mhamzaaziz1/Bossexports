/**
 * GenZ Theme - Main JavaScript
 * Provides interactivity for the GenZ theme
 */

// Wait for the document to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the theme
    initGenZTheme();
});

/**
 * Initialize the GenZ theme
 */
function initGenZTheme() {
    console.log('GenZ Theme Initialized');
    
    // Add page loader
    addPageLoader();
    
    // Initialize animations
    initAnimations();
    
    // Initialize dark mode toggle
    initDarkModeToggle();
    
    // Initialize hover effects
    initHoverEffects();
    
    // Initialize tooltips and popovers
    initTooltipsAndPopovers();
    
    // Initialize custom scrollbars
    initCustomScrollbars();
    
    // Add gradient text effect to headings
    addGradientTextEffect();
    
    // Initialize notification animations
    initNotificationAnimations();
}

/**
 * Add page loader animation
 */
function addPageLoader() {
    // Create loader elements
    const loaderDiv = document.createElement('div');
    loaderDiv.className = 'page-loader';
    
    const spinnerDiv = document.createElement('div');
    spinnerDiv.className = 'loader-spinner';
    
    loaderDiv.appendChild(spinnerDiv);
    document.body.appendChild(loaderDiv);
    
    // Remove loader after page is fully loaded
    window.addEventListener('load', function() {
        loaderDiv.classList.add('loaded');
        
        // Remove from DOM after animation completes
        setTimeout(function() {
            if (loaderDiv.parentNode) {
                loaderDiv.parentNode.removeChild(loaderDiv);
            }
        }, 500);
    });
}

/**
 * Initialize animations for various elements
 */
function initAnimations() {
    // Add animation classes to elements
    
    // Add float animation to cards with .float-animation class
    document.querySelectorAll('.float-animation').forEach(function(element) {
        element.style.animation = 'float 3s ease-in-out infinite';
    });
    
    // Add hover-card class to dashboard widgets
    document.querySelectorAll('.widget, .dashboard-card').forEach(function(element) {
        element.classList.add('hover-card');
    });
}

/**
 * Initialize dark mode toggle
 */
function initDarkModeToggle() {
    // Create dark mode toggle button if it doesn't exist
    if (!document.querySelector('.dark-mode-toggle')) {
        const toggleButton = document.createElement('button');
        toggleButton.className = 'dark-mode-toggle';
        toggleButton.innerHTML = '<i class="fa fa-moon-o"></i>';
        toggleButton.setAttribute('title', 'Toggle Dark Mode');
        document.body.appendChild(toggleButton);
        
        // Add click event to toggle dark mode
        toggleButton.addEventListener('click', function() {
            toggleDarkMode();
        });
    }
}

/**
 * Toggle dark mode
 */
function toggleDarkMode() {
    // Get current dark mode state
    const isDarkMode = document.body.classList.contains('dark-mode');
    
    // Toggle dark mode class on body
    if (isDarkMode) {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('genz_dark_mode', 'off');
        document.querySelector('.dark-mode-toggle i').className = 'fa fa-moon-o';
    } else {
        document.body.classList.add('dark-mode');
        localStorage.setItem('genz_dark_mode', 'on');
        document.querySelector('.dark-mode-toggle i').className = 'fa fa-sun-o';
    }
    
    // Save preference via AJAX
    $.post(admin_url + 'genz_theme/save_dark_mode_preference', {
        dark_mode: !isDarkMode ? 1 : 0
    });
}

/**
 * Initialize hover effects
 */
function initHoverEffects() {
    // Add hover effects to buttons
    document.querySelectorAll('.btn').forEach(function(button) {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = 'var(--genz-shadow-md)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
}

/**
 * Initialize tooltips and popovers
 */
function initTooltipsAndPopovers() {
    // Initialize Bootstrap tooltips
    if (typeof $.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip({
            boundary: 'window',
            container: 'body',
            trigger: 'hover'
        });
    }
    
    // Initialize Bootstrap popovers
    if (typeof $.fn.popover === 'function') {
        $('[data-toggle="popover"]').popover({
            container: 'body',
            trigger: 'focus'
        });
    }
}

/**
 * Initialize custom scrollbars
 */
function initCustomScrollbars() {
    // Add custom scrollbar to elements with .custom-scrollbar class
    document.querySelectorAll('.custom-scrollbar').forEach(function(element) {
        element.style.overflowY = 'auto';
    });
}

/**
 * Add gradient text effect to headings
 */
function addGradientTextEffect() {
    // Add gradient text effect to main headings
    document.querySelectorAll('h1.gradient-heading, h2.gradient-heading').forEach(function(heading) {
        heading.classList.add('gradient-text');
    });
}

/**
 * Initialize notification animations
 */
function initNotificationAnimations() {
    // Add pulse animation to notification icons
    document.querySelectorAll('.icon-notifications').forEach(function(icon) {
        if (icon.querySelector('.badge') && icon.querySelector('.badge').textContent !== '0') {
            icon.classList.add('notification-bell', 'active');
        }
    });
}

/**
 * Check if an element is in viewport
 * @param {Element} element - The element to check
 * @returns {boolean} - True if element is in viewport
 */
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

/**
 * Add scroll animations
 */
function addScrollAnimations() {
    // Get all elements with .scroll-animation class
    const elements = document.querySelectorAll('.scroll-animation');
    
    // Function to check if elements are in viewport and add animation
    function checkScroll() {
        elements.forEach(function(element) {
            if (isInViewport(element) && !element.classList.contains('animated')) {
                element.classList.add('animated');
                element.style.animation = 'fadeIn 0.5s ease-out forwards';
            }
        });
    }
    
    // Add scroll event listener
    window.addEventListener('scroll', checkScroll);
    
    // Check on initial load
    checkScroll();
}

// Initialize scroll animations
document.addEventListener('DOMContentLoaded', function() {
    addScrollAnimations();
});