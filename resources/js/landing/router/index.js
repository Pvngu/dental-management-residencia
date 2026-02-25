import LandingPage from '../views/LandingPage.vue';
import NotFound from '../views/NotFound.vue';

/**
 * Define routes for the landing page application
 * Note: These routes should be consistent with those in landing.js
 */
const routes = [
    // Root route for the landing page
    {
        path: '/',
        component: LandingPage,
        name: 'landing-home',
        props: (route) => {
            // Get company slug from window or DOM
            const companySlug = window.initialCompanySlug || 
                document.getElementById('landing-app')?.getAttribute('data-company-slug') || '';
            return { companySlug };
        }
    },
    // Dynamic route that captures any path parameter as pathMatch
    {
        path: '/:pathMatch(.*)*',
        component: LandingPage,
        name: 'dynamic-landing',
        props: (route) => {
            // Use either the path parameter or window/DOM as fallback
            const pathSlug = route.params.pathMatch || '';
            const windowSlug = window.initialCompanySlug || 
                document.getElementById('landing-app')?.getAttribute('data-company-slug') || '';
            
            return { 
                companySlug: pathSlug || windowSlug,
                pathMatch: route.params.pathMatch
            };
        }
    },
    // 404 route
    {
        path: '/404',
        component: NotFound,
        name: 'not-found'
    }
];

export default routes;
