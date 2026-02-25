<template>
    <a-modal
        :open="visible"
        :title="editMode ? $t('dental_treat_monitor.edit') : $t('dental_treat_monitor.add')"
        :width="800"
        @cancel="onClose"
        :footer="null"
    >
        <a-form
            :model="formData"
            layout="vertical"
            @finish="onSubmit"
        >
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('dental_treat_monitor.content')"
                        name="content"
                        :help="rules.content ? rules.content.message : null"
                        :validateStatus="rules.content ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.content"
                            :placeholder="$t('common.placeholder_default_text', [$t('dental_treat_monitor.content')])"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('dental_chart.tooth_number')"
                        name="tooth_number"
                        :help="rules.tooth_number ? rules.tooth_number.message : null"
                        :validateStatus="rules.tooth_number ? 'error' : null"
                    >
                        <a-select
                            v-model:value="formData.tooth_number"
                            :placeholder="$t('common.select_default_text', [$t('dental_chart.tooth_number')])"
                            :allowClear="true"
                            style="width: 100%"
                            show-search
                            optionFilterProp="title"
                        >
                            <a-select-option
                                v-for="tooth in toothNumbers"
                                :key="tooth"
                                :value="tooth.toString()"
                                :title="tooth.toString()"
                            >
                                {{ $t('dental_chart.tooth') }} {{ tooth }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('dental_treat_monitor.type')"
                        name="type"
                        :help="rules.type ? rules.type.message : null"
                        :validateStatus="rules.type ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.type"
                            :placeholder="$t('common.select_default_text', [$t('dental_treat_monitor.type')])"
                            style="width: 100%"
                        >
                            <a-select-option value="low">
                                {{ $t('common.low') }}
                            </a-select-option>
                            <a-select-option value="medium">
                                {{ $t('common.medium') }}
                            </a-select-option>
                            <a-select-option value="high">
                                {{ $t('common.high') }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('dental_treat_monitor.status')"
                        name="status"
                        :help="rules.status ? rules.status.message : null"
                        :validateStatus="rules.status ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.status"
                            :placeholder="$t('common.select_default_text', [$t('dental_treat_monitor.status')])"
                            style="width: 100%"
                        >
                            <a-select-option value="pending">
                                {{ $t('common.pending') }}
                            </a-select-option>
                            <a-select-option value="resolved">
                                {{ $t('common.resolved') }}
                            </a-select-option>
                            <a-select-option value="deleted">
                                {{ $t('common.deleted') }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('dental_treat_monitor.comment')"
                        name="comment"
                        :help="rules.comment ? rules.comment.message : null"
                        :validateStatus="rules.comment ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.comment"
                            :placeholder="$t('common.placeholder_default_text', [$t('dental_treat_monitor.comment')])"
                            :auto-size="{ minRows: 4, maxRows: 6 }"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <div class="flex justify-end gap-2 mt-6">
                <a-button @click="onClose">
                    {{ $t('common.cancel') }}
                </a-button>
                <a-button
                    type="primary"
                    html-type="submit"
                    :loading="loading"
                >
                    <template #icon>
                        <SaveOutlined />
                    </template>
                    {{ editMode ? $t('common.update') : $t('common.create') }}
                </a-button>
            </div>
        </a-form>
    </a-modal>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { SaveOutlined } from '@ant-design/icons-vue';
import { useI18n } from 'vue-i18n';
import apiAdmin from '../../../../../../common/composable/apiAdmin';

export default {
    name: 'TreatmentAddEdit',
    components: {
        SaveOutlined
    },
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        formData: {
            type: Object,
            required: true
        },
        editMode: {
            type: Boolean,
            default: false
        },
        patientId: {
            type: String,
            required: true
        }
    },
    emits: ['closed', 'success'],
    setup(props, { emit }) {
        const { t } = useI18n();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

        // All possible tooth numbers
        const toothNumbers = [
            18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28,
            38, 37, 36, 35, 34, 33, 32, 31, 41, 42, 43, 44, 45, 46, 47, 48
        ];

        const onSubmit = () => {
            const url = props.editMode
                ? `patients/${props.patientId}/dental-chart/treat-monitor/${props.formData.xid}`
                : `patients/${props.patientId}/dental-chart/treat-monitor`;

            addEditRequestAdmin({
                url: url,
                data: props.formData,
                successMessage: props.editMode 
                    ? t('dental_treat_monitor.updated')
                    : t('dental_treat_monitor.created'),
                success: () => {
                    emit('success');
                }
            });
        };

        const onClose = () => {
            rules.value = {};
            emit('closed');
        };

        return {
            loading,
            rules,
            toothNumbers,
            onSubmit,
            onClose
        };
    }
};
</script>