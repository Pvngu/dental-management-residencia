<template>
    <a-select
        v-model:value="inputValue"
        style="width: 100%"
        show-search
        allow-clear
        optionFilterProp="title"
        :placeholder="$t('common.select_default_text', [placeholder])"
        @change="(value) => $emit('onChange', value)"
        @search="(value) => (formData.name = value)"
        @dropdownVisibleChange="(visible) => { if (!visible) onManageMode = false; }"
        :options="filteredOptions"
        :mode="mode"
    >
        <template #option="{ value, label }">
            <a-flex justify="space-between" class="manage-select-item">
                <span>{{ label }}</span>
                <a-space v-if="onManageMode">
                    <a-button v-if="showStatus" type="text" size="small" @click.stop="toggleStatus(value)">
                        <span class="text-xs">
                            {{ options.find((option) => option.xid === value).is_active ? $t("common.mark_as_inactive") : $t("common.mark_as_active") }}
                        </span>
                    </a-button>
                    <a-button type="text" size="small" @click.stop="onDelete(value)">
                        <template #icon>
                            <DeleteOutlined style="color: red" />
                        </template>
                    </a-button>
                </a-space>
            </a-flex>
        </template>
        <template #dropdownRender="{ menuNode: menu }">
            <v-nodes :vnodes="menu" />
            <div v-if="permsArray.includes(`${url}_manage`) || permsArray.includes('admin')">
                <a-divider style="margin: 8px 0" />
                <a-row>
                    <a-col :span="24">
                        <a-flex v-if="simpleForm" :gap="8">
                            <a-input
                                v-model:value="formData.name"
                                placeholder="Please enter new item"
                            />
                            <a-button
                                type="text"
                                @click="addItem"
                                :disabled="!formData.name"
                                :loading="loading"
                            >
                                <template #icon>
                                    <PlusOutlined />
                                </template>
                                {{ $t("common.add") }}
                            </a-button>
                        </a-flex>
                        <a-button v-else block>
                            <PlusOutlined />
                            {{ $t("common.add") }}
                        </a-button>
                    </a-col>
                    <a-col :span="24" class="mt-2" v-if="options.length">
                        <a-button block @click="onManageMode = !onManageMode">
                            {{ onManageMode ? $t("common.cancel") : $t("common.manage") }}
                        </a-button>
                    </a-col>
                </a-row>
            </div>
        </template>
    </a-select>
</template>

<script>
import { defineComponent, onMounted, ref, watch, computed } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
} from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import apiAdmin from "../../../composable/apiAdmin";
import common from "../../../composable/common";
import { useI18n } from "vue-i18n";

const VNodes = defineComponent({
    props: {
        vnodes: {
            type: Object,
            required: true,
        },
    },
    render() {
        return this.vnodes;
    },
});

export default defineComponent({
    props: {
        value: {
            type: [String, Array],
            default: undefined,
        },
        simpleForm: {
            type: Boolean,
            default: false,
        },
        url: {
            type: String,
            default: "",
        },
        options: {
            type: Array,
            default: () => [],
        },
        placeholder: {
            type: String,
            default: "common.option",
        },
        showStatus: {
            type: Boolean,
            default: false,
        },
        mode: {
            type: String,
            default: undefined,
        },
        params: {
            type: Object,
            default: () => ({}),
        },
    },
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        VNodes,
    },
    emits: ["onChange", "onAddEditSuccess", "onDelete"],
    setup(props, { emit }) {
        const { addEditRequestAdmin, rules, loading } = apiAdmin();
        const { permsArray } = common();
        const { t } = useI18n();
        const inputValue = ref(undefined);
        const formData = ref({
            name: "",
        });
        const options = ref([]);
        const onManageMode = ref(false);

        const filteredOptions = computed(() => {
            return options.value
            .filter((option) => {
                if (props.showStatus) {
                    return onManageMode.value ? true : option.is_active == 1;
                }
                return true;
            })
            .map((option) => ({
                label: option.name,
                value: option.xid,
                title: option.name,
                disabled: onManageMode.value,
            }));
        });

        onMounted(() => {
            if (!props.options) {
                axiosAdmin.get(`${url}/all`).then((res) => {
                    options.value = res.data;
                });
            } else {
                options.value = props.options;
            }
        });

        const addItem = () => {
            loading.value = true;

            addEditRequestAdmin({
                url: props.url,
                data: {
                    name: formData.value.name,
                    ...props.params,
                },
                successMessage: t("common.add_success"),
                success: (res) => {
                    // Add new option to options list
                    const newOption = {
                        xid: res.xid,
                        name: formData.value.name,
                        ...(props.showStatus ? { is_active: 1 } : {}),
                    };
                    options.value.push(newOption);

                    formData.value.name = "";

                    // Handle multiple mode
                    if (props.mode === 'multiple') {
                        // Keep existing values and add the new one
                        const newValues = Array.isArray(inputValue.value) ? [...inputValue.value] : [];
                        newValues.push(res.xid);
                        inputValue.value = newValues;
                        emit("onChange", newValues);
                    } else {
                        // Single mode - replace the value
                        inputValue.value = res.xid;
                        emit("onChange", res.xid);
                    }
                    
                    emit("onAddEditSuccess", res);
                },
            });
        };

        const onDelete = (xid) => {
            loading.value = true;

            addEditRequestAdmin({
                url: props.url + "/" + xid,
                data: {
                    _method: "delete",
                },
                successMessage: t("common.delete_success"),
                success: (res) => {
                    options.value = options.value.filter(
                        (option) => option.xid !== xid
                    );
                    
                    // Handle multiple mode
                    if (props.mode === 'multiple' && Array.isArray(inputValue.value)) {
                        if (inputValue.value.includes(xid)) {
                            const newValues = inputValue.value.filter(val => val !== xid);
                            inputValue.value = newValues;
                            emit("onChange", newValues);
                        }
                    } else if (inputValue.value === xid) {
                        // Single mode
                        inputValue.value = undefined;
                        emit("onChange", undefined);
                    }

                    emit("onDelete", xid);
                },
            });
        }

        const toggleStatus = (xid) => {
            loading.value = true;

            addEditRequestAdmin({
                url: props.url + "/update-status",
                data: {
                    xid: xid,
                },
                successMessage: t("common.update_success"),
                success: (res) => {
                    options.value = options.value.map((option) => {
                        if (option.xid === xid) {
                            option.is_active = res.is_active;
                        }
                        return option;
                    });

                    // If the option is inactive, remove it from selection
                    if (res.is_active === 0) {
                        // Handle multiple mode
                        if (props.mode === 'multiple' && Array.isArray(inputValue.value)) {
                            if (inputValue.value.includes(xid)) {
                                const newValues = inputValue.value.filter(val => val !== xid);
                                inputValue.value = newValues;
                                emit("onChange", newValues);
                            }
                        } else if (inputValue.value === xid) {
                            // Single mode
                            inputValue.value = undefined;
                            emit("onChange", undefined);
                        }
                    }
                },
            });
        };

        watch(
            () => props.value,
            (newValue) => {
                if (newValue) {
                    // Handle both string and array values
                    inputValue.value = newValue;
                } else {
                    // Reset to undefined or empty array based on mode
                    inputValue.value = props.mode === 'multiple' ? [] : undefined;
                }
            },
            { immediate: true }
        );

        watch(
            () => rules.value,
            (newValue) => {
                if (newValue.name) {
                    message.error(newValue.name.message);
                }
            }
        );

        return {
            options,
            formData,
            addItem,
            onDelete,

            inputValue,
            rules,

            loading,
            permsArray,
            onManageMode,
            toggleStatus,
            filteredOptions
        };
    },
});
</script>

<style>
/* .ant-select-item
.ant-select-item-option-content 
.manage-select-item
.ant-space {
    visibility: hidden;
    transition: none !important;
}

.ant-select-item:hover 
.ant-select-item-option-content 
.manage-select-item
.ant-space {
    visibility: visible;
    transition: none !important;
} */

.ant-select-dropdown .ant-select-item-option-disabled {
    color: unset !important;
}
</style>