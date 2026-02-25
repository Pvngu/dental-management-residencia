<template>
    <section id="doctors" class="doctors-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" :style="{ color: primaryColor }">Expertos en Odontología</span>
                <h2 class="section-title">Nuestros <span :style="{ color: primaryColor }">Especialistas</span></h2>
                <p class="section-subtitle">Contamos con un equipo de especialistas altamente calificados para brindar el mejor cuidado dental</p>
            </div>
            
            <div class="doctors-container">
                <div v-if="doctors.length === 0" class="no-doctors">
                    <p>No hay especialistas disponibles en este momento.</p>
                </div>
                
                <div v-else class="doctor-cards">
                    <div 
                        v-for="doctor in doctors" 
                        :key="doctor.xid" 
                        class="doctor-card"
                    >
                        <div class="doctor-image-wrapper">
                            <img 
                                :src="doctor.photo_url || '/images/doctor-placeholder.png'" 
                                :alt="`Dr. ${doctor.name}`" 
                                class="doctor-image"
                            />
                            <div class="doctor-social" :style="{ backgroundColor: primaryColor }">
                                <a v-if="doctor.social?.linkedin" :href="doctor.social.linkedin" target="_blank" rel="noopener noreferrer" class="social-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/>
                                    </svg>
                                </a>
                                <a v-if="doctor.social?.twitter" :href="doctor.social.twitter" target="_blank" rel="noopener noreferrer" class="social-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/>
                                    </svg>
                                </a>
                                <a v-if="doctor.social?.instagram" :href="doctor.social.instagram" target="_blank" rel="noopener noreferrer" class="social-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="doctor-info">
                            <h3 class="doctor-name">Dr. {{ doctor.name }}</h3>
                            <p class="doctor-specialty">{{ doctor.specialty?.name || "Odontólogo General" }}</p>
                            <div class="doctor-credentials">
                                <div class="credential">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5ZM8 8.46 1.758 5.965 8 3.052l6.242 2.913L8 8.46Z"/>
                                        <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Zm-.068 1.873.22-.748 3.496 1.311a.5.5 0 0 0 .352 0l3.496-1.311.22.748L8 12.46l-3.892-1.556Z"/>
                                    </svg>
                                    <span>{{ doctor.education || "Universidad Nacional" }}</span>
                                </div>
                                <div class="credential">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
                                        <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 8.5A.5.5 0 0 1 .5 8h5.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5"/>
                                    </svg>
                                    <span>{{ doctor.experience || "10+ años de experiencia" }}</span>
                                </div>
                            </div>
                            <button 
                                @click="bookWithDoctor(doctor)"
                                class="book-button"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                <span class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                                    </svg>
                                </span>
                                Agendar Cita
                            </button>
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
    name: 'DoctorsSection',
    
    props: {
        doctors: {
            type: Array,
            required: true
        },
        primaryColor: {
            type: String,
            required: true
        }
    },
    
    setup() {
        const bookWithDoctor = (doctor) => {
            // Scroll to appointment section
            const appointmentSection = document.getElementById('appointment');
            if (appointmentSection) {
                appointmentSection.scrollIntoView({ behavior: 'smooth' });
                
                // If there's a doctor selection in the appointment form, we could set it
                const event = new CustomEvent('select-doctor', { detail: doctor });
                document.dispatchEvent(event);
            }
        };
        
        return {
            bookWithDoctor
        };
    }
});
</script>

<style scoped>
.doctors-section {
    padding: 6rem 0;
    background-color: #ffffff;
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

.no-doctors {
    text-align: center;
    padding: 3rem;
    background-color: #f7fafc;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.doctor-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 768px) {
    .doctor-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .doctor-cards {
        grid-template-columns: repeat(3, 1fr);
    }
}

.doctor-card {
    background-color: #fff;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.doctor-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.doctor-image-wrapper {
    position: relative;
    height: 280px;
    overflow: hidden;
}

.doctor-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.doctor-card:hover .doctor-image {
    transform: scale(1.05);
}

.doctor-social {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 0.75rem;
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.doctor-card:hover .doctor-social {
    transform: translateY(0);
}

.social-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background-color: rgba(255, 255, 255, 0.4);
}

.doctor-info {
    padding: 1.5rem;
}

.doctor-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: #1a202c;
}

.doctor-specialty {
    font-size: 0.95rem;
    color: #64748b;
    margin-bottom: 1rem;
}

.doctor-credentials {
    margin-bottom: 1.5rem;
}

.credential {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
}

.credential svg {
    margin-right: 0.5rem;
    color: #4a5568;
}

.book-button {
    width: 100%;
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    border: none;
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.book-button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.icon {
    display: flex;
    align-items: center;
    margin-right: 0.5rem;
}

@media (min-width: 768px) {
    .section-title {
        font-size: 2.75rem;
    }
}
</style>
