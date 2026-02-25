import { ref, readonly, computed } from 'vue';
import apiService from '../services/api';

/**
 * Composable function to manage company state
 */
export default function useCompany() {
    // State
    const company = ref(null);
    const services = ref([]);
    const doctors = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // Computed
    const hasCompany = computed(() => company.value !== null);
    
    // Get company logo URL with default fallback
    const companyLogo = computed(() => {
        if (company.value && company.value.logo_url) {
            return company.value.logo_url;
        }
        return '/images/default-logo.png';
    });
    
    // Get company primary color with default fallback
    const primaryColor = computed(() => {
        if (company.value && company.value.settings && company.value.settings.primary_color) {
            return company.value.settings.primary_color;
        }
        return '#4299e1'; // Default blue color
    });

    /**
     * Load company data by slug
     * 
     * @param {string} slug - The company slug from URL
     */
    async function loadCompany(slug) {
        if (!slug) {
            error.value = 'No se proporcionó un identificador de empresa';
            loading.value = false; // Ensure loading is false
            return;
        }
        
        loading.value = true;
        error.value = null;
        
        // Debugging
        console.log('Load company called with slug:', slug);
        console.log('window.company available:', !!window.company);
        console.log('window.initialCompanySlug:', window.initialCompanySlug);
        
        try {
            // First, try to use company data from window if available
            if (window.company && (window.initialCompanySlug === slug || slug === 'example')) {
                console.log('Using company data from window.company');
                company.value = window.company;
            } else {
                // Otherwise fetch from API
                console.log('Fetching company from API with slug:', slug);
                const response = await apiService.getCompanyBySlug(slug);
                company.value = response.data;
            }
            
            console.log('Company data:', company.value);
            
            // For debugging purposes, log if we have a valid XID
            if (company.value && company.value.xid) {
                console.log('Valid company XID found:', company.value.xid);
                
                // Only try to load related data if we have valid company data
                try {
                    await Promise.all([
                        loadServices(company.value.id),
                        loadDoctors(company.value.id)
                    ]);
                    console.log('Related data loaded successfully');
                } catch (err) {
                    console.error('Error loading related data:', err);
                    // Continue even if related data fails to load
                }
            } else {
                console.error('Company data missing XID');
                error.value = 'Datos de empresa incompletos';
            }
        } catch (err) {
            console.error('Error loading company data:', err);
            error.value = err.response?.data?.message || 'Error cargando datos de la empresa';
        } finally {
            // Always ensure loading is set to false when done
            loading.value = false;
            console.log('Loading complete. loading state:', loading.value, 'hasCompany:', !!company.value);
        }
    }

    /**
     * Load company services
     * 
     * @param {string} companyId - The company XID
     */
    async function loadServices(companyId) {
        try {
            const response = await apiService.getCompanyServices(companyId);
            services.value = response.data;
        } catch (err) {
            console.error('Error loading services:', err);
        }
    }

    /**
     * Load company doctors
     * 
     * @param {string} companyId - The company XID
     */
    async function loadDoctors(companyId) {
        try {
            const response = await apiService.getCompanyDoctors(companyId);
            doctors.value = response.data;
        } catch (err) {
            console.error('Error loading doctors:', err);
        }
    }

    /**
     * Submit contact form
     * 
     * @param {object} formData - The contact form data
     * @return {Promise} - API response
     */
    function submitContactForm(formData) {
        const data = {
            ...formData,
            company_id: company.value ? company.value.xid : null
        };
        
        return apiService.submitContactForm(data);
    }
    
    /**
     * Book appointment
     * 
     * @param {object} appointmentData - The appointment data
     * @return {Promise} - API response
     */
    function bookAppointment(appointmentData) {
        const data = {
            ...appointmentData,
            company_id: company.value ? company.value.xid : null
        };
        
        return apiService.bookAppointment(data);
    }

    // Expose readable state and methods
    return {
        company: readonly(company),
        services: readonly(services),
        doctors: readonly(doctors),
        loading: readonly(loading),
        error: readonly(error),
        hasCompany,
        companyLogo,
        primaryColor,
        loadCompany,
        submitContactForm,
        bookAppointment
    };
}
