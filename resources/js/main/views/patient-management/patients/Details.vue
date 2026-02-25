<template>
    <admin-page-table-content>
        <!-- Patient Info Section -->
        <a-skeleton
            v-if="loading"
            active
            :paragraph="{ rows: 3 }"
            :avatar="{ shape: 'square', size: 100 }"
            class="p-5 mb-4 mt-3 bg-white rounded-lg"
        />
        <div v-else class="bg-white p-5 rounded-lg shadow-sm mt-3 relative">
            <!-- Action Buttons -->
            <div class="flex justify-end mb-4 absolute top-4 right-4">
                <a-space>
                    <!-- Open Case Alert Button - Shows when patient has open critical/high cases -->
                    <a-button
                        v-if="hasOpenCriticalCases"
                        type="primary"
                        danger
                        @click="handleOpenCaseModal(openCriticalCase)"
                        :icon="h(AlertOutlined)"
                    >
                        {{ $t("open_cases.urgent_case") }}
                    </a-button>
                    <a-button
                        type="primary"
                        @click="handleScheduleAppointment"
                        :icon="h(CalendarOutlined)"
                    >
                        {{
                            hasAppointmentToday
                                ? $t("patients.reschedule_appointment")
                                : $t("patients.schedule_appointment")
                        }}
                    </a-button>
                    <a-dropdown>
                        <template #overlay>
                            <a-menu>
                                <a-menu-item
                                    key="assign-doctor"
                                    @click="handleAssignDoctor"
                                >
                                    <UserOutlined />
                                    <span class="ml-2">{{
                                        $t("patients.assign_doctor")
                                    }}</span>
                                </a-menu-item>
                                <a-menu-item
                                    v-if="
                                        permsArray.includes('patients_edit') ||
                                        permsArray.includes('admin')
                                    "
                                    key="edit-patient"
                                    @click="handleEditPatient"
                                >
                                    <EditOutlined />
                                    <span class="ml-2">{{
                                        $t("patients.edit")
                                    }}</span>
                                </a-menu-item>
                                <a-menu-item
                                    v-if="
                                        permsArray.includes('sales_create') ||
                                        permsArray.includes('admin')
                                    "
                                    key="pos-mode"
                                    @click="handleOpenPOS"
                                >
                                    <ShoppingCartOutlined />
                                    <span class="ml-2">{{
                                        $t("sales.pos_mode")
                                    }}</span>
                                </a-menu-item>
                            </a-menu>
                        </template>
                        <a-button>
                            {{ $t("common.actions") }}
                            <DownOutlined />
                        </a-button>
                    </a-dropdown>
                </a-space>
            </div>
            <div class="flex flex-wrap items-start">
                <div class="mr-5 mb-4">
                    <a-avatar
                        :size="100"
                        class="bg-gray-200 flex items-center justify-center"
                    >
                        <template #icon>
                            <UserOutlined
                                v-if="!patient?.user?.profile_image_url"
                            />
                            <img
                                v-else
                                :src="patient?.user?.profile_image_url"
                                alt="Patient Image"
                            />
                        </template>
                    </a-avatar>
                </div>
                <div class="flex-grow mb-4">
                    <h2 class="font-bold text-2xl mb-2">
                        {{ patient?.user?.name }} {{ patient?.user?.last_name }}
                    </h2>
                    <div class="flex flex-wrap">
                        <a-tag
                            v-if="patient?.user?.status === 'enabled'"
                            color="blue"
                            >{{ $t("patients.active_patient") }}</a-tag
                        >
                        <a-tag v-else color="red">{{
                            $t("patients.disabled_patient")
                        }}</a-tag>
                        <!-- Appointment Status for today -->
                        <a-tag
                            v-if="hasAppointmentToday && currentAppointment"
                            :color="getStatusColor(currentAppointment)"
                        >
                            <ClockCircleOutlined />
                            {{ getStatusText(currentAppointment) }}
                        </a-tag>
                    </div>
                    <div class="mt-4 flex gap-4">
                        <div>
                            <div class="flex mb-1">
                                <div
                                    class="font-medium text-gray-600 w-30 mr-3"
                                >
                                    <ClockCircleOutlined />
                                    {{ $t("patients.patient_since") }}:
                                </div>
                                <div>{{ formatDate(patient?.created_at) }}</div>
                            </div>
                            <div class="flex mb-1">
                                <div
                                    class="font-medium text-gray-600 w-30 mr-3"
                                >
                                    <CalendarOutlined />
                                    {{ $t("user.date_of_birth") }}:
                                </div>
                                <div>
                                    {{
                                        formatDate(patient?.user?.date_of_birth)
                                    }}
                                </div>
                            </div>
                            <div class="flex mb-1">
                                <div
                                    class="font-medium text-gray-600 w-30 mr-3"
                                >
                                    <PhoneOutlined /> {{ $t("user.phone") }}:
                                </div>
                                <div>{{ patient?.user?.phone || "-" }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="flex mb-1">
                                <div
                                    class="font-medium text-gray-600 w-20 mr-3"
                                >
                                    <MailOutlined /> {{ $t("user.email") }}:
                                </div>
                                <div>{{ patient?.user?.email || "-" }}</div>
                            </div>
                            <div class="flex mb-1">
                                <div
                                    class="font-medium text-gray-600 w-20 mr-3"
                                >
                                    <HomeOutlined /> {{ $t("user.address") }}:
                                </div>
                                <div v-if="patient?.user?.default_address?.full_address" class="flex-grow">
                                    <a-typography-paragraph
                                        :ellipsis="{
                                            rows: 2,
                                            expandable: true,
                                            symbol: $t('common.more'),
                                        }"
                                        :content="patient?.user?.default_address?.full_address"
                                    />
                                </div>
                                <div v-else>—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Check-in Actions Section -->
        <CheckInActions
            ref="checkinActionsRef"
            :patientId="id"
            :loading="loading"
            @selectRoom="handleSelectRoom"
            @appointmentUpdated="handleAppointmentUpdated"
            @createPrescription="handleCreatePrescription"
            @viewPrescription="handleViewPrescription"
            @update:loading="(val) => (loading = val)"
        />

        <!-- Active Cases Alert Section -->
        <ActiveCasesAlert
            :active-cases="patientOpenCases"
            :loading="activeCasesLoading"
            @viewCases="handleViewOpenCases"
        />

        <!-- Main Content Area with Sidebar -->
        <a-skeleton
            v-if="loading"
            active
            :paragraph="{ rows: 8 }"
            class="bg-white px-6 py-5 rounded-lg shadow-sm"
        />
        <div v-else class="flex gap-4 mt-4 mb-3 min-h-[calc(100vh-312px)]">
            <!-- Main Tabs Section -->
            <div
                class="bg-white rounded-lg shadow-sm overflow-hidden px-6 flex-1 relative"
            >
                <a-tabs
                    class="flex-1 main-tabs"
                    v-model:activeKey="activeTabKey"
                    @change="handleTabChange"
                >
                    <!-- OVERVIEW TAB -->
                    <a-tab-pane key="overview">
                        <template #tab>
                            <span>
                                <HistoryOutlined />
                                {{ $t("patients.overview") }}
                            </span>
                        </template>
                        <div>
                            <Overview :patientId="id" :patientData="patient" />
                        </div>
                    </a-tab-pane>

                    <!-- CLINICAL CARE GROUP -->
                    <a-tab-pane key="clinical-care">
                        <template #tab>
                            <span>
                                <AlertOutlined />
                                {{ $t("patients.clinical_care") }}
                            </span>
                        </template>
                        <div>
                            <a-tabs
                                v-model:activeKey="clinicalCareSubTab"
                                class="sub-tabs"
                                @change="handleSubTabChange"
                            >
                                <a-tab-pane key="dental-chart">
                                    <template #tab>
                                        <AlertOutlined />
                                        {{ $t("patients.dental_chart") }}
                                    </template>
                                    <DentalChart :patientId="id" />
                                </a-tab-pane>

                                <a-tab-pane key="treatment-plan">
                                    <template #tab>
                                        <CalendarOutlined />
                                        {{ $t("patients.treatment_plan") }}
                                    </template>
                                    <TreatmentPlan :patientId="id" />
                                </a-tab-pane>

                                <a-tab-pane key="prescriptions">
                                    <template #tab>
                                        <MedicineBoxOutlined />
                                        {{ $t("prescriptions.prescriptions") }}
                                    </template>
                                    <Prescriptions
                                        ref="prescriptionsRef"
                                        :patientId="id"
                                    />
                                </a-tab-pane>

                                <a-tab-pane key="open-cases">
                                    <template #tab>
                                        <AlertOutlined />
                                        {{ $t("open_cases.open_cases") }}
                                    </template>
                                    <OpenCases 
                                        :patientId="id" 
                                        @casesUpdated="handleCasesUpdated"
                                    />
                                </a-tab-pane>
                            </a-tabs>
                        </div>
                    </a-tab-pane>

                    <!-- SCHEDULING & VISITS GROUP -->
                    <a-tab-pane key="scheduling-visits">
                        <template #tab>
                            <span>
                                <CalendarOutlined />
                                {{ $t("patients.scheduling_visits") }}
                            </span>
                        </template>
                        <div>
                            <a-tabs
                                v-model:activeKey="schedulingSubTab"
                                class="sub-tabs"
                                @change="handleSubTabChange"
                            >
                                <a-tab-pane key="appointments">
                                    <template #tab>
                                        <CalendarOutlined />
                                        {{ $t("menu.appointments") }}
                                    </template>
                                    <Appointments :patientId="id" />
                                </a-tab-pane>

                                <a-tab-pane key="history">
                                    <template #tab>
                                        <HistoryOutlined />
                                        {{ $t("patients.history") }}
                                    </template>
                                    <History :patientId="id" />
                                </a-tab-pane>
                            </a-tabs>
                        </div>
                    </a-tab-pane>

                    <!-- FINANCIAL GROUP -->
                    <a-tab-pane key="financial">
                        <template #tab>
                            <span>
                                <ShoppingCartOutlined />
                                {{ $t("patients.financial") }}
                            </span>
                        </template>
                        <div>
                            <a-tabs
                                v-model:activeKey="financialSubTab"
                                class="sub-tabs"
                                @change="handleSubTabChange"
                            >
                                <a-tab-pane key="sales">
                                    <template #tab>
                                        <ShoppingCartOutlined />
                                        {{ $t("menu.sales") }}
                                    </template>
                                    <Sales :patientId="id" />
                                </a-tab-pane>

                                <a-tab-pane key="payment-methods">
                                    <template #tab>
                                        <CreditCardOutlined />
                                        {{ $t("payment.payment_methods") }}
                                    </template>
                                    <PaymentMethods :patientId="id" />
                                </a-tab-pane>
                            </a-tabs>
                        </div>
                    </a-tab-pane>

                    <!-- COMMUNICATION & RECORDS GROUP -->
                    <a-tab-pane key="communication-records">
                        <template #tab>
                            <span>
                                <MessageOutlined />
                                {{ $t("patients.communication_records") }}
                                <a-badge
                                    v-if="unreadMessagesCount > 0"
                                    :count="unreadMessagesCount"
                                    :number-style="{ backgroundColor: '#52c41a', marginLeft: '8px' }"
                                />
                            </span>
                        </template>
                        <div>
                            <a-tabs
                                v-model:activeKey="communicationSubTab"
                                class="sub-tabs"
                                @change="handleSubTabChange"
                            >
                                <a-tab-pane key="messages">
                                    <template #tab>
                                        <MessageOutlined />
                                        {{ $t("patients.messages") }}
                                        <a-badge
                                            v-if="unreadMessagesCount > 0"
                                            :count="unreadMessagesCount"
                                            :number-style="{ backgroundColor: '#52c41a', marginLeft: '8px' }"
                                        />
                                    </template>
                                    <Messages
                                        ref="messagesRef"
                                        :patientId="id"
                                        :patientPhone="patient?.user?.phone"
                                        :isVisible="activeTabKey === 'communication-records' && communicationSubTab === 'messages'"
                                        @update:unreadCount="(count) => unreadMessagesCount = count"
                                    />
                                </a-tab-pane>

                                <a-tab-pane key="notes">
                                    <template #tab>
                                        <FileTextOutlined />
                                        {{ $t("patient_notes.notes") }}
                                    </template>
                                    <Notes
                                        :patientId="id"
                                        @noteUpdated="handleNoteUpdated"
                                    />
                                </a-tab-pane>

                                <a-tab-pane key="patient-files">
                                    <template #tab>
                                        <FileOutlined />
                                        {{ $t("menu.patient_files") }}
                                    </template>
                                    <PatientFiles :id="id" />
                                </a-tab-pane>

                                <a-tab-pane key="emails">
                                    <template #tab>
                                        <MailOutlined />
                                        {{ $t("common.emails") }}
                                    </template>
                                    <Emails :patientId="id" />
                                </a-tab-pane>

                                <a-tab-pane key="faxes">
                                    <template #tab>
                                        <PrinterOutlined />
                                        {{ $t("fax.faxes") }}
                                    </template>
                                    <Faxes :patientXid="id" />
                                </a-tab-pane>
                            </a-tabs>
                        </div>
                    </a-tab-pane>
                </a-tabs>

                <!-- Notes Sidebar Toggle Button - moved to original position -->
                <div>
                    <a-button
                        class="absolute! top-2 right-2"
                        type="text"
                        @click="toggleNotesSidebar"
                        :title="
                            notesSidebarVisible
                                ? $t('patient_notes.hide_notes')
                                : $t('patient_notes.show_notes')
                        "
                    >
                        <template #icon>
                            <MenuUnfoldOutlined v-if="notesSidebarVisible" />
                            <MenuFoldOutlined v-else />
                        </template>
                    </a-button>
                </div>
            </div>

            <!-- Notes Sidebar -->
            <div
                v-if="notesSidebarVisible"
                class="bg-white rounded-lg shadow-sm overflow-hidden w-80 transition-all duration-300"
            >
                <div class="pt-4 pl-4 pr-4 border-b">
                    <h3 class="font-medium text-lg flex items-center gap-2">
                        <FileOutlined />
                        {{ $t("patient_notes.all_notes") }}
                    </h3>
                </div>
                <div class="px-4">
                    <HighlightedNotes
                        :patientId="id"
                        :refreshTrigger="notesRefreshTrigger"
                    />
                </div>
            </div>
        </div>
    </admin-page-table-content>

    <!-- Appointment Component -->
    <Appointment
        :visible="appointmentVisible"
        :formData="appointmentFormData"
        :autoSelectPatient="true"
        :patientId="id"
        :rooms="rooms"
        @closed="appointmentVisible = false"
        @addEditSuccess="handleAppointmentSuccess"
    />

    <!-- Patient AddEdit Component -->
    <PatientAddEdit
        :visible="patientAddEditVisible"
        :formData="patientFormData"
        :data="patient"
        :url="`patients/${id}`"
        :addEditType="'edit'"
        :pageTitle="$t('patients.edit')"
        :successMessage="$t('patients.updated')"
        @closed="patientAddEditVisible = false"
        @addEditSuccess="handlePatientEditSuccess"
    />

    <!-- Room Selection Modal -->
    <RoomSelectionModal
        v-model:visible="roomSelectionVisible"
        :currentAppointment="currentAppointment"
        :loading="loading"
        @roomAssigned="handleRoomAssignment"
        @cancel="roomSelectionVisible = false"
    />

    <!-- Prescription Modal -->
    <a-modal
        v-model:open="prescriptionModalVisible"
        :title="$t('prescriptions.create_prescription')"
        :footer="null"
        width="900px"
        :maskClosable="false"
        centered
    >
        <CreatePrescription
            :patientId="id"
            :doctorId="currentDoctorId"
            :appointmentId="prescriptionAppointmentId"
            @success="handlePrescriptionCreated"
            @cancel="prescriptionModalVisible = false"
        />
    </a-modal>

    <!-- Case Details Modal -->
    <CaseModal
        :visible="caseModalVisible"
        :caseData="selectedCase"
        @update:visible="caseModalVisible = $event"
        @edit="handleEditCase"
    />

    <!-- View Prescription Modal -->
    <ViewPrescriptionModal
        v-model:visible="viewPrescriptionVisible"
        :prescription="selectedPrescriptionToView"
        @deleted="handlePrescriptionDeleted"
    />

    <!-- Active Cases Modal -->
    <ActiveCasesModal
        v-model:visible="activeCasesModalVisible"
        :active-cases="patientOpenCases"
        :loading="activeCasesLoading"
        :patient-name="patient?.user?.name + ' ' + (patient?.user?.last_name || '')"
        :can-edit="permsArray.includes('open_cases_edit') || permsArray.includes('admin')"
        @viewCase="handleOpenCaseModal"
        @editCase="handleEditCaseFromModal"
    />
</template>

<script setup>
import { ref, onMounted, computed, h, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import {
    UserOutlined,
    PhoneOutlined,
    MailOutlined,
    HomeOutlined,
    CalendarOutlined,
    ClockCircleOutlined,
    EyeOutlined,
    DownloadOutlined,
    PlusOutlined,
    AlertOutlined,
    EnvironmentOutlined,
    HistoryOutlined,
    FileOutlined,
    FileTextOutlined,
    MenuFoldOutlined,
    MenuUnfoldOutlined,
    LogoutOutlined,
    CreditCardOutlined,
    EditOutlined,
    ShoppingCartOutlined,
    MessageOutlined,
    MedicineBoxOutlined,
    DownOutlined,
    PrinterOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import { useRouter } from "vue-router";
import { notification, Modal } from "ant-design-vue";
const axiosAdmin = window.axiosAdmin;
import DentalChart from "../dental-chart/index.vue";
import useToothImageCache from "../dental-chart/composables/useToothImageCache.js";
import Overview from "./components/Overview.vue";
import PatientFiles from "../../../components/patient-files/index.vue";
import PaymentMethods from "./components/payment-method/index.vue";
import Appointments from "./components/Appointments.vue";
import Notes from "./components/Notes.vue";
import History from "./components/History.vue";
import TreatmentPlan from "./components/TreatmentPlan.vue";
import HighlightedNotes from "./components/notes/HighlightedNotes.vue";
import Messages from "./components/Messages.vue";
import Prescriptions from "./components/Prescriptions.vue";
import Emails from "../emails/index.vue";
import Faxes from "./components/Faxes.vue";
import Appointment from "../../../components/appointment/index.vue";
import RoomSelectionModal from "../../room-management/rooms/components/RoomSelectionModal.vue";
import PatientAddEdit from "./AddEdit.vue";
import Sales from "./components/Sales.vue";
import CheckInActions from "./components/CheckinActions.vue";
import CreatePrescription from "./components/CreatePrescription.vue";
import ViewPrescriptionModal from "./components/modals/PrescriptionDetailsModal.vue";
import OpenCases from "./components/OpenCases.vue";
import CaseModal from "./components/CaseModal.vue";
import ActiveCasesAlert from "./components/ActiveCasesAlert.vue";
import ActiveCasesModal from "./components/ActiveCasesModal.vue";
import fields from "./fields";

const props = defineProps({
    id: {
        required: true,
    },
    activeTab: {
        type: String,
        default: "overview",
    },
    activeChildTab: {
        type: String,
        default: "",
    },
});

const { t } = useI18n();
const store = useStore();

const router = useRouter();
const loading = ref(false);
const { permsArray, formatDate } = common();

// Initialize tooth image cache for dental chart
const { preloadVisibleImages } = useToothImageCache();

const patient = ref(null);
const activeTabKey = ref(props.activeTab);

// Sub-tabs state
const clinicalCareSubTab = ref(
    props.activeTab === "clinical-care" && props.activeChildTab
        ? props.activeChildTab
        : "dental-chart"
);
const schedulingSubTab = ref(
    props.activeTab === "scheduling-visits" && props.activeChildTab
        ? props.activeChildTab
        : "appointments"
);
const financialSubTab = ref(
    props.activeTab === "financial" && props.activeChildTab
        ? props.activeChildTab
        : "sales"
);
const communicationSubTab = ref(
    props.activeTab === "communication-records" && props.activeChildTab
        ? props.activeChildTab
        : "messages"
);

const appointmentVisible = ref(false);
const appointmentFormData = ref({
    doctor_id: null,
    patient_id: null,
    description: "",
    room_id: null,
    treatment_type_id: null,
    duration: 30,
});
const rooms = ref([]);

// Patient AddEdit state
const patientAddEditVisible = ref(false);
const patientFormData = ref({});

// Room selection state
const roomSelectionVisible = ref(false);

// Prescription modal state
const prescriptionModalVisible = ref(false);
const prescriptionAppointmentId = ref(null);

// CheckinActions ref and Prescriptions ref
const checkinActionsRef = ref(null);
const prescriptionsRef = ref(null);
const messagesRef = ref(null);

// Case modal state
const caseModalVisible = ref(false);
const selectedCase = ref(null);

// View Prescription Modal State
const viewPrescriptionVisible = ref(false);
const selectedPrescriptionToView = ref(null);

// Unread messages count
const unreadMessagesCount = ref(0);

// Get current doctor ID from store
const currentDoctorId = ref(
    store.state.auth?.user?.doctor_id || store.state.auth?.user?.xid || ""
);

// Notes refresh trigger
const notesRefreshTrigger = ref(false);

// Notes sidebar state from VueX
const notesSidebarVisible = computed({
    get: () => store.state.ui.notesSidebarVisible,
    set: (value) => store.commit("ui/SET_NOTES_SIDEBAR_VISIBLE", value),
});

// Computed property to check if patient has appointment today
const hasAppointmentToday = computed(() => {
    if (!patient.value?.next_appointment?.appointment_date) return false;

    const appointmentDate = new Date(
        patient.value.next_appointment.appointment_date
    );
    const today = new Date();

    return appointmentDate.toDateString() === today.toDateString();
});

// Computed property for current appointment (used in header and room assignment)
const currentAppointment = computed(() => {
    // First check if CheckinActions component has a current appointment
    if (checkinActionsRef.value?.currentAppointment) {
        return checkinActionsRef.value.currentAppointment;
    }
    // Fallback to patient's next appointment
    return hasAppointmentToday.value ? patient.value?.next_appointment : null;
});

// Active cases data from API
const patientOpenCases = ref([]);
const activeCasesLoading = ref(false);
const activeCasesModalVisible = ref(false);

// Fetch active cases for patient
const fetchActiveCases = async () => {
    try {
        activeCasesLoading.value = true;
        const response = await axiosAdmin.get(
            `patients/${props.id}/active-cases`
        );
        patientOpenCases.value = response.data || [];
    } catch (error) {
        console.error("Error fetching active cases:", error);
        patientOpenCases.value = [];
    } finally {
        activeCasesLoading.value = false;
    }
};

// Computed property to check if patient has any open cases
const hasOpenCases = computed(() => {
    return patientOpenCases.value.some(
        (c) => c.status === "open" || c.status === "in_progress"
    );
});

// Computed property to check if patient has open critical/high cases
const hasOpenCriticalCases = computed(() => {
    return patientOpenCases.value.some(
        (c) =>
            (c.priority === "critical" || c.priority === "high") &&
            (c.status === "open" || c.status === "in_progress")
    );
});

// Get the first critical/high case to display in modal
const openCriticalCase = computed(() => {
    return (
        patientOpenCases.value.find(
            (c) =>
                (c.priority === "critical" || c.priority === "high") &&
                (c.status === "open" || c.status === "in_progress")
        ) || null
    );
});

// Simple status display functions for header tags
const getStatusForAppointment = (appointment) => {
    if (!appointment) return "scheduled";
    if (appointment.checkout_datetime) return "checked_out";
    if (appointment.completed_datetime) return "completed";
    if (appointment.in_progress_datetime) return "in_progress";
    if (appointment.checkin_datetime) return "checked_in";
    if (appointment.arrive_datetime) return "checked_in";
    return "scheduled";
};

const getStatusText = (appointment) => {
    const s = getStatusForAppointment(appointment);
    switch (s) {
        case "scheduled":
            return t("appointments.scheduled") || "Scheduled";
        case "checked_in":
            return t("appointments.checked_in") || "Checked in";
        case "in_progress":
            return t("appointments.in_progress") || "In Progress";
        case "completed":
            return t("appointments.completed") || "Completed";
        case "checked_out":
            return t("appointments.checked_out") || "Checked out";
        case "cancelled":
            return t("appointments.cancelled") || "Cancelled";
        default:
            return s;
    }
};

const getStatusColor = (appointment) => {
    const s = getStatusForAppointment(appointment);
    switch (s) {
        case "scheduled":
            return "default";
        case "checked_in":
            return "processing";
        case "in_progress":
            return "cyan";
        case "completed":
            return "blue";
        case "checked_out":
            return "green";
        case "cancelled":
            return "red";
        default:
            return "default";
    }
};

const fetchPatientData = () => {
    loading.value = true;

    // Fetch patient basic data only
    axiosAdmin.get(
        `patients/${props.id}?fields=xid,updated_at,user{xid,name,last_name,email,phone,date_of_birth,profile_image,profile_image_url,status,default_address}`
    )
        .then((response) => {
            patient.value = response.data;
        })
        .catch((error) => {
            console.error("Error fetching patient data:", error);
            notification.error({
                message: t("common.error"),
                description: "Failed to fetch patient data",
            });
        })
        .finally(() => {
            loading.value = false;
        });
};

const fetchRooms = () => {
    const roomsPromise = axiosAdmin.get("rooms?fields=id,xid,name");
    Promise.all([roomsPromise])
        .then(([roomsResponse]) => {
            rooms.value = roomsResponse.data;
        })
        .catch((error) => {
            console.error("Error fetching appointment data:", error);
        });
};

const fetchUnreadMessagesCount = async () => {
    try {
        const response = await axiosAdmin.get(`patients/${props.id}/messages/unread-count`);
        unreadMessagesCount.value = response.unread_count || 0;
    } catch (error) {
        console.error("Error fetching unread messages count:", error);
    }
};

const handleTabChange = (key) => {
    let childTab = "";
    switch (key) {
        case "clinical-care":
            childTab = clinicalCareSubTab.value;
            break;
        case "scheduling-visits":
            childTab = schedulingSubTab.value;
            break;
        case "financial":
            childTab = financialSubTab.value;
            break;
        case "communication-records":
            childTab = communicationSubTab.value;
            break;
    }

    // Update the URL to include the selected tab
    router.push({
        name: "admin.patients.detail",
        params: { id: props.id, tab: key, childtab: childTab },
        replace: true, // Use replace to avoid adding entries to browser history
    });

    // Preload tooth images when clinical care tab is selected (which contains dental chart)
    if (key === "clinical-care") {
        preloadVisibleImages("all")
            .then(() => {
                console.log("Tooth images preloaded for instant access");
            })
            .catch((error) => {
                console.warn("Some tooth images failed to preload:", error);
            });
    }
};

const handleSubTabChange = (key) => {
    // Update the URL to include the selected sub-tab
    router.push({
        name: "admin.patients.detail",
        params: {
            id: props.id,
            tab: activeTabKey.value,
            childtab: key,
        },
        replace: true,
    });

    // Preload tooth images when dental chart sub-tab is selected
    if (key === "dental-chart") {
        preloadVisibleImages("all")
            .then(() => {
                console.log("Tooth images preloaded for instant access");
            })
            .catch((error) => {
                console.warn("Some tooth images failed to preload:", error);
            });
    }
};

const handleScheduleAppointment = () => {
    // Initialize with basic required fields
    appointmentFormData.value = {
        doctor_id: null,
        patient_id: patient.value?.xid || null,
        description: "",
        room_id: null,
        treatment_type_id: null,
        duration: 30,
    };

    // If patient has a next appointment, populate the form with existing data
    if (patient.value?.next_appointment) {
        const nextAppointment = patient.value.next_appointment;
        appointmentFormData.value = {
            ...appointmentFormData.value,
            xid: nextAppointment.xid,
            doctor_id: nextAppointment.x_doctor_id,
            patient_id: patient.value.xid,
            room_id: nextAppointment.x_room_id,
            treatment_type_id: nextAppointment.x_treatment_type_id,
            duration: nextAppointment.duration,
            description: nextAppointment.treatment_details || "",
            // Parse the appointment date to get individual components for calendar
            currentMonth: new Date(nextAppointment.appointment_date).getMonth(),
            currentYear: new Date(
                nextAppointment.appointment_date
            ).getFullYear(),
            selectedDate: new Date(nextAppointment.appointment_date).getDate(),
            selectedTimeSlot: new Date(
                nextAppointment.appointment_date
            ).toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                hour12: true,
            }),
        };
    }

    appointmentVisible.value = true;
};

const handleAppointmentSuccess = () => {
    appointmentVisible.value = false;
    // Refresh patient data to get updated appointment info
    fetchPatientData();
};

const handlePatientEditSuccess = () => {
    patientAddEditVisible.value = false;
    // Refresh patient data to show updated information
    fetchPatientData();
};

const handleAssignDoctor = () => {
    router.push({
        name: "admin.patients.assign_doctor",
        params: { patientId: props.id },
    });
};

const handleOpenPOS = () => {
    router.push({
        name: "admin.sales.pos.patient",
        params: { patientId: props.id },
    });
};

const handleEditPatient = () => {
    if (patient.value) {
        patientFormData.value = {
            xid: patient.value.xid,
        };
    }

    patientAddEditVisible.value = true;
};

const handleSelectRoom = () => {
    roomSelectionVisible.value = true;
};

const handleAppointmentUpdated = (updatedAppointment) => {
    // CheckinActions now manages appointment state internally
    // This handler can be used if other components need to react to appointment updates
    console.log('Appointment updated:', updatedAppointment);
};

const handleRoomAssignment = async ({ room, appointment }) => {
    try {
        loading.value = true;

        // Tell CheckinActions to mark this as a local update to prevent duplicate notifications
        if (checkinActionsRef.value?.markAsLocalUpdate) {
            checkinActionsRef.value.markAsLocalUpdate(appointment.xid);
        }

        const response = await axiosAdmin.put(
            `appointments/${appointment.xid}`,
            {
                room_id: room.xid,
            }
        );

        if (response && response.data) {
            roomSelectionVisible.value = false;
            
            // Refresh CheckinActions to show the updated room
            if (checkinActionsRef.value?.fetchNextAppointment) {
                await checkinActionsRef.value.fetchNextAppointment();
            }
            
            notification.success({
                message: t("patients.room_assigned") || "Room Assigned",
                description: `${patient.value?.user?.name} has been assigned to ${room.name}.`,
                duration: 4,
            });
        }
    } catch (error) {
        console.error("Error assigning room:", error);
        notification.error({
            message: t("common.error"),
            description: "Failed to assign room to patient",
        });
    } finally {
        loading.value = false;
    }
};

const handleNoteUpdated = () => {
    // Toggle the refresh trigger to update highlighted notes
    notesRefreshTrigger.value = !notesRefreshTrigger.value;
};

const toggleNotesSidebar = () => {
    notesSidebarVisible.value = !notesSidebarVisible.value;
};

const handleCreatePrescription = (appointment) => {
    prescriptionAppointmentId.value = appointment?.xid || null;
    prescriptionModalVisible.value = true;
};

const handleViewPrescription = (prescription) => {
    console.log("handleViewPrescription called", prescription);
    selectedPrescriptionToView.value = prescription;
    viewPrescriptionVisible.value = true;
};

const handlePrescriptionCreated = () => {
    prescriptionModalVisible.value = false;
    notification.success({
        message: t("prescriptions.created_successfully"),
        description: t("prescriptions.created"),
    });
    // Refresh the prescriptions tab if it's active
    if (activeTabKey.value === "prescriptions") {
        notesRefreshTrigger.value = !notesRefreshTrigger.value;
    }
    // Refresh CheckinActions to show the new prescription
    if (checkinActionsRef.value?.fetchNextAppointment) {
        checkinActionsRef.value.fetchNextAppointment();
    }
};

const handlePrescriptionDeleted = () => {
    viewPrescriptionVisible.value = false;
    notification.success({
        message: t("prescriptions.deleted_successfully"),
        description: t("prescriptions.deleted"),
    });
    // Refresh CheckinActions to reflect prescription deletion
    if (checkinActionsRef.value?.fetchNextAppointment) {
        checkinActionsRef.value.fetchNextAppointment();
    }
    // Refresh the prescriptions tab if it's active
    if (
        activeTabKey.value === "clinical-care" &&
        clinicalCareSubTab.value === "prescriptions"
    ) {
        notesRefreshTrigger.value = !notesRefreshTrigger.value;
    }
};

const handleViewOpenCases = () => {
    // Show modal instead of navigating
    activeCasesModalVisible.value = true;
};

const handleCasesUpdated = () => {
    // Refresh active cases data in Details component
    fetchActiveCases();
};

const handleOpenCaseModal = (caseData) => {
    selectedCase.value = caseData;
    caseModalVisible.value = true;
};

const handleEditCase = (caseData) => {
    // This would open the AddEdit drawer for cases
    // For now, we'll just close the modal
    caseModalVisible.value = false;
};

const handleEditCaseFromModal = (caseData) => {
    // Close the active cases modal and open the edit modal
    activeCasesModalVisible.value = false;
    selectedCase.value = caseData;
    caseModalVisible.value = true;
};

onMounted(() => {
    fetchRooms();
    fetchPatientData();
    fetchUnreadMessagesCount();
    fetchActiveCases();
});
</script>

<style scoped>
.patient-detail-container {
    border-radius: 8px;
}

.patient-details {
    line-height: 1.8;
}

.detail-row {
    margin-bottom: 5px;
}

.detail-label {
    font-weight: 500;
    color: #666;
    width: 150px;
}

.gap-15 {
    gap: 15px;
}

h3 {
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 15px;
}

.patient-avatar :deep(.ant-avatar) {
    display: flex;
    align-items: center;
    justify-content: center;
}

.patient-tabs {
    overflow: hidden;
}

.notes-sidebar {
    transition: width 0.3s ease;
}

/* Main tabs styling */
:deep(.main-tabs > .ant-tabs-nav) {
    margin-bottom: 16px;
}

/* Sub-tabs styling */
:deep(.sub-tabs) {
    margin-top: -16px;
}

:deep(.sub-tabs > .ant-tabs-nav) {
    background-color: #fafafa;
    padding: 8px 16px;
    margin: 0 -24px 16px -24px;
    border-bottom: 1px solid #f0f0f0;
}

:deep(.sub-tabs .ant-tabs-tab) {
    padding: 8px 16px;
    margin: 0 4px;
}

:deep(.sub-tabs .ant-tabs-tab-active) {
    background-color: white;
    border-radius: 4px 4px 0 0;
}

/* Remove extra padding from sub-tab content */
:deep(.sub-tabs > .ant-tabs-content-holder) {
    padding-top: 0;
}
</style>
