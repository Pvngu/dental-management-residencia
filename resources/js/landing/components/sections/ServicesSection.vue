<template>
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" :style="{ color: primaryColor }">Nuestras Especialidades</span>
                <h2 class="section-title">Servicios Dentales <span :style="{ color: primaryColor }">Profesionales</span></h2>
                <p class="section-subtitle">Ofrecemos una amplia gama de servicios dentales con la más alta calidad para cuidar de tu sonrisa y salud bucal</p>
            </div>
            
            <div class="services-container">
                <div v-if="services.length === 0" class="no-services">
                    <p>No hay servicios disponibles en este momento.</p>
                </div>
                
                <div v-else class="service-cards">
                    <div 
                        v-for="service in services" 
                        :key="service.xid" 
                        class="service-card"
                    >
                        <div class="service-icon" :style="{ backgroundColor: primaryColor }">
                            <svg v-if="service.icon === 'implant'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2m-3 14v-1h1v1zm2-11a.5.5 0 0 1-.5.5h-5a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5m0 3a.5.5 0 0 1-.5.5h-5a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5m-.5 2.5h-5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1"/>
                            </svg>
                            <svg v-else-if="service.icon === 'extraction'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1"/>
                                <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m-5 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m5 2a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1a.5.5 0 0 1 .5-.5m-5 0a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1a.5.5 0 0 1 .5-.5M8 4a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 4m0 6a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 8 10"/>
                            </svg>
                            <svg v-else-if="service.icon === 'braces'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                                <path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.72.72 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.72.72 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/>
                            </svg>
                            <svg v-else-if="service.icon === 'whitening'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2m0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8"/>
                            </svg>
                            <svg v-else-if="service.icon === 'cleaning'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2 1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h9.586a2 2 0 0 1 1.414.586l2 2V2a1 1 0 0 0-1-1zm12-1a2 2 0 0 1 2 2v12.793a.5.5 0 0 1-.854.353l-2.853-2.853a1 1 0 0 0-.707-.293H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z"/>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M7.998 11.415c-1.863 1.822-3.152 2.76-3.153 4.128-.001 1.092.643 1.953 1.929 1.953 1.334 0 1.953-.908 1.953-2.06 0-.071.018-.196.018-.196s.012.134.018.195c.001 1.143.625 2.06 1.952 2.06 1.286 0 1.931-.86 1.931-1.952 0-1.368-1.29-2.305-3.151-4.127L8 10.948zM7.375 1.723C6.316 1.723 5.93 2.65 5.93 3.301c0 .513.306 1.18.557 1.912.155.454.195 1.12.168 1.83-.146 3.742-2.345 3.754-2.345 6.682 0 .044 0 .087.002.13a4.726 4.726 0 0 0 .345 1.654l.636-.805c.316-.534.56-1.055.56-1.914 0-1.165.845-2.319 1.222-3.039.374-.72.624-1.609.624-2.174 0-1.704-.108-2.567 1.17-2.567 1.088 0 1.703.812 1.703 2.313 0 1.297-.495 2.979-.727 3.443-.432.859-.764 1.406-.764 2.128 0 .859.244 1.379.56 1.914l.636.805a4.726 4.726 0 0 0 .345-1.655c.003-.042.003-.085.003-.129 0-2.927-2.199-2.939-2.345-6.681-.027-.712.013-1.376.168-1.83.252-.733.558-1.4.558-1.912 0-.648-.387-1.578-1.444-1.578z"/>
                            </svg>
                        </div>
                        <div class="service-content">
                            <h3 class="service-title">{{ service.name }}</h3>
                            <p class="service-description">{{ service.description }}</p>
                            <a 
                                href="#appointment" 
                                class="service-link" 
                                @click.prevent="bookService(service)"
                            >
                                <span>Agendar cita</span>
                                <span class="arrow-icon" :style="{ backgroundColor: primaryColor }">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'ServicesSection',
    
    props: {
        services: {
            type: Array,
            required: true
        },
        primaryColor: {
            type: String,
            required: true
        }
    },
    
    setup() {
        const bookService = (service) => {
            // Scroll to appointment section
            const appointmentSection = document.getElementById('appointment');
            if (appointmentSection) {
                appointmentSection.scrollIntoView({ behavior: 'smooth' });
                
                // If there's a service selection in the appointment form, we could set it
                // This would require emitting an event or using a global state
                const event = new CustomEvent('select-service', { detail: service });
                document.dispatchEvent(event);
            }
        };
        
        return {
            bookService
        };
    }
});
</script>

<style scoped>
.services-section {
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

.no-services {
    text-align: center;
    padding: 3rem;
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.service-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 768px) {
    .service-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .service-cards {
        grid-template-columns: repeat(3, 1fr);
    }
}

.service-card {
    background-color: #fff;
    border-radius: 1rem;
    padding: 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.service-icon {
    color: white;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.service-icon svg {
    width: 40px;
    height: 40px;
}

.service-content {
    padding: 2rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.service-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #1a202c;
}

.service-description {
    font-size: 0.95rem;
    color: #64748b;
    margin-bottom: 2rem;
    line-height: 1.7;
    flex-grow: 1;
}

.service-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    color: #1a202c;
    transition: all 0.3s ease;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    margin-top: auto;
}

.service-link:hover {
    color: inherit;
}

.arrow-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
}

.service-link:hover .arrow-icon {
    transform: translateX(5px);
}

@media (min-width: 768px) {
    .section-title {
        font-size: 2.75rem;
    }
}
</style>
