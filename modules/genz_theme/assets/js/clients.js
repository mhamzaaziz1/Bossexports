/**
 * GenZ Theme - Clients JavaScript
 * Provides interactivity for the client area
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize client area
    initClientArea();
});

/**
 * Initialize client area
 */
function initClientArea() {
    console.log('GenZ Theme Client Area Initialized');
    
    // Add modern UI enhancements
    enhanceClientUI();
    
    // Initialize animations
    initClientAnimations();
    
    // Initialize dark mode toggle
    initClientDarkModeToggle();
    
    // Enhance tables
    enhanceTables();
    
    // Enhance forms
    enhanceForms();
    
    // Add scroll animations
    addClientScrollAnimations();
}

/**
 * Enhance client UI with modern elements
 */
function enhanceClientUI() {
    // Add modern card styling
    document.querySelectorAll('.panel, .card').forEach(function(card) {
        card.classList.add('hover-card');
    });
    
    // Enhance buttons
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
    
    // Add gradient text to main headings
    document.querySelectorAll('h1.customer-profile-group-heading').forEach(function(heading) {
        heading.classList.add('gradient-text');
    });
    
    // Enhance navigation
    const navItems = document.querySelectorAll('.customers-nav-item');
    navItems.forEach(function(item, index) {
        item.style.animation = 'fadeIn 0.5s ease-out forwards';
        item.style.animationDelay = (0.1 + index * 0.1) + 's';
    });
}

/**
 * Initialize animations for client area
 */
function initClientAnimations() {
    // Add animation to content wrapper
    const contentWrapper = document.querySelector('.content');
    if (contentWrapper) {
        contentWrapper.style.animation = 'fadeIn 0.5s ease-out';
    }
    
    // Add animation to panels
    document.querySelectorAll('.panel, .card').forEach(function(panel, index) {
        panel.style.animation = 'slideInUp 0.5s ease-out forwards';
        panel.style.animationDelay = (0.1 + index * 0.1) + 's';
        panel.style.opacity = '0';
    });
    
    // Add pulse animation to notification icons
    document.querySelectorAll('.icon-notifications').forEach(function(icon) {
        if (icon.querySelector('.badge') && icon.querySelector('.badge').textContent !== '0') {
            icon.classList.add('notification-bell', 'active');
        }
    });
}

/**
 * Initialize dark mode toggle for client area
 */
function initClientDarkModeToggle() {
    // Create dark mode toggle button if it doesn't exist
    if (!document.querySelector('.dark-mode-toggle')) {
        const toggleButton = document.createElement('button');
        toggleButton.className = 'dark-mode-toggle';
        toggleButton.innerHTML = '<i class="fa fa-moon-o"></i>';
        toggleButton.setAttribute('title', 'Toggle Dark Mode');
        document.body.appendChild(toggleButton);
        
        // Add click event to toggle dark mode
        toggleButton.addEventListener('click', function() {
            toggleClientDarkMode();
        });
    }
}

/**
 * Toggle dark mode for client area
 */
function toggleClientDarkMode() {
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
    $.post(site_url + 'genz_theme/save_client_dark_mode_preference', {
        dark_mode: !isDarkMode ? 1 : 0
    });
}

/**
 * Enhance tables in client area
 */
function enhanceTables() {
    // Add modern styling to tables
    document.querySelectorAll('.table').forEach(function(table) {
        table.classList.add('table-modern');
        
        // Add animation to table rows
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row, index) {
            row.style.animation = 'fadeIn 0.5s ease-out forwards';
            row.style.animationDelay = (0.1 + index * 0.05) + 's';
            row.style.opacity = '0';
            
            // Add hover effect
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    });
    
    // Enhance table filters
    document.querySelectorAll('.dataTables_filter input').forEach(function(input) {
        input.classList.add('modern-search');
        input.placeholder = 'Search...';
        input.style.borderRadius = 'var(--genz-border-radius-md)';
        input.style.border = '1px solid var(--genz-border-color)';
        input.style.padding = '0.5rem 1rem';
        input.style.transition = 'all 0.3s ease';
    });
}

/**
 * Enhance forms in client area
 */
function enhanceForms() {
    // Add modern styling to form controls
    document.querySelectorAll('.form-control').forEach(function(control) {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Add animation to form groups
    document.querySelectorAll('.form-group').forEach(function(group, index) {
        group.style.animation = 'fadeIn 0.5s ease-out forwards';
        group.style.animationDelay = (0.1 + index * 0.05) + 's';
        group.style.opacity = '0';
    });
    
    // Enhance select elements
    document.querySelectorAll('select.form-control').forEach(function(select) {
        select.style.appearance = 'none';
        select.style.backgroundImage = 'url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23718096\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e")';
        select.style.backgroundRepeat = 'no-repeat';
        select.style.backgroundPosition = 'right 1rem center';
        select.style.backgroundSize = '1em';
    });
}

/**
 * Add scroll animations to client area
 */
function addClientScrollAnimations() {
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
    
    // Check if an element is in viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Add scroll event listener
    window.addEventListener('scroll', checkScroll);
    
    // Check on initial load
    checkScroll();
}

/**
 * Add custom styling to client dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add custom styling to dashboard widgets
    const dashboardWidgets = document.querySelectorAll('.widget, .widget-data');
    
    dashboardWidgets.forEach(function(widget, index) {
        // Add animation
        widget.style.animation = 'fadeIn 0.5s ease-out forwards';
        widget.style.animationDelay = (0.1 + index * 0.1) + 's';
        widget.style.opacity = '0';
        
        // Add modern styling
        widget.style.borderRadius = 'var(--genz-border-radius-lg)';
        widget.style.overflow = 'hidden';
        widget.style.boxShadow = 'var(--genz-shadow-sm)';
        widget.style.transition = 'all 0.3s ease';
        
        // Add hover effect
        widget.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = 'var(--genz-shadow-md)';
        });
        
        widget.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = 'var(--genz-shadow-sm)';
        });
    });
});