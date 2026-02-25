<template>
    <header class="landing-header" :style="{ backgroundColor: company.settings?.header_color || '#ffffff' }">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img :src="logo" :alt="company.name + ' logo'" />
                </div>
                
                <nav class="desktop-nav">
                    <ul>
                        <li><a href="#hero" @click.prevent="scrollToSection('hero')">Inicio</a></li>
                        <li><a href="#services" @click.prevent="scrollToSection('services')">Servicios</a></li>
                        <li v-if="hasDoctors"><a href="#doctors" @click.prevent="scrollToSection('doctors')">Doctores</a></li>
                        <li><a href="#about" @click.prevent="scrollToSection('about')">Nosotros</a></li>
                        <li v-if="hasTestimonials"><a href="#testimonials" @click.prevent="scrollToSection('testimonials')">Testimonios</a></li>
                        <li><a href="#contact" @click.prevent="scrollToSection('contact')">Contacto</a></li>
                        <li><a href="#appointment" class="appointment-link" @click.prevent="scrollToSection('appointment')">Cita</a></li>
                    </ul>
                </nav>
                
                <div class="mobile-menu">
                    <button @click="toggleMobileMenu" class="menu-button">
                        <span v-if="!isMobileMenuOpen">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                        </span>
                        <span v-else>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mobile-nav" :class="{ 'is-open': isMobileMenuOpen }">
            <ul>
                <li><a href="#hero" @click="scrollToSectionMobile('hero')">Inicio</a></li>
                <li><a href="#services" @click="scrollToSectionMobile('services')">Servicios</a></li>
                <li v-if="hasDoctors"><a href="#doctors" @click="scrollToSectionMobile('doctors')">Doctores</a></li>
                <li><a href="#about" @click="scrollToSectionMobile('about')">Nosotros</a></li>
                <li v-if="hasTestimonials"><a href="#testimonials" @click="scrollToSectionMobile('testimonials')">Testimonios</a></li>
                <li><a href="#contact" @click="scrollToSectionMobile('contact')">Contacto</a></li>
                <li><a href="#appointment" class="appointment-link" @click="scrollToSectionMobile('appointment')">Cita</a></li>
            </ul>
        </div>
    </header>
</template>

<script>
import { defineComponent, ref, computed, onMounted, onBeforeUnmount } from 'vue';

export default defineComponent({
    name: 'LandingHeader',
    
    props: {
        company: {
            type: Object,
            required: true
        },
        logo: {
            type: String,
            required: true
        }
    },
    
    setup(props) {
        const isMobileMenuOpen = ref(false);
        const isHeaderFixed = ref(false);
        
        // Computed properties
        const hasDoctors = computed(() => {
            return props.company.settings?.show_doctors !== false;
        });
        
        const hasTestimonials = computed(() => {
            return props.company.settings?.show_testimonials !== false;
        });
        
        // Methods
        const toggleMobileMenu = () => {
            isMobileMenuOpen.value = !isMobileMenuOpen.value;
            
            // Prevent body scroll when menu is open
            if (isMobileMenuOpen.value) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        };
        
        const scrollToSection = (sectionId) => {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        };
        
        const scrollToSectionMobile = (sectionId) => {
            scrollToSection(sectionId);
            isMobileMenuOpen.value = false;
            document.body.style.overflow = '';
        };
        
        const handleScroll = () => {
            isHeaderFixed.value = window.scrollY > 50;
        };
        
        // Lifecycle hooks
        onMounted(() => {
            window.addEventListener('scroll', handleScroll);
            handleScroll();
        });
        
        onBeforeUnmount(() => {
            window.removeEventListener('scroll', handleScroll);
            document.body.style.overflow = '';
        });
        
        return {
            isMobileMenuOpen,
            isHeaderFixed,
            hasDoctors,
            hasTestimonials,
            toggleMobileMenu,
            scrollToSection,
            scrollToSectionMobile
        };
    }
});
</script>

<style scoped>
.landing-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    padding: 1rem 0;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    flex: 0 0 auto;
}

.logo img {
    height: 40px;
    max-width: 150px;
    object-fit: contain;
}

.desktop-nav {
    display: none;
}

@media (min-width: 768px) {
    .desktop-nav {
        display: block;
    }
    
    .mobile-menu {
        display: none;
    }
}

.desktop-nav ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

.desktop-nav li {
    margin-left: 1.5rem;
}

.desktop-nav a {
    color: #4a5568;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

.desktop-nav a:hover {
    color: v-bind('company.settings?.primary_color || "#4299e1"');
}

.appointment-link {
    background-color: v-bind('company.settings?.primary_color || "#4299e1"');
    color: white !important;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
}

.appointment-link:hover {
    opacity: 0.9;
}

.mobile-menu {
    display: block;
}

.menu-button {
    background: none;
    border: none;
    color: #4a5568;
    cursor: pointer;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.menu-button svg {
    width: 24px;
    height: 24px;
}

.mobile-nav {
    position: fixed;
    top: 70px;
    left: 0;
    width: 100%;
    height: 0;
    background-color: #ffffff;
    overflow: hidden;
    transition: height 0.3s ease;
}

.mobile-nav.is-open {
    height: calc(100vh - 70px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

.mobile-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mobile-nav li {
    border-bottom: 1px solid #e2e8f0;
}

.mobile-nav a {
    display: block;
    padding: 1rem;
    color: #4a5568;
    text-decoration: none;
    font-weight: 500;
    text-align: center;
}

.mobile-nav .appointment-link {
    margin: 1rem;
}
</style>
