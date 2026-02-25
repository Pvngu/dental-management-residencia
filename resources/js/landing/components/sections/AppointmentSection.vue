<template>
    <section id="appointment" class="appointment-section">
        <div class="container">
            <div class="appointment-wrapper">
                <div class="appointment-content">
                    <span class="section-tag" :style="{ color: primaryColor }">Agenda tu Cita</span>
                    <h2 class="section-title">Programa una <span :style="{ color: primaryColor }">consulta</span> con nuestros especialistas</h2>
                    
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon" :style="{ backgroundColor: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                </svg>
                            </div>
                            <div class="benefit-text">Horarios flexibles</div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon" :style="{ backgroundColor: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                </svg>
                            </div>
                            <div class="benefit-text">Atención personalizada</div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon" :style="{ backgroundColor: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                </svg>
                            </div>
                            <div class="benefit-text">Diagnóstico completo</div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon" :style="{ backgroundColor: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                </svg>
                            </div>
                            <div class="benefit-text">Plan de tratamiento detallado</div>
                        </div>
                    </div>
                </div>
                
                <div class="appointment-form-wrapper">
                    <form @submit.prevent="submitAppointment" class="appointment-form">
                        <h3 class="form-title">Reserva tu Cita</h3>
                        
                        <div class="form-group">
                            <label for="name">Nombre Completo</label>
                            <input 
                                type="text" 
                                id="name" 
                                v-model="formData.name" 
                                placeholder="Ingresa tu nombre completo"
                                :class="{ 'error': errors.name }"
                                required
                            >
                            <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    v-model="formData.email" 
                                    placeholder="tu@email.com"
                                    :class="{ 'error': errors.email }"
                                    required
                                >
                                <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    v-model="formData.phone" 
                                    placeholder="Tu número de teléfono"
                                    :class="{ 'error': errors.phone }"
                                    required
                                >
                                <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date">Fecha Preferida</label>
                                <input 
                                    type="date" 
                                    id="date" 
                                    v-model="formData.date"
                                    :min="minDate"
                                    :class="{ 'error': errors.date }"
                                    required
                                >
                                <span v-if="errors.date" class="error-text">{{ errors.date }}</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="time">Hora Preferida</label>
                                <select 
                                    id="time" 
                                    v-model="formData.time"
                                    :class="{ 'error': errors.time }"
                                    required
                                >
                                    <option value="" disabled selected>Selecciona una hora</option>
                                    <option v-for="time in availableTimes" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span v-if="errors.time" class="error-text">{{ errors.time }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="service">Servicio</label>
                            <select 
                                id="service" 
                                v-model="formData.service_id"
                                :class="{ 'error': errors.service_id }"
                                required
                            >
                                <option value="" disabled selected>Selecciona un servicio</option>
                                <option v-for="service in company.services" :key="service.xid" :value="service.xid">
                                    {{ service.name }}
                                </option>
                            </select>
                            <span v-if="errors.service_id" class="error-text">{{ errors.service_id }}</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="doctor">Doctor Preferido (Opcional)</label>
                            <select 
                                id="doctor" 
                                v-model="formData.doctor_id"
                                :class="{ 'error': errors.doctor_id }"
                            >
                                <option value="" selected>Cualquier doctor disponible</option>
                                <option v-for="doctor in company.doctors" :key="doctor.xid" :value="doctor.xid">
                                    Dr. {{ doctor.name }}
                                </option>
                            </select>
                            <span v-if="errors.doctor_id" class="error-text">{{ errors.doctor_id }}</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Mensaje / Síntomas (Opcional)</label>
                            <textarea 
                                id="message" 
                                v-model="formData.message" 
                                rows="3" 
                                placeholder="Describe brevemente tus síntomas o preocupaciones"
                                :class="{ 'error': errors.message }"
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
                            <span v-else>Agendar Cita</span>
                        </button>
                        
                        <p class="form-note">
                            Te enviaremos un correo de confirmación una vez que tu cita haya sido agendada.
                        </p>
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
                <h3>¡Cita Agendada!</h3>
                <p>Hemos recibido tu solicitud de cita. Te enviaremos un correo de confirmación pronto.</p>
                <button @click="closeModal" class="modal-button" :style="{ backgroundColor: primaryColor }">Cerrar</button>
            </div>
        </div>
    </section>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';

export default defineComponent({
    name: 'AppointmentSection',
    
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
            date: '',
            time: '',
            service_id: '',
            doctor_id: '',
            message: ''
        });
        
        const errors = ref({});
        const loading = ref(false);
        const showSuccessModal = ref(false);
        
        // Generate available times from 9 AM to 6 PM
        const availableTimes = [
            '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
            '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
            '18:00'
        ];
        
        // Set minimum date to tomorrow
        const minDate = computed(() => {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            return tomorrow.toISOString().split('T')[0];
        });
        
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
            
            if (!formData.value.phone) {
                newErrors.phone = 'El teléfono es requerido';
            }
            
            if (!formData.value.date) {
                newErrors.date = 'La fecha es requerida';
            }
            
            if (!formData.value.time) {
                newErrors.time = 'La hora es requerida';
            }
            
            if (!formData.value.service_id) {
                newErrors.service_id = 'El servicio es requerido';
            }
            
            errors.value = newErrors;
            return Object.keys(newErrors).length === 0;
        };
        
        const submitAppointment = async () => {
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
                
                // Emit submit-appointment event
                const result = await emit('submit-appointment', data);
                
                if (result && result.success) {
                    // Clear form
                    formData.value = {
                        name: '',
                        email: '',
                        phone: '',
                        date: '',
                        time: '',
                        service_id: '',
                        doctor_id: '',
                        message: ''
                    };
                    
                    // Show success modal
                    showSuccessModal.value = true;
                } else {
                    // Handle validation errors from server
                    if (result && result.errors) {
                        errors.value = result.errors;
                    } else {
                        alert('Ocurrió un error al agendar la cita. Por favor, intenta nuevamente.');
                    }
                }
            } catch (error) {
                console.error('Error submitting appointment form:', error);
                alert('Ocurrió un error al agendar la cita. Por favor, intenta nuevamente.');
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
            availableTimes,
            minDate,
            showSuccessModal,
            submitAppointment,
            closeModal
        };
    }
});
</script>

<style scoped>
.appointment-section {
    padding: 6rem 0;
    background-color: #ffffff;
    position: relative;
    overflow: hidden;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.appointment-wrapper {
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

@media (min-width: 992px) {
    .appointment-wrapper {
        flex-direction: row;
        align-items: center;
    }
    
    .appointment-content {
        flex: 5;
        padding-right: 3rem;
    }
    
    .appointment-form-wrapper {
        flex: 6;
    }
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
    margin-bottom: 2rem;
    color: #1a202c;
    line-height: 1.3;
}

.benefits-list {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .benefits-list {
        grid-template-columns: repeat(2, 1fr);
    }
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.benefit-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.benefit-text {
    font-size: 1.125rem;
    font-weight: 500;
    color: #4a5568;
}

.appointment-form-wrapper {
    background-color: #f8f9fa;
    border-radius: 1rem;
    padding: 2.5rem;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
}

.form-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 1.5rem;
    text-align: center;
}

.form-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
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
    margin-bottom: 1.25rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #4a5568;
    font-size: 0.95rem;
}

input, select, textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    background-color: white;
    font-size: 1rem;
    transition: all 0.3s;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
}

input[type="date"] {
    padding: 0.7rem 1rem;
}

select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234a5568' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: calc(100% - 1rem) center;
    padding-right: 2.5rem;
}

input.error, select.error, textarea.error {
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
    margin-top: 0.5rem;
    margin-bottom: 1rem;
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

.form-note {
    font-size: 0.875rem;
    color: #718096;
    text-align: center;
    margin: 0;
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
    
    .appointment-form-wrapper {
        padding: 3rem;
    }
}
</style>
