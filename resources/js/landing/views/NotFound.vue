<template>
    <div class="not-found">
        <div class="content">
            <h1>404</h1>
            <h2>Página no encontrada</h2>
            <p>Lo sentimos, no pudimos encontrar la página que estás buscando.</p>
            <router-link to="/" class="home-link">Volver al inicio</router-link>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NotFound'
}
</script>

<style scoped>
.not-found {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    text-align: center;
    background-color: #f7fafc;
}

.content {
    max-width: 500px;
    padding: 2rem;
}

h1 {
    font-size: 6rem;
    margin: 0;
    color: #2d3748;
}

h2 {
    font-size: 2rem;
    color: #4a5568;
    margin-bottom: 1rem;
}

p {
    color: #718096;
    margin-bottom: 2rem;
}

.home-link {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background-color: #4299e1;
    color: white;
    text-decoration: none;
    border-radius: 0.25rem;
    font-weight: 600;
    transition: background-color 0.3s;
}

.home-link:hover {
    background-color: #3182ce;
}
</style>
