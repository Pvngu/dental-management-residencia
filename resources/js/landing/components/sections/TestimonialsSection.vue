<template>
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" :style="{ color: primaryColor }">Testimonios</span>
                <h2 class="section-title">Lo que dicen <span :style="{ color: primaryColor }">nuestros pacientes</span></h2>
                <p class="section-subtitle">Conoce las experiencias de quienes han confiado en nuestros servicios de salud dental.</p>
            </div>
            
            <div class="testimonials-wrapper">
                <div class="testimonial-controls">
                    <button @click="prevTestimonial" class="control-btn prev" :disabled="currentIndex === 0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
                        </svg>
                    </button>
                    <button @click="nextTestimonial" class="control-btn next" :disabled="currentIndex >= testimonials.length - visibleCount">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                        </svg>
                    </button>
                </div>
                
                <div class="testimonials-slider" ref="sliderRef">
                    <div 
                        v-for="(testimonial, index) in testimonials" 
                        :key="index"
                        class="testimonial-card"
                        :class="{ 'active': index >= currentIndex && index < currentIndex + visibleCount }"
                        :style="{ transform: `translateX(${-100 * currentIndex}%)` }"
                    >
                        <div class="testimonial-content">
                            <div class="testimonial-quote" :style="{ color: primaryColor }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M12 12a1 1 0 0 0 1-1V8.558a1 1 0 0 0-1-1h-1.388c0-.351.021-.703.062-1.054.062-.372.166-.703.31-.992.145-.29.331-.517.559-.683.227-.186.516-.279.868-.279V3c-.579 0-1.085.124-1.52.372a3.322 3.322 0 0 0-1.085.992 4.92 4.92 0 0 0-.62 1.458A7.712 7.712 0 0 0 9 7.558V11a1 1 0 0 0 1 1zm-6 0a1 1 0 0 0 1-1V8.558a1 1 0 0 0-1-1H4.612c0-.351.021-.703.062-1.054.062-.372.166-.703.31-.992.145-.29.331-.517.559-.683.227-.186.516-.279.868-.279V3c-.579 0-1.085.124-1.52.372a3.322 3.322 0 0 0-1.085.992 4.92 4.92 0 0 0-.62 1.458A7.712 7.712 0 0 0 3 7.558V11a1 1 0 0 0 1 1z"/>
                                </svg>
                            </div>
                            <p class="testimonial-text">{{ testimonial.text }}</p>
                            <div class="testimonial-rating">
                                <span 
                                    v-for="star in 5" 
                                    :key="star"
                                    :class="{ 'filled': star <= testimonial.rating }"
                                    :style="star <= testimonial.rating ? { color: primaryColor } : {}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                    </svg>
                                </span>
                            </div>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img :src="testimonial.image || defaultImage" :alt="testimonial.name">
                                </div>
                                <div class="author-info">
                                    <h4>{{ testimonial.name }}</h4>
                                    <p>{{ testimonial.title }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-dots">
                    <button 
                        v-for="(_, index) in getDotCount()" 
                        :key="index"
                        class="dot"
                        :class="{ active: currentSlide === index }"
                        @click="goToSlide(index)"
                        :style="currentSlide === index ? { backgroundColor: primaryColor } : {}"
                    ></button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { defineComponent, ref, computed, onMounted, onUnmounted } from 'vue';

export default defineComponent({
    name: 'TestimonialsSection',
    
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
        const currentIndex = ref(0);
        const sliderRef = ref(null);
        const windowWidth = ref(window.innerWidth);
        
        // Default image for testimonials without images
        const defaultImage = 'https://randomuser.me/api/portraits/men/32.jpg';
        
        // Mock testimonials data - in real implementation, this would come from the company prop
        const testimonials = ref([
            {
                text: "He quedado muy satisfecho con el tratamiento dental. El doctor fue muy profesional y me explicó todo el proceso detalladamente. Recomiendo ampliamente esta clínica.",
                rating: 5,
                name: "Juan Pérez",
                title: "Paciente de Ortodoncia",
                image: "https://randomuser.me/api/portraits/men/32.jpg"
            },
            {
                text: "Excelente atención y servicio. El personal es muy amable y el lugar está impecable. Me sentí muy cómoda durante todo el procedimiento y los resultados fueron mejor de lo que esperaba.",
                rating: 5,
                name: "María Rodríguez",
                title: "Paciente de Blanqueamiento",
                image: "https://randomuser.me/api/portraits/women/44.jpg"
            },
            {
                text: "Mi experiencia en esta clínica dental fue extraordinaria. Desde la recepción hasta la consulta con el doctor, todo fue de primera calidad. Definitivamente volveré para mis próximos tratamientos.",
                rating: 4,
                name: "Carlos González",
                title: "Paciente de Implantes",
                image: "https://randomuser.me/api/portraits/men/67.jpg"
            },
            {
                text: "Tenía mucho miedo de ir al dentista, pero el equipo médico me hizo sentir muy tranquila. El procedimiento fue rápido y sin dolor. Estoy muy contenta con los resultados.",
                rating: 5,
                name: "Laura Sánchez",
                title: "Paciente de Endodoncia",
                image: "https://randomuser.me/api/portraits/women/17.jpg"
            },
            {
                text: "El trato humano y profesional que recibí fue excepcional. La tecnología que utilizan es de vanguardia y los precios son muy competitivos. Totalmente recomendable.",
                rating: 5,
                name: "Roberto Martínez",
                title: "Paciente de Rehabilitación",
                image: "https://randomuser.me/api/portraits/men/4.jpg"
            }
        ]);
        
        // Compute how many testimonials are visible based on screen width
        const visibleCount = computed(() => {
            if (windowWidth.value >= 1200) {
                return 3;
            } else if (windowWidth.value >= 768) {
                return 2;
            } else {
                return 1;
            }
        });
        
        // Compute current slide for dots navigation
        const currentSlide = computed(() => {
            return Math.floor(currentIndex.value / visibleCount.value);
        });
        
        // Get the number of dots based on visible slides
        const getDotCount = () => {
            return Math.ceil((testimonials.value.length - visibleCount.value + 1) / visibleCount.value);
        };
        
        // Navigate to specific slide
        const goToSlide = (index) => {
            currentIndex.value = index * visibleCount.value;
        };
        
        // Previous testimonial
        const prevTestimonial = () => {
            if (currentIndex.value > 0) {
                currentIndex.value--;
            }
        };
        
        // Next testimonial
        const nextTestimonial = () => {
            if (currentIndex.value < testimonials.value.length - visibleCount.value) {
                currentIndex.value++;
            }
        };
        
        // Handle window resize
        const handleResize = () => {
            windowWidth.value = window.innerWidth;
            
            // Adjust current index if needed after resize
            if (currentIndex.value > testimonials.value.length - visibleCount.value) {
                currentIndex.value = Math.max(0, testimonials.value.length - visibleCount.value);
            }
        };
        
        // Auto-advance the testimonials every 5 seconds
        let autoPlayInterval;
        
        const startAutoPlay = () => {
            autoPlayInterval = setInterval(() => {
                if (currentIndex.value < testimonials.value.length - visibleCount.value) {
                    currentIndex.value++;
                } else {
                    currentIndex.value = 0;
                }
            }, 5000);
        };
        
        onMounted(() => {
            window.addEventListener('resize', handleResize);
            startAutoPlay();
        });
        
        onUnmounted(() => {
            window.removeEventListener('resize', handleResize);
            clearInterval(autoPlayInterval);
        });
        
        return {
            currentIndex,
            sliderRef,
            testimonials,
            visibleCount,
            currentSlide,
            getDotCount,
            goToSlide,
            prevTestimonial,
            nextTestimonial,
            defaultImage
        };
    }
});
</script>

<style scoped>
.testimonials-section {
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

.testimonials-wrapper {
    position: relative;
    padding: 1rem 0;
}

.testimonial-controls {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-bottom: 2rem;
}

.control-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border: 1px solid #e2e8f0;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s ease;
}

.control-btn:hover {
    background-color: #f1f5f9;
    color: #1a202c;
}

.control-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.testimonials-slider {
    display: flex;
    overflow: hidden;
    margin: 0 -1rem;
}

.testimonial-card {
    min-width: 100%;
    padding: 0 1rem;
    transition: transform 0.5s ease;
    
    @media (min-width: 768px) {
        min-width: 50%;
    }
    
    @media (min-width: 1200px) {
        min-width: 33.333%;
    }
}

.testimonial-content {
    background-color: #fff;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.testimonial-quote {
    margin-bottom: 1.5rem;
    opacity: 0.8;
}

.testimonial-text {
    flex-grow: 1;
    font-size: 1.125rem;
    line-height: 1.7;
    color: #4a5568;
    font-style: italic;
    margin-bottom: 1.5rem;
}

.testimonial-rating {
    display: flex;
    margin-bottom: 1.5rem;
    gap: 0.25rem;
}

.testimonial-rating span {
    color: #e2e8f0;
}

.testimonial-rating span.filled {
    color: #f59e0b;
}

.testimonial-author {
    display: flex;
    align-items: center;
}

.author-image {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 1rem;
    flex-shrink: 0;
}

.author-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.author-info h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.25rem;
    color: #1a202c;
}

.author-info p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.testimonial-dots {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #e2e8f0;
    border: none;
    padding: 0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dot.active {
    transform: scale(1.2);
}

@media (min-width: 768px) {
    .section-title {
        font-size: 2.75rem;
    }
    
    .testimonial-content {
        padding: 2.5rem;
    }
}
</style>
