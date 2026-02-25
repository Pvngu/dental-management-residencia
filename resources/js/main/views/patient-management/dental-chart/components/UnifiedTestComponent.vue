<template>
    <div class="test-component">
        <!-- Hierarchical tests (cold, heat with sub-options) -->
        <div v-if="testConfig.type === 'hierarchical'" class="flex flex-col gap-2">
            <!-- Show main options if no sub-level is active -->
            <div v-if="!showSubOptions" class="flex flex-col gap-2">
                <a-button 
                    v-for="option in testConfig.options"
                    :key="option.value"
                    class="w-full h-12 flex items-center justify-center text-base font-medium"
                    :type="value === option.value ? 'primary' : 'default'"
                    :loading="loading === option.value"
                    size="medium"
                    @click="handleMainOptionClick(option)"
                >
                    {{ option.label }}
                </a-button>
                
                <a-button 
                    class="w-full h-12 flex items-center justify-center text-base font-medium mt-6"
                    :loading="loading === 'clear'"
                    size="medium"
                    @click="updateValue(null)"
                    danger
                >
                    {{ $t("common.clear") }}
                </a-button>
            </div>

            <!-- Show sub-options when applicable -->
            <div v-else class="flex flex-col gap-2">
                <!-- Back button -->
                <a-button 
                    class="w-full h-10 flex items-center justify-center text-sm font-medium mb-2"
                    @click="goBackToMainOptions"
                    type="dashed"
                >
                    <template #icon><LeftOutlined /></template>
                    {{ $t("common.back") }}
                </a-button>

                <!-- Sub-options -->
                <a-button 
                    v-for="subOption in currentSubOptions"
                    :key="subOption.value"
                    class="w-full h-12 flex items-center justify-center text-base font-medium"
                    :type="value === subOption.value ? 'primary' : 'default'"
                    :loading="loading === subOption.value"
                    size="medium"
                    @click="updateValue(subOption.value)"
                >
                    {{ subOption.label }}
                </a-button>
                
                <a-button 
                    class="w-full h-12 flex items-center justify-center text-base font-medium mt-6"
                    :loading="loading === 'clear'"
                    size="medium"
                    @click="updateValue(null)"
                    danger
                >
                    {{ $t("common.clear") }}
                </a-button>
            </div>
        </div>

        <!-- Button-based tests (percussion, palpation) -->
        <div v-else-if="testConfig.type === 'buttons'" class="flex flex-col gap-2">
            <a-button 
                v-for="option in testConfig.options"
                :key="option.value"
                class="w-full h-12 flex items-center justify-center text-base font-medium"
                :type="value === option.value ? 'primary' : 'default'"
                :loading="loading === option.value"
                size="medium"
                @click="updateValue(option.value)"
            >
                {{ option.label }}
            </a-button>
            
            <a-button 
                class="w-full h-12 flex items-center justify-center text-base font-medium mt-6"
                :loading="loading === 'clear'"
                size="medium"
                @click="updateValue(null)"
                danger
            >
                {{ $t("common.clear") }}
            </a-button>
        </div>
        
        <!-- Numeric grid for electricity test -->
        <div v-else-if="testConfig.type === 'numeric'" class="space-y-4">
            <div class="grid grid-cols-5 gap-2">
                <a-button 
                    v-for="n in testConfig.range" 
                    :key="n"
                    :type="value === n ? 'primary' : 'default'"
                    :loading="loading === n"
                    class="h-12 flex items-center justify-center text-base font-medium"
                    size="medium"
                    @click="updateValue(n)"
                >
                    {{ n }}
                </a-button>
            </div>
            <a-button 
                class="w-full h-12 flex items-center justify-center text-base font-medium"
                :loading="loading === 'clear'"
                size="medium"
                @click="updateValue(null)"
                danger
            >
                {{ $t("common.clear") }}
            </a-button>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { LeftOutlined } from '@ant-design/icons-vue';
import { createTestConfigurations } from './testConfigurations.js';

export default defineComponent({
    name: 'UnifiedTestComponent',
    components: {
        LeftOutlined
    },
    props: {
        testType: {
            type: String,
            required: true,
            validator: (value) => ['cold', 'heat', 'percussion', 'palpation', 'electricity'].includes(value)
        },
        value: {
            type: [String, Number],
            default: null
        },
        toothId: {
            type: [Number, String],
            required: true
        },
        patientId: {
            type: [Number, String],
            required: true
        }
    },
    emits: ['update:value', 'test-completed'],
    setup(props, { emit }) {
        const { t } = useI18n();
        const loading = ref(null);
        const showSubOptions = ref(false);
        const currentSubOptions = ref([]);

        // Get test configurations from external file
        const testConfigurations = createTestConfigurations(t);
        const testConfig = testConfigurations[props.testType];

        const handleMainOptionClick = (option) => {
            if (option.subOptions && option.subOptions.length > 0) {
                // Show sub-options for this main option
                currentSubOptions.value = option.subOptions;
                showSubOptions.value = true;
            } else {
                // No sub-options, directly update value
                updateValue(option.value);
            }
        };

        const goBackToMainOptions = () => {
            showSubOptions.value = false;
            currentSubOptions.value = [];
        };

        const updateValue = async (newValue) => {
            // Set loading state for the clicked button
            loading.value = newValue === null ? 'clear' : newValue;
            
            try {
                // Make API call to save the test result
                const payload = {
                    patientId: props.patientId,
                    toothId: props.toothId,
                    testType: props.testType,
                    value: newValue
                };
                
                // Simulated API call - replace with actual API call
                await new Promise(resolve => setTimeout(resolve, 800));
                
                // Emit the value update event
                emit('update:value', newValue);
                
                // Emit test completed event
                emit('test-completed');

                // Reset sub-options view when value is updated
                if (newValue !== null) {
                    showSubOptions.value = false;
                    currentSubOptions.value = [];
                }
            } catch (error) {
                console.error(`Error saving ${props.testType} test result:`, error);
                // Handle error (e.g., show notification)
            } finally {
                // Clear loading state
                loading.value = null;
            }
        };

        return {
            loading,
            testConfig,
            showSubOptions,
            currentSubOptions,
            handleMainOptionClick,
            goBackToMainOptions,
            updateValue
        };
    }
});
</script>
