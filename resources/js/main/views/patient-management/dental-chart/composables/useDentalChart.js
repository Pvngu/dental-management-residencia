import { ref, reactive } from "vue";
import { useI18n } from "vue-i18n";

export default function useDentalChart() {
    const { t } = useI18n();

    // Default periodontal structure for each position
    const defaultPeriodontalPosition = {
        bleeding: false,
        plaque: false,
        tartar: false,
        pus: false,
        probingDepth: '',
        gingivalMargin: ''
    };

    // Default periodontal structure for a tooth
    const defaultPeriodontal = {
        disto_palatal: { ...defaultPeriodontalPosition },
        palatal: { ...defaultPeriodontalPosition },
        mesio_palatal: { ...defaultPeriodontalPosition },
        disto_buccal: { ...defaultPeriodontalPosition },
        buccal: { ...defaultPeriodontalPosition },
        mesio_buccal: { ...defaultPeriodontalPosition }
    };

    // Default structure for all teeth (empty state)
    const defaultTeeth = reactive({
        // Upper teeth
        18: { id: 18, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        17: { id: 17, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        16: { id: 16, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        15: { id: 15, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        14: { id: 14, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        13: { id: 13, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        12: { id: 12, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        11: { id: 11, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        21: { id: 21, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        22: { id: 22, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        23: { id: 23, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        24: { id: 24, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        25: { id: 25, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        26: { id: 26, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        27: { id: 27, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        28: { id: 28, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        // Lower teeth
        38: { id: 38, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        37: { id: 37, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        36: { id: 36, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        35: { id: 35, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        34: { id: 34, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        33: { id: 33, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        32: { id: 32, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        31: { id: 31, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        41: { id: 41, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        42: { id: 42, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        43: { id: 43, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        44: { id: 44, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        45: { id: 45, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        46: { id: 46, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        47: { id: 47, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
        48: { id: 48, conditions: {}, pathology: {}, restoration: {}, periodontal: { ...defaultPeriodontal }, tests: {} },
    });

    // Current dental chart state (merged with backend data)
    const dentalChart = reactive({ ...defaultTeeth });

    // Loading states
    const loading = ref(false);
    const saving = ref(false);

    /**
     * Deep merge two objects recursively
     */
    const deepMerge = (target, source) => {
        for (const key in source) {
            if (source[key] !== null && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                if (!target[key] || typeof target[key] !== 'object') {
                    target[key] = {};
                }
                deepMerge(target[key], source[key]);
            } else {
                target[key] = source[key];
            }
        }
        return target;
    };

    /**
     * Check if a value is considered empty/null
     */
    const isEmpty = (value) => {
        if (value === null || value === undefined || value === '') return true;
        if (typeof value === 'boolean') return false; // booleans are never considered empty
        if (typeof value === 'number') return false; // numbers (including 0) are never considered empty
        if (Array.isArray(value)) return value.length === 0;
        if (typeof value === 'object') {
            return Object.keys(value).length === 0 || 
                   Object.values(value).every(v => isEmpty(v));
        }
        return false;
    };

    /**
     * Clean data by removing empty/null values
     */
    const cleanData = (data) => {
        if (Array.isArray(data)) {
            const cleaned = data.filter(item => !isEmpty(item)).map(item => cleanData(item));
            return cleaned.length > 0 ? cleaned : null;
        }
        
        if (data && typeof data === 'object') {
            const cleaned = {};
            for (const [key, value] of Object.entries(data)) {
                if (!isEmpty(value)) {
                    const cleanedValue = cleanData(value);
                    if (!isEmpty(cleanedValue)) {
                        cleaned[key] = cleanedValue;
                    }
                }
            }
            return Object.keys(cleaned).length > 0 ? cleaned : null;
        }
        
        return isEmpty(data) ? null : data;
    };

    /**
     * Check if periodontal data has any actual values (not defaults)
     */
    const hasPeriodontalData = (periodontalData) => {
        if (!periodontalData || typeof periodontalData !== 'object') return false;
        
        for (const position of Object.values(periodontalData)) {
            if (position && typeof position === 'object') {
                // Check if any value differs from defaults
                if (position.bleeding === true || 
                    position.plaque === true || 
                    position.tartar === true || 
                    position.pus === true ||
                    (position.probingDepth !== null && position.probingDepth !== undefined && position.probingDepth !== '' && position.probingDepth !== 2) ||
                    (position.gingivalMargin !== null && position.gingivalMargin !== undefined && position.gingivalMargin !== '' && position.gingivalMargin !== 0)) {
                    return true;
                }
            }
        }
        return false;
    };

    /**
     * Load dental chart from backend
     */
    const loadDentalChart = async (patientId) => {
        loading.value = true;
        try {
            const response = await axiosAdmin.get(`patients/${patientId}/dental-chart`);
            const backendChart = response.data.conditions || [];

            // Reset chart to default state with deep copy to avoid reference issues
            const freshDefaultTeeth = {};
            Object.keys(defaultTeeth).forEach(toothId => {
                freshDefaultTeeth[toothId] = {
                    id: parseInt(toothId),
                    conditions: {},
                    pathology: {},
                    restoration: {},
                    periodontal: JSON.parse(JSON.stringify(defaultPeriodontal)),
                    tests: {}
                };
            });
            
            Object.assign(dentalChart, freshDefaultTeeth);

            // Merge backend data into default structure
            if (Array.isArray(backendChart)) {
                backendChart.forEach(tooth => {
                    if (tooth.id && dentalChart[tooth.id]) {
                        // Ensure periodontal has the complete structure before merging
                        if (tooth.periodontal) {
                            Object.keys(defaultPeriodontal).forEach(position => {
                                if (!tooth.periodontal[position]) {
                                    tooth.periodontal[position] = { ...defaultPeriodontalPosition };
                                } else {
                                    tooth.periodontal[position] = {
                                        ...defaultPeriodontalPosition,
                                        ...tooth.periodontal[position]
                                    };
                                }
                            });
                        }
                        deepMerge(dentalChart[tooth.id], tooth);
                    }
                });
            }

            return dentalChart;
        } catch (error) {
            console.error('Error loading dental chart:', error);
            throw error;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Save a specific section of a tooth
     */
    const saveDentalChartSection = async (patientId, toothId, section, data) => {
        saving.value = true;
        try {
            // Clean the data before saving
            let cleanedData = cleanData(data);
            
            // Special handling for periodontal data
            if (section === 'periodontal') {
                if (!hasPeriodontalData(data)) {
                    cleanedData = null; // Don't save default periodontal data
                }
            }

            // If cleaned data is null/empty, don't save to database
            if (isEmpty(cleanedData)) {
                // Update local chart to remove the section
                if (dentalChart[toothId]) {
                    if (section === 'periodontal') {
                        dentalChart[toothId][section] = JSON.parse(JSON.stringify(defaultPeriodontal));
                    } else {
                        dentalChart[toothId][section] = {};
                    }
                }
                return { success: true, message: 'No data to save' };
            }

            const response = await axiosAdmin.post(`patients/${patientId}/dental-chart-section`, {
                tooth_id: toothId,
                section: section,
                data: cleanedData
            });

            // Update local chart with saved data
            if (dentalChart[toothId]) {
                if (!dentalChart[toothId][section]) {
                    if (section === 'periodontal') {
                        dentalChart[toothId][section] = JSON.parse(JSON.stringify(defaultPeriodontal));
                    } else {
                        dentalChart[toothId][section] = {};
                    }
                }
                deepMerge(dentalChart[toothId][section], cleanedData);
            }

            return response.data;
        } catch (error) {
            console.error('Error saving dental chart section:', error);
            throw error;
        } finally {
            saving.value = false;
        }
    };

    /**
     * Get tooth data by ID
     */
    const getToothData = (toothId) => {
        return dentalChart[toothId] || defaultTeeth[toothId];
    };

    /**
     * Update tooth data locally (for immediate UI feedback)
     */
    const updateToothData = (toothId, section, data) => {
        if (dentalChart[toothId]) {
            if (!dentalChart[toothId][section]) {
                // Initialize section with default structure if it doesn't exist
                if (section === 'periodontal') {
                    dentalChart[toothId][section] = JSON.parse(JSON.stringify(defaultPeriodontal));
                } else {
                    dentalChart[toothId][section] = {};
                }
            }
            deepMerge(dentalChart[toothId][section], data);
        }
    };

    /**
     * Check if tooth has any active conditions
     */
    const hasActiveConditions = (toothId) => {
        const tooth = dentalChart[toothId];
        if (!tooth) return false;

        // Check if tooth is missing
        if (tooth.conditions?.missing) return true;

        // Check pathology
        if (tooth.pathology && Object.keys(tooth.pathology).length > 0) {
            const pathologyData = tooth.pathology;
            for (const key in pathologyData) {
                if (key !== 'selectedType' && pathologyData[key] && typeof pathologyData[key] === 'object') {
                    if (Object.keys(pathologyData[key]).length > 0) return true;
                }
            }
        }

        // Check restoration
        if (tooth.restoration && Object.keys(tooth.restoration).length > 0) {
            const restorationData = tooth.restoration;
            for (const key in restorationData) {
                if (key !== 'selectedType' && restorationData[key] && typeof restorationData[key] === 'object') {
                    if (Object.keys(restorationData[key]).length > 0) return true;
                }
            }
        }

        // Check tests
        if (tooth.tests && Object.keys(tooth.tests).length > 0) {
            for (const testKey in tooth.tests) {
                if (tooth.tests[testKey] !== null && tooth.tests[testKey] !== undefined && tooth.tests[testKey] !== '') {
                    return true;
                }
            }
        }

        // Check periodontal
        if (tooth.periodontal && Object.keys(tooth.periodontal).length > 0) {
            for (const position in tooth.periodontal) {
                const positionData = tooth.periodontal[position];
                if (positionData && typeof positionData === 'object') {
                    for (const measurement in positionData) {
                        if (positionData[measurement] !== null && positionData[measurement] !== undefined && positionData[measurement] !== 0) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    };

    /**
     * Get all teeth with active conditions
     */
    const getTeethWithConditions = () => {
        const teethWithConditions = [];
        for (const toothId in dentalChart) {
            if (hasActiveConditions(toothId)) {
                teethWithConditions.push(dentalChart[toothId]);
            }
        }
        return teethWithConditions;
    };

    /**
     * Reset a tooth to default state
     */
    const resetTooth = (toothId) => {
        if (dentalChart[toothId]) {
            Object.assign(dentalChart[toothId], {
                id: toothId,
                conditions: {},
                pathology: {},
                restoration: {},
                periodontal: JSON.parse(JSON.stringify(defaultPeriodontal)),
                tests: {}
            });
        }
    };

    /**
     * Reset entire dental chart
     */
    const resetDentalChart = () => {
        Object.assign(dentalChart, JSON.parse(JSON.stringify(defaultTeeth)));
    };

    return {
        dentalChart,
        defaultTeeth,
        defaultPeriodontal,
        defaultPeriodontalPosition,
        loading,
        saving,
        loadDentalChart,
        saveDentalChartSection,
        getToothData,
        updateToothData,
        hasActiveConditions,
        getTeethWithConditions,
        resetTooth,
        resetDentalChart,
        deepMerge,
        cleanData,
        isEmpty,
        hasPeriodontalData
    };
}
