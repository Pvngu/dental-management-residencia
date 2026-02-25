<template>
    <a-drawer
        :title="$t('calendar.appointment_overview')"
        :open="visible"
        :width="drawerWidth"
        :maskClosable="true"
        @close="onClose"
    >
        <!-- Header extra: show index of total and prev/next buttons -->
        <template #extra>
            <div class="flex items-center gap-3 w-full">
                <span class="text-sm whitespace-nowrap"
                    >{{ index }} of {{ total }}</span
                >
                <a-input-group compact>
                    <a-button
                        :disabled="index <= 1"
                        @click="onPrev"
                        :class="{ 'flash-red': flashLeft }"
                    >
                        <template #icon>
                            <LeftOutlined />
                        </template>
                    </a-button>
                    <a-button
                        :disabled="index >= total"
                        @click="onNext"
                        :class="{ 'flash-red': flashRight }"
                    >
                        <template #icon>
                            <RightOutlined />
                        </template>
                    </a-button>
                </a-input-group>
            </div>
        </template>
        <!-- Personal detail card (uses appointmentData or dummy values) -->
        <div class="bg-white rounded-lg p-4 shadow-sm mb-4">
            <div class="flex gap-3 items-center mb-3">
                <a-avatar
                    size="large"
                    class="bg-gray-100 text-gray-800 font-semibold"
                    >{{ patientName.charAt(0) }}</a-avatar
                >
                <div>
                    <h3 class="text-lg font-semibold m-0">{{ patientName }}</h3>
                    <div class="flex gap-2 items-center text-gray-400 text-sm">
                        <span class="flex items-center gap-2"
                            ><PhoneOutlined /> {{ patientPhone }}</span
                        >
                        <span>•</span>
                        <span class="flex items-center gap-2"
                            ><MailOutlined /> {{ patientEmail }}</span
                        >
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-md p-3 mb-3">
                <strong>{{ t("common.reason") || "Reason" }}</strong>
                <p class="mt-2 text-gray-600 leading-relaxed">
                    {{ patientReason }}
                </p>
            </div>

            <div class="flex gap-6 items-start">
                <div>
                    <div class="font-semibold mb-2 text-gray-800">
                        {{ t("common.diagnose") || "Diagnose" }}
                    </div>
                    <ul class="m-0 pl-4 text-gray-600">
                        <li v-for="(d, i) in diagnoses" :key="i">{{ d }}</li>
                    </ul>
                </div>
                <div>
                    <div class="font-semibold mb-2 text-gray-800">
                        {{
                            t("common.preferred_pharmacy") ||
                            "Preferred Pharmacy"
                        }}
                    </div>
                    <div class="flex flex-wrap">
                        <a-tag
                            v-for="(p, i) in pharmacies"
                            :key="i"
                            class="m-1"
                            >{{ p }}</a-tag
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Information (card) -->
        <div class="mb-3">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">
                {{ t("calendar.booking_information") || "Booking Information" }}
            </h4>
        </div>
        <a-card bordered class="mb-4">
            <div class="flex items-center justify-between">
                <div class="flex-1 pr-4">
                    <div
                        class="text-sm text-gray-500 flex items-center gap-2 mb-2"
                    >
                        <CalendarOutlined /> {{ t("common.date") || "Date" }}
                    </div>
                    <div class="text-base text-gray-900">
                        {{ bookingDateString }}
                    </div>
                </div>

                <div class="flex-1 pl-4">
                    <div
                        class="text-sm text-gray-500 flex items-center gap-2 mb-2"
                    >
                        <UserOutlined />
                        {{
                            t("booking.appointment_type") || "Appointment Type"
                        }}
                    </div>
                    <div>
                        <a-tag color="#f0f2f5" class="inline-flex items-center">
                            <span class="mr-2">🟢</span>
                            {{ apptType }}
                        </a-tag>
                    </div>
                </div>
            </div>
        </a-card>

        <!-- Planning Schedule (timeline) -->
        <div class="mb-3">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">
                {{ t("calendar.planning_schedule") || "Planning Schedule" }}
            </h4>
        </div>
        <div class="mt-2">
            <a-timeline>
                <a-timeline-item
                    v-for="(item, idx) in planningItems"
                    :key="idx"
                >
                    <a-row>
                        <a-col :span="24">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-2 h-2 bg-blue-500 rounded-full mt-2"
                                ></div>
                                <div class="ml-0 flex-1">
                                    <div class="text-sm text-gray-500 mb-2">
                                        {{
                                            moment(item.date).format(
                                                "DD MMM YYYY",
                                            )
                                        }}
                                        &nbsp; {{ item.time }}
                                    </div>
                                    <a-card
                                        bordered
                                        class="rounded-lg p-3 border-gray-100 bg-white"
                                    >
                                        <h5
                                            class="text-sm font-semibold text-blue-600 mb-3"
                                        >
                                            {{ item.title }}
                                        </h5>
                                        <div
                                            class="grid grid-cols-3 gap-3 items-center"
                                        >
                                            <div>
                                                <div
                                                    class="text-sm text-gray-400 flex items-center gap-2 mb-1"
                                                >
                                                    <UserOutlined /> Doctor
                                                </div>
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <a-avatar
                                                        size="small"
                                                        class="bg-white border border-gray-100 text-gray-800 font-semibold"
                                                        >{{
                                                            item.doctor.charAt(
                                                                0,
                                                            )
                                                        }}</a-avatar
                                                    >
                                                    <div
                                                        class="text-sm font-semibold text-gray-900"
                                                    >
                                                        {{ item.doctor }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <div
                                                    class="text-sm text-gray-400 flex items-center gap-2 mb-1"
                                                >
                                                    <TeamOutlined /> Assistant
                                                    Doctor
                                                </div>
                                                <div
                                                    class="text-sm text-gray-900"
                                                >
                                                    {{ item.assistant }}
                                                </div>
                                            </div>

                                            <div>
                                                <div
                                                    class="text-sm text-gray-400 flex items-center gap-2 mb-1"
                                                >
                                                    <HomeOutlined /> Room
                                                </div>
                                                <div
                                                    class="text-sm text-gray-900"
                                                >
                                                    {{ item.room }}
                                                </div>
                                            </div>
                                        </div>
                                    </a-card>
                                </div>
                            </div>
                        </a-col>
                    </a-row>
                </a-timeline-item>
            </a-timeline>
        </div>

        <!-- History Schedule -->
        <div class="mb-3 mt-6 border-t pt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-4">
                {{ t("common.history") || "History" }}
            </h4>
        </div>
        <div class="mt-2 pl-2">
            <a-timeline>
                <a-timeline-item
                    v-for="(item, idx) in histories"
                    :key="idx"
                    color="gray"
                >
                    <template #dot>
                        <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                    </template>
                    <div class="flex flex-col pb-4">
                        <div class="text-xs text-gray-400 mb-1">
                            {{
                                moment(item.created_at).format(
                                    "ddd DD MMM YYYY",
                                )
                            }}
                        </div>
                        <div class="text-xs text-gray-400 font-bold mb-2">
                            {{ moment(item.created_at).format("h:mm A") }}
                        </div>
                        <div class="flex items-start gap-3">
                            <a-avatar
                                size="small"
                                :src="item.user?.profile_image_url"
                                class="bg-blue-500 text-white text-xs flex-shrink-0"
                            >
                                {{
                                    item.user?.name
                                        ? item.user.name.charAt(0).toUpperCase()
                                        : "S"
                                }}
                            </a-avatar>
                            <div>
                                <div
                                    class="text-sm font-semibold text-gray-900 leading-none mb-1"
                                >
                                    {{ item.user?.name || "System" }}
                                </div>
                                <div class="text-sm text-gray-600 leading-snug">
                                    {{ item.description }}
                                </div>
                            </div>
                        </div>
                    </div>
                </a-timeline-item>
                <div
                    v-if="histories.length === 0"
                    class="text-gray-400 italic text-sm"
                >
                    No history recorded yet.
                </div>
            </a-timeline>
        </div>

        <template #footer>
            <a-space style="width: 100%; justify-content: flex-end">
                <a-button @click="onClose">{{ t("common.close") }}</a-button>
            </a-space>
        </template>
    </a-drawer>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import {
    RightOutlined,
    LeftOutlined,
    PhoneOutlined,
    MailOutlined,
    CalendarOutlined,
    UserOutlined,
    TeamOutlined,
    HomeOutlined,
    ClockCircleOutlined,
} from "@ant-design/icons-vue";
import moment from "moment";

const props = defineProps({
    visible: { type: Boolean, default: false },
    appointmentData: { type: Object, default: () => ({}) },
    index: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
});

const emits = defineEmits(["closed", "update:open", "prev", "next"]);

const { t } = useI18n();

const drawerWidth = computed(() => (window.innerWidth <= 991 ? "90%" : "40%"));

// Derived patient / appointment details (use dummy defaults when missing)
const patientName = computed(() => props.appointmentData?.patient_name || "");
const patientPhone = computed(() => props.appointmentData?.patient_phone || "");
const patientEmail = computed(
    () => props.appointmentData?.patient_email || "jeromebellingham93@mail.com",
);
const patientReason = computed(
    () =>
        props.appointmentData?.reason ||
        "Eating sweet foods, not brushing your teeth regularly. often drink cold water when eating food that is still hot.",
);
const diagnoses = computed(
    () =>
        props.appointmentData?.diagnoses || [
            "Cavities",
            "Exposed nerves causing pain",
            "Tartar teeth",
        ],
);
const pharmacies = computed(
    () =>
        props.appointmentData?.pharmacies || [
            "Cataflam 50 mg",
            "Ponstan 500 mg",
            "Mefinal 500 mg",
            "Ibuprofen 400 mg",
        ],
);

// Booking info
const apptDate = computed(
    () => props.appointmentData?.appointment_date || null,
);
const apptStart = computed(
    () => props.appointmentData?.start_time || "09:00 AM",
);
const apptEnd = computed(() => props.appointmentData?.end_time || "10:00 AM");
const apptType = computed(
    () => props.appointmentData?.appointment_type || "Chat WhatsApp",
);

const bookingDateString = computed(() => {
    if (!apptDate.value) return "Thursday, 12 November, 09.00 AM - 10.00AM";
    try {
        const m = moment(apptDate.value);
        const start = apptStart.value;
        const end = apptEnd.value;
        return `${m.format("dddd, DD MMMM")}, ${start} - ${end}`;
    } catch (e) {
        return `${apptDate.value} ${apptStart.value} - ${apptEnd.value}`;
    }
});

// Planning schedule (use appointmentData.planning if present)
const planningItems = computed(() => {
    const data = props.appointmentData?.planning;
    if (Array.isArray(data) && data.length > 0) return data;

    // Dummy items
    return [
        {
            date: "2023-10-12",
            time: "10:30 AM",
            title: "Check up tooth",
            doctor: "Drg. Dianne Rachel",
            assistant: "Maria Kitty",
            room: "Dental A12",
        },
        {
            date: "2023-10-12",
            time: "10:30 AM",
            title: "Prosthetic Tooth Fabrication",
            doctor: "Drg. Dianne Rachel",
            assistant: "Markonah Nicky",
            room: "Laboratorium Tooth 1",
        },
    ];
});

const histories = computed(() => {
    return props.appointmentData?.histories || [];
});

const onClose = () => {
    // emit both a semantic closed event and update the parent's `open` if using v-model:open
    emits("closed");
    emits("update:open", false);
};

import { ref } from "vue";

const onPrev = () => {
    if (props.index <= 1) {
        // flash left
        flashLeft.value = true;
        setTimeout(() => (flashLeft.value = false), 800);
        return;
    }
    emits("prev");
};

const onNext = () => {
    if (props.index >= props.total) {
        // flash right
        flashRight.value = true;
        setTimeout(() => (flashRight.value = false), 800);
        return;
    }
    emits("next");
};

// flash states for UI feedback
const flashLeft = ref(false);
const flashRight = ref(false);

// Keyboard navigation: ArrowLeft -> prev, ArrowRight -> next
const isTypingElement = (el) => {
    if (!el) return false;
    const tag = el.tagName ? el.tagName.toUpperCase() : "";
    if (["INPUT", "TEXTAREA", "SELECT"].includes(tag)) return true;
    if (el.isContentEditable) return true;
    return false;
};

const keydownHandler = (e) => {
    // ignore when modifier keys used
    if (e.altKey || e.ctrlKey || e.metaKey) return;
    if (!props.visible) return;

    const active = document.activeElement;
    if (isTypingElement(active)) return;

    if (e.key === "ArrowLeft") {
        e.preventDefault();
        if (props.index <= 1) {
            flashLeft.value = true;
            setTimeout(() => (flashLeft.value = false), 800);
        } else {
            emits("prev");
        }
    } else if (e.key === "ArrowRight") {
        e.preventDefault();
        if (props.index >= props.total) {
            flashRight.value = true;
            setTimeout(() => (flashRight.value = false), 800);
        } else {
            emits("next");
        }
    }
};

onMounted(() => window.addEventListener("keydown", keydownHandler));
onBeforeUnmount(() => window.removeEventListener("keydown", keydownHandler));
</script>

<style scoped>
/* minimal styling, drawer will inherit global styles */
.flash-red {
    animation: flash-red 0.8s ease-in-out;
    border-color: #ff4d4f !important;
    color: #fff !important;
    background: #ff4d4f !important;
}

@keyframes flash-red {
    0% {
        box-shadow: 0 0 0 rgba(255, 77, 79, 0);
    }
    20% {
        box-shadow: 0 0 8px rgba(255, 77, 79, 0.6);
    }
    50% {
        box-shadow: 0 0 0 rgba(255, 77, 79, 0);
    }
    80% {
        box-shadow: 0 0 8px rgba(255, 77, 79, 0.6);
    }
    100% {
        box-shadow: 0 0 0 rgba(255, 77, 79, 0);
    }
}
</style>
