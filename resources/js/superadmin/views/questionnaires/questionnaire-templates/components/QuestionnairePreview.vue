<template>
    <a-modal
        class="questionnaire-preview-modal"
        :title="$t('questionnaire_templates.preview') || 'Preview'"
        :open="open"
        :width="900"
        :footer="null"
        @cancel="$emit('close')"
    >
        <div class="preview-container">
            <div class="preview-header">
                <h2 class="preview-title">{{ template.name || 'Untitled Template' }}</h2>
                <p v-if="template.description" class="preview-description">{{ template.description }}</p>
                <div class="preview-meta" v-if="template.instructions">
                    <a-tag color="blue">
                        <template #icon><ClockCircleOutlined /></template>
                        {{ template.instructions }}
                    </a-tag>
                </div>
            </div>

            <a-divider />

            <div class="preview-content">
                <div v-if="(template.sections || []).length">
                    <div class="preview-section" v-for="(section, sIdx) in template.sections" :key="section.uid">
                        <div class="section-preview-header">
                            <h3 class="section-preview-title">{{ sIdx + 1 }}. {{ section.name }}</h3>
                            <p v-if="section.description" class="section-preview-description">{{ section.description }}</p>
                        </div>
                        <div class="section-questions">
                            <div class="question-preview-item" v-for="(q, qIdx) in section.questions" :key="q.uid">
                                <div class="question-number">{{ qIdx + 1 }}</div>
                                <div class="question-content">
                                    <QuestionPreview :question="q" :disabled="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="preview-empty">
                    <a-empty :image="Empty.PRESENTED_IMAGE_SIMPLE">
                        <template #description>{{ $t('questionnaire_templates.no_sections_yet') || 'No sections yet' }}</template>
                    </a-empty>
                </div>
            </div>

            <div class="preview-footer">
                <a-row justify="space-between" align="middle">
                    <a-col>
                        <a-space>
                            <a-statistic :title="$t('questionnaire_templates.sections') || 'Sections'" :value="(template.sections || []).length" :value-style="{ color: '#1890ff' }" />
                            <a-statistic :title="$t('questionnaire_templates.questions') || 'Questions'" :value="totalQuestions" :value-style="{ color: '#52c41a' }" />
                            <a-statistic v-if="requiredQuestions" :title="$t('questionnaire_templates.required') || 'Required'" :value="requiredQuestions" :value-style="{ color: '#ff4d4f' }" />
                        </a-space>
                    </a-col>
                    <a-col>
                        <a-button type="primary" @click="$emit('close')">{{ $t('common.close') || 'Close' }}</a-button>
                    </a-col>
                </a-row>
            </div>
        </div>
    </a-modal>
</template>

<script>
import { computed } from 'vue';
import { Empty } from 'ant-design-vue';
import { ClockCircleOutlined } from '@ant-design/icons-vue';
import QuestionPreview from './QuestionPreview.vue';

export default {
    name: 'QuestionnairePreview',
    components: {
                QuestionPreview,
                ClockCircleOutlined,
    },
    props: {
                open: { type: Boolean, default: false },
                template: { type: Object, required: true },
    },
    emits: ['close'],
    setup(props) {
                const totalQuestions = computed(() =>
                    (props.template.sections || []).reduce((sum, s) => sum + (s.questions ? s.questions.length : 0), 0)
                );
                const requiredQuestions = computed(() =>
                    (props.template.sections || []).reduce((sum, s) => sum + (s.questions ? s.questions.filter(q => q.is_required).length : 0), 0)
                );
                return { Empty, totalQuestions, requiredQuestions };
    },
};
</script>

<style scoped>
.questionnaire-preview-modal :deep(.ant-modal-body) {
    max-height: 70vh;
    overflow-y: auto;
    padding: 0;
}

.preview-container {
    padding: 24px;
}

.preview-header {
    text-align: center;
    margin-bottom: 24px;
}

.preview-title {
    font-size: 24px;
    font-weight: 600;
    color: #262626;
    margin-bottom: 8px;
}

.preview-description {
    font-size: 16px;
    color: #8c8c8c;
    margin-bottom: 16px;
    line-height: 1.5;
}

.preview-meta {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 16px;
}

.preview-content {
    margin-bottom: 24px;
}

.preview-section {
    margin-bottom: 32px;
    padding: 20px;
    background: #fafafa;
    border-radius: 8px;
    border: 1px solid #f0f0f0;
}

.section-preview-header {
    margin-bottom: 20px;
}

.section-preview-title {
    font-size: 18px;
    font-weight: 600;
    color: #262626;
    margin-bottom: 8px;
}

.section-preview-description {
    font-size: 14px;
    color: #8c8c8c;
    margin-bottom: 0;
    line-height: 1.4;
}

.section-questions {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.question-preview-item {
    display: flex;
    gap: 16px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e8e8e8;
}

.question-number {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1890ff;
    color: white;
    border-radius: 50%;
    font-weight: 600;
    font-size: 14px;
}

.question-content {
    flex: 1;
}

.preview-empty {
    padding: 60px 20px;
    text-align: center;
}

.preview-footer {
    padding-top: 24px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .questionnaire-preview-modal :deep(.ant-modal) {
        width: 95% !important;
        margin: 10px;
    }
    
    .preview-container {
        padding: 16px;
    }
    
    .question-preview-item {
        flex-direction: column;
        gap: 12px;
    }
    
    .question-number {
        align-self: flex-start;
    }
}
</style>
