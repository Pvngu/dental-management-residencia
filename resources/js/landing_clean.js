/**
 * Landing Page Application Entry Point
 * 
 * This file serves as the entry point for the landing page application.
 * It's completely separate from the main admin application.
 */

// Import Vue and router
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

// Import components and routes
import App from './landing/layouts/App.vue';
import routes from './landing/router/index';

// Try-catch the entire module to catch any loading errors
try {
    // Add a global marker that landing.js has loaded
    window.landingJsLoaded = true;
    
    const isLandingPage = window.location.pathname.startsWith('/landing/');
    
    if (isLandingPage) {
        // Add a global log function if it doesn't exist
        if (typeof window.logLoadingState !== 'function') {
            window.logLoadingState = function(message) {
                // Landing log function for debugging if needed
            };
        }
        
        // Function to initialize the application
        async function initApp() {
            try {
                // Verify the landing app element exists before continuing
                const landingAppEl = document.getElementById('landing-app');
                if (!landingAppEl) {
                    throw new Error('#landing-app element not found in DOM when initializing Vue app');
                }
                
                // Get company slug from DOM or window object
                const landingApp = document.getElementById('landing-app');
                const companySlug = landingApp?.getAttribute('data-company-slug') || window.initialCompanySlug || '';
                
                // Create router instance with base URL
                const router = createRouter({
                    history: createWebHistory('/landing/'),
                    routes
                });
                
                // Create Vue application
                const app = createApp(App);
                
                // Add company slug as a global property
                app.config.globalProperties.$companySlug = companySlug;
                
                // Use router
                app.use(router);
                
                // Mount app to the landing app element
                app.mount('#landing-app');
                
            } catch (error) {
                console.error('Error initializing landing app:', error);
                
                // Show the fallback content
                const fallback = document.getElementById('fallback');
                if (fallback) {
                    fallback.style.display = 'block';
                }
            }
        }
        
        // Start the app
        initApp();
    }
    
} catch (error) {
    console.error('Fatal error in landing page script:', error);
    
    // Only show fallback for actual errors, not for "not on landing page" errors
    if (error.message !== 'Not on landing page') {
        // Show the fallback content
        const fallback = document.getElementById('fallback');
        if (fallback) {
            fallback.style.display = 'block';
        }
    }
}
