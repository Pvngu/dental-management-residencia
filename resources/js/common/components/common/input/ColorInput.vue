<template>
    <div class="flex flex-col gap-2">
        <a-select
            v-if="showPredefinedColors"
            v-model:value="inputColor"
            @change="emitChange"
            :disabled="disabled"
            :placeholder="
                $t('common.select_default_text', [$t('doctors.color')])
            "
            style="width: 100%"
            :getPopupContainer="(triggerNode) => triggerNode.parentNode"
        >
            <a-select-option
                v-for="color in googleColors"
                :key="color.value"
                :value="color.value"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="w-4 h-4 rounded-full border border-gray-200 my-1.5"
                        :style="{ backgroundColor: color.value }"
                    ></div>
                    <!-- {{ color.label }} -->
                </div>
            </a-select-option>
        </a-select>

        <div class="flex items-center gap-2" v-if="showCustomColor">
            <input
                type="color"
                v-model="inputColor"
                @change="emitChange"
                class="w-10 h-10 border border-gray-300 rounded cursor-pointer"
                :disabled="disabled"
            />
            <a-input
                v-model:value="inputColor"
                @change="emitChange"
                :placeholder="
                    placeholder
                        ? $t('common.placeholder_default_text', [
                              $t(placeholder),
                          ])
                        : ''
                "
                style="flex: 1"
                pattern="^#[0-9A-Fa-f]{6}$"
                maxlength="7"
                :disabled="disabled"
            />
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, watch } from "vue";

export default defineComponent({
    emits: ["colorChanged"],
    props: {
        value: {
            default: "#039be5",
        },
        placeholder: {
            default: "doctors.color",
        },
        disabled: {
            default: false,
        },
        showPredefinedColors: {
            type: Boolean,
            default: true,
        },
        showCustomColor: {
            type: Boolean,
            default: true,
        },
    },
    setup(props, { emit }) {
        const inputColor = ref(null);

        const googleColors = [
            { value: "#d50000", label: "Tomato" },
            { value: "#e67c73", label: "Flamingo" },
            { value: "#f4511e", label: "Tangerine" },
            { value: "#f6bf26", label: "Banana" },
            { value: "#33b679", label: "Sage" },
            { value: "#0b8043", label: "Basil" },
            { value: "#039be5", label: "Peacock" },
            { value: "#3f51b5", label: "Blueberry" },
            { value: "#7986cb", label: "Lavender" },
            { value: "#8e24aa", label: "Grape" },
            { value: "#616161", label: "Graphite" },
        ];

        watch(
            () => props.value,
            (newValue) => {
                inputColor.value = newValue;
            },
            { immediate: true },
        );

        const emitChange = () => {
            emit("colorChanged", inputColor.value);
        };

        return {
            inputColor,
            emitChange,
            googleColors,
        };
    },
});
</script>
