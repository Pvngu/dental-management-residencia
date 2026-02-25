<template>
    <div class="loading-overlay">
        <div class="spinner"></div>
        <p>Cargando...</p>
    </div>
</template>

<script>
export default {
    name: 'LoadingOverlay'
}
</script>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.9);
    z-index: 9999;
}

.spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border-left-color: #4299e1;
    animation: spin 1s ease infinite;
    margin-bottom: 1rem;
}

p {
    font-size: 1.125rem;
    color: #2d3748;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
