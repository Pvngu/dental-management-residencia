<template>
    <footer class="landing-footer" :style="{ backgroundColor: company.settings?.footer_color || '#1a202c' }">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img :src="logoUrl" :alt="company.name + ' logo'" />
                    <p class="tagline">{{ company.settings?.tagline || 'Cuidando de tu sonrisa' }}</p>
                </div>
                
                <div class="footer-info">
                    <div class="navigation">
                        <h3>Enlaces Rápidos</h3>
                        <ul class="footer-nav">
                            <li><a href="#hero" @click.prevent="scrollToSection('hero')">Inicio</a></li>
                            <li><a href="#services" @click.prevent="scrollToSection('services')">Servicios</a></li>
                            <li v-if="hasDoctors"><a href="#doctors" @click.prevent="scrollToSection('doctors')">Doctores</a></li>
                            <li><a href="#about" @click.prevent="scrollToSection('about')">Nosotros</a></li>
                            <li v-if="hasTestimonials"><a href="#testimonials" @click.prevent="scrollToSection('testimonials')">Testimonios</a></li>
                            <li><a href="#appointment" @click.prevent="scrollToSection('appointment')">Agendar Cita</a></li>
                            <li><a href="#contact" @click.prevent="scrollToSection('contact')">Contacto</a></li>
                        </ul>
                    </div>
                    
                    <div class="contact-info">
                        <h3>Contacto</h3>
                        <p v-if="company.address"><strong>Dirección:</strong> {{ company.address }}</p>
                        <p v-if="company.phone"><strong>Teléfono:</strong> {{ company.phone }}</p>
                        <p v-if="company.email"><strong>Email:</strong> {{ company.email }}</p>
                    </div>
                    
                    <div class="hours">
                        <h3>Horario</h3>
                        <p v-if="company.settings?.schedule?.weekdays">
                            <strong>Lunes-Viernes:</strong> {{ company.settings.schedule.weekdays }}
                        </p>
                        <p v-if="company.settings?.schedule?.saturday">
                            <strong>Sábado:</strong> {{ company.settings.schedule.saturday }}
                        </p>
                        <p v-if="company.settings?.schedule?.sunday">
                            <strong>Domingo:</strong> {{ company.settings.schedule.sunday }}
                        </p>
                    </div>
                    
                    <div class="social-links">
                        <h3>Síguenos</h3>
                        <div class="social-icons">
                            <a v-if="company.settings?.social?.facebook" :href="company.settings.social.facebook" target="_blank" rel="noopener noreferrer" class="social-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/>
                                </svg>
                            </a>
                            <a v-if="company.settings?.social?.instagram" :href="company.settings.social.instagram" target="_blank" rel="noopener noreferrer" class="social-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/>
                                </svg>
                            </a>
                            <a v-if="company.settings?.social?.twitter" :href="company.settings.social.twitter" target="_blank" rel="noopener noreferrer" class="social-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/>
                                </svg>
                            </a>
                            <a v-if="company.settings?.social?.linkedin" :href="company.settings.social.linkedin" target="_blank" rel="noopener noreferrer" class="social-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="copyright">&copy; {{ currentYear }} {{ company.name }}. Todos los derechos reservados.</p>
                <p class="powered-by">Powered by <a href="https://800dent.com" target="_blank" rel="noopener noreferrer">800dent</a></p>
            </div>
        </div>
    </footer>
</template>

<script>
import { defineComponent, computed } from 'vue';

export default defineComponent({
    name: 'LandingFooter',
    
    props: {
        company: {
            type: Object,
            required: true
        }
    },
    
    setup(props) {
        const currentYear = computed(() => {
            return new Date().getFullYear();
        });
        
        const logoUrl = computed(() => {
            // Use white logo for footer if available, otherwise use standard logo
            return props.company.white_logo_url || props.company.logo_url || '/images/default-logo-white.png';
        });
        
        // Computed properties for conditional sections
        const hasDoctors = computed(() => {
            return props.company.settings?.show_doctors !== false;
        });
        
        const hasTestimonials = computed(() => {
            return props.company.settings?.show_testimonials !== false;
        });
        
        // Method for smooth scrolling
        const scrollToSection = (sectionId) => {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        };
        
        return {
            currentYear,
            logoUrl,
            hasDoctors,
            hasTestimonials,
            scrollToSection
        };
    }
});
</script>

<style scoped>
.landing-footer {
    padding: 3rem 0 1.5rem;
    color: #e2e8f0;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.footer-content {
    display: flex;
    flex-direction: column;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .footer-content {
        flex-direction: row;
    }
}

.footer-logo {
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .footer-logo {
        width: 30%;
        margin-bottom: 0;
    }
    
    .footer-info {
        width: 70%;
        display: flex;
        flex-wrap: wrap;
    }
    
    .navigation,
    .contact-info,
    .hours,
    .social-links {
        width: calc(25% - 1rem);
        margin-right: 1rem;
    }
}

.footer-logo img {
    height: 40px;
    max-width: 150px;
    object-fit: contain;
    margin-bottom: 1rem;
}

.tagline {
    font-size: 0.875rem;
    opacity: 0.8;
    margin-bottom: 1rem;
}

.navigation,
.contact-info,
.hours,
.social-links {
    margin-bottom: 1.5rem;
}

.footer-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-nav li {
    margin-bottom: 0.75rem;
}

.footer-nav a {
    color: #e2e8f0;
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.3s;
}

.footer-nav a:hover {
    color: v-bind('company.settings?.primary_color || "#4299e1"');
}

h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #f7fafc;
}

p {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.social-icons {
    display: flex;
    gap: 1rem;
}

.social-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: #f7fafc;
    transition: background-color 0.3s;
}

.social-icon:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.social-icon svg {
    width: 16px;
    height: 16px;
    fill: currentColor;
}

.footer-bottom {
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    text-align: center;
}

@media (min-width: 768px) {
    .footer-bottom {
        flex-direction: row;
        justify-content: space-between;
        text-align: left;
    }
}

.copyright,
.powered-by {
    font-size: 0.75rem;
    opacity: 0.7;
}

.powered-by a {
    color: inherit;
    text-decoration: none;
    font-weight: 600;
}

.powered-by a:hover {
    text-decoration: underline;
}
</style>
