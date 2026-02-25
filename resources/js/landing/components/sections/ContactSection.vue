<template>
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" :style="{ color: primaryColor }">Comunícate con Nosotros</span>
                <h2 class="section-title">¿Tienes alguna <span :style="{ color: primaryColor }">pregunta</span>?</h2>
                <p class="section-subtitle">Estamos aquí para ayudarte. Contáctanos y te responderemos lo antes posible.</p>
            </div>
            
            <div class="contact-container">
                <div class="contact-info">
                    <div class="info-card">
                        <div class="info-icon" :style="{ backgroundColor: primaryColor }">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
                                <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                            </svg>
                        </div>
                        <h3>Dirección</h3>
                        <p>{{ company.address || 'Calle Principal #123, Ciudad' }}</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon" :style="{ backgroundColor: primaryColor }">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                            </svg>
                        </div>
                        <h3>Teléfono</h3>
                        <p>{{ company.phone || '+52 123 456 7890' }}</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon" :style="{ backgroundColor: primaryColor }">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                            </svg>
                        </div>
                        <h3>Email</h3>
                        <p>{{ company.email || 'contacto@clinica.com' }}</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon" :style="{ backgroundColor: primaryColor }">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
                                <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 8.5A.5.5 0 0 1 .5 8h5.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                        </div>
                        <h3>Horarios</h3>
                        <p>{{ company.settings?.schedule?.weekdays || 'Lun-Vie: 9:00 - 19:00' }}</p>
                    </div>
                </div>
                
                <div class="contact-form-wrapper">
                    <form class="contact-form" @submit.prevent="submitForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    v-model="formData.name" 
                                    placeholder="Tu nombre completo"
                                    :class="{ 'error': errors.name }"
                                    required
                                />
                                <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    v-model="formData.email" 
                                    placeholder="tu@email.com"
                                    :class="{ 'error': errors.email }"
                                    required
                                />
                                <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    v-model="formData.phone" 
                                    placeholder="Tu número telefónico"
                                    :class="{ 'error': errors.phone }"
                                />
                                <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
                            </div>
                            <div class="form-group">
                                <label for="subject">Asunto</label>
                                <input 
                                    type="text" 
                                    id="subject" 
                                    v-model="formData.subject" 
                                    placeholder="Asunto del mensaje"
                                    :class="{ 'error': errors.subject }"
                                    required
                                />
                                <span v-if="errors.subject" class="error-text">{{ errors.subject }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="message">Mensaje</label>
                            <textarea 
                                id="message" 
                                v-model="formData.message" 
                                rows="5" 
                                placeholder="Tu mensaje"
                                :class="{ 'error': errors.message }"
                                required
                            ></textarea>
                            <span v-if="errors.message" class="error-text">{{ errors.message }}</span>
                        </div>
                        <button 
                            type="submit" 
                            class="submit-button" 
                            :style="{ backgroundColor: primaryColor }"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="loading-spinner"></span>
                            <span v-else>Enviar Mensaje</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Success Modal -->
        <div class="modal" v-if="showSuccessModal">
            <div class="modal-content">
                <div class="modal-icon success" :style="{ backgroundColor: primaryColor }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                    </svg>
                </div>
                <h3>¡Mensaje Enviado!</h3>
                <p>Gracias por contactarnos. Te responderemos a la brevedad.</p>
                <button @click="closeModal" class="modal-button" :style="{ backgroundColor: primaryColor }">Cerrar</button>
            </div>
        </div>
    </section>
</template>

<script>
import { defineComponent, ref } from 'vue';

export default defineComponent({
    name: 'ContactSection',
    
    props: {
        company: {
            type: Object,
            required: true
        },
        primaryColor: {
            type: String,
            required: true
        }
    },
    
    setup(props, { emit }) {
        const formData = ref({
            name: '',
            email: '',
            phone: '',
            subject: '',
            message: ''
        });
        
        const errors = ref({});
        const loading = ref(false);
        const showSuccessModal = ref(false);
        
        const validateForm = () => {
            const newErrors = {};
            
            if (!formData.value.name) {
                newErrors.name = 'El nombre es requerido';
            }
            
            if (!formData.value.email) {
                newErrors.email = 'El email es requerido';
            } else if (!/\S+@\S+\.\S+/.test(formData.value.email)) {
                newErrors.email = 'El email no es válido';
            }
            
            if (!formData.value.subject) {
                newErrors.subject = 'El asunto es requerido';
            }
            
            if (!formData.value.message) {
                newErrors.message = 'El mensaje es requerido';
            }
            
            errors.value = newErrors;
            return Object.keys(newErrors).length === 0;
        };
        
        const submitForm = async () => {
            if (!validateForm()) {
                return;
            }
            
            loading.value = true;
            
            try {
                // Add company_id to the form data
                const data = {
                    ...formData.value,
                    company_id: props.company.xid
                };
                
                // Emit submit-contact event
                const result = await emit('submit-contact', data);
                
                if (result && result.success) {
                    // Clear form
                    formData.value = {
                        name: '',
                        email: '',
                        phone: '',
                        subject: '',
                        message: ''
                    };
                    
                    // Show success modal
                    showSuccessModal.value = true;
                } else {
                    // Handle validation errors from server
                    if (result && result.errors) {
                        errors.value = result.errors;
                    } else {
                        alert('Ocurrió un error al enviar el mensaje. Por favor, intenta nuevamente.');
                    }
                }
            } catch (error) {
                console.error('Error submitting contact form:', error);
                alert('Ocurrió un error al enviar el mensaje. Por favor, intenta nuevamente.');
            } finally {
                loading.value = false;
            }
        };
        
        const closeModal = () => {
            showSuccessModal.value = false;
        };
        
        return {
            formData,
            errors,
            loading,
            showSuccessModal,
            submitForm,
            closeModal
        };
    }
});
</script>

<style scoped>
.contact-section {
    padding: 6rem 0;
    background-color: #f8f9fa;
    position: relative;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.section-header {
    text-align: center;
    margin-bottom: 4rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.section-tag {
    display: inline-block;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 2.25rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #1a202c;
    line-height: 1.3;
}

.section-subtitle {
    font-size: 1.125rem;
    color: #64748b;
    margin: 0 auto;
    line-height: 1.7;
}

.contact-container {
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

@media (min-width: 992px) {
    .contact-container {
        flex-direction: row;
        align-items: flex-start;
    }
    
    .contact-info {
        flex: 1;
        margin-top: 0.75rem;
    }
    
    .contact-form-wrapper {
        flex: 2;
    }
}

.contact-info {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
}

@media (min-width: 640px) {
    .contact-info {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 992px) {
    .contact-info {
        grid-template-columns: repeat(1, 1fr);
    }
}

.info-card {
    background-color: #fff;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.info-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-bottom: 1rem;
}

.info-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1a202c;
}

.info-card p {
    color: #64748b;
    margin: 0;
    font-size: 1rem;
    line-height: 1.6;
}

.contact-form-wrapper {
    background-color: #fff;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.form-row {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
    .form-row {
        flex-direction: row;
    }
    
    .form-row .form-group {
        flex: 1;
    }
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #4a5568;
    font-size: 0.95rem;
}

input, textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    background-color: #f8fafc;
    font-size: 1rem;
    transition: all 0.3s;
}

input:focus, textarea:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
}

input.error, textarea.error {
    border-color: #e53e3e;
}

.error-text {
    display: block;
    color: #e53e3e;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.submit-button {
    width: 100%;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.submit-button:hover {
    opacity: 0.9;
}

.submit-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.3s;
}

.modal-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin: 0 auto 1.5rem;
}

.modal-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #1a202c;
}

.modal-content p {
    color: #64748b;
    margin-bottom: 1.5rem;
}

.modal-button {
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 0.5rem;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.3s;
}

.modal-button:hover {
    opacity: 0.9;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (min-width: 768px) {
    .section-title {
        font-size: 2.75rem;
    }
    
    .contact-form-wrapper {
        padding: 3rem;
    }
}
</style>
