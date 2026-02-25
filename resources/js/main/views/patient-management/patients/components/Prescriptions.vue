<template>
    <div class="prescriptions-container">
        <!-- Statistics Cards - Only show when not filtered by patient -->
        <a-row v-if="!patientId" :gutter="[16,16]" class="mb-4">
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('prescriptions.total_prescriptions')"
                    :value="prescriptionStats.totalPrescriptions || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('prescriptions.active_prescriptions')"
                    :value="prescriptionStats.activePrescriptions || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('prescriptions.prescriptions_this_month')"
                    :value="prescriptionStats.prescriptionsThisMonth || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('prescriptions.most_prescribed_medicine')"
                    :value="prescriptionStats.mostPrescribedMedicine || '-'"
                    :loading="loadingStats"
                    :isText="true"
                />
            </a-col>
        </a-row>

        <!-- Header with Add Button -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <MedicineBoxOutlined />
                {{ $t("prescriptions.prescriptions_history") }}
            </h3>
            <a-button
                v-if="
                    permsArray.includes('prescriptions_create') ||
                    permsArray.includes('admin')
                "
                type="primary"
                @click="showCreateModal = true"
                :icon="h(PlusOutlined)"
            >
                {{ $t("prescriptions.add") }}
            </a-button>
        </div>

        <!-- Prescriptions List -->
        <div v-if="loading" class="space-y-4">
            <a-card v-for="i in 3" :key="'skeleton-' + i" class="shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <a-skeleton :paragraph="{ rows: 3 }" active />
                    </div>
                </div>
                <div class="mt-4">
                    <a-skeleton :paragraph="{ rows: 2 }" active />
                </div>
            </a-card>
        </div>

        <!-- Empty State -->
        <div v-else-if="prescriptions.length === 0" class="text-center py-12">
            <MedicineBoxOutlined class="text-5xl text-gray-300 mb-4" />
            <p class="text-gray-500">
                {{ $t("prescriptions.no_prescriptions") }}
            </p>
            <a-button
                v-if="
                    permsArray.includes('prescriptions_create') ||
                    permsArray.includes('admin')
                "
                type="primary"
                class="mt-4"
                @click="showCreateModal = true"
            >
                {{ $t("prescriptions.create_first_prescription") }}
            </a-button>
        </div>

        <!-- Prescriptions Cards -->
        <div v-else class="space-y-4">
            <a-card
                v-for="prescription in prescriptions"
                :key="prescription.prescription_number"
                class="shadow-sm hover:shadow-md transition-shadow"
            >
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h4 class="font-semibold text-base">
                                {{ $t("prescriptions.prescription") }} #{{
                                    prescription.prescription_number ||
                                    prescription.xid.slice(0, 8)
                                }}
                            </h4>
                            <a-tag
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
                        <div class="text-sm text-gray-500 space-y-1">
                            <div class="flex items-center gap-2">
                                <UserOutlined />
                                <span
                                    >{{ $t("prescriptions.prescribed_by") }}:
                                    {{
                                        prescription.doctor?.user?.name || "-"
                                    }}</span
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <CalendarOutlined />
                                <span>{{
                                    formatDate(prescription.created_at)
                                }}</span>
                            </div>
                            <div
                                v-if="prescription.appointment"
                                class="flex items-center gap-2"
                            >
                                <CalendarOutlined />
                                <span
                                    >{{ $t("prescriptions.appointment") }}:
                                    {{
                                        formatDate(
                                            prescription.appointment
                                                .appointment_date
                                        )
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>
                    <a-dropdown>
                        <a-button type="text" :icon="h(MoreOutlined)" />
                        <template #overlay>
                            <a-menu>
                                <a-menu-item
                                    @click="viewPrescription(prescription)"
                                >
                                    <EyeOutlined /> {{ $t("common.view") }}
                                </a-menu-item>
                                <a-menu-item
                                    @click="downloadPrescription(prescription)"
                                >
                                    <DownloadOutlined />
                                    {{ $t("common.download") }}
                                </a-menu-item>
                                <a-menu-item
                                    v-if="
                                        permsArray.includes(
                                            'prescriptions_delete'
                                        ) || permsArray.includes('admin')
                                    "
                                    danger
                                    @click="confirmDelete(prescription)"
                                >
                                    <DeleteOutlined /> {{ $t("common.delete") }}
                                </a-menu-item>
                            </a-menu>
                        </template>
                    </a-dropdown>
                </div>

                <!-- Medicines List -->
                <div class="mt-4">
                    <h5 class="font-medium text-sm mb-2">
                        {{ $t("prescriptions.medicines") }}:
                    </h5>
                    <a-table
                        :columns="medicineColumns"
                        :data-source="prescription.prescription_items || []"
                        :pagination="false"
                        size="small"
                        :show-header="true"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'name'">
                                <span class="font-medium">{{
                                    record.medicine?.name ||
                                    record.medicine_name
                                }}</span>
                            </template>
                        </template>
                    </a-table>
                </div>

                <!-- Notes -->
                <div
                    v-if="prescription.notes"
                    class="mt-4 p-3 bg-gray-50 rounded-lg"
                >
                    <h5 class="font-medium text-sm mb-1">
                        {{ $t("prescriptions.notes") }}:
                    </h5>
                    <p class="text-sm text-gray-700">
                        {{ prescription.notes }}
                    </p>
                </div>
            </a-card>
        </div>
    </div>

    <!-- Create Prescription Modal -->
    <a-modal
        v-model:open="showCreateModal"
        :title="$t('prescriptions.create_prescription')"
        :footer="null"
        width="900px"
        :maskClosable="false"
        centered
    >
        <CreatePrescription
            :patient-id="patientId"
            :doctor-id="currentDoctorId"
            :appointment-id="selectedAppointmentId"
            @success="handlePrescriptionCreated"
            @cancel="showCreateModal = false"
        />
    </a-modal>

    <!-- View Prescription Modal -->
    <ViewPrescriptionModal
        v-model:visible="showViewModal"
        :prescription="selectedPrescription"
        @deleted="deleteFromView"
    />
</template>

<script setup>
import { ref, onMounted, h } from "vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import {
    MedicineBoxOutlined,
    PlusOutlined,
    UserOutlined,
    CalendarOutlined,
    MoreOutlined,
    EyeOutlined,
    DownloadOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import { message, Modal } from "ant-design-vue";
import common from "../../../../../common/composable/common";
import CreatePrescription from "./CreatePrescription.vue";
import ViewPrescriptionModal from "./modals/PrescriptionDetailsModal.vue";
import StateWidget from "../../../../../common/components/common/card/StateWidget.vue";

const axiosAdmin = window.axiosAdmin;

const props = defineProps({
    patientId: {
        type: String,
        required: true,
    },
});

const { t } = useI18n();
const store = useStore();
const { formatDate, permsArray } = common();

// State
const loading = ref(false);
const prescriptions = ref([]);
const showCreateModal = ref(false);
const showViewModal = ref(false);
const selectedPrescription = ref(null);
const selectedAppointmentId = ref(null);

// Statistics
const prescriptionStats = ref({
    totalPrescriptions: 0,
    activePrescriptions: 0,
    completedPrescriptions: 0,
    prescriptionsThisMonth: 0,
    mostPrescribedMedicine: '-',
    mostActiveDoctor: '-'
});
const loadingStats = ref(true);

// Get current doctor ID from store (assuming it's stored in Vuex)
const currentDoctorId = ref(
    store.state.auth?.user?.doctor_id || store.state.auth?.user?.xid || ""
);

// Table columns for medicines
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

const fetchPrescriptions = async () => {
    try {
        loading.value = true;
        const response = await axiosAdmin.get("prescriptions", {
            params: {
                patient_id: props.patientId,
                fields: "id,xid,prescription_number,prescription_date,notes,status,created_at,updated_at,x_patient_id,x_doctor_id,x_appointment_id,prescriptionItems{id,xid,medicine_name,dosage,frequency,duration,instructions}",
            },
        });

        prescriptions.value = response.data || [];
    } catch (error) {
        console.error("Error fetching prescriptions:", error);
        message.error(t("prescriptions.fetch_error"));
    } finally {
        loading.value = false;
    }
};

const fetchPrescriptionStats = async () => {
    if (props.patientId) {
        // Don't fetch stats if we're viewing a specific patient's prescriptions
        loadingStats.value = false;
        return;
    }

    try {
        loadingStats.value = true;
        const response = await axiosAdmin.get("prescriptions/stats");
        prescriptionStats.value = response;
    } catch (error) {
        console.error("Error fetching prescription statistics:", error);
    } finally {
        loadingStats.value = false;
    }
};

const handlePrescriptionCreated = (prescription) => {
    showCreateModal.value = false;
    fetchPrescriptions();
    message.success(t("prescriptions.created_successfully"));
};

const viewPrescription = (prescription) => {
    selectedPrescription.value = prescription;
    showViewModal.value = true;
};

const deleteFromView = () => {
    showViewModal.value = false;
    fetchPrescriptions();
};
const downloadPrescription = async (prescription) => {
    try {
        const response = await axiosAdmin.get(
            `prescriptions/${prescription.xid}/download`,
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
                prescription.prescription_number || prescription.xid
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

const confirmDelete = (prescription) => {
    Modal.confirm({
        title: t("prescriptions.delete_confirmation_title"),
        content: t("prescriptions.delete_confirmation_message"),
        okText: t("common.delete"),
        okType: "danger",
        cancelText: t("common.cancel"),
        onOk: () => deletePrescription(prescription),
    });
};

const deletePrescription = async (prescription) => {
    try {
        await axiosAdmin.delete(`prescriptions/${prescription.xid}`);
        message.success(t("prescriptions.deleted_successfully"));
        fetchPrescriptions();
    } catch (error) {
        console.error("Error deleting prescription:", error);
        message.error(t("prescriptions.delete_error"));
    }
};

onMounted(() => {
    fetchPrescriptions();
    fetchPrescriptionStats();
});

// Expose viewPrescription method for parent component
defineExpose({
    viewPrescription,
});
</script>

<style scoped>
.prescriptions-container {
    padding: 0;
}
</style>
