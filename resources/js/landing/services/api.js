import axios from 'axios';

// Base API URL
const API_URL = '/api/landing/';

// Create axios instance
const apiClient = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

export default {
    /**
     * Get company data by slug/name
     * 
     * @param {string} companySlug - The company slug or identifier
     * @return {Promise} - The company data
     */
    getCompanyBySlug(companySlug) {
        return apiClient.get(`company/${companySlug}`);
    },

    /**
     * Get services of a company
     * 
     * @param {string} companyId - The company XID
     * @return {Promise} - The company services
     */
    getCompanyServices(companyId) {
        return apiClient.get(`${companyId}/services`);
    },
    
    /**
     * Get doctors of a company
     * 
     * @param {string} companyId - The company XID
     * @return {Promise} - The company doctors
     */
    getCompanyDoctors(companyId) {
        return apiClient.get(`${companyId}/doctors`);
    },
    
    /**
     * Submit contact form
     * 
     * @param {object} formData - The form data
     * @return {Promise} - The response
     */
    submitContactForm(formData) {
        return apiClient.post('landing/contact', formData);
    },
    
    /**
     * Book an appointment
     * 
     * @param {object} appointmentData - The appointment data
     * @return {Promise} - The response
     */
    bookAppointment(appointmentData) {
        return apiClient.post('landing/appointments', appointmentData);
    }
};
