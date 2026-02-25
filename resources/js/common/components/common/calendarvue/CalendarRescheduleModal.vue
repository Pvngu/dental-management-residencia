<template>
    <a-modal
        :open="visible"
        :title="
            $t(
                'calendar.reschedule_this_appointment',
                'Reschedule this appointment?',
            )
        "
        :closable="true"
        @cancel="handleCancel"
        destroyOnClose
        :footer="null"
        width="480px"
    >
        <div class="flex flex-col gap-5 mt-6 px-2">
            <div class="flex items-start gap-4 text-[15px]">
                <span class="text-gray-500 w-[45px] text-right">{{
                    $t("common.from", "From:")
                }}</span>
                <div class="flex-1 flex gap-4 text-gray-800 tracking-wide">
                    <span>{{ data?.originalStartStr }}</span>
                    <span>{{ data?.originalTimeStr }}</span>
                </div>
            </div>

            <div class="flex items-start gap-4 text-[15px]">
                <span class="text-gray-500 w-[45px] text-right">{{
                    $t("common.to", "To:")
                }}</span>
                <div class="flex-1 flex gap-4 text-gray-800 tracking-wide">
                    <span>{{ data?.newStartStr }}</span>
                    <span>{{ data?.newTimeStr }}</span>
                </div>
            </div>

            <div
                v-if="data?.appointment?.type !== 'event'"
                class="mt-3 text-[15px]"
            >
                <a-checkbox
                    v-model:checked="localNotifyGuests"
                    class="text-gray-800"
                >
                    {{ $t("calendar.notify_provider_guests") }}
                </a-checkbox>
            </div>

            <div class="flex justify-end items-center gap-4 mt-8">
                <a-button type="text" @click="handleCancel">
                    {{ $t("calendar.no_keep") }}
                </a-button>
                <a-button
                    type="primary"
                    :loading="loading"
                    @click="handleConfirm"
                >
                    {{ $t("calendar.yes_reschedule") }}
                </a-button>
            </div>
        </div>
    </a-modal>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    data: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:visible", "confirm", "cancel"]);

const localNotifyGuests = ref(true);

watch(
    () => props.visible,
    (newVal) => {
        if (newVal) {
            localNotifyGuests.value = true;
        }
    },
);

const handleCancel = () => {
    emit("update:visible", false);
    emit("cancel");
};

const handleConfirm = () => {
    emit("confirm", localNotifyGuests.value);
};
</script>
