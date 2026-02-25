<template>
    <a-card class="doctor-selection-card" :bordered="false">
        <template #title>
            <span style="font-weight: 600">{{ title || $t("doctor_breaks.select_doctors") }}</span>
        </template>
        <div class="doctor-selection-content">
            <!-- Search Input -->
            <a-input-search
                v-model:value="searchText"
                :placeholder="$t('common.search')"
                @change="$emit('search', searchText)"
                class="mb-3"
                allow-clear
            />

            <!-- Doctor List -->
            <div class="doctor-list">
                <!-- Skeleton Loading -->
                <div v-if="loading">
                    <div v-for="i in 5" :key="`skeleton-${i}`" style="margin-bottom: 8px">
                        <div style="padding: 12px; border: 1px solid #f0f0f0; border-radius: 8px">
                            <a-flex align="center" gap="12">
                                <a-skeleton-avatar
                                    :size="40"
                                    :active="true"
                                    shape="circle"
                                />
                                <div style="flex: 1">
                                    <a-skeleton
                                        :rows="2"
                                        :paragraph="{ rows: 2, width: ['100%', '80%'] }"
                                        size="small"
                                        :active="true"
                                    />
                                </div>
                            </a-flex>
                        </div>
                    </div>
                </div>
                
                <!-- Actual Content -->
                <div v-else>
                    <a-empty 
                        v-if="filteredDoctors.length === 0"
                        :description="$t('common.no_data')"
                    />
                    <div
                        v-for="doctor in filteredDoctors"
                        :key="doctor.xid"
                        class="doctor-item"
                        :class="{ selected: selectedDoctorIds.includes(doctor.xid) }"
                        @click="toggleDoctor(doctor.xid)"
                    >
                        <a-flex align="center" gap="12">
                            <a-avatar
                                :size="40"
                                :src="doctor.user.profile_image_url"
                            >
                                {{ getInitials(doctor.user) }}
                            </a-avatar>
                            <div style="flex: 1">
                                <div style="font-weight: 500">
                                    {{ doctor.user.name }}
                                    {{ doctor.user.last_name || '' }}
                                </div>
                                <div style="font-size: 12px; color: #8c8c8c; margin-top: 2px">
                                    <template v-if="showHolidayCount">
                                        <span v-if="doctor.total_holidays_count > 0">
                                            {{ doctor.total_holidays_count }}
                                            {{ doctor.total_holidays_count === 1 ? $t("doctor_holidays.holiday") : $t("doctor_holidays.holidays") }}
                                        </span>
                                        <span v-if="doctor.upcoming_holidays_count > 0" style="margin-left: 8px">
                                            • {{ doctor.upcoming_holidays_count }} {{ $t("doctor_holidays.upcoming") }}
                                        </span>
                                    </template>
                                    <template v-else-if="showBreakCount && doctor.total_breaks_count !== undefined">
                                        {{ doctor.total_breaks_count }} {{ $t("doctor_breaks.breaks") }}
                                    </template>
                                </div>
                            </div>
                            <CheckCircleOutlined
                                v-if="selectedDoctorIds.includes(doctor.xid)"
                                style="color: #1890ff; font-size: 20px"
                            />
                        </a-flex>
                    </div>
                </div>
            </div>
        </div>
    </a-card>
</template>

<script>
import { defineComponent, computed } from "vue";
import { CheckCircleOutlined } from "@ant-design/icons-vue";

export default defineComponent({
    components: {
        CheckCircleOutlined,
    },
    props: {
        doctors: {
            type: Array,
            default: () => [],
        },
        selectedDoctorIds: {
            type: Array,
            default: () => [],
        },
        searchText: {
            type: String,
            default: "",
        },
        loading: {
            type: Boolean,
            default: false,
        },
        showHolidayCount: {
            type: Boolean,
            default: false,
        },
        showBreakCount: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: "",
        },
    },
    emits: ["update:selectedDoctorIds", "update:searchText", "toggle", "search"],
    setup(props, { emit }) {
        const filteredDoctors = computed(() => {
            if (!props.searchText) return props.doctors;

            const searchLower = props.searchText.toLowerCase();
            return props.doctors.filter((doctor) => {
                const fullName = `${doctor.user.name} ${doctor.user.last_name}`.toLowerCase();
                return fullName.includes(searchLower);
            });
        });

        const toggleDoctor = (doctorId) => {
            const currentIds = [...props.selectedDoctorIds];
            const index = currentIds.indexOf(doctorId);
            
            if (index > -1) {
                currentIds.splice(index, 1);
            } else {
                currentIds.push(doctorId);
            }
            
            emit("update:selectedDoctorIds", currentIds);
            emit("toggle", doctorId);
        };

        const getInitials = (user) => {
            if (!user) return "?";
            const firstInitial = user.name ? user.name.charAt(0).toUpperCase() : "";
            const lastInitial = user.last_name ? user.last_name.charAt(0).toUpperCase() : "";
            return `${firstInitial}${lastInitial}`;
        };

        return {
            filteredDoctors,
            toggleDoctor,
            getInitials,
        };
    },
});
</script>

<style scoped>
.doctor-selection-card :deep(.ant-card-body) {
    padding: 16px;
    height: calc(100svh - 80px);
}

.doctor-selection-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.doctor-list {
    flex: 1;
    overflow-y: auto;
}

.doctor-item {
    padding: 12px;
    border: 1px solid #f0f0f0;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.doctor-item:hover {
    background-color: #f5f5f5;
    border-color: #d9d9d9 !important;
}

.doctor-item.selected {
    background-color: #e6f7ff;
    border-color: #1890ff !important;
}
</style>
