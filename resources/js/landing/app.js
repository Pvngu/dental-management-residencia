import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './layouts/App.vue';
import routes from './router';
import 'ant-design-vue/dist/reset.css'; // Incluimos los estilos de Ant Design

// Función para inicializar la aplicación
async function bootstrap() {
    try {
        // Get initial company slug from window if available
        const initialCompanySlug = window.initialCompanySlug || '';
        console.log('Initial company slug:', initialCompanySlug);
        
        // Create Vue Router instance with a base URL of '/landing/'
        const router = createRouter({
            history: createWebHistory('/landing/'),
            routes
        });
        
        // Create Vue App
        const app = createApp(App);
        
        // Set initial company slug as global property
        app.config.globalProperties.$initialCompanySlug = initialCompanySlug;
        
        // Use Router
        app.use(router);
        
        // Mount App with company slug from data attribute if available
        const landingApp = document.getElementById('landing-app');
        if (landingApp) {
            const companySlug = landingApp.getAttribute('data-company-slug') || initialCompanySlug;
            console.log('Company slug from DOM:', companySlug);
            app.mount('#landing-app');
        } else {
            console.error('Landing app element not found');
        }
    } catch (error) {
        console.error('Error during bootstrap:', error);
    }
}

// Iniciar la aplicación
bootstrap();
