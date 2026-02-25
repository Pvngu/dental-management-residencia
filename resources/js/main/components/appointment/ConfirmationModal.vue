<template>
    <a-modal
        :open="open"
        :title="title"
        :ok-text="t('common.confirm')"
        :cancel-text="t('common.cancel')"
        @ok="onConfirm"
        @cancel="onCancel"
        :confirmLoading="loading"
        @update:open="updateOpen"
    >
        <div v-if="action === 'checked_in'">
            <div class="font-medium mb-2">
                {{ t("appointments.patient_information") }}
            </div>
            <div class="bg-gray-50 p-4 rounded border border-gray-200 text-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-lg font-semibold mb-1">
                            {{ patient?.fullName }}
                        </div>
                        <div class="text-sm text-gray-700">
                            {{ t("common.phone") }}: {{ patient?.phone }}
                        </div>
                        <div class="text-sm text-gray-700">
                            {{ t("common.address") }}: {{ patient?.address }}
                        </div>
                        <div class="text-sm text-gray-700">
                            {{ t("appointments.ssn") }}: {{ patient?.ssn }}
                        </div>
                    </div>
                    <div>
                        <a-button
                            type="text"
                            size="small"
                            @click="$emit('editPatient')"
                        >
                            <template #icon><EditOutlined /></template>
                        </a-button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="text-sm">
                {{
                    t("appointments.confirm_action_message") ||
                    "Are you sure you want to perform this action?"
                }}
            </div>
        </div>
    </a-modal>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import { EditOutlined } from "@ant-design/icons-vue";

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "",
    },
    action: {
        type: String,
        default: "",
    },
    patient: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:open", "confirm", "cancel", "editPatient"]);
const { t } = useI18n();

const onConfirm = () => {
    emit("confirm");
};

const onCancel = () => {
    emit("update:open", false);
    emit("cancel");
};

const updateOpen = (val) => {
    emit("update:open", val);
};
</script>
