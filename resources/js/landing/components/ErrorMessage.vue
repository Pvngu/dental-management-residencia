<template>
    <div class="error-message">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2>¡Ups! Algo salió mal</h2>
        <p>{{ message || 'No pudimos cargar la página solicitada. Por favor, intente nuevamente más tarde.' }}</p>
        <button @click="refreshPage" class="refresh-button">
            Intentar nuevamente
        </button>
    </div>
</template>

<script>
export default {
    name: 'ErrorMessage',
    props: {
        message: {
            type: String,
            default: ''
        }
    },
    methods: {
        refreshPage() {
            window.location.reload();
        }
    }
}
</script>

<style scoped>
.error-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 600px;
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.icon {
    width: 60px;
    height: 60px;
    color: #e53e3e;
    margin-bottom: 1rem;
}

h2 {
    font-size: 1.5rem;
    color: #2d3748;
    margin-bottom: 1rem;
}

p {
    color: #4a5568;
    margin-bottom: 1.5rem;
}

.refresh-button {
    padding: 0.75rem 1.5rem;
    background-color: #4299e1;
    color: white;
    border: none;
    border-radius: 0.25rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.refresh-button:hover {
    background-color: #3182ce;
}
</style>
