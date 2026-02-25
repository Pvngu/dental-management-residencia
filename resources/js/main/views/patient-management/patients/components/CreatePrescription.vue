<template>
    <div class="create-prescription-container">
        <a-card :loading="loading" class="shadow-sm">
            <template #title>
                <div class="flex items-center gap-2">
                    <MedicineBoxOutlined class="text-xl" />
                    <span>{{ $t('prescriptions.create_prescription') }}</span>
                </div>
            </template>

            <a-form layout="vertical" @submit.prevent="submitPrescription">
                <!-- Medicine Search Section -->
                <a-form-item 
                    :label="$t('prescriptions.search_medicine')"
                    :help="errors.medicines ? errors.medicines : null"
                    :validate-status="errors.medicines ? 'error' : null"
                >
                    <div class="relative">
                        <a-input-search
                            v-model:value="searchQuery"
                            :placeholder="$t('prescriptions.search_medicine_placeholder')"
                            @input="onSearchInput"
                            :loading="searchLoading"
                        />
                        
                        <!-- Search Results Dropdown -->
                        <div 
                            v-if="showSearchResults && searchResults.length > 0"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                        >
                            <div
                                v-for="medicine in searchResults"
                                :key="medicine.xid"
                                @click="addMedicine(medicine)"
                                class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
                            >
                                <div class="font-medium text-gray-900">{{ medicine.name }}</div>
                                <div v-if="medicine.description" class="text-sm text-gray-500">{{ medicine.description }}</div>
                            </div>
                        </div>
                        
                        <!-- No Results Message with Add Custom Option -->
                        <div 
                            v-if="showSearchResults && searchQuery && searchResults.length === 0 && !searchLoading"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg text-center text-gray-500"
                        >
                            <div class="p-4">
                                {{ $t('prescriptions.no_medicines_found') }}
                            </div>
                            <div class="border-t border-gray-200 p-3 bg-blue-50 hover:bg-blue-100 cursor-pointer transition-colors" @click="addCustomMedicine">
                                <PlusOutlined class="mr-2" />
                                <span class="text-blue-600 font-medium">{{ $t('prescriptions.add_custom_medicine') }}: "{{ searchQuery }}"</span>
                            </div>
                        </div>
                    </div>
                </a-form-item>

                <!-- Prescribed Medicines List -->
                <div v-if="prescriptionItems.length > 0" class="mb-6">
                    <h3 class="text-base font-semibold mb-3 flex items-center gap-2">
                        <UnorderedListOutlined />
                        {{ $t('prescriptions.prescribed_medicines') }}
                    </h3>
                    
                    <div class="space-y-4">
                        <a-card
                            v-for="(item, index) in prescriptionItems"
                            :key="item.medicine.xid"
                            size="small"
                            class="border border-gray-200"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-medium text-gray-900">{{ item.medicine.name }}</h4>
                                        <a-tag v-if="item.medicine.isCustom" color="blue" size="small">
                                            {{ $t('prescriptions.custom') }}
                                        </a-tag>
                                    </div>
                                    <p v-if="item.medicine.description" class="text-sm text-gray-500">
                                        {{ item.medicine.description }}
                                    </p>
                                </div>
                                <a-button
                                    type="text"
                                    danger
                                    size="small"
                                    @click="removeMedicine(index)"
                                    :icon="h(DeleteOutlined)"
                                />
                            </div>
                            
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="8">
                                    <a-form-item
                                        :label="$t('prescriptions.dosage')"
                                        :help="item.errors.dosage ? item.errors.dosage : null"
                                        :validate-status="item.errors.dosage ? 'error' : null"
                                        class="mb-0"
                                    >
                                        <a-input
                                            v-model:value="item.dosage"
                                            :placeholder="$t('prescriptions.dosage_placeholder')"
                                            @input="clearError(index, 'dosage')"
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item
                                        :label="$t('prescriptions.frequency')"
                                        :help="item.errors.frequency ? item.errors.frequency : null"
                                        :validate-status="item.errors.frequency ? 'error' : null"
                                        class="mb-0"
                                    >
                                        <a-input
                                            v-model:value="item.frequency"
                                            :placeholder="$t('prescriptions.frequency_placeholder')"
                                            @input="clearError(index, 'frequency')"
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item
                                        :label="$t('prescriptions.duration')"
                                        :help="item.errors.duration ? item.errors.duration : null"
                                        :validate-status="item.errors.duration ? 'error' : null"
                                        class="mb-0"
                                    >
                                        <a-input
                                            v-model:value="item.duration"
                                            :placeholder="$t('prescriptions.duration_placeholder')"
                                            @input="clearError(index, 'duration')"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-card>
                    </div>
                </div>

                <!-- General Notes -->
                <a-form-item 
                    :label="$t('prescriptions.notes')"
                >
                    <a-textarea
                        v-model:value="notes"
                        :placeholder="$t('prescriptions.notes_placeholder')"
                        :auto-size="{ minRows: 3, maxRows: 6 }"
                    />
                </a-form-item>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 mt-6">
                    <a-button
                        @click="handleCancel"
                    >
                        {{ $t('common.cancel') }}
                    </a-button>
                    <a-button
                        type="primary"
                        html-type="submit"
                        :loading="submitting"
                        :disabled="prescriptionItems.length === 0"
                    >
                        <template #icon>
                            <SaveOutlined />
                        </template>
                        {{ $t('prescriptions.save_prescription') }}
                    </a-button>
                </div>
            </a-form>
        </a-card>
    </div>
</template>

<script setup>
import { ref, reactive, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    MedicineBoxOutlined, 
    SearchOutlined, 
    DeleteOutlined,
    SaveOutlined,
    UnorderedListOutlined,
    PlusOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const axiosAdmin = window.axiosAdmin;

const props = defineProps({
    patientId: {
        type: String,
        required: true
    },
    doctorId: {
        type: String,
        required: true
    },
    appointmentId: {
        type: String,
        required: false,
        default: null
    }
});

const emit = defineEmits(['success', 'cancel']);

const { t } = useI18n();

// State
const loading = ref(false);
const submitting = ref(false);
const searchQuery = ref('');
const searchLoading = ref(false);
const searchResults = ref([]);
const showSearchResults = ref(false);
const notes = ref('');
const prescriptionItems = ref([]);
const errors = reactive({
    medicines: ''
});

// Debounce timer
let searchTimeout = null;

// Search medicines with debounce
const onSearchInput = () => {
    clearTimeout(searchTimeout);
    
    if (!searchQuery.value || searchQuery.value.trim().length < 2) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }
    
    searchTimeout = setTimeout(() => {
        searchMedicines();
    }, 300);
};

const searchMedicines = async () => {
    try {
        searchLoading.value = true;
        showSearchResults.value = true;
        
        const response = await axiosAdmin.get('medicines/search', {
            params: {
                search: searchQuery.value
            }
        });
        
        // Data is already flattened from the backend
        searchResults.value = response.data || [];
    } catch (error) {
        console.error('Error searching medicines:', error);
        message.error(t('prescriptions.search_error'));
        searchResults.value = [];
    } finally {
        searchLoading.value = false;
    }
};

const addMedicine = (medicine) => {
    // Check for duplicates
    const exists = prescriptionItems.value.some(
        item => item.medicine.xid === medicine.xid || item.medicine.name === medicine.name
    );
    
    if (exists) {
        message.warning(t('prescriptions.medicine_already_added'));
        return;
    }
    
    // Add medicine to prescription items
    prescriptionItems.value.push({
        medicine: { ...medicine },
        dosage: '',
        frequency: '',
        duration: '',
        errors: {
            dosage: '',
            frequency: '',
            duration: ''
        }
    });
    
    // Clear search
    searchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
    errors.medicines = '';
};

const addCustomMedicine = () => {
    const customName = searchQuery.value.trim();
    
    if (!customName) {
        return;
    }
    
    // Check for duplicates
    const exists = prescriptionItems.value.some(
        item => item.medicine.name.toLowerCase() === customName.toLowerCase()
    );
    
    if (exists) {
        message.warning(t('prescriptions.medicine_already_added'));
        return;
    }
    
    // Add custom medicine (without xid, indicating it's custom)
    prescriptionItems.value.push({
        medicine: {
            name: customName,
            description: null,
            isCustom: true // Flag to identify custom medicines
        },
        dosage: '',
        frequency: '',
        duration: '',
        errors: {
            dosage: '',
            frequency: '',
            duration: ''
        }
    });
    
    // Clear search
    searchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
    errors.medicines = '';
    
    message.success(t('prescriptions.custom_medicine_added'));
};

const removeMedicine = (index) => {
    prescriptionItems.value.splice(index, 1);
};

const clearError = (index, field) => {
    if (prescriptionItems.value[index]) {
        prescriptionItems.value[index].errors[field] = '';
    }
};

const validateForm = () => {
    let isValid = true;
    
    // Check if at least one medicine is added
    if (prescriptionItems.value.length === 0) {
        errors.medicines = t('prescriptions.at_least_one_medicine');
        isValid = false;
    } else {
        errors.medicines = '';
    }
    
    // Validate each medicine item
    prescriptionItems.value.forEach((item, index) => {
        if (!item.dosage || item.dosage.trim() === '') {
            item.errors.dosage = t('prescriptions.dosage_required');
            isValid = false;
        } else {
            item.errors.dosage = '';
        }
        
        if (!item.frequency || item.frequency.trim() === '') {
            item.errors.frequency = t('prescriptions.frequency_required');
            isValid = false;
        } else {
            item.errors.frequency = '';
        }
        
        if (!item.duration || item.duration.trim() === '') {
            item.errors.duration = t('prescriptions.duration_required');
            isValid = false;
        } else {
            item.errors.duration = '';
        }
    });
    
    return isValid;
};

const submitPrescription = async () => {
    if (!validateForm()) {
        message.error(t('prescriptions.validation_failed'));
        return;
    }
    
    try {
        submitting.value = true;
        
        // Prepare payload
        const payload = {
            patient_id: props.patientId,
            doctor_id: props.doctorId,
            appointment_id: props.appointmentId,
            notes: notes.value || '',
            medicines: prescriptionItems.value.map(item => ({
                medicine_id: item.medicine.xid || null, // null for custom medicines
                medicine_name: item.medicine.name, // Always send the medicine name
                dosage: item.dosage,
                frequency: item.frequency,
                duration: item.duration
            }))
        };
        
        const response = await axiosAdmin.post('prescriptions', payload);
        
        if (response && response.data) {
            emit('success', response.data);
            resetForm();
        }
    } catch (error) {
        console.error('Error creating prescription:', error);
        
        if (error.response && error.response.data && error.response.data.errors) {
            // Handle validation errors from backend
            const backendErrors = error.response.data.errors;
            Object.keys(backendErrors).forEach(key => {
                message.error(backendErrors[key][0]);
            });
        } else {
            message.error(t('prescriptions.creation_failed'));
        }
    } finally {
        submitting.value = false;
    }
};

const resetForm = () => {
    prescriptionItems.value = [];
    notes.value = '';
    searchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
    errors.medicines = '';
};

const handleCancel = () => {
    resetForm();
    emit('cancel');
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        showSearchResults.value = false;
    }
};

// Add event listener for clicking outside
if (typeof document !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
}
</script>

<style scoped>
.create-prescription-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Custom scrollbar for search results */
.overflow-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
