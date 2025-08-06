// Beatroute Integration JavaScript

// Check if moment.js is already loaded
if (typeof moment === 'undefined') {
    console.error('Moment.js is not loaded. Loading it now...');
    
    // Create a script element to load moment.js
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js';
    script.integrity = 'sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==';
    script.crossOrigin = 'anonymous';
    script.onload = function() {
        console.log('Moment.js loaded successfully');
        // Re-initialize any functions that depend on moment.js
        initializeDateFormatting();
    };
    document.head.appendChild(script);
} else {
    console.log('Moment.js is already loaded');
    // Initialize date formatting functions
    initializeDateFormatting();
}

// Function to initialize date formatting
function initializeDateFormatting() {
    console.log('Initializing date formatting functions');
    
    // Define a global function to format dates if it doesn't exist
    if (typeof formatDateTime !== 'function') {
        window.formatDateTime = function(dateString) {
            if (!dateString) return '-';
            return moment(dateString).format('YYYY-MM-DD HH:mm:ss');
        };
    }
}

// Make sure jQuery is loaded before executing any jQuery code
$(function() {
    console.log('Beatroute integration JavaScript initialized');
});