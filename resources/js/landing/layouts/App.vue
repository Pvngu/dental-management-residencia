<template>
    <div class="landing-app">
        <router-view v-if="ready" />
        <div v-else class="loading-container">
            <img src="/images/loading.svg" alt="Cargando..." class="loading-spinner" />
            <p>Cargando página...</p>
        </div>
        
        <!-- Debug information when forced ready but component isn't rendering -->
        <div v-if="ready && maxWaitReached" class="debug-info">
            <h3>Información de depuración</h3>
            <p>La ruta actual es: {{ $route.fullPath }}</p>
            <p>Los componentes están disponibles pero pueden no haberse renderizado correctamente.</p>
            <p>Revise la consola del navegador para más información.</p>
            <button @click="reload" class="debug-button">Recargar página</button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LandingApp',
    data() {
        return {
            ready: false,
            maxWaitReached: false
        };
    },
    methods: {
        reload() {
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('Manual page reload triggered');
            }
            window.location.reload();
        }
    },
    mounted() {
        if (typeof window.logLoadingState === 'function') {
            window.logLoadingState('App component mounted');
        }
        
        // Set ready after a brief delay to ensure route is properly matched
        setTimeout(() => {
            this.ready = true;
            if (typeof window.logLoadingState === 'function') {
                window.logLoadingState('App ready set to true');
            }
        }, 100);
        
        // Failsafe - if after 3 seconds we're still not ready, force it to be ready
        // This prevents the user from being stuck at the loading screen forever
        setTimeout(() => {
            if (!this.ready) {
                this.ready = true;
                this.maxWaitReached = true;
                if (typeof window.logLoadingState === 'function') {
                    window.logLoadingState('FORCED App ready due to timeout');
                    console.warn('Forced ready state due to timeout - route may not be properly matched');
                }
            }
        }, 3000);
    }
};
</script>

<style>
/* Global styles for landing page */
.landing-app {
    font-family: 'Poppins', sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
}

/* Loading styles */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    text-align: center;
}

.loading-spinner {
    width: 80px;
    height: 80px;
    margin-bottom: 16px;
    animation: spin 1.5s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Debug information */
.debug-info {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-width: 400px;
    font-size: 14px;
}

.debug-info h3 {
    margin-top: 0;
    color: #dc3545;
}

.debug-button {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    cursor: pointer;
    margin-top: 8px;
}

.debug-button:hover {
    background-color: #0069d9;
}
</style>
