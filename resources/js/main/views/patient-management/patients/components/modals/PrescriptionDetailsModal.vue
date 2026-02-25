<template>
    <a-modal
        :open="visible"
        :title="$t('prescriptions.prescription_details')"
        width="700px"
        @cancel="onClose"
        :footer="null"
    >
        <div v-if="prescription">
            <div class="mb-4 pb-4 border-b">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500"
                            >{{
                                $t("prescriptions.prescription_number")
                            }}:</span
                        >
                        <span class="ml-2 font-medium"
                            >#{{
                                prescription.prescription_number ||
                                prescription.xid.slice(0, 8)
                            }}</span
                        >
                    </div>
                    <div>
                        <span class="text-gray-500"
                            >{{ $t("common.status") }}:</span
                        >
                        <a-tag
                            class="ml-2"
                            :color="
                                prescription.status === 'active'
                                    ? 'green'
                                    : 'default'
                            "
                        >
                            {{
                                prescription.status === "active"
                                    ? $t("prescriptions.active")
                                    : $t("prescriptions.completed")
                            }}
                        </a-tag>
                    </div>
                    <div>
                        <span class="text-gray-500"
                            >{{ $t("prescriptions.prescribed_by") }}:</span
                        >
                        <span class="ml-2 font-medium">{{
                            prescription.doctor?.user?.name || "-"
                        }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500"
                            >{{ $t("common.date") }}:</span
                        >
                        <span class="ml-2 font-medium">{{
                            formatDate(prescription.created_at)
                        }}</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="font-medium mb-3">
                    {{ $t("prescriptions.prescribed_medicines") }}:
                </h5>
                <a-table
                    :columns="medicineColumns"
                    :data-source="prescription.prescription_items || []"
                    :pagination="false"
                    size="small"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'name'">
                            <span class="font-medium">{{
                                record.medicine?.name || record.medicine_name
                            }}</span>
                        </template>
                    </template>
                </a-table>
            </div>

            <div v-if="prescription.notes" class="p-3 bg-gray-50 rounded-lg">
                <h5 class="font-medium text-sm mb-2">
                    {{ $t("prescriptions.notes") }}:
                </h5>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">
                    {{ prescription.notes }}
                </p>
            </div>
        </div>
        <div v-else class="text-center py-4">
            <a-spin />
        </div>

        <div class="flex justify-between mt-4 border-t pt-4">
            <a-button
                type="primary"
                @click="downloadPrescription"
                :icon="h(DownloadOutlined)"
            >
                {{ $t("common.download") }}
            </a-button>
            <div class="flex gap-2">
                <a-button @click="onClose">
                    {{ $t("common.close") }}
                </a-button>
                <a-button
                    v-if="
                        permsArray.includes('prescriptions_delete') ||
                        permsArray.includes('admin')
                    "
                    danger
                    @click="confirmDelete"
                    :icon="h(DeleteOutlined)"
                >
                    {{ $t("common.delete") }}
                </a-button>
            </div>
        </div>
    </a-modal>
</template>

<script setup>
import { ref, h } from "vue";
import { useI18n } from "vue-i18n";
import { DownloadOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import { message, Modal } from "ant-design-vue";
import common from "../../../../../../common/composable/common";

const axiosAdmin = window.axiosAdmin;

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    prescription: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:visible", "deleted"]);

const { t } = useI18n();
const { formatDate, permsArray } = common();

const medicineColumns = [
    {
        title: t("prescriptions.medicine"),
        key: "name",
        dataIndex: "name",
    },
    {
        title: t("prescriptions.dosage"),
        key: "dosage",
        dataIndex: "dosage",
    },
    {
        title: t("prescriptions.frequency"),
        key: "frequency",
        dataIndex: "frequency",
    },
    {
        title: t("prescriptions.duration"),
        key: "duration",
        dataIndex: "duration",
    },
];

const onClose = () => {
    emit("update:visible", false);
};

const downloadPrescription = async () => {
    if (!props.prescription) return;

    try {
        const response = await axiosAdmin.get(
            `prescriptions/${props.prescription.xid}/download`,
            {
                responseType: "blob",
            }
        );

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute(
            "download",
            `prescription_${
                props.prescription.prescription_number || props.prescription.xid
            }.pdf`
        );
        document.body.appendChild(link);
        link.click();
        link.remove();

        message.success(t("prescriptions.download_success"));
    } catch (error) {
        console.error("Error downloading prescription:", error);
        message.error(t("prescriptions.download_error"));
    }
};

const confirmDelete = () => {
    if (!props.prescription) return;

    Modal.confirm({
        title: t("prescriptions.delete_confirmation_title"),
        content: t("prescriptions.delete_confirmation_message"),
        okText: t("common.delete"),
        okType: "danger",
        cancelText: t("common.cancel"),
        onOk: deletePrescription,
    });
};

const deletePrescription = async () => {
    if (!props.prescription) return;

    try {
        await axiosAdmin.delete(`prescriptions/${props.prescription.xid}`);
        message.success(t("prescriptions.deleted_successfully"));
        emit("deleted");
        onClose();
    } catch (error) {
        console.error("Error deleting prescription:", error);
        message.error(t("prescriptions.delete_error"));
    }
};
</script>

<style scoped></style>
