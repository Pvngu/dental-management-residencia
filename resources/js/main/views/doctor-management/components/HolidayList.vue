<template>
    <div>
        <!-- Skeleton Loading -->
        <div v-if="loading && (!holidays || holidays.length === 0)">
            <div v-for="i in 3" :key="i" style="margin-bottom: 16px">
                <a-skeleton
                    active
                    :paragraph="{ rows: 3 }"
                    style="padding: 16px; border: 1px solid #f0f0f0; border-radius: 8px"
                />
            </div>
        </div>
        
        <!-- Content -->
        <a-spin :spinning="loading">
            <div v-if="holidays && holidays.length > 0">
                <div
                    v-for="holiday in holidays"
                    :key="holiday.xid"
                    class="holiday-card"
                    style="
                        margin-bottom: 16px;
                        padding: 16px;
                        border: 1px solid #f0f0f0;
                        border-radius: 8px;
                        transition: all 0.3s;
                    "
                >
                    <a-flex justify="space-between" align="start">
                        <a-flex gap="16" align="start" style="flex: 1">
                            <CalendarOutlined
                                style="
                                    font-size: 24px;
                                    color: #8b5cf6;
                                    margin-top: 4px;
                                "
                            />
                            <div style="flex: 1">
                                <h3
                                    style="
                                        margin: 0 0 8px 0;
                                        font-size: 16px;
                                    "
                                >
                                    {{
                                        holiday.reason ||
                                        $t("doctor_holidays.no_reason")
                                    }}
                                </h3>
                                <a-flex
                                    gap="8"
                                    wrap="wrap"
                                    style="margin-bottom: 12px"
                                >
                                    <a-tag color="blue">
                                        <CalendarOutlined
                                            style="margin-right: 4px"
                                        />
                                        {{ formatDateRange(holiday) }}
                                    </a-tag>
                                    <a-tag color="default">
                                        <ClockCircleOutlined
                                            style="margin-right: 4px"
                                        />
                                        {{ getDaysDuration(holiday) }}
                                    </a-tag>
                                    <a-tag
                                        :color="
                                            getHolidayTypeColor(
                                                holiday.holiday_type
                                            )
                                        "
                                    >
                                        {{
                                            getHolidayTypeLabel(
                                                holiday.holiday_type
                                            )
                                        }}
                                    </a-tag>
                                    <a-tag
                                        :color="getStatusColor(holiday.status)"
                                    >
                                        {{ getStatusLabel(holiday.status) }}
                                    </a-tag>
                                </a-flex>
                                <user-info
                                    v-if="showDoctorInfo && holiday.doctor"
                                    :user="holiday.doctor.user"
                                    :showEmail="false"
                                />
                            </div>
                        </a-flex>
                        <a-flex gap="8" v-if="showActions">
                            <a-button
                                v-if="canEdit"
                                type="text"
                                @click="$emit('edit', holiday)"
                            >
                                <template #icon><EditOutlined /></template>
                            </a-button>
                            <a-button
                                v-if="canDelete"
                                type="text"
                                danger
                                @click="$emit('delete', holiday.xid)"
                            >
                                <template #icon><DeleteOutlined /></template>
                            </a-button>
                        </a-flex>
                    </a-flex>
                </div>
            </div>
            <a-empty
                v-else
                :description="$t('doctor_holidays.no_holidays')"
            />
        </a-spin>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import {
    CalendarOutlined,
    ClockCircleOutlined,
    EditOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import UserInfo from "../../../../common/components/user/UserInfo.vue";

export default defineComponent({
    name: "HolidayList",
    components: {
        CalendarOutlined,
        ClockCircleOutlined,
        EditOutlined,
        DeleteOutlined,
        UserInfo,
    },
    props: {
        holidays: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: false,
        },
        showDoctorInfo: {
            type: Boolean,
            default: true,
        },
        showActions: {
            type: Boolean,
            default: true,
        },
        canEdit: {
            type: Boolean,
            default: true,
        },
        canDelete: {
            type: Boolean,
            default: true,
        },
        holidayTypes: {
            type: Array,
            default: () => [
                { value: "vacation", label: "Vacation" },
                { value: "sick_leave", label: "Sick Leave" },
                { value: "personal", label: "Personal" },
                { value: "other", label: "Other" },
            ],
        },
        statusOptions: {
            type: Array,
            default: () => [
                { value: "pending", label: "Pending" },
                { value: "approved", label: "Approved" },
                { value: "rejected", label: "Rejected" },
            ],
        },
    },
    emits: ["edit", "delete"],
    setup(props) {
        const { formatDate, dayjs } = common();

        const formatDateRange = (holiday) => {
            const start = holiday.start_date || holiday.date;
            const end = holiday.end_date || holiday.date;

            if (start === end) {
                return formatDate(start);
            }
            return `${formatDate(start)} - ${formatDate(end)}`;
        };

        const getDaysDuration = (holiday) => {
            const start = dayjs(holiday.start_date || holiday.date);
            const end = dayjs(holiday.end_date || holiday.date);
            const days = end.diff(start, "day") + 1;

            return days === 1
                ? `${days} day`
                : `${days} days`;
        };

        const getHolidayTypeLabel = (type) => {
            const typeObj = props.holidayTypes.find((t) => t.value === type);
            return typeObj ? typeObj.label : type;
        };

        const getHolidayTypeColor = (type) => {
            const colors = {
                vacation: "blue",
                sick_leave: "orange",
                personal: "green",
                other: "purple",
            };
            return colors[type] || "default";
        };

        const getStatusLabel = (status) => {
            const statusObj = props.statusOptions.find((s) => s.value === status);
            return statusObj ? statusObj.label : status;
        };

        const getStatusColor = (status) => {
            const colors = {
                pending: "orange",
                approved: "green",
                rejected: "red",
            };
            return colors[status] || "default";
        };

        return {
            formatDateRange,
            getDaysDuration,
            getHolidayTypeLabel,
            getHolidayTypeColor,
            getStatusLabel,
            getStatusColor,
        };
    },
});
</script>

<style scoped>
.holiday-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-color: #d9d9d9 !important;
}
</style>
