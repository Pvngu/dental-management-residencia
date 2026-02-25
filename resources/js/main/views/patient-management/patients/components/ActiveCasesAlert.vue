<template>
    <div
        v-if="activeCases.length > 0 && !loading"
        class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4 mb-4"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <AlertOutlined class="text-red-500 text-xl" />
                <div>
                    <h3 class="font-semibold text-red-800">
                        {{ $t("open_cases.active_cases_alert") || "Active Cases Alert" }}
                    </h3>
                    <p class="text-red-600 text-sm">
                        {{ 
                            $t("open_cases.patient_has_active_cases", { count: activeCases.length }) || 
                            `This patient has ${activeCases.length} active case${activeCases.length > 1 ? 's' : ''} that need attention.`
                        }}
                    </p>
                </div>
            </div>
            <a-button
                type="primary"
                danger
                @click="$emit('viewCases')"
                class="flex items-center gap-2"
            >
                {{ $t("open_cases.view_all_cases") || "View All Cases" }}
                <a-badge
                    :count="activeCases.length"
                    :number-style="{
                        backgroundColor: '#fff',
                        color: '#dc2626',
                        boxShadow: '0 0 0 1px #dc2626 inset',
                        fontSize: '12px'
                    }"
                />
            </a-button>
        </div>
        
        <!-- Show critical/high priority cases inline -->
        <div
            v-if="hasCriticalCases"
            class="mt-3 pt-3 border-t border-red-200"
        >
            <p class="text-red-700 font-medium text-sm mb-2">
                {{ $t("open_cases.high_priority_cases") || "High Priority Cases:" }}
            </p>
            <div
                v-for="criticalCase in criticalCases"
                :key="criticalCase.xid"
                class="bg-red-100 rounded p-2 mb-2 last:mb-0"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <a-tag
                                :color="criticalCase.priority === 'critical' ? 'red' : 'orange'"
                                class="uppercase text-xs font-bold"
                            >
                                {{ criticalCase.priority }}
                            </a-tag>
                            <span class="font-medium text-sm text-red-800">
                                {{ criticalCase.title }}
                            </span>
                        </div>
                        <p class="text-red-700 text-xs leading-relaxed">
                            {{ criticalCase.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { AlertOutlined } from '@ant-design/icons-vue';

const props = defineProps({
    activeCases: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    }
});

defineEmits(['viewCases']);

const { t } = useI18n();

// Computed properties for critical cases
const criticalCases = computed(() => {
    return props.activeCases.filter(c => 
        (c.priority === 'critical' || c.priority === 'high') &&
        (c.status === 'open' || c.status === 'in_progress')
    );
});

const hasCriticalCases = computed(() => {
    return criticalCases.value.length > 0;
});
</script>