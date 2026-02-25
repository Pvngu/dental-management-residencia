<template>
    <div class="dental-tests-container">
        <!-- Main Menu View -->
        <div v-if="!activeTest" class="space-y-4">
            <h3 class="text-lg font-medium">{{ $t("dental_chart.endodontic_test") }}</h3>
            
            <!-- Test List -->
            <div class="bg-white border rounded-lg divide-y grid grid-cols-2 gap-1 text-sm">
                <div 
                    v-for="test in testTypes"
                    :key="test.key"
                    class="p-2 flex items-center justify-between hover:bg-gray-50 cursor-pointer transition-colors rounded-lg" 
                    @click="selectTest(test.key)"
                >
                    <div class="flex items-center">
                        <component :is="test.icon" class="text-blue-500 mr-2 text-base" />
                        <span class="font-medium">{{ test.label }}</span>
                    </div>
                    <div class="flex items-center min-h-[22px]">
                        <span v-if="getTestValue(test.key)" class="text-xs text-gray-600 mr-1 bg-blue-100 px-1.5 py-0.5 rounded">
                            {{ formatTestValue(test.key, getTestValue(test.key)) }}
                        </span>
                        <RightOutlined class="text-gray-400 text-xs" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Detail View -->
        <div v-else class="space-y-4">
            <!-- Header with back button -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center cursor-pointer" @click="goBack">
                    <div class="mr-2"><LeftOutlined /></div>
                    <component :is="getActiveTestIcon()" class="text-blue-500 mr-2 text-lg" />
                    <h3 class="text-lg font-medium mt-2!">{{ getActiveTestTitle() }}</h3>
                </div>
            </div>
            
            <!-- Test Component -->
            <UnifiedTestComponent 
                :testType="activeTest"
                :value="getTestValue(activeTest)"
                :toothId="toothId"
                :patientId="patientId"
                @update:value="updateTest(getTestKey(activeTest), $event)"
                @test-completed="activeTest = null;"
            />
        </div>
    </div>
</template>

<script>
import { defineComponent, ref } from 'vue';
import { 
    BulbOutlined, 
    SoundOutlined, 
    ExperimentOutlined, 
    FireOutlined, 
    ThunderboltOutlined,
    RightOutlined,
    LeftOutlined
} from '@ant-design/icons-vue';
import { useI18n } from 'vue-i18n';
import UnifiedTestComponent from './UnifiedTestComponent.vue';
import { createTestTypes, getTestValue, getTestKey, createTestConfigurations } from './testConfigurations.js';

export default defineComponent({
    name: 'DentalTestsComponent',
    components: {
        BulbOutlined,
        SoundOutlined,
        ExperimentOutlined,
        FireOutlined,
        ThunderboltOutlined,
        RightOutlined,
        LeftOutlined,
        UnifiedTestComponent
    },
    props: {
        testData: {
            type: Object,
            required: true
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
    emits: ['update:testData'],
    setup(props, { emit }) {
        const { t } = useI18n();
        const activeTest = ref(null);

        // Get test types and configurations from external configuration
        const testTypes = createTestTypes(t);
        const testConfigurations = createTestConfigurations(t);

        const selectTest = (testType) => {
            activeTest.value = testType;
        };

        const goBack = () => {
            activeTest.value = null;
        };

        const updateTest = (testKey, value) => {
            const updatedData = { ...props.testData, [testKey]: value };
            emit('update:testData', updatedData);
        };

        const getActiveTestIcon = () => {
            const activeTestConfig = testTypes.find(test => test.key === activeTest.value);
            return activeTestConfig ? activeTestConfig.icon : null;
        };

        const getActiveTestTitle = () => {
            const config = testConfigurations[activeTest.value];
            return config ? config.title : '';
        };

        const formatTestValue = (testType, value) => {
            if (!value) return '';
            
            const config = testConfigurations[testType];
            if (config && config.type === 'buttons') {
                const option = config.options.find(opt => opt.value === value);
                return option ? option.label : value;
            } else if (config && config.type === 'hierarchical') {
                // First check if it's a main option value
                const mainOption = config.options.find(opt => opt.value === value);
                if (mainOption) {
                    return mainOption.label;
                }
                
                // Then check if it's a sub-option value
                for (const option of config.options) {
                    if (option.subOptions) {
                        const subOption = option.subOptions.find(sub => sub.value === value);
                        if (subOption) {
                            // For cold and heat tests, show both main and sub option
                            if (testType === 'cold' || testType === 'heat') {
                                return `${option.label}, ${subOption.label}`;
                            }
                            return subOption.label;
                        }
                    }
                }
            }
            return value;
        };

        // Use helper functions from external configuration
        const getTestValueHelper = (testType) => getTestValue(testType, props.testData);
        const getTestKeyHelper = (testType) => getTestKey(testType);

        return {
            activeTest,
            testTypes,
            selectTest,
            goBack,
            updateTest,
            getActiveTestIcon,
            getActiveTestTitle,
            formatTestValue,
            getTestValue: getTestValueHelper,
            getTestKey: getTestKeyHelper
        };
    }
});
</script>
