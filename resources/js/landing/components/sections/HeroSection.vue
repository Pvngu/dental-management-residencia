<template>
    <section id="hero" class="hero-section">
        <div class="hero-background" :style="heroStyle"></div>
        <div class="container">
            <div class="hero-layout">
                <div class="hero-content">
                    <span class="hero-subtitle" :style="{ color: primaryColor }">Cuidado dental profesional</span>
                    <h1>{{ heroTitle }}</h1>
                    <p>{{ heroSubtitle }}</p>
                    <div class="cta-buttons">
                        <button 
                            @click="scrollToSection('appointment')" 
                            class="primary-button"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                                </svg>
                            </span>
                            Agenda una cita
                        </button>
                        <button 
                            @click="scrollToSection('services')" 
                            class="secondary-button"
                            :style="{ borderColor: primaryColor, color: primaryColor }"
                        >
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                                    <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52z"/>
                                </svg>
                            </span>
                            Nuestros servicios
                        </button>
                    </div>
                </div>
                <div class="hero-image">
                    <img :src="company.settings?.hero?.secondary_image || '/images/doctor-hero.png'" alt="Doctor sonriente">
                </div>
            </div>
        </div>

        <div class="hero-stats" :style="{ backgroundColor: primaryColor }">
            <div class="container">
                <div class="stats-container">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h4>{{ company.settings?.stats?.patients || '5000+' }}</h4>
                            <p>Pacientes Atendidos</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
                                <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 8.5A.5.5 0 0 1 .5 8h5.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h4>{{ company.settings?.stats?.years || '15+' }}</h4>
                            <p>Años de Experiencia</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zM14 2a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zm-1 3v-.5a.5.5 0 0 0-1 0V5h-2V3h2v.5a.5.5 0 0 1 1 0V3h1v2zm-6 5.5A1.5 1.5 0 0 1 8.5 9h-1a1.5 1.5 0 0 1 1.5 1.5m.5-3a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5M9 11a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m0 1a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5M1.5 10.5A.5.5 0 0 1 2 10h1a.5.5 0 0 1 0 1H2a.5.5 0 0 1-.5-.5m0 1a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H2a.5.5 0 0 1-.5-.5M3 8.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m1-2.5a1.5 1.5 0 0 0-1.5 1.5V10h1V7.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1H4z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h4>{{ company.settings?.stats?.procedures || '10,000+' }}</h4>
                            <p>Procedimientos Exitosos</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h4>{{ company.settings?.stats?.locations || '3' }}</h4>
                            <p>Clínicas a su servicio</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { defineComponent, computed } from 'vue';

export default defineComponent({
    name: 'HeroSection',
    
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
    
    setup(props) {
        // Computed properties for hero content
        const heroTitle = computed(() => {
            return props.company.settings?.hero?.title || `Bienvenido a ${props.company.name}`;
        });
        
        const heroSubtitle = computed(() => {
            return props.company.settings?.hero?.subtitle || 'Especialistas en cuidado dental de alta calidad. Nuestra misión es proporcionar el mejor tratamiento para tu sonrisa.';
        });
        
        const heroStyle = computed(() => {
            const heroImage = props.company.settings?.hero?.image_url || '/images/default-hero-bg.jpg';
            
            return {
                backgroundImage: `url(${heroImage})`,
            };
        });
        
        // Methods
        const scrollToSection = (sectionId) => {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        };
        
        return {
            heroTitle,
            heroSubtitle,
            heroStyle,
            scrollToSection,
            company
        };
    }
});
</script>

<style scoped>
.hero-section {
    position: relative;
    overflow: hidden;
    padding: 0;
    margin-top: 70px;
    color: #1a202c;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    opacity: 0.08;
    z-index: -1;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.hero-layout {
    display: flex;
    flex-direction: column;
    padding: 5rem 0;
}

@media (min-width: 992px) {
    .hero-layout {
        flex-direction: row;
        align-items: center;
        padding: 6rem 0 3rem;
    }
}

.hero-content {
    flex: 1;
    padding-right: 1rem;
    text-align: center;
}

@media (min-width: 992px) {
    .hero-content {
        text-align: left;
        padding-right: 3rem;
    }
}

.hero-image {
    flex: 1;
    display: none;
    justify-content: flex-end;
    align-items: flex-end;
}

@media (min-width: 992px) {
    .hero-image {
        display: flex;
    }
}

.hero-image img {
    max-width: 100%;
    height: auto;
    max-height: 450px;
    object-fit: contain;
    border-radius: 0.25rem;
}

.hero-subtitle {
    display: inline-block;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1rem;
}

h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

p {
    font-size: 1.125rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    line-height: 1.6;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

@media (min-width: 992px) {
    .cta-buttons {
        justify-content: flex-start;
    }
}

.primary-button, 
.secondary-button {
    padding: 0.875rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.primary-button {
    color: white;
    border: none;
}

.primary-button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.secondary-button {
    background-color: transparent;
    border: 2px solid;
}

.secondary-button:hover {
    opacity: 0.8;
    transform: translateY(-2px);
}

.icon {
    display: flex;
    align-items: center;
    margin-right: 0.5rem;
}

/* Stats Section */
.hero-stats {
    padding: 2rem 0;
    color: white;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
}

@media (min-width: 640px) {
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 992px) {
    .stats-container {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
}

.stat-icon {
    margin-right: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
}

.stat-content h4 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.stat-content p {
    font-size: 0.875rem;
    margin: 0.25rem 0 0;
    opacity: 0.9;
}
</style>
