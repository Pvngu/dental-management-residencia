<template>
    <div
        class="bg-white transition-[width,opacity] duration-300 ease-in-out shrink-0 overflow-hidden"
        :class="
            visible
                ? 'w-64 border-r border-gray-200 opacity-100'
                : 'w-0 border-r-0 opacity-0'
        "
    >
        <div class="w-64 flex flex-col">
            <!-- Header -->
            <div class="p-4 flex items-center border-b border-gray-100">
                <span class="font-semibold text-gray-800 truncate">{{
                    $t("calendar.dentist") || "Doctors"
                }}</span>
            </div>

            <!-- Filter List -->
            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                <!-- All Team Option (Hidden in Single Select Mode) -->
                <div
                    v-if="!singleSelect"
                    class="flex items-center gap-3 mb-1 rounded hover:bg-gray-50 p-2 cursor-pointer transition-colors"
                    @click.self="toggleAll"
                >
                    <a-checkbox
                        :checked="isAllSelected"
                        :indeterminate="isIndeterminate"
                        @change="toggleAll"
                    />
                    <div
                        class="flex items-center gap-3 overflow-hidden"
                        @click="toggleAll"
                    >
                        <div
                            class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 text-gray-500 border border-gray-200"
                        >
                            <TeamOutlined />
                        </div>
                        <span class="text-sm font-medium text-gray-800 truncate"
                            >All team ({{ dentists.length }})</span
                        >
                    </div>
                </div>

                <!-- Individual Doctors -->
                <div
                    v-for="dentist in dentists"
                    :key="dentist.xid"
                    class="flex items-center gap-3 mb-2 rounded hover:bg-gray-50 p-2 cursor-pointer transition-colors"
                    @click.self="toggleDentist(dentist.xid)"
                >
                    <a-radio
                        v-if="singleSelect"
                        :checked="
                            selectedDentists.length === 1 &&
                            selectedDentists[0] === dentist.xid
                        "
                        @change="() => toggleDentist(dentist.xid)"
                    />
                    <a-checkbox
                        v-else
                        :checked="selectedDentists.includes(dentist.xid)"
                        @change="() => toggleDentist(dentist.xid)"
                    />
                    <div
                        class="flex items-center gap-3 overflow-hidden"
                        @click="toggleDentist(dentist.xid)"
                    >
                        <a-avatar
                            :src="dentist.image"
                            :alt="dentist.name"
                            :size="24"
                            class="flex-shrink-0 bg-gray-200 border border-gray-200 text-gray-600"
                        >
                            {{ dentist.name.charAt(0) }}
                        </a-avatar>
                        <span
                            class="text-sm text-gray-700 truncate"
                            :title="dentist.name"
                            >{{ dentist.name }}</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { TeamOutlined } from "@ant-design/icons-vue";

const props = defineProps({
    dentists: {
        type: Array,
        required: true,
        default: () => [],
    },
    selectedDentists: {
        type: Array,
        required: true,
        default: () => [],
    },
    visible: {
        type: Boolean,
        default: true,
    },
    singleSelect: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:selectedDentists"]);

const isAllSelected = computed(() => {
    return (
        props.dentists.length > 0 &&
        props.selectedDentists.length === props.dentists.length
    );
});

const isIndeterminate = computed(() => {
    return (
        props.selectedDentists.length > 0 &&
        props.selectedDentists.length < props.dentists.length
    );
});

const toggleAll = () => {
    if (isAllSelected.value) {
        // Deselect all
        emit("update:selectedDentists", []);
    } else {
        // Select all
        const allIds = props.dentists.map((d) => d.xid);
        emit("update:selectedDentists", allIds);
    }
};

const toggleDentist = (id) => {
    if (props.singleSelect) {
        emit("update:selectedDentists", [id]);
        return;
    }

    const newSelection = [...props.selectedDentists];
    const index = newSelection.indexOf(id);
    if (index > -1) {
        newSelection.splice(index, 1);
    } else {
        newSelection.push(id);
    }
    emit("update:selectedDentists", newSelection);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 20px;
}
</style>
