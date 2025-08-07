/**
 * GenZ Theme - Login Page JavaScript
 * Provides interactivity for the login page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize login page effects
    initLoginPage();
});

/**
 * Initialize login page effects
 */
function initLoginPage() {
    console.log('GenZ Theme Login Page Initialized');
    
    // Add background animation
    addBackgroundAnimation();
    
    // Add form animations
    addFormAnimations();
    
    // Add interactive form validation
    addInteractiveValidation();
    
    // Add floating labels
    addFloatingLabels();
}

/**
 * Add background animation to login page
 */
function addBackgroundAnimation() {
    // Create a canvas element for the background
    const canvas = document.createElement('canvas');
    canvas.className = 'login-bg-canvas';
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.zIndex = '-1';
    
    // Insert canvas as the first element in the body
    document.body.insertBefore(canvas, document.body.firstChild);
    
    // Set canvas size
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    // Get canvas context
    const ctx = canvas.getContext('2d');
    
    // Create gradient background
    const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
    gradient.addColorStop(0, '#FF5E7A'); // Accent color
    gradient.addColorStop(1, '#6C63FF'); // Secondary color
    
    // Fill background
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Create particles
    const particles = [];
    const particleCount = 50;
    
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 5 + 1,
            color: 'rgba(255, 255, 255, ' + (Math.random() * 0.3 + 0.1) + ')',
            speedX: Math.random() * 2 - 1,
            speedY: Math.random() * 2 - 1
        });
    }
    
    // Animate particles
    function animateParticles() {
        // Clear canvas
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Draw and update particles
        for (let i = 0; i < particleCount; i++) {
            const p = particles[i];
            
            // Draw particle
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.fill();
            
            // Update position
            p.x += p.speedX;
            p.y += p.speedY;
            
            // Bounce off edges
            if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
            if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
        }
        
        // Request next frame
        requestAnimationFrame(animateParticles);
    }
    
    // Start animation
    animateParticles();
    
    // Resize canvas on window resize
    window.addEventListener('resize', function() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        // Update gradient
        const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
        gradient.addColorStop(0, '#FF5E7A');
        gradient.addColorStop(1, '#6C63FF');
    });
}

/**
 * Add animations to the login form
 */
function addFormAnimations() {
    // Add animation class to login container
    const loginContainer = document.querySelector('.login-container, .authentication-form-wrapper');
    if (loginContainer) {
        loginContainer.style.animation = 'fadeIn 0.8s ease-out';
        loginContainer.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
        loginContainer.style.backdropFilter = 'blur(10px)';
        loginContainer.style.borderRadius = '20px';
        loginContainer.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
        loginContainer.style.overflow = 'hidden';
    }
    
    // Add animation to form elements
    const formElements = document.querySelectorAll('input, button, .form-group, .checkbox');
    formElements.forEach(function(element, index) {
        element.style.animation = 'slideInUp 0.5s ease-out forwards';
        element.style.animationDelay = (0.1 + index * 0.1) + 's';
        element.style.opacity = '0';
    });
    
    // Add animation to logo
    const logo = document.querySelector('.login-logo, .company-logo');
    if (logo) {
        logo.style.animation = 'pulse 2s infinite';
    }
}

/**
 * Add interactive validation to form inputs
 */
function addInteractiveValidation() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="password"], input[type="email"]');
    
    inputs.forEach(function(input) {
        // Add event listener for input
        input.addEventListener('input', function() {
            validateInput(this);
        });
        
        // Add event listener for blur
        input.addEventListener('blur', function() {
            validateInput(this);
        });
    });
    
    // Validate input function
    function validateInput(input) {
        if (input.value.trim() !== '') {
            input.classList.add('is-valid');
            input.classList.remove('is-invalid');
        } else {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        }
    }
    
    // Add submit event listener
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate all inputs
            inputs.forEach(function(input) {
                if (input.value.trim() === '') {
                    input.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // If not valid, prevent form submission
            if (!isValid) {
                e.preventDefault();
                
                // Shake the form to indicate error
                form.style.animation = 'shake 0.5s';
                setTimeout(function() {
                    form.style.animation = '';
                }, 500);
            } else {
                // Add loading state to button
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                    submitButton.disabled = true;
                }
            }
        });
    }
    
    // Add shake animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Add floating labels to form inputs
 */
function addFloatingLabels() {
    const inputs = document.querySelectorAll('.form-group input');
    
    inputs.forEach(function(input) {
        // Get the label
        const label = input.previousElementSibling;
        if (label && label.tagName === 'LABEL') {
            // Add floating label class
            label.classList.add('floating-label');
            
            // Position the label
            label.style.position = 'absolute';
            label.style.top = '10px';
            label.style.left = '15px';
            label.style.pointerEvents = 'none';
            label.style.transition = 'all 0.3s ease';
            label.style.color = '#718096';
            
            // Set input padding
            input.style.paddingTop = '25px';
            
            // Check if input has value
            if (input.value !== '') {
                label.style.top = '5px';
                label.style.fontSize = '0.75rem';
                label.style.color = '#FF5E7A';
            }
            
            // Add event listeners
            input.addEventListener('focus', function() {
                label.style.top = '5px';
                label.style.fontSize = '0.75rem';
                label.style.color = '#FF5E7A';
            });
            
            input.addEventListener('blur', function() {
                if (input.value === '') {
                    label.style.top = '10px';
                    label.style.fontSize = '';
                    label.style.color = '#718096';
                }
            });
        }
    });
}