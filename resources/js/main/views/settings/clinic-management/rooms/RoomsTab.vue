<template>
    <div>
        <a-space class="mb-3">
            <template
                v-if="
                    permsArray.includes('rooms_create') ||
                    permsArray.includes('admin')
                "
            >
                <a-button type="primary" @click="addItem">
                    <PlusOutlined />
                    {{ $t("room.add") }}
                </a-button>
            </template>
            <a-button
                v-if="
                    table.selectedRowKeys.length > 0 &&
                    (permsArray.includes('rooms_delete') ||
                        permsArray.includes('admin'))
                "
                type="primary"
                @click="showSelectedDeleteConfirm"
                danger
            >
                <template #icon><DeleteOutlined /></template>
                {{ $t("common.delete") }}
            </a-button>
        </a-space>

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
            :roomTypes="roomTypes"
        />

        <a-row class="mt-5">
            <a-col :span="24">
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
                            <a-row justify="end" align="middle" class="table-header">
                                <a-col 
                                    :xs="21"
                                    :sm="16"
                                    :md="16"
                                    :lg="12"
                                    :xl="8"
                                >
                                    <a-input-search
                                        style="width: 100%"
                                        v-model:value="table.searchString"
                                        :placeholder="$t('common.search')"
                                        show-search
                                        @search="onTableSearch"
                                        @change="onTableSearch"
                                        :loading="table.loading"
                                    />
                                </a-col>
                                <a-col class="ml-2">
                                    <Filters 
                                        @onReset="resetFilters"
                                        :filters="filters"
                                    >
                                        <a-col :span="24">
                                            <a-form-item :label="$t('room.room_type')">
                                                <a-select
                                                    v-model:value="filters.room_type_id"
                                                    :placeholder="$t('common.select_default_text', [$t('room.room_type')])"
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    optionFilterProp="title"
                                                    show-search
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="roomType in roomTypes"
                                                        :key="roomType.xid"
                                                        :title="roomType.name"
                                                        :value="roomType.xid"
                                                    >
                                                        {{ roomType.name }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                            <a-form-item :label="$t('room.floor')">
                                                <a-input
                                                    v-model:value="filters.floor"
                                                    :placeholder="$t('common.placeholder_default_text', [$t('room.floor')])"
                                                    @pressEnter="setUrlData"
                                                    @change="setUrlData"
                                                    style="width: 100%"
                                                />
                                            </a-form-item>
                                            <a-form-item :label="$t('common.date')">
                                                <DateRangePicker
                                                    @dateTimeChanged="
                                                        (changedDateTime) => {
                                                            extraFilters.dates = changedDateTime;
                                                            setUrlData();
                                                        }
                                                    "
                                                />
                                            </a-form-item>
                                        </a-col>
                                    </Filters>
                                </a-col>
                            </a-row>
                        </template>

                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'room_type_id'">
                                {{ record.room_type ? record.room_type.name : '' }}
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag
                                    :color="getStatusColor(record.status)"
                                >
                                    {{ $t(`room.${record.status.toLowerCase()}`) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'notes'">
                                <p style="text-align: justify; white-space: wrap;" >
                                    <a-typography-paragraph
                                        :ellipsis="{
                                            rows: 2,
                                            expandable: true,
                                            symbol: $t('common.more'),
                                        }"
                                        :content="record.notes"
                                    />
                                </p>
                            </template>
                            <template v-if="column.dataIndex === 'floor'">
                                <span>
                                    {{
                                        record.floor === 0
                                        ? 'Ground floor'
                                        :record.floor === 1
                                        ? '1st floor'
                                        : record.floor === 2
                                        ? '2nd floor'
                                        : record.floor === 3
                                        ? '3rd floor'
                                        : record.floor
                                        ? `${record.floor}th floor`
                                        : ''
                                    }}
                                </span>
                            </template>
                            <template v-if="column.dataIndex === 'capacity'">
                                <span>
                                    {{
                                        record.capacity === 1
                                            ? `1 ${$t('room.patient')}`
                                            : `${record.capacity} ${$t('room.patients')}`
                                    }}
                                </span>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="permsArray.includes('rooms_edit') || permsArray.includes('admin')"
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="(permsArray.includes('rooms_delete') || permsArray.includes('admin'))"
                                    type="primary"
                                    @click="showDeleteConfirm(record.xid)"
                                    style="margin-left: 4px"
                                    danger
                                >
                                    <template #icon><DeleteOutlined /></template>
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../../common/composable/crud";
import common from "../../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../../../common/components/common/select/Filters.vue";
import DateRangePicker from "../../../../../common/components/common/calendar/DateRangePicker.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        AddEdit,
        Filters,
        DateRangePicker,
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
            roomTypes,
            getPrefetchData,
        } = fields();
        const { permsArray } = common();
        const crudVariables = crud();
        
        const filters = ref({
            status: "",
            room_type_id: undefined,
            floor: "",
        });

        const extraFilters = ref({
            dates: [],
        });

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;
                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "room";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };
                setUrlData();
            });
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                filters,
                extraFilters: extraFilters.value
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        const resetFilters = () => {
            filters.value = {
                status: "",
                room_type_id: undefined,
                floor: "",
            };
            extraFilters.value = {
                dates: [],
            };
            setUrlData();
        };

        const getStatusColor = (status) => {
            switch (status) {
                case 'Available':
                    return 'green';
                case 'Occupied':
                    return 'orange';
                case 'Reserved':
                    return 'blue';
                case 'Maintenance':
                    return 'red';
                default:
                    return '';
            }
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            filters,
            extraFilters,
            roomTypes,
            setUrlData,
            resetFilters,
            getStatusColor,
        };
    },
};
</script>
