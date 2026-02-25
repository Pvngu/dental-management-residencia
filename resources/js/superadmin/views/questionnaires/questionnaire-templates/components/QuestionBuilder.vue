<template>
  <a-card class="question-builder-card" :class="{ 'is-editing': true }" size="small">
    <div class="question-header">
      <holder-outlined class="question-handle" />
      <span class="question-number">#{{ index }}</span>
      <component :is="typeIcon" class="question-type-icon" />
      <span class="question-type-label">{{ typeLabel }}</span>
      <a-space class="question-actions">
        <a-switch v-model:checked="local.is_required" :checked-children="$t('common.required') || 'Req'" :un-checked-children="$t('common.optional') || 'Opt'" @change="emitUpdate" />
        <a-popconfirm :title="$t('common.delete_confirm') || 'Delete this question?'" @confirm="$emit('delete-question')">
          <a-button type="text" danger :icon="h(DeleteOutlined)" />
        </a-popconfirm>
      </a-space>
    </div>

    <div class="question-content">
      <div class="question-prompt">
        <a-input
          v-model:value="local.prompt"
          class="prompt-input"
          :placeholder="$t('questionnaire_templates.question_prompt') || 'Question text'"
          @change="emitUpdate"
        />
      </div>

      <a-row :gutter="12" class="question-settings">
        <a-col :span="10">
          <a-select v-model:value="local.response_type" style="width: 100%" @change="onTypeChange">
            <a-select-option value="TEXT">{{ $t('questionnaire_templates.text_input') || 'Text' }}</a-select-option>
            <a-select-option value="SINGLE_CHOICE">{{ $t('questionnaire_templates.single_choice') || 'Single choice' }}</a-select-option>
            <a-select-option value="MULTI_CHOICE">{{ $t('questionnaire_templates.multiple_choice') || 'Multiple choice' }}</a-select-option>
            <a-select-option value="DATE">{{ $t('questionnaire_templates.date') || 'Date' }}</a-select-option>
            <a-select-option value="NUMERIC">{{ $t('questionnaire_templates.numeric') || 'Numeric' }}</a-select-option>
            <a-select-option value="BOOL">{{ $t('questionnaire_templates.yes_no') || 'Yes/No' }}</a-select-option>
            <a-select-option value="FILE">{{ $t('questionnaire_templates.file_upload') || 'File' }}</a-select-option>
            <a-select-option value="INFO">{{ $t('questionnaire_templates.info_text') || 'Info' }}</a-select-option>
          </a-select>
        </a-col>
        <a-col :span="8">
          <a-input-number v-model:value="local.weight" :min="0" :max="100" :step="0.5" style="width: 100%" :placeholder="$t('questionnaire_templates.weight') || 'Weight'" @change="emitUpdate" />
        </a-col>
        <a-col :span="6">
          <a-input v-model:value="local.code" :placeholder="$t('common.code') || 'Code'" @change="emitUpdate" />
        </a-col>
      </a-row>

      <div class="advanced-row">
        <a-space>
          <a-button size="small" @click="skipModalOpen = true">{{ $t('questionnaire_templates.skip_logic') || 'Skip logic' }}</a-button>
          <a-button size="small" @click="validationModalOpen = true">{{ $t('questionnaire_templates.validation_rules') || 'Validation rules' }}</a-button>
        </a-space>
      </div>

      <!-- Skip Logic Modal -->
      <a-modal v-model:open="skipModalOpen" :title="$t('questionnaire_templates.skip_logic') || 'Skip logic'" width="880px" :footer="null">
        <a-alert
          type="info"
          :message="$t('questionnaire_templates.skip_logic_help') || 'Control when to show or hide this question based on earlier answers.'"
          show-icon
          style="margin-bottom: 12px"
        />
        <a-row :gutter="12">
          <a-col :xs="24" :md="12" v-for="group in logicGroups" :key="group.key" style="margin-bottom: 12px">
            <a-card size="small" :title="group.title">
              <div v-if="(local.skip_logic?.[group.key] || []).length === 0" style="margin-bottom: 6px">
                <a-typography-text type="secondary">{{ $t('common.none') || 'No conditions' }}</a-typography-text>
              </div>
              <div v-for="(cond, ci) in (local.skip_logic?.[group.key] || [])" :key="ci" class="logic-row">
                <a-row :gutter="8" align="middle">
                  <a-col :span="10">
                    <a-select
                      v-model:value="cond.question_code"
                      show-search
                      optionFilterProp="title"
                      :placeholder="$t('questionnaire_templates.logic_question_code') || 'Question code'"
                      style="width: 100%"
                      @change="emitUpdate"
                    >
                      <a-select-option
                        v-for="pq in previousQuestionOptions"
                        :key="pq.code"
                        :value="pq.code"
                        :title="pq.label"
                      >
                        {{ pq.label }}
                      </a-select-option>
                    </a-select>
                  </a-col>
                  <a-col :span="8">
                    <a-select
                      :value="getConditionType(cond)"
                      style="width: 100%"
                      @change="(type) => changeConditionType(group.key, ci, type)"
                    >
                      <a-select-option value="option_code">option_code</a-select-option>
                      <a-select-option value="equals_bool">equals_bool</a-select-option>
                      <a-select-option value="equals_text">equals_text</a-select-option>
                      <a-select-option value="greater_than">greater_than</a-select-option>
                      <a-select-option value="less_than">less_than</a-select-option>
                    </a-select>
                  </a-col>
                  <a-col :span="4">
                    <template v-if="getConditionType(cond) === 'equals_bool'">
                      <a-switch :checked="!!cond.equals_bool" @change="(val) => { cond.equals_bool = !!val; emitUpdate(); }" />
                    </template>
                    <template v-else-if="getConditionType(cond) === 'greater_than' || getConditionType(cond) === 'less_than'">
                      <a-input-number
                        v-model:value="cond[getConditionType(cond)]"
                        style="width: 100%"
                        :placeholder="getConditionType(cond)"
                        @change="emitUpdate"
                      />
                    </template>
                    <template v-else>
                      <a-input
                        v-model:value="cond[getConditionType(cond)]"
                        :placeholder="getConditionType(cond)"
                        @change="emitUpdate"
                      />
                    </template>
                  </a-col>
                  <a-col :span="2" style="text-align: right">
                    <a-button type="text" danger :icon="h(DeleteOutlined)" @click="removeLogic(group.key, ci)" />
                  </a-col>
                </a-row>
              </div>
              <a-button size="small" type="dashed" :icon="h(PlusOutlined)" @click="addLogic(group.key)">
                {{ $t('common.add') || 'Add' }}
              </a-button>
            </a-card>
          </a-col>
        </a-row>
        <div style="text-align: right; margin-top: 8px">
          <a-button type="primary" @click="skipModalOpen = false">{{ $t('common.done') || 'Done' }}</a-button>
        </div>
      </a-modal>

      <!-- Validation Rules Modal -->
      <a-modal v-model:open="validationModalOpen" :title="$t('questionnaire_templates.validation_rules') || 'Validation rules'" width="880px" :footer="null">
        <a-row :gutter="12">
          <a-col :xs="24" :md="12">
            <a-card size="small" :title="$t('questionnaire_templates.common_rules') || 'Common'">
              <a-row :gutter="8">
                <a-col :span="12">
                  <a-form-item :label="$t('questionnaire_templates.required_if_field') || 'required_if.field'">
                    <a-input :value="local.validation_rules.required_if?.field" @change="(e) => setRequiredIfField(e.target.value)" />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item :label="$t('questionnaire_templates.required_if_value') || 'required_if.value'">
                    <template v-if="requiredIfValueType === 'bool'">
                      <a-switch :checked="!!(local.validation_rules.required_if?.value)" @change="(v) => setRequiredIfValue(v)" />
                    </template>
                    <template v-else-if="requiredIfValueType === 'number'">
                      <a-input-number :value="local.validation_rules.required_if?.value" style="width: 100%" @change="(v) => setRequiredIfValue(v)" />
                    </template>
                    <template v-else>
                      <a-input :value="local.validation_rules.required_if?.value" @change="(e) => setRequiredIfValue(e.target.value)" />
                    </template>
                    <div style="margin-top: 6px">
                      <a-segmented
                        v-model:value="requiredIfValueType"
                        :options="[
                          { label: 'text', value: 'text' },
                          { label: 'number', value: 'number' },
                          { label: 'bool', value: 'bool' }
                        ]"
                      />
                    </div>
                  </a-form-item>
                </a-col>
              </a-row>
            </a-card>
          </a-col>

          <a-col :xs="24" :md="12" v-if="local.response_type === 'TEXT'">
            <a-card size="small" :title="$t('questionnaire_templates.text_rules') || 'Text rules'">
              <a-row :gutter="8">
                <a-col :span="12">
                  <a-form-item label="min_length">
                    <a-input-number v-model:value="local.validation_rules.min_length" style="width: 100%" :min="0" @change="emitUpdate" />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item label="max_length">
                    <a-input-number v-model:value="local.validation_rules.max_length" style="width: 100%" :min="0" @change="emitUpdate" />
                  </a-form-item>
                </a-col>
                <a-col :span="24">
                  <a-form-item label="pattern">
                    <a-input v-model:value="local.validation_rules.pattern" @change="emitUpdate" />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-card>
          </a-col>

          <a-col :xs="24" :md="12" v-if="local.response_type === 'NUMERIC'">
            <a-card size="small" :title="$t('questionnaire_templates.numeric_rules') || 'Numeric rules'">
              <a-row :gutter="8">
                <a-col :span="8">
                  <a-form-item label="min">
                    <a-input-number v-model:value="local.validation_rules.min" style="width: 100%" @change="emitUpdate" />
                  </a-form-item>
                </a-col>
                <a-col :span="8">
                  <a-form-item label="max">
                    <a-input-number v-model:value="local.validation_rules.max" style="width: 100%" @change="emitUpdate" />
                  </a-form-item>
                </a-col>
                <a-col :span="8">
                  <a-form-item label="integer_only">
                    <a-switch :checked="!!local.validation_rules.integer_only" @change="(v) => { local.validation_rules.integer_only = !!v; emitUpdate(); }" />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-card>
          </a-col>
        </a-row>
        <div style="text-align: right; margin-top: 8px">
          <a-button type="primary" @click="validationModalOpen = false">{{ $t('common.done') || 'Done' }}</a-button>
        </div>
      </a-modal>

      <template v-if="isChoice">
        <div class="question-options">
          <div class="options-header">
            <span>{{ $t('questionnaire_templates.options') || 'Options' }}</span>
            <a-button size="small" type="dashed" :icon="h(PlusOutlined)" @click="addOption">{{ $t('common.add') || 'Add' }}</a-button>
          </div>
          <draggable v-model="local.options" item-key="uid" handle=".option-handle" ghost-class="sortable-ghost" chosen-class="sortable-chosen" drag-class="sortable-drag" @end="reindexOptions">
            <template #item="{ element: opt, index: oi }">
              <div class="option-item">
                <holder-outlined class="option-handle" />
                <a-input v-model:value="opt.label" class="option-input" :placeholder="$t('common.label') || 'Label'" @change="emitUpdate" />
                <a-input-number v-model:value="opt.value_numeric" :placeholder="$t('questionnaire_templates.value') || 'Value'" :step="1" style="width: 120px" @change="emitUpdate" />
                <a-popconfirm :title="$t('common.delete_confirm') || 'Remove this option?'" @confirm="removeOption(oi)">
                  <a-button type="text" danger class="option-delete" :icon="h(DeleteOutlined)" />
                </a-popconfirm>
              </div>
            </template>
          </draggable>
        </div>
      </template>
    </div>


  </a-card>
</template>

<script>
import { reactive, ref, computed, watch, h } from 'vue';
import draggable from 'vuedraggable';
import {
  HolderOutlined,
  DeleteOutlined,
  PlusOutlined,
  FontSizeOutlined,
  CheckSquareOutlined,
  AppstoreOutlined,
  CalendarOutlined,
  NumberOutlined,
  FileTextOutlined,
  UploadOutlined,
} from '@ant-design/icons-vue';

export default {
  name: 'QuestionBuilder',
  components: {
    draggable,
    HolderOutlined,
  },
  props: {
    question: { type: Object, required: true },
    index: { type: Number, default: 1 },
  previousQuestions: { type: Array, default: () => [] },
  },
  emits: ['update-question', 'delete-question'],
  setup(props, { emit }) {
    const uid = () => Math.random().toString(36).slice(2, 10);

  const local = reactive(props.question);
  const skipModalOpen = ref(false);
  const validationModalOpen = ref(false);

  watch(
      () => props.question,
      (q) => {
        Object.assign(local, q || {});
    // ensure structures exist after external replace
    ensureSkipLogic();
    ensureValidationRules();
      },
      { deep: true }
    );

    const isChoice = computed(() => ['SINGLE_CHOICE', 'MULTI_CHOICE'].includes(local.response_type));

    const iconsMap = {
      TEXT: FontSizeOutlined,
      SINGLE_CHOICE: AppstoreOutlined,
      MULTI_CHOICE: CheckSquareOutlined,
      DATE: CalendarOutlined,
      NUMERIC: NumberOutlined,
      BOOL: CheckSquareOutlined,
      FILE: UploadOutlined,
      INFO: FileTextOutlined,
    };
    const labelsMap = {
      TEXT: 'Text',
      SINGLE_CHOICE: 'Single Choice',
      MULTI_CHOICE: 'Multiple Choice',
      DATE: 'Date',
      NUMERIC: 'Numeric',
      BOOL: 'Yes/No',
      FILE: 'File',
      INFO: 'Info',
    };
    const typeIcon = computed(() => iconsMap[local.response_type] || FontSizeOutlined);
    const typeLabel = computed(() => labelsMap[local.response_type] || local.response_type);

    const emitUpdate = () => {
      emit('update-question', { ...local });
    };

    const onTypeChange = () => {
      if (isChoice.value && !Array.isArray(local.options)) local.options = [];
      if (!isChoice.value) local.options = [];
      emitUpdate();
    };

    const reindexOptions = () => {
      (local.options || []).forEach((o, i) => (o.position = i + 1));
      emitUpdate();
    };

    const addOption = () => {
      if (!Array.isArray(local.options)) local.options = [];
      local.options.push({ uid: uid(), code: `OPT_${local.options.length + 1}`, label: `Option ${local.options.length + 1}`, value_numeric: null, position: local.options.length + 1, response_tags: [] });
      emitUpdate();
    };
    const removeOption = (idx) => {
      local.options.splice(idx, 1);
      reindexOptions();
    };

    // --- Skip logic helpers ---
    const ensureSkipLogic = () => {
      if (!local.skip_logic || typeof local.skip_logic !== 'object') local.skip_logic = {};
      ['show_if_any', 'show_if_all', 'hide_if_any', 'hide_if_all'].forEach((k) => {
        if (!Array.isArray(local.skip_logic[k])) local.skip_logic[k] = [];
      });
    };

    const logicGroups = [
      { key: 'show_if_any', title: 'Show if ANY' },
      { key: 'show_if_all', title: 'Show if ALL' },
      { key: 'hide_if_any', title: 'Hide if ANY' },
      { key: 'hide_if_all', title: 'Hide if ALL' },
    ];

    const getConditionType = (cond) => {
      if (!cond || typeof cond !== 'object') return 'option_code';
      if ('equals_bool' in cond) return 'equals_bool';
      if ('equals_text' in cond) return 'equals_text';
      if ('greater_than' in cond) return 'greater_than';
      if ('less_than' in cond) return 'less_than';
      return 'option_code';
    };

    const blankByType = (type) => {
      switch (type) {
        case 'equals_bool':
          return false;
        case 'greater_than':
        case 'less_than':
          return null;
        default:
          return '';
      }
    };

    const changeConditionType = (groupKey, index, newType) => {
      ensureSkipLogic();
      const arr = local.skip_logic[groupKey];
      const cond = arr[index] || {};
      // cleanup existing value keys
      delete cond.option_code;
      delete cond.equals_bool;
      delete cond.equals_text;
      delete cond.greater_than;
      delete cond.less_than;
      cond[newType] = blankByType(newType);
      arr[index] = cond;
      emitUpdate();
    };

    const addLogic = (groupKey) => {
      ensureSkipLogic();
      local.skip_logic[groupKey].push({ question_code: '', option_code: '' });
      emitUpdate();
    };

    const removeLogic = (groupKey, index) => {
      if (!local.skip_logic || !Array.isArray(local.skip_logic[groupKey])) return;
      local.skip_logic[groupKey].splice(index, 1);
      emitUpdate();
    };

    // --- Validation rules helpers ---
    const ensureValidationRules = () => {
      if (!local.validation_rules || typeof local.validation_rules !== 'object') local.validation_rules = {};
      // don't force required_if; it's optional, create when used
    };

    const requiredIfValueType = computed({
      get() {
        const v = local.validation_rules?.required_if?.value;
        if (typeof v === 'boolean') return 'bool';
        if (typeof v === 'number') return 'number';
        return 'text';
      },
      set(type) {
        let v = local.validation_rules?.required_if?.value;
        if (!local.validation_rules) local.validation_rules = {};
        if (!local.validation_rules.required_if) local.validation_rules.required_if = { field: '', value: '' };
        if (type === 'bool') v = !!v;
        else if (type === 'number') v = Number(v || 0);
        else v = v == null ? '' : String(v);
        local.validation_rules.required_if.value = v;
        emitUpdate();
      },
    });

    const setRequiredIfValue = (val) => {
      if (!local.validation_rules) local.validation_rules = {};
      if (!local.validation_rules.required_if) local.validation_rules.required_if = { field: '', value: '' };
      local.validation_rules.required_if.value = val;
      emitUpdate();
    };

    const setRequiredIfField = (val) => {
      if (!local.validation_rules) local.validation_rules = {};
      if (!local.validation_rules.required_if) local.validation_rules.required_if = { field: '', value: '' };
      local.validation_rules.required_if.field = val;
      emitUpdate();
    };

    // initialize structures
    ensureSkipLogic();
    ensureValidationRules();

    const previousQuestionOptions = computed(() => {
      return (props.previousQuestions || [])
        .filter((q) => (typeof q.position === 'number' ? q.position < props.index : true))
        .map((q, i) => ({
          code: q.code,
          label: `#${q.position ?? i + 1} ${q.code}${q.prompt ? ' — ' + q.prompt : ''}`,
        }));
    });

    return {
      h,
      local,
  skipModalOpen,
  validationModalOpen,
      isChoice,
      typeIcon,
      typeLabel,
      emitUpdate,
      onTypeChange,
      reindexOptions,
      addOption,
      removeOption,
      // logic bindings
      logicGroups,
      getConditionType,
      changeConditionType,
      addLogic,
      removeLogic,
      requiredIfValueType,
      setRequiredIfValue,
  previousQuestionOptions,
  setRequiredIfField,
      DeleteOutlined,
      PlusOutlined,
    };
  },
};
</script>

<style scoped>
.question-builder-card {
  transition: all 0.3s ease;
  margin-bottom: 12px;
}

.question-builder-card.is-editing {
  box-shadow: 0 4px 12px rgba(24, 144, 255, 0.15);
}

.question-header {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}

.question-handle {
  cursor: grab;
  color: #8c8c8c;
}

.question-handle:active {
  cursor: grabbing;
}

.question-number {
  font-weight: 600;
  color: #1890ff;
  min-width: 20px;
}

.question-type-icon {
  color: #1890ff;
}

.question-type-label {
  flex: 1;
  font-size: 13px;
}

.question-actions { display: inline-flex; }

.question-content {
  margin-top: 16px;
}

.question-prompt {
  position: relative;
  margin-bottom: 16px;
}

.prompt-input {
  border: 2px dashed #d9d9d9;
}

.prompt-input:focus {
  border-color: #1890ff;
  border-style: solid;
}

.prompt-display {
  padding: 8px 12px;
  min-height: 40px;
}

.prompt-display:hover { opacity: 0.98; }

.required-mark { color: #ff4d4f; }

.question-options { margin-top: 12px; }

.options-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }

.options-list { list-style: none; padding: 0; margin: 0; }

.option-item { display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #f0f0f0; border-radius: 6px; background: #fafafa; margin-bottom: 8px; }

.option-handle { cursor: grab; color: #8c8c8c; }

.option-handle:active { cursor: grabbing; }

.option-control { display: inline-flex; }

.option-input { flex: 1; }

.option-input:focus { border-color: #1890ff; }

.option-delete { opacity: 0.8; }

.option-item:hover .option-delete { opacity: 1; }

.question-settings { margin-bottom: 8px; }

.numeric-settings,
.text-settings,
.file-settings,
.common-settings { margin-top: 8px; }

/* Dragging styles */
.sortable-ghost { opacity: 0.6; }

.sortable-chosen { background: #f5faff; }

.sortable-drag { box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15); }
</style>
