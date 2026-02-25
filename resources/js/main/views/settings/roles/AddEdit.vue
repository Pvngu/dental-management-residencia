<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('role.display_name')"
                        name="display_name"
                        :help="rules.display_name ? rules.display_name.message : null"
                        :validateStatus="rules.display_name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.display_name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.display_name'),
                                ])
                            "
                            v-on:keyup="formData.name = slugify($event.target.value)"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('role.role_name')"
                        name="name"
                        :help="rules.name ? rules.name.message : null"
                        :validateStatus="rules.name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.role_name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('role.description')"
                        name="description"
                        :help="rules.description ? rules.description.message : null"
                        :validateStatus="rules.description ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.description"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.description'),
                                ])
                            "
                            :rows="4"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('role.permissions')"
                        name="permissions"
                        :help="rules.permissions ? rules.permissions.message : null"
                        :validateStatus="rules.permissions ? 'error' : null"
                    >
                        <div class="d-flex flex-column scroll-y">
                            <div class="tbl-responsive">
                                <a-checkbox-group v-model:value="checkedPermissions">
                                    <table
                                        class="table align-middle table-row-dashed row-gap"
                                    >
                                        <tbody class="text-gray-600 fw-bold">
                                            <tr
                                                v-for="group in sortedPermissionGroups"
                                                :key="group.resource"
                                            >
                                                <td class="text-gray-800">
                                                    {{ $t(group.label) }}
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap">
                                                        <label
                                                            v-for="action in group.actions"
                                                            :key="`${group.resource}_${action}`"
                                                            class="form-check form-check-custom me-1 me-lg-5"
                                                        >
                                                            <a-checkbox
                                                                v-if="permissions[`${group.resource}_${action}`]"
                                                                :value="permissions[`${group.resource}_${action}`]"
                                                            >
                                                                {{ $t(getActionLabel(action)) }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </a-checkbox-group>
                            </div>
                        </div>
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-button
                type="primary"
                @click="onSubmit"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>
<script>
import { defineComponent, ref, onMounted, computed, watch } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import { getPermissionGroups, getActionTranslationKey } from "./permissionConfig";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const roles = ref([]);
        const { t } = useI18n();
        const permissions = ref([]);
        const checkedPermissions = ref([]);
        const { slugify } = common();

        onMounted(() => {
            axiosAdmin
                .get("permissions?fields=id,xid,name&limit=10000")
                .then((response) => {
                    const permissionArray = [];
                    response.data.map((res) => {
                        permissionArray[res.name] = res.xid;
                    });
                    permissions.value = permissionArray;
                });
        });

        const onSubmit = () => {
            const newFormData = {
                ...props.formData,
                permissions: checkedPermissions.value,
            };

            addEditRequestAdmin({
                url: props.url,
                data: newFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        // Computed property to get and sort permission groups
        const sortedPermissionGroups = computed(() => {
            const groups = getPermissionGroups();
            return groups.sort((a, b) => {
                const labelA = t(a.label).toLowerCase();
                const labelB = t(b.label).toLowerCase();
                return labelA.localeCompare(labelB);
            });
        });

        // Helper to get action translation label
        const getActionLabel = (action) => {
            return getActionTranslationKey(action);
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                if (newVal && props.addEditType == "edit") {
                    props.data.permissions.forEach((value) => {
                        checkedPermissions.value.push(value.xid);
                    });
                } else {
                    checkedPermissions.value = [];
                }
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,
            permissions,
            sortedPermissionGroups,
            getActionLabel,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "80%",
            checkedPermissions,
            slugify,
        };
    },
});
</script>

<style lang="less">
.flex-column {
    flex-direction: column !important;
}

.d-flex {
    display: flex !important;
}

.flex-wrap {
    flex-wrap: wrap !important;
}

.tbl-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
}

.align-middle {
    vertical-align: middle !important;
}
.table > tbody {
    vertical-align: inherit;
}
.text-gray-600 {
    color: #7e8299 !important;
}
.fw-bold {
    font-weight: 500 !important;
}
tbody,
td,
tfoot,
th,
thead,
tr {
    border-color: inherit;
    border-style: solid;
    border-width: 0;
}

.table.table-row-dashed tr {
    border-bottom-width: 1px;
    border-bottom-style: dashed;
    border-bottom-color: #eff2f5;
}

.table td:first-child,
.table th:first-child,
.table tr:first-child {
    padding-left: 0;
}

.form-check.form-check-custom {
    display: flex;
    align-items: center;
    padding-left: 0;
    margin: 0;
}

.me-9 {
    margin-right: 2.25rem !important;
}

.table.row-gap td,
.table.row-gap th {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.me-lg-5 {
    margin-right: 5rem !important;
}

.ant-checkbox-group {
    width: 100%;
}
</style>
