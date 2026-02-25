<template>
    {{ template }}
    <div class="questionnaire-builder">
        <a-page-header :title="$t('questionnaire_templates.builder_title') || 'Questionnaire Builder'">
            <template #extra>
                <a-space>
                    <a-button type="primary" @click="onSave" :icon="h(SaveOutlined)">{{ $t('common.save') || 'Save' }}</a-button>
                </a-space>
            </template>
        </a-page-header>

        <div class="builder-content">
            <a-row :gutter="16">
                <a-col :xs="24" :md="24">
                    <a-card class="sections-container" :title="$t('questionnaire_templates.sections') || 'Sections'">
                        <draggable
                            v-model="template.sections"
                            item-key="uid"
                            handle=".section-handle"
                            ghost-class="sortable-ghost"
                            chosen-class="sortable-chosen"
                            drag-class="sortable-drag"
                            @end="reindexSections"
                        >
                            <template #item="{ element: section, index: sIdx }">
                                <a-card class="section-card" :key="section.uid" size="small">
                                    <div class="section-header">
                                        <a-space align="center" class="w-100">
                                            <holder-outlined class="section-handle" />
                                            <a-input
                                                v-model:value="section.name"
                                                class="section-name-input"
                                                :placeholder="$t('questionnaire_templates.section_name') || 'Section name'"
                                                @change="autoCode(section, 'SEC')"
                                            />
                                            <a-input
                                                v-model:value="section.code"
                                                style="max-width: 180px"
                                                :placeholder="$t('common.code') || 'Code'"
                                            />
                                            <a-switch v-model:checked="section.is_required" :checked-children="$t('common.required') || 'Required'" :un-checked-children="$t('common.optional') || 'Optional'" />
                                            <a-popconfirm :title="$t('common.delete_confirm') || 'Delete this section?'" @confirm="removeSection(sIdx)">
                                                <a-button type="text" danger :icon="h(DeleteOutlined)" />
                                            </a-popconfirm>
                                        </a-space>
                                        <a-textarea v-model:value="section.description" class="section-description" :rows="2" :placeholder="$t('common.description') || 'Description'" />
                                    </div>

                                    <div class="questions-container">
                                        <draggable
                                            v-model="section.questions"
                                            item-key="uid"
                                            handle=".question-handle"
                                            ghost-class="sortable-ghost"
                                            chosen-class="sortable-chosen"
                                            drag-class="sortable-drag"
                                            @end="reindexQuestions(section)"
                                        >
                                            <template #item="{ element: q, index: qIdx }">
                                                <question-builder
                                                    :key="q.uid"
                                                    :question="q"
                                                    :index="qIdx + 1"
                                                    :previous-questions="section.questions.slice(0, qIdx).map((qq, idx) => ({ code: qq.code, prompt: qq.prompt, position: idx + 1 }))"
                                                    @update-question="(updated) => updateQuestion(section, q.uid, updated)"
                                                    @delete-question="() => removeQuestion(section, q.uid)"
                                                />
                                            </template>
                                        </draggable>

                                        <div class="add-question-area">
                                            <a-button type="dashed" block :icon="h(PlusOutlined)" @click="addQuestion(section)">
                                                {{ $t('questionnaire_templates.add_question') || 'Add question' }}
                                            </a-button>
                                        </div>
                                    </div>
                                </a-card>
                            </template>
                        </draggable>

                        <div class="add-section-area">
                            <a-button type="dashed" block :icon="h(PlusOutlined)" @click="addSection">
                                {{ $t('questionnaire_templates.add_section') || 'Add section' }}
                            </a-button>
                        </div>
                    </a-card>
                </a-col>
            </a-row>
        </div>
    </div>
</template>

<script>
import { reactive, h } from 'vue';
import { message } from 'ant-design-vue';
import draggable from 'vuedraggable';
import QuestionBuilder from './components/QuestionBuilder.vue';
import {
    PlusOutlined,
    DeleteOutlined,
    SaveOutlined,
    HolderOutlined,
} from '@ant-design/icons-vue';

export default {
    name: 'QuestionnaireBuilder',
    components: {
        QuestionBuilder,
        draggable,
        PlusOutlined,
        DeleteOutlined,
        SaveOutlined,
        HolderOutlined,
    },
    setup() {
        const uid = () => Math.random().toString(36).slice(2, 10);

        const template = reactive({
            id: null,
            code: 'TEMPLATE_1',
            name: '',
            description: '',
            instructions: '',
            sections: [
                {
                    uid: uid(),
                    code: 'SEC_1',
                    name: 'Section 1',
                    description: '',
                    instructions: '',
                    position: 1,
                    is_required: true,
                    skip_logic: null,
                    questions: [
                        {
                            uid: uid(),
                            code: 'Q_1',
                            prompt: 'Your question here',
                            response_type: 'TEXT',
                            position: 1,
                            is_required: true,
                            weight: null,
                            skip_logic: null,
                            validation_rules: {},
                            metadata: {},
                            // help_text: '',
                            options: [],
                        },
                    ],
                },
            ],
        });

        

        const reindexSections = () => {
            template.sections.forEach((s, i) => (s.position = i + 1));
        };

        const reindexQuestions = (section) => {
            section.questions.forEach((q, i) => (q.position = i + 1));
        };

        const slugFrom = (val) =>
            (val || '')
                .toString()
                .trim()
                .toUpperCase()
                .replace(/[^A-Z0-9]+/g, '_')
                .replace(/^_|_$/g, '')
                .slice(0, 50);

        const autoCode = (entity, prefix) => {
            if (!entity.code || entity.code === '' || entity.code.startsWith(prefix)) {
                const base = slugFrom(entity.name) || `${prefix}_${Date.now()}`;
                entity.code = base;
            }
        };

        const addSection = () => {
            const nextIndex = template.sections.length + 1;
            template.sections.push({
                uid: uid(),
                code: `SEC_${nextIndex}`,
                name: `Section ${nextIndex}`,
                description: '',
                instructions: '',
                position: nextIndex,
                is_required: true,
                skip_logic: null,
                questions: [],
            });
        };

        const removeSection = (index) => {
            template.sections.splice(index, 1);
            reindexSections();
        };

        const addQuestion = (section) => {
            const nextIndex = section.questions.length + 1;
            section.questions.push({
                uid: uid(),
                code: `Q_${nextIndex}`,
                prompt: `Question ${nextIndex}`,
                response_type: 'TEXT',
                position: nextIndex,
                is_required: true,
                weight: null,
                skip_logic: null,
                validation_rules: {},
                metadata: {},
                // help_text: '',
                options: [],
            });
        };

        const removeQuestion = (section, qUid) => {
            const idx = section.questions.findIndex((x) => x.uid === qUid);
            if (idx >= 0) {
                section.questions.splice(idx, 1);
                reindexQuestions(section);
            }
        };

        const updateQuestion = (section, qUid, updated) => {
            const idx = section.questions.findIndex((x) => x.uid === qUid);
            if (idx >= 0) {
                section.questions[idx] = { ...section.questions[idx], ...updated };
            }
        };

        

        const buildPayload = () => {
            reindexSections();
            template.sections.forEach(reindexQuestions);

            return {
                code: template.code || slugFrom(template.name) || 'TEMPLATE',
                name: template.name,
                description: template.description,
                instructions: template.instructions,
                sections: template.sections.map((s, si) => ({
                    code: s.code || `SEC_${si + 1}`,
                    name: s.name,
                    description: s.description,
                    instructions: s.instructions,
                    position: si + 1,
                    is_required: !!s.is_required,
                    skip_logic: s.skip_logic ?? null,
                    questions: (s.questions || []).map((q, qi) => ({
                        code: q.code || `Q_${qi + 1}`,
                        prompt: q.prompt,
                        response_type: q.response_type,
                        position: qi + 1,
                        is_required: !!q.is_required,
                        weight: q.weight ?? null,
                        skip_logic: q.skip_logic ?? null,
                        validation_rules: q.validation_rules || {},
                        metadata: q.metadata || {},
                        // help_text: q.help_text || '',
                        options: (q.options || []).map((o, oi) => ({
                            code: o.code || `OPT_${oi + 1}`,
                            label: o.label,
                            value_numeric: o.value_numeric ?? null,
                            position: oi + 1,
                            response_tags: o.response_tags || [],
                        })),
                    })),
                })),
            };
        };

        const onSave = () => {
            const payload = buildPayload();
            // Frontend-only: just log and notify for now
            // eslint-disable-next-line no-console
            console.log('[QuestionnaireBuilder] Prepared payload', payload);
            message.success('Template object prepared. Ready to send to backend.');
        };

        return {
            h,
            template,
            addSection,
            removeSection,
            addQuestion,
            removeQuestion,
            updateQuestion,
            reindexSections,
            reindexQuestions,
            autoCode,
            onSave,
            PlusOutlined,
            DeleteOutlined,
            SaveOutlined,
        };
    },
};
</script>

<style scoped>
.questionnaire-builder {
    min-height: 100vh;
    background-color: #f5f5f5;
}

.builder-content {
    padding: 20px;
}

.template-card {
    margin-bottom: 16px;
}

.question-types {
    max-height: 400px;
    overflow-y: auto;
}

.question-types-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.question-type-item {
    display: flex;
    align-items: center;
    padding: 12px;
    background: white;
    border: 1px solid #d9d9d9;
    border-radius: 6px;
    cursor: grab;
    transition: all 0.3s;
}

.question-type-item:hover {
    border-color: #1890ff;
    box-shadow: 0 2px 8px rgba(24, 144, 255, 0.2);
}

.question-type-item:active {
    cursor: grabbing;
}

.question-type-icon {
    margin-right: 8px;
    color: #1890ff;
}

.question-type-label {
    flex: 1;
    font-size: 14px;
}

.template-header {
    margin-bottom: 20px;
}

.template-name-input {
    margin-bottom: 12px;
}

.template-description-input {
    margin-bottom: 0;
}

.sections-container {
    min-height: 300px;
}

.sections-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.section-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 12px;
}

.section-wrapper {
    border: none;
}

.section-header {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 8px;
}

.section-handle {
    cursor: grab;
    color: #8c8c8c;
}

.section-handle:active {
    cursor: grabbing;
}

.section-name-input {
    font-weight: 600;
}

.section-name-input:focus {
    border-color: #1890ff;
}

.section-description {
    margin-top: 4px;
}

.questions-container {
    margin-top: 8px;
}

.questions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.add-question-area,
.add-section-area {
    margin-top: 12px;
}

.add-question-btn,
.add-section-btn {
    width: 100%;
}

/* Dragging styles */
.sortable-ghost {
    opacity: 0.6;
}

.sortable-chosen {
    background: #f5faff;
}

.sortable-drag {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}
</style>
