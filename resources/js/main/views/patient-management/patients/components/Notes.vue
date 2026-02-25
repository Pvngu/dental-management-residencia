<template>
    <div>
        <Notes :patientId="patientId" @noteUpdated="handleNoteUpdated" />
    </div>
</template>

<script setup>
import Notes from "./notes/index.vue";

const props = defineProps({
    patientId: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['noteUpdated']);

const handleNoteUpdated = () => {
    emit('noteUpdated');
};
</script>
