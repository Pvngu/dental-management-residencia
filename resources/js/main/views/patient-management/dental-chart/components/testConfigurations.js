import { 
    BulbOutlined, 
    FireOutlined, 
    SoundOutlined, 
    ExperimentOutlined, 
    ThunderboltOutlined 
} from '@ant-design/icons-vue';

/**
 * Dental test configurations object
 * This centralized configuration makes it easy to:
 * - Add new test types
 * - Modify existing test options
 * - Maintain consistency across components
 * - Support internationalization
 */
export const createTestConfigurations = (t) => ({
    cold: {
        type: 'hierarchical',
        title: t('dental_chart.cold_test'),
        icon: BulbOutlined,
        apiKey: 'coldTest',
        options: [
            { 
                value: 'positive', 
                label: t('dental_chart.positive'), 
                class: 'success',
                subOptions: [
                    { value: 'within_limits', label: t('dental_chart.within_limits'), class: 'success' },
                    { value: 'unpleasant', label: t('dental_chart.unpleasant'), class: 'warning' },
                    { value: 'pain_stimulus', label: t('dental_chart.pain_stimulus'), class: 'danger' },
                    { value: 'pain_lingering', label: t('dental_chart.pain_lingering'), class: 'danger' }
                ]
            },
            { value: 'uncertain', label: t('dental_chart.uncertain'), class: 'warning' },
            { value: 'negative', label: t('dental_chart.negative'), class: 'danger' },
            { 
                value: 'not_applicable', 
                label: t('dental_chart.not_applicable'), 
                class: 'info',
                subOptions: [
                    { value: 'existing_root_canal_treatment', label: t('dental_chart.existing_root_canal_treatment'), class: 'info' },
                    { value: 'previously_initiated_therapy', label: t('dental_chart.previously_initiated_therapy'), class: 'warning' }
                ]
            }
        ]
    },
    heat: {
        type: 'hierarchical',
        title: t('dental_chart.heat_test'),
        icon: FireOutlined,
        apiKey: 'heatTest',
        options: [
            { 
                value: 'positive', 
                label: t('dental_chart.positive'), 
                class: 'success',
                subOptions: [
                    { value: 'within_limits', label: t('dental_chart.within_limits'), class: 'success' },
                    { value: 'unpleasant', label: t('dental_chart.unpleasant'), class: 'warning' },
                    { value: 'pain_stimulus', label: t('dental_chart.pain_stimulus'), class: 'danger' },
                    { value: 'pain_lingering', label: t('dental_chart.pain_lingering'), class: 'danger' }
                ]
            },
            { value: 'uncertain', label: t('dental_chart.uncertain'), class: 'warning' },
            { value: 'negative', label: t('dental_chart.negative'), class: 'danger' },
            { value: 'not_applicable', label: t('dental_chart.not_applicable'), class: 'info' }
        ]
    },
    percussion: {
        type: 'buttons',
        title: t('dental_chart.percussion_test'),
        icon: SoundOutlined,
        apiKey: 'percussionTest',
        options: [
            { value: 'not_painful', label: t('dental_chart.not_painful'), class: 'success' },
            { value: 'unpleasant', label: t('dental_chart.unpleasant'), class: 'warning' },
            { value: 'painful', label: t('dental_chart.painful'), class: 'danger' }
        ]
    },
    palpation: {
        type: 'buttons',
        title: t('dental_chart.palpation_test'),
        icon: ExperimentOutlined,
        apiKey: 'palpationTest',
        options: [
            { value: 'not_painful', label: t('dental_chart.not_painful'), class: 'success' },
            { value: 'unpleasant', label: t('dental_chart.unpleasant'), class: 'warning' },
            { value: 'painful', label: t('dental_chart.painful'), class: 'danger' }
        ]
    },
    electricity: {
        type: 'numeric',
        title: t('dental_chart.electricity_test'),
        icon: ThunderboltOutlined,
        apiKey: 'electricityTest',
        range: 10, // Creates numbers 1-10
        layout: 'grid', // grid or inline
        gridCols: 5 // for grid layout
    }
});

/**
 * Test types for display in lists/menus
 */
export const createTestTypes = (t) => [
    {
        key: 'cold',
        label: t('dental_chart.cold'),
        icon: BulbOutlined
    },
    {
        key: 'percussion',
        label: t('dental_chart.percussion'),
        icon: SoundOutlined
    },
    {
        key: 'palpation',
        label: t('dental_chart.palpation'),
        icon: ExperimentOutlined
    },
    {
        key: 'heat',
        label: t('dental_chart.heat'),
        icon: FireOutlined
    },
    {
        key: 'electricity',
        label: t('dental_chart.electricity'),
        icon: ThunderboltOutlined
    }
];

/**
 * Helper function to get test value from testData
 */
export const getTestValue = (testType, testData) => {
    const testKeyMap = {
        'cold': 'coldTest',
        'heat': 'heatTest',
        'percussion': 'percussionTest',
        'palpation': 'palpationTest',
        'electricity': 'electricityTest'
    };
    return testData[testKeyMap[testType]];
};

/**
 * Helper function to get test key for API calls
 */
export const getTestKey = (testType) => {
    const testKeyMap = {
        'cold': 'coldTest',
        'heat': 'heatTest',
        'percussion': 'percussionTest',
        'palpation': 'palpationTest',
        'electricity': 'electricityTest'
    };
    return testKeyMap[testType];
};
