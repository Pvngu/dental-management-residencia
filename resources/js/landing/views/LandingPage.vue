<template>
    <div class="landing-page">
        <LoadingOverlay v-if="loading" />
        
        <ErrorMessage v-if="error && !loading" :message="error" />
        
        <template v-if="hasCompany && !loading && !error">
            <LandingHeader :company="company" :logo="companyLogo" />
            
            <main>
                <HeroSection :company="company" :primaryColor="primaryColor" />
                
                <ServicesSection :services="services" :primaryColor="primaryColor" />
                
                <AboutSection :company="company" :primaryColor="primaryColor" />
                
                <DoctorsSection :doctors="doctors" :primaryColor="primaryColor" />
                
                <TestimonialsSection :company="company" :primaryColor="primaryColor" />
                
                <AppointmentSection 
                    :company="company"
                    :primaryColor="primaryColor"
                    @submit-appointment="handleAppointmentBook"
                />
                
                <ContactSection 
                    :company="company" 
                    :primaryColor="primaryColor"
                    @submit-contact="handleContactSubmit"
                />
            </main>
            
            <LandingFooter :company="company" />
        </template>
    </div>
</template>

<script>
import { defineComponent, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import useCompany from '../composables/useCompany';

// Components
import LoadingOverlay from '../components/LoadingOverlay.vue';
import ErrorMessage from '../components/ErrorMessage.vue';
import LandingHeader from '../components/LandingHeader.vue';
import LandingFooter from '../components/LandingFooter.vue';

// Sections
import HeroSection from '../components/sections/HeroSection.vue';
import ServicesSection from '../components/sections/ServicesSection.vue';
import DoctorsSection from '../components/sections/DoctorsSection.vue';
import AboutSection from '../components/sections/AboutSection.vue';
import TestimonialsSection from '../components/sections/TestimonialsSection.vue';
import ContactSection from '../components/sections/ContactSection.vue';
import AppointmentSection from '../components/sections/AppointmentSection.vue';

export default defineComponent({
    name: 'LandingPage',
    
    components: {
        LoadingOverlay,
        ErrorMessage,
        LandingHeader,
        LandingFooter,
        HeroSection,
        ServicesSection,
        DoctorsSection,
        AboutSection,
        TestimonialsSection,
        ContactSection,
        AppointmentSection
    },
    
    props: {
        companySlug: {
            type: String,
            required: false,
            default: ''
        },
        pathMatch: {
            type: String,
            required: false,
            default: ''
        }
    },
    
    setup(props, { expose }) {
        // Get route and company composable
        const route = useRoute();
        const { 
            company,
            services, 
            doctors, 
            loading, 
            error,
            hasCompany,
            companyLogo,
            primaryColor,
            loadCompany,
            submitContactForm,
            bookAppointment
        } = useCompany();
        
        // Methods
        const loadCompanyData = () => {
            // Get company slug with fallbacks in order of priority
            let slug = null;
            
            // Log all possible sources for better debugging
            const sourceInfo = {
                'props.companySlug': props.companySlug,
                'props.pathMatch': props.pathMatch,
                'route.params.pathMatch': route.params.pathMatch,
                'window.initialCompanySlug': window.initialCompanySlug,
                'data-attribute': document.getElementById('landing-app')?.getAttribute('data-company-slug'),
                'route.path': route.path,
                'route.fullPath': route.fullPath
            };
            
            console.log('Available slug sources:', sourceInfo);
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Slug sources: ' + JSON.stringify(sourceInfo));
            }
            
            // Determine the slug to use (in order of priority)
            // 1. Check route params (for direct URL navigation)
            if (route.params.pathMatch) {
                if (typeof route.params.pathMatch === 'string') {
                    slug = route.params.pathMatch;
                } else if (Array.isArray(route.params.pathMatch)) {
                    slug = route.params.pathMatch[0];
                }
                console.log('Using company slug from route params:', slug);
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('Using slug from route params: ' + slug);
                }
            }
            // 2. Check if we have an explicit prop value
            else if (props.companySlug) {
                slug = props.companySlug;
                console.log('Using company slug from props:', slug);
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('Using slug from props: ' + slug);
                }
            }
            // 3. Check if we have server-side data
            else if (window.initialCompanySlug) {
                slug = window.initialCompanySlug;
                console.log('Using company slug from window.initialCompanySlug:', slug);
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('Using slug from window: ' + slug);
                }
            }
            // 4. Check DOM attributes as last resort
            else if (document.getElementById('landing-app')?.getAttribute('data-company-slug')) {
                slug = document.getElementById('landing-app').getAttribute('data-company-slug');
                console.log('Using company slug from DOM data attribute:', slug);
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('Using slug from DOM: ' + slug);
                }
            }
            
            if (slug) {
                console.log('Final company slug to use:', slug);
                loadCompany(slug);
            } else {
                console.error('No company slug available from any source');
                error.value = 'No se pudo determinar la compañía a mostrar';
            }
        };
        
        const handleContactSubmit = async (formData) => {
            try {
                const response = await submitContactForm(formData);
                return {
                    success: true,
                    message: 'Mensaje enviado correctamente',
                    data: response.data
                };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Error al enviar el mensaje',
                    errors: error.response?.data?.errors || {}
                };
            }
        };
        
        const handleAppointmentBook = async (appointmentData) => {
            try {
                const response = await bookAppointment(appointmentData);
                return {
                    success: true,
                    message: 'Cita agendada correctamente',
                    data: response.data
                };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Error al agendar la cita',
                    errors: error.response?.data?.errors || {}
                };
            }
        };
        
        // Load company data on mount
        onMounted(() => {
            console.log('LandingPage mounted, companySlug:', props.companySlug);
            loadCompanyData();
        });
        
        // Watch for route changes to reload company data
        watch(() => props.companySlug, (newSlug, oldSlug) => {
            console.log('companySlug changed:', oldSlug, '->', newSlug);
            if (newSlug && newSlug !== oldSlug) {
                loadCompanyData();
            }
        });
        
        return {
            company,
            services,
            doctors,
            loading,
            error,
            hasCompany,
            companyLogo,
            primaryColor,
            handleContactSubmit,
            handleAppointmentBook
        };
    }
});
</script>

<style scoped>
.landing-page {
    min-height: 100vh;
}

main {
    padding-top: 60px;
}
</style>
