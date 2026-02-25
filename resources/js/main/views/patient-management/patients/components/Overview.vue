<template>
    <div class="patient-overview">
        <!-- Summary Cards Row -->
        <a-skeleton active :loading="loading" :paragraph="{ rows: 1 }" v-if="loading">
            <template #title>
                <div style="height: 100px;"></div>
            </template>
        </a-skeleton>
        <a-row :gutter="[16,16]" class="mb-4" v-else>
            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <StateWidget
                    :title="$t('appointments.total_appointments')"
                    :value="appointmentStats.total || 0"
                    icon="calendar"
                    color="#1890ff"
                />
            </a-col>
            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <StateWidget
                    :title="$t('appointments.upcoming')"
                    :value="appointmentStats.upcoming || 0"
                    icon="clock"
                    color="#52c41a"
                />
            </a-col>
            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <StateWidget
                    :title="$t('appointments.completed')"
                    :value="appointmentStats.completed || 0"
                    icon="check"
                    color="#722ed1"
                />
            </a-col>
        </a-row>

        <!-- Medical Alerts Section -->
        <a-skeleton active :loading="loading" :paragraph="{ rows: 1 }" v-if="loading" class="mb-4">
        </a-skeleton>
        <div v-else-if="patientData">
            <a-alert
                v-if="!patientData?.allergies || patientData?.allergies.length === 0"
                type="success"
                show-icon
                :message="$t('patients.no_known_allergies')"
                class="mb-4"
            />
            <a-alert
                v-else
                type="warning"
                show-icon
                :message="$t('patients.medical_alerts')"
                :description="`${$t('patients.allergies')}: ${patientData?.allergies}`"
                class="mb-4"
            />
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4 pb-4">
            <!-- Upcoming Appointments Section -->
            <div class="bg-white p-5 rounded-lg shadow-sm">
                <div class="flex items-center mb-4">
                    <CalendarOutlined class="mr-2 mb-2" />
                    <h3 class="text-lg font-medium">{{ $t('appointments.upcoming_appointments') }}</h3>
                </div>
                <a-skeleton active :loading="loading" :paragraph="{ rows: 4 }" v-if="loading"></a-skeleton>
    
                <div v-else-if="upcomingAppointments.length === 0" class="text-gray-500 text-center py-5">
                    {{ $t('appointments.no_upcoming_appointments') }}
                </div>
    
                <div v-else>
                    <a-list item-layout="horizontal" :data-source="upcomingAppointments">
                        <template #renderItem="{ item }">
                            <a-list-item>
                                <a-list-item-meta>
                                    <template #title>
                                        <div>{{ item.title }}</div>
                                    </template>
                                    <template #description>
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <CalendarOutlined class="mr-2" />
                                                <span>{{ item.date }}</span>
                                                <ClockCircleOutlined class="ml-4 mr-2" />
                                                <span>{{ item.time }}</span>
                                                <EnvironmentOutlined class="ml-4 mr-2" />
                                                <span>{{ item.location }}</span>
                                            </div>
                                            <div>{{ item.description }}</div>
                                        </div>
                                    </template>
                                </a-list-item-meta>
                                <div>
                                    <a-tag :color="getStatusColor(item.status)">
                                        {{ item.status }}
                                    </a-tag>
                                </div>
                            </a-list-item>
                        </template>
                    </a-list>
                </div>
            </div>
    
            <!-- Recent Appointments Section -->
            <div class="bg-white p-5 rounded-lg shadow-sm">
                <div class="flex items-center mb-4">
                    <HistoryOutlined class="mr-2 mb-2" />
                    <h3 class="text-lg font-medium">{{ $t('appointments.recent_appointments') }}</h3>
                </div>
                
                <a-skeleton active :loading="loading" :paragraph="{ rows: 4 }" v-if="loading"></a-skeleton>
    
                <div v-else-if="recentAppointments.length === 0" class="text-gray-500 text-center py-5">
                    {{ $t('appointments.no_recent_appointments') }}
                </div>
    
                <div v-else>
                    <a-list item-layout="horizontal" :data-source="recentAppointments">
                        <template #renderItem="{ item }">
                            <a-list-item>
                                <a-list-item-meta>
                                    <template #title>
                                        <div>{{ item.title }}</div>
                                    </template>
                                    <template #description>
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <CalendarOutlined class="mr-2" />
                                                <span>{{ item.date }}</span>
                                                <ClockCircleOutlined class="ml-4 mr-2" />
                                                <span>{{ item.time }}</span>
                                                <UserOutlined class="ml-4 mr-2" />
                                                <span>{{ item.doctor }}</span>
                                            </div>
                                            <div>{{ item.description }}</div>
                                        </div>
                                    </template>
                                </a-list-item-meta>
                                <div>
                                    <a-tag :color="getStatusColor(item.status)">
                                        {{ item.status }}
                                    </a-tag>
                                </div>
                            </a-list-item>
                        </template>
                    </a-list>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import {
    CalendarOutlined,
    ClockCircleOutlined,
    EnvironmentOutlined,
    HistoryOutlined,
    UserOutlined,
    AlertOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../../common/composable/apiAdmin";
import StateWidget from "../../../../../common/components/common/card/StateWidget.vue";

const props = defineProps({
    patientId: {
        type: [String, Number],
        required: true
    },
    patientData: {
        type: Array,
        default: () => ([])
    }
});

const { loading } = apiAdmin();

const appointmentStats = ref({});

const upcomingAppointments = ref([]);
const recentAppointments = ref([]);

const fetchPatientOverview = async () => {
    loading.value = true;
    try {
        const response = await axiosAdmin.get(`patients/${props.patientId}/overview`);
        appointmentStats.value = response.data.stats;
        upcomingAppointments.value = response.data.upcomingAppointments || [];
        recentAppointments.value = response.data.recentAppointments || [];

        loading.value = false;
    } catch (error) {
        // loading.value = false;
        console.error('Error fetching patient overview:', error);
    }
};

const getStatusColor = (status) => {
    switch (status.toLowerCase()) {
        case 'completed':
            return 'green';
        case 'scheduled':
            return 'blue';
        case 'cancelled':
            return 'red';
        default:
            return 'default';
    }
};

onMounted(() => {
    fetchPatientOverview();
    console.log('Overview component mounted for patient ID:', props.patientId);
});
</script>
