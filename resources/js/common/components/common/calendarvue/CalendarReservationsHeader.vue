<template>
    <div class="w-full">
        <div
            class="grid grid-cols-3 items-center p-4 bg-white border-b border-gray-100"
        >
            <div class="nav-left flex items-center gap-4">
                <a-button
                    type="text"
                    @click="$emit('toggle-sidebar')"
                    class="text-gray-500 flex items-center gap-2"
                >
                    <template #icon>
                        <MenuFoldOutlined v-if="sidebarVisible" />
                        <MenuUnfoldOutlined v-else />
                    </template>
                    {{
                        selectedDentists.length === dentists.length &&
                        dentists.length > 0
                            ? $t("calendar.all_doctor_calendars")
                            : $t("calendar.selected_calendars", {
                                  count: selectedDentists.length,
                              })
                    }}
                </a-button>

                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <CalendarOutlined />
                    <span>
                        {{ totalAppointments }}
                    </span>
                </div>
            </div>

            <div class="nav-center flex items-center justify-center gap-4">
                <div
                    class="flex items-center gap-3 text-base font-semibold text-gray-800"
                >
                    <span class="min-w-[160px] text-center">{{
                        formattedDate || formattedCurrentDate
                    }}</span>
                    <a-button
                        @click="$emit('previous-date')"
                        type="text"
                        shape="circle"
                    >
                        <template #icon>
                            <LeftOutlined style="font-size: 12px" />
                        </template>
                    </a-button>
                    <a-button
                        @click="$emit('next-date')"
                        type="text"
                        shape="circle"
                    >
                        <template #icon>
                            <RightOutlined style="font-size: 12px" />
                        </template>
                    </a-button>
                </div>
                <a-button @click="$emit('go-today')" type="text">
                    {{ $t("calendar.today") }}
                </a-button>
            </div>

            <div class="nav-right flex justify-end gap-2">
                <!-- Dropdown View Calendar Menu -->
                <a-tooltip :title="$t('calendar.calendar_view')">
                    <a-dropdown :trigger="['click']" placement="bottomRight">
                        <a-button type="text" shape="circle">
                            <template #icon>
                                <TeamOutlined v-if="viewMode === 'day'" />
                                <CalendarOutlined
                                    v-else-if="viewMode === 'week'"
                                />
                                <AppstoreOutlined
                                    v-else-if="viewMode === 'month'"
                                />
                                <ProfileOutlined v-else />
                            </template>
                        </a-button>
                        <template #overlay>
                            <a-menu :selectedKeys="[viewMode]">
                                <!-- <a-menu-item
                                    key="day"
                                    @click="$emit('update:viewMode', 'day')"
                                >
                                    <div class="flex items-center gap-2">
                                        <TeamOutlined />
                                        {{ $t("calendar.doctors") }}
                                    </div>
                                </a-menu-item>
                                <a-menu-item
                                    key="week"
                                    @click="$emit('update:viewMode', 'week')"
                                >
                                    <div class="flex items-center gap-2">
                                        <CalendarOutlined />
                                        {{ $t("calendar.week") }}
                                    </div>
                                </a-menu-item> -->
                                <a-menu-item
                                    key="month"
                                    @click="$emit('update:viewMode', 'month')"
                                >
                                    <div class="flex items-center gap-2">
                                        <AppstoreOutlined />
                                        Month
                                    </div>
                                </a-menu-item>
                                <!-- <a-menu-item
                                    key="agenda"
                                    @click="$emit('update:viewMode', 'agenda')"
                                >
                                    <div class="flex items-center gap-2">
                                        <ProfileOutlined />
                                        Agenda
                                    </div>
                                </a-menu-item> -->
                            </a-menu>
                        </template>
                    </a-dropdown>
                </a-tooltip>

                <!-- Add Dropdown Menu -->
                <a-tooltip :title="$t('common.add')">
                    <a-dropdown :trigger="['click']" placement="bottomRight">
                        <a-button type="text" shape="circle">
                            <template #icon
                                ><PlusOutlined style="font-size: 12px"
                            /></template>
                        </a-button>
                        <template #overlay>
                            <a-menu>
                                <a-menu-item
                                    key="appointment"
                                    @click="
                                        $emit('add-reservation', 'appointment')
                                    "
                                >
                                    <template #icon>
                                        <CalendarOutlined />
                                    </template>
                                    {{ $t("appointments.new_appointment") }}
                                </a-menu-item>
                                <a-menu-item
                                    key="event"
                                    @click="$emit('add-reservation', 'event')"
                                >
                                    <template #icon>
                                        <TeamOutlined />
                                    </template>
                                    {{ $t("calendar.new_event") }}
                                </a-menu-item>
                            </a-menu>
                        </template>
                    </a-dropdown>
                </a-tooltip>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import {
    PlusOutlined,
    TeamOutlined,
    LeftOutlined,
    RightOutlined,
    MenuFoldOutlined,
    MenuUnfoldOutlined,
    CalendarOutlined,
    ProfileOutlined,
    AppstoreOutlined,
} from "@ant-design/icons-vue";

const props = defineProps({
    currentDate: Object, // Moment
    formattedDate: String, // String
    viewMode: String,
    totalAppointments: Number,
    dentists: Array,
    selectedDentists: Array,
    sidebarVisible: Boolean,
});

defineEmits([
    "update:selectedDentists",
    "update:viewMode",
    "add-reservation",
    "go-today",
    "previous-date",
    "next-date",
    "toggle-sidebar",
]);

const formattedCurrentDate = computed(() =>
    props.currentDate.format("ddd, DD MMM YYYY"),
);
</script>
