<template>
    <AddEdit
        :addEditType="addEditType"
        :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
        />
        
        <a-row :gutter="16">
            <!-- Left Sidebar - Doctor Selection -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="8">
                <DoctorSelection
                    :doctors="doctors"
                    v-model:selectedDoctorIds="selectedDoctorIds"
                    v-model:searchText="doctorSearchText"
                    :loading="doctorsLoading"
                    :showBreakCount="true"
                    @toggle="toggleDoctorSelection"
                />
            </a-col>

            <!-- Right Content - Breaks List -->
            <a-col :xs="24" :sm="24" :md="18" :lg="18" :xl="16">
                <div class="table-responsive">
                    <a-table
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        bordered
                        size="middle"
                    >
                        <template #title>
                            <a-row justify="space-between" align="middle" class="table-header">
                                <a-col>
                                    <div v-if="selectedDoctorIds.length > 0">
                                        <a-avatar-group :max-count="3" :size="32">
                                            <a-avatar
                                                v-for="doctorId in selectedDoctorIds"
                                                :key="doctorId"
                                                :src="
                                                    getDoctorById(doctorId)?.user
                                                        ?.profile_image_url
                                                "
                                            >
                                                {{
                                                    getInitials(
                                                        getDoctorById(doctorId)?.user
                                                    )
                                                }}
                                            </a-avatar>
                                        </a-avatar-group>
                                        <span
                                            style="margin-left: 12px; font-weight: 500"
                                        >
                                            {{ getSelectedDoctorNames() }}
                                        </span>
                                    </div>
                                    <div v-else style="color: #8c8c8c">
                                        {{ $t("doctor_breaks.no_doctor_selected") }}
                                    </div>
                                </a-col>
                            </a-row>
                        </template>
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'doctor'">
                                <user-info :user="record.doctor.user" :showEmail="true" />
                            </template>
                            <template v-if="column.dataIndex === 'date'">
                                <span v-if="record.date && record.every_day === 0">
                                    {{ formatDate(record.date) }}
                                </span>
                                <span v-else>
                                    <a-tag
                                        color="blue"
                                    >
                                        {{ $t("doctor_breaks.every_day") }}
                                    </a-tag>
                                </span>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <!-- <a-button
                                    v-if="
                                        permsArray.includes('doctor_breaks_edit') ||
                                        permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button> -->
                                <a-button
                                    v-if="
                                        (permsArray.includes('doctor_breaks_delete') ||
                                            permsArray.includes('admin')) &&
                                        (!record.children || record.children.length == 0)
                                    "
                                    type="primary"
                                    @click="showDeleteConfirm(record.xid)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><DeleteOutlined /></template>
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { PlusOutlined, EditOutlined, DeleteOutlined, UserOutlined } from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import DoctorSelection from "../components/DoctorSelection.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        UserOutlined,
        AddEdit,
        UserInfo,
        DoctorSelection,
    },
    setup() {
        const { url, addEditUrl, initData, columns, hashableColumns, doctors, getPrefetchData } = fields();
        const { permsArray, formatDate } = common();
        const crudVariables = crud();

        const selectedDoctorIds = ref([]);
        const doctorSearchText = ref("");
        const doctorsLoading = ref(true);
        
        const handleAddBreak = () => {
            crudVariables.addItem();
        };

        onMounted(() => {
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "doctor_breaks";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            // Load doctors without selecting any by default
            if (doctors.value && doctors.value.length > 0) {
                doctorsLoading.value = false;
            } else {
                getPrefetchData().then(() => {
                    doctorsLoading.value = false;
                });
            }

            setUrlData();
            
            window.addEventListener('add-break', handleAddBreak);
        });
        
        onUnmounted(() => {
            window.removeEventListener('add-break', handleAddBreak);
        });

        const toggleDoctorSelection = (doctorId) => {
            setUrlData();
        };

        const getDoctorById = (doctorId) => {
            return doctors.value.find((d) => d.xid === doctorId);
        };

        const getInitials = (user) => {
            if (!user) return "?";
            const firstInitial = user.name
                ? user.name.charAt(0).toUpperCase()
                : "";
            const lastInitial = user.last_name
                ? user.last_name.charAt(0).toUpperCase()
                : "";
            return `${firstInitial}${lastInitial}`;
        };

        const getSelectedDoctorNames = () => {
            const names = selectedDoctorIds.value
                .map((id) => {
                    const doctor = doctors.value.find((d) => d.xid === id);
                    if (!doctor) return null;
                    const fullName = [doctor.user.name, doctor.user.last_name]
                        .filter(Boolean)
                        .join(" ");
                    return fullName;
                })
                .filter(Boolean);

            if (names.length > 2) {
                return `${names.slice(0, 2).join(", ")} +${names.length - 2}`;
            }
            return names.join(", ");
        };

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                extraFilters: {
                    doctor_ids: selectedDoctorIds.value,
                },
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        // Override addItem to pre-populate selected doctors
        const originalAddItem = crudVariables.addItem;
        crudVariables.addItem = () => {
            originalAddItem();
            
            // Pre-populate doctor_id with selected doctors
            if (selectedDoctorIds.value.length > 0) {
                crudVariables.formData.value.doctor_id = [...selectedDoctorIds.value];
            }
        };

        // Override addEditSuccess to refresh the list
        const originalAddEditSuccess = crudVariables.addEditSuccess;
        crudVariables.addEditSuccess = (id) => {
            originalAddEditSuccess(id);
            
            // Refresh doctors list to update break counts
            getPrefetchData();
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            formatDate,
            setUrlData,
            selectedDoctorIds,
            doctorSearchText,
            doctorsLoading,
            doctors,
            toggleDoctorSelection,
            getSelectedDoctorNames,
            getDoctorById,
            getInitials,
        };
    },
}
</script>
