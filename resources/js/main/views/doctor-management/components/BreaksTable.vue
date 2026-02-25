<template>
    <div>
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
            :hideDoctorSelect="true"
        />
        
        <a-row :gutter="[0, 16]">
            <a-col :span="24">
                <a-button type="primary" @click="addItem">
                    <PlusOutlined />
                    {{ $t("doctor_breaks.add") }}
                </a-button>
            </a-col>
            <a-col :span="24">
                <a-table
                    :columns="columns"
                    :data-source="table.data"
                    :loading="table.loading"
                    :row-key="(record) => record.xid"
                    :pagination="table.pagination"
                    @change="handleTableChange"
                    bordered
                    size="middle"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'date'">
                            <span v-if="record.date && record.every_day === 0">
                                {{ formatDate(record.date) }}
                            </span>
                            <a-tag v-else color="blue">
                                {{ $t("doctor_breaks.every_day") }}
                            </a-tag>
                        </template>
                        <template v-if="column.dataIndex === 'action'">
                            <a-button
                                type="primary"
                                danger
                                size="small"
                                @click="showDeleteConfirm(record.xid)"
                            >
                                <DeleteOutlined />
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent, watch, ref } from "vue";
import { PlusOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import crud from "../../../../common/composable/crud";
import AddEdit from "../breaks/AddEdit.vue";

export default defineComponent({
    props: ["doctorId"],
    components: {
        PlusOutlined,
        DeleteOutlined,
        AddEdit,
    },
    setup(props) {
        const { dayjs } = common();
        const crudVariables = crud();

        const addEditUrl = "doctor-breaks";
        
        const filters = ref({});
        
        const columns = [
            {
                title: 'Break From',
                dataIndex: 'break_from',
            },
            {
                title: 'Break To',
                dataIndex: 'break_to',
            },
            {
                title: 'Date',
                dataIndex: 'date',
            },
            {
                title: 'Action',
                dataIndex: 'action',
            },
        ];

        const initData = {
            doctor_id: props.doctorId,
            break_from: "",
            break_to: "",
            every_day: 1,
            date: "",
        };

        const formatDate = (date) => {
            if (!date) return '';
            return dayjs(date).format('MMM DD, YYYY');
        };

        const fetchData = () => {
            if (!props.doctorId) return;
            
            crudVariables.tableUrl.value = {
                url: `doctor-breaks?fields=id,xid,doctor_id,x_doctor_id,break_from,break_to,every_day,date&doctor_id=${props.doctorId}`,
                filters,
            };
            
            crudVariables.fetch({
                page: 1,
            });
        };

        // Initialize
        crudVariables.crudUrl.value = addEditUrl;
        crudVariables.langKey.value = "doctor_breaks";
        crudVariables.initData.value = { ...initData };
        crudVariables.formData.value = { ...initData };

        // Watch for doctor ID changes
        watch(() => props.doctorId, (newId) => {
            if (newId) {
                crudVariables.formData.value.doctor_id = newId;
                crudVariables.initData.value.doctor_id = newId;
                fetchData();
            }
        }, { immediate: true });

        // Override editItem to ensure all fields are properly populated
        const editItem = (record) => {
            crudVariables.editItem(record);
            // Ensure doctor_id is set from the prop
            crudVariables.formData.value.doctor_id = props.doctorId;
            // Ensure time and date fields are populated
            crudVariables.formData.value.break_from = record.break_from || '';
            crudVariables.formData.value.break_to = record.break_to || '';
            crudVariables.formData.value.date = record.date || '';
            crudVariables.formData.value.every_day = record.every_day !== undefined ? record.every_day : 1;
        };

        return {
            ...crudVariables,
            editItem,
            columns,
            formatDate,
            addEditUrl,
        };
    },
});
</script>
