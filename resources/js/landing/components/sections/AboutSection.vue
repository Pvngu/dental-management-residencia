<template>
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-wrapper">
                <div class="about-image-container">
                    <div class="about-image-box">
                        <img :src="company.about_image || defaultImage" alt="Sobre nuestra clínica" class="main-image">
                        <div class="experience-badge" :style="{ backgroundColor: primaryColor }">
                            <span class="years">{{ company.years_experience || '15+' }}</span>
                            <span class="text">Años de Experiencia</span>
                        </div>
                    </div>
                    <div class="about-pattern" :style="{ backgroundColor: primaryColor }"></div>
                </div>
                
                <div class="about-content">
                    <span class="section-tag" :style="{ color: primaryColor }">Sobre Nosotros</span>
                    <h2 class="section-title">Brindamos <span :style="{ color: primaryColor }">el mejor cuidado</span> para tu salud dental</h2>
                    
                    <div class="about-description">
                        <p>{{ company.about_description || 'Somos una clínica dental comprometida con brindar el mejor servicio y cuidado para tu salud bucal. Contamos con profesionales altamente calificados y la más avanzada tecnología para garantizar tratamientos de calidad y resultados óptimos.' }}</p>
                        <p>Nuestro enfoque se centra en el paciente, ofreciendo una atención personalizada y cercana para que tu experiencia sea lo más cómoda y satisfactoria posible.</p>
                    </div>
                    
                    <div class="about-features">
                        <div class="feature-item">
                            <div class="feature-icon" :style="{ backgroundColor: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8.5 5.034v1.1l.953-.55.5.867L9 7l.953.55-.5.866-.953-.55v1.1h-1v-1.1l-.953.55-.5-.866L7 7l-.953-.55.5-.866.953.55v-1.1h1ZM13.25 9a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5a.25.25 0 0 0 .25-.25v-.5a.25.25 0 0 0-.25-.25h-.5ZM13 11.25a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25v-.5Zm.25 1.75a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5a.25.25 0 0 0 .25-.25v-.5a.25.25 0 0 0-.25-.25h-.5Zm-11-4a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5A.25.25 0 0 0 3 9.75v-.5A.25.25 0 0 0 2.75 9h-.5Zm0 2a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5a.25.25 0 0 0 .25-.25v-.5a.25.25 0 0 0-.25-.25h-.5ZM2 13.25a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25v-.5Z"/>
                                    <path d="M5 1a2 2 0 0 0-2 2v13h14V3a2 2 0 0 0-2-2H5Zm7 14H6v-1.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15Zm7-1.5V4H1v9.5A1.5 1.5 0 0 0 2.5 15h.5v-1.5a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5V15h6.5a1.5 1.5 0 0 0 1.5-1.5Z"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h3>Tecnología Avanzada</h3>
                                <p>Utilizamos equipos de última generación para diagnósticos precisos y tratamientos efectivos.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon" :style="{ backgroundColor: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664z"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h3>Especialistas Certificados</h3>
                                <p>Nuestro equipo está formado por profesionales con amplia experiencia y certificaciones.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon" :style="{ backgroundColor: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h3>Tratamientos Garantizados</h3>
                                <p>Ofrecemos garantía en nuestros tratamientos para tu tranquilidad y satisfacción.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-number" :style="{ color: primaryColor }">{{ company.stats?.patients || '2500+' }}</div>
                            <div class="stat-label">Pacientes Satisfechos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" :style="{ color: primaryColor }">{{ company.stats?.treatments || '4800+' }}</div>
                            <div class="stat-label">Tratamientos Exitosos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" :style="{ color: primaryColor }">{{ company.stats?.doctors || '25+' }}</div>
                            <div class="stat-label">Especialistas</div>
                        </div>
                    </div>
                    
                    <div class="about-buttons">
                        <a :href="'#contact'" class="primary-button" :style="{ backgroundColor: primaryColor }">
                            Contáctanos
                        </a>
                        <a :href="'#services'" class="secondary-button" :style="{ borderColor: primaryColor, color: primaryColor }">
                            Nuestros Servicios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'AboutSection',
    
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
    
    setup() {
        const defaultImage = '/images/landing/dental-clinic.jpg';
        
        return {
            defaultImage
        };
    }
});
</script>

<style scoped>
.about-section {
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

.about-wrapper {
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

@media (min-width: 992px) {
    .about-wrapper {
        flex-direction: row;
        align-items: center;
    }
    
    .about-image-container {
        flex: 5;
    }
    
    .about-content {
        flex: 7;
        padding-left: 3rem;
    }
}

.about-image-container {
    position: relative;
}

.about-image-box {
    position: relative;
    z-index: 2;
}

.main-image {
    width: 100%;
    height: auto;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.experience-badge {
    position: absolute;
    bottom: -25px;
    right: 20px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 3;
}

.years {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.text {
    font-size: 0.9rem;
    text-align: center;
    max-width: 100px;
}

.about-pattern {
    position: absolute;
    width: 60%;
    height: 60%;
    bottom: -30px;
    left: -30px;
    border-radius: 1rem;
    opacity: 0.1;
    z-index: 1;
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

.about-description {
    margin-bottom: 2rem;
}

.about-description p {
    font-size: 1.125rem;
    color: #4a5568;
    line-height: 1.7;
    margin-bottom: 1rem;
}

.about-features {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .about-features {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1200px) {
    .about-features {
        grid-template-columns: repeat(3, 1fr);
    }
}

.feature-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.feature-content h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1a202c;
}

.feature-content p {
    font-size: 0.95rem;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

.stats-container {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 2rem;
}

.stat-item {
    flex: 1;
    min-width: 120px;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.95rem;
    color: #4a5568;
}

.about-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.primary-button, .secondary-button {
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.primary-button {
    color: white;
}

.primary-button:hover {
    opacity: 0.9;
}

.secondary-button {
    background-color: transparent;
    border: 2px solid;
}

.secondary-button:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

@media (min-width: 768px) {
    .section-title {
        font-size: 2.75rem;
    }
}
</style>
