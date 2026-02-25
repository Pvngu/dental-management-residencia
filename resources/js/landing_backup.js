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
    
    // Function to check if #landing-app element exists and log its attributes
    function checkLandingAppElement() {
        const landingAppEl = document.getElementById('landing-app');
        if (landingAppEl) {
            window.logLoadingState(`#landing-app element found with attributes: ${JSON.stringify({
                'data-company-slug': landingAppEl.getAttribute('data-company-slug'),
                'children': landingAppEl.childElementCount
            })}`);
        } else {
            window.logLoadingState('#landing-app element NOT found in DOM');
        }
    }
    
    // Check immediately
    checkLandingAppElement();
    
    // And check again after DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        window.logLoadingState('DOMContentLoaded event fired');
        checkLandingAppElement();
    });
    
    // Function to initialize the application
    async function initApp() {
        try {
            window.logLoadingState('Initializing landing application');
            
            // Verify the landing app element exists before continuing
            const landingAppEl = document.getElementById('landing-app');
            if (!landingAppEl) {
                throw new Error('#landing-app element not found in DOM when initializing Vue app');
            }
            
            // Get company slug from DOM or window object
            const landingApp = document.getElementById('landing-app');
            const companySlug = landingApp?.getAttribute('data-company-slug') || window.initialCompanySlug || '';
            
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Using company slug: ' + companySlug);
                window.logLoadingState('Current path: ' + window.location.pathname);
            }
            
            // Log the imported routes for debugging
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Routes config: ' + JSON.stringify(routes.map(r => ({ 
                    path: r.path, 
                    name: r.name 
                }))));
            }
            
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Creating Vue router');
            }
            
            // Create router instance with base URL
            const router = createRouter({
                history: createWebHistory('/landing/'),
                routes
            });
            
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Creating Vue app');
            }
            
            // Create Vue application
            const app = createApp(App);
            
            // Add company slug as a global property
            app.config.globalProperties.$companySlug = companySlug;
            
            // Error handling
            app.config.errorHandler = (err, vm, info) => {
                console.error('Vue Error:', err);
                console.error('Error Info:', info);
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('Vue Error: ' + err.message);
                }
            };
            
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Using router');
            }
            
            // Use router
            app.use(router);
            
            window.logLoadingState('Mounting app to #landing-app');
            
            // Double-check element exists before mounting
            const mountElement = document.getElementById('landing-app');
            if (!mountElement) {
                throw new Error('#landing-app element disappeared before mounting Vue app');
            }
            
            // Mount application
            const mountedApp = app.mount('#landing-app');
            
            // Store a reference to the app globally for debugging
            window.vueApp = app;
            
            window.logLoadingState('App mounted successfully');
            
            // Check if the app actually rendered anything
            setTimeout(() => {
                const appElement = document.getElementById('landing-app');
                if (appElement && appElement.childElementCount > 0) {
                    window.logLoadingState('Confirmed: App has rendered content in #landing-app');
                } else {
                    window.logLoadingState('WARNING: App mounted but no content rendered in #landing-app');
                }
            }, 500);
        } catch (error) {
            console.error('Error initializing app:', error);
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Init Error: ' + error.message);
            }
            
            // Show the fallback content
            const fallback = document.getElementById('fallback');
            if (fallback) {
                fallback.style.display = 'block';
            }
        }
    }
    
    // Start the app
    initApp();
    
} catch (error) {
    console.error('Fatal error in landing page script:', error);
    if (typeof window.logLoadingState === 'function') {
        window.logLoadingState('Fatal Error: ' + error.message);
    }
    
    // Only show fallback for actual errors, not for "not on landing page" errors
    if (error.message !== 'Not on landing page') {
        // Show the fallback content
        const fallback = document.getElementById('fallback');
        if (fallback) {
            fallback.style.display = 'block';
        }
    }
}

// Detect conflicts with main app
window.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.startsWith('/landing/')) {
        setTimeout(() => {
            // Check if main app has been initialized
            if (window.i18n) {
                console.error('[Landing App] CONFLICT DETECTED: Main app (app.js) has been loaded on landing page!');
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('CONFLICT: Main app detected on landing page');
                }
            }
            
            // Check if Vue apps are mounted to wrong elements
            const appEl = document.getElementById('app');
            const landingAppEl = document.getElementById('landing-app');
            
            if (appEl && appEl.childElementCount > 0) {
                console.error('[Landing App] PROBLEM: Main app mounted to #app element on landing page');
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('PROBLEM: Main app mounted to #app on landing page');
                }
            }
            
            if (landingAppEl && landingAppEl.childElementCount === 0) {
                console.error('[Landing App] PROBLEM: Landing app not mounted to #landing-app');
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('PROBLEM: Landing app not mounted');
                }
            }
        }, 1000);
    }
});
