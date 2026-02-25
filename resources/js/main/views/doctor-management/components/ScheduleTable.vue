<template>
    <div class="border rounded-lg overflow-hidden">
        <a-space direction="vertical" style="width: 100%">
            <a-row
                align="center"
                justify="space-between"
                style="
                    background: #fafafa;
                    border-bottom: 1px solid #f0f0f0;
                    padding: 12px 0;
                    font-weight: 600;
                "
            >
                <a-col :xs="24" :sm="6" :md="5" :lg="4">
                    <span>{{
                        $t("doctor_schedules.available_on")
                    }}</span>
                </a-col>
                <a-col :xs="24" :sm="6" :md="5" :lg="4">
                    <span>{{
                        $t("doctor_schedules.available_from")
                    }}</span>
                </a-col>
                <a-col
                    :xs="2"
                    :sm="2"
                    :md="1"
                    :lg="1"
                    style="text-align: center"
                >
                    <span>→</span>
                </a-col>
                <a-col :xs="24" :sm="6" :md="5" :lg="4">
                    <span>{{
                        $t("doctor_schedules.available_to")
                    }}</span>
                </a-col>
                <a-col :xs="24" :sm="4" :md="3" :lg="3">
                    <span>{{ $t("common.status") }}</span>
                </a-col>
            </a-row>
            <a-row
                v-for="day in days"
                :key="day"
                align="middle"
                justify="space-between"
                :gutter="16"
                style="padding-block: 12px"
                :class="
                    !isDayAvailable(day) ? 'bg-gray-200 opacity-70' : ''
                "
            >
                <a-col :xs="24" :sm="6" :md="5" :lg="4">
                    <span style="font-weight: 500">{{
                        $t("common.day_" + day)
                    }}</span>
                </a-col>
                <a-col :xs="24" :sm="6" :md="5" :lg="4">
                    <TimePicker
                        :time="schedule[day].from"
                        @timeChanged="
                            (changeDateTime) =>
                                (schedule[day].from = changeDateTime)
                        "
                        style="width: 100%"
                        :disabled="
                            !schedule[day].status ||
                            !isDayAvailable(day)
                        "
                        :disabledTime="getDisabledTimesForDay(day)"
                        :class="
                            rules && rules[`schedule.${day}.from`]
                                ? 'border-red-500'
                                : ''
                        "
                    />
                    <div
                        v-if="rules && rules[`schedule.${day}.from`]"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ rules[`schedule.${day}.from`].message }}
                    </div>
                </a-col>
                <a-col
                    :xs="2"
                    :sm="2"
                    :md="1"
                    :lg="1"
                    style="text-align: center"
                >
                    <span>-</span>
                </a-col>
                <a-col :xs="24" :sm="6" :md="5" :lg="4">
                    <TimePicker
                        :time="schedule[day].to"
                        @timeChanged="
                            (changeDateTime) =>
                                (schedule[day].to = changeDateTime)
                        "
                        style="width: 100%"
                        :disabled="
                            !schedule[day].status ||
                            !isDayAvailable(day)
                        "
                        :disabledTime="getDisabledTimesForDay(day)"
                        :class="
                            rules && rules[`schedule.${day}.to`]
                                ? 'border-red-500'
                                : ''
                        "
                    />
                    <div
                        v-if="rules && rules[`schedule.${day}.to`]"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ rules[`schedule.${day}.to`].message }}
                    </div>
                </a-col>
                <a-col :xs="24" :sm="4" :md="3" :lg="3">
                    <a-switch
                        v-model:checked="schedule[day].status"
                        style="margin-left: 8px"
                        :checkedValue="1"
                        :uncheckedValue="0"
                        @change="
                            (isChecked) =>
                                handleDaySwitch(day, isChecked)
                        "
                        :disabled="!isDayAvailable(day)"
                        :style="
                            !isDayAvailable(day)
                                ? {
                                      background: '#f0f0f0',
                                      borderColor: '#d9d9d9',
                                  }
                                : {}
                        "
                    />
                </a-col>
            </a-row>
        </a-space>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import TimePicker from "../../../../common/components/common/calendar/TimePicker.vue";
import { useI18n } from "vue-i18n";

export default defineComponent({
    name: "ScheduleTable",
    props: {
        schedule: {
            type: Object,
            required: true,
        },
        days: {
            type: Array,
            required: true,
        },
        handleDaySwitch: {
            type: Function,
            required: true,
        },
        isDayAvailable: {
            type: Function,
            required: true,
        },
        getDisabledTimesForDay: {
            type: Function,
            required: true,
        },
        rules: {
            type: Object,
            default: () => ({}),
        },
    },
    components: {
        TimePicker,
    },
    setup() {
        const { t } = useI18n();

        return {
            t,
        };
    },
});
</script>
