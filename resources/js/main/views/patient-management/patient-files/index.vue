<template>
    <!-- Patient Files Statistics Cards -->
    <a-row :gutter="[16,16]" class="mb-4">
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('patient_files.total_files')"
                :value="fileStats.totalFiles || 0"
                :loading="loadingStats"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('patient_files.total_size')"
                :value="formatTotalSize(fileStats.totalSize)"
                :loading="loadingStats"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('patient_files.image_files')"
                :value="fileStats.imageFiles || 0"
                :loading="loadingStats"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('patient_files.pdf_files')"
                :value="fileStats.pdfFiles || 0"
                :loading="loadingStats"
            />
        </a-col>
    </a-row>

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
                    :row-selection="{
                        selectedRowKeys: table.selectedRowKeys,
                        onChange: onRowSelectChange,
                        getCheckboxProps: (record) => ({
                            disabled: false,
                            name: record.xid,
                        }),
                    }"
                    bordered
                    size="middle"
                >
                    <template #title>
                        <a-row justify="space-between" align="middle" class="table-header">
                            <a-col>
                                <a-space>
                                    <a-button
                                        v-if="
                                            table.selectedRowKeys.length > 0 &&
                                            (permsArray.includes('patient_files_delete') ||
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
                            </a-col>
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
                        </a-row>
                    </template>
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'name'">
                            <a-button>
                                <template #icon>
                                    <component :is="getFileIcon(record)" style="font-size: 14px;" />
                                </template>
                            </a-button>
                            <span class="ml-2">
                                <a-typography-text
                                    :style="{ width: '320px' }"
                                    :ellipsis="{ tooltip: record.name }"
                                    :content="record.name"
                                />
                            </span>
                        </template>
                        <template v-if="column.dataIndex === 'patient'">
                            <UserInfo :user="record.patient?.user" :showRole="false" />
                        </template>
                        <template v-if="column.dataIndex === 'uploaded_by'">
                            <UserInfo :user="record.creator" :showRole="false" />
                        </template>
                        <template v-if="column.dataIndex === 'file_size'">
                            {{ bToReadable(record.file_size) }}
                        </template>
                        <template v-if="column.dataIndex === 'created_at'">
                            {{ formatDate(record.created_at) }}
                        </template>
                        <template v-if="column.dataIndex === 'action'">
                            <a-dropdown>
                                <a-button type="text" shape="circle">
                                    <template #icon><EllipsisOutlined /></template>
                                </a-button>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item @click="downloadS3File(record.file, 'patient-files')">
                                            <DownloadOutlined /> {{ $t("common.download") }}
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="
                                                permsArray.includes('patient_files_edit') ||
                                                permsArray.includes('admin')
                                            "
                                            @click="editItem(record)"
                                        >
                                            <EditOutlined /> {{ $t("common.edit") }}
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="
                                                permsArray.includes('patient_files_delete') ||
                                                permsArray.includes('admin')
                                            "
                                            @click="showDeleteConfirm(record.xid)"
                                        >
                                            <DeleteOutlined /> {{ $t("common.delete") }}
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>

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
</template>

<script>
import { ref, onMounted, onUnmounted } from "vue";
import { DeleteOutlined, EditOutlined, DownloadOutlined, EllipsisOutlined, FileOutlined, PictureOutlined, FilePdfOutlined, FileWordOutlined } from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import StateWidget from "../../../../common/components/common/card/StateWidget.vue";

export default {
    components: {
        DeleteOutlined,
        EditOutlined,
        DownloadOutlined,
        EllipsisOutlined,
        FileOutlined,
        PictureOutlined,
        FilePdfOutlined,
        FileWordOutlined,
        AddEdit,
        UserInfo,
        StateWidget,
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
        } = fields();
        const { permsArray, formatDate, downloadS3File } = common();
        const crudVariables = crud();

        const fileStats = ref({
            totalFiles: 0,
            totalSize: 0,
            imageFiles: 0,
            pdfFiles: 0,
            docFiles: 0,
            otherFiles: 0,
        });

        const loadingStats = ref(true);

        const fetchFileStats = () => {
            axiosAdmin
                .get("/patient-files/stats")
                .then((response) => {
                    fileStats.value = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching file statistics:", error);
                })
                .finally(() => {
                    loadingStats.value = false;
                });
        };

        const formatTotalSize = (bytes) => {
            if (!bytes) return "0 B";
            if (bytes < 1024) return `${bytes} B`;
            const kb = bytes / 1024;
            if (kb < 1024) return `${kb.toFixed(2)} KB`;
            const mb = kb / 1024;
            if (mb < 1024) return `${mb.toFixed(2)} MB`;
            const gb = mb / 1024;
            return `${gb.toFixed(2)} GB`;
        };

        const getFileIcon = (record) => {
            const type = record.file_type;
            let ext = "";
            if (record.file) {
                const parts = record.file.split(".");
                if (parts.length > 1) {
                    ext = parts.pop().toLowerCase();
                }
            }
            if (ext === "pdf") return "FilePdfOutlined";
            if (ext === 'docx' || ext === 'doc') return "FileWordOutlined";
            if (type && type.startsWith("image")) return "PictureOutlined";
            return "FileOutlined";
        };

        const bToReadable = (bytes) => {
            if (bytes === null || bytes === undefined) return "0 KB";
            if (bytes < 1024) return `${bytes} B`;
            const kb = bytes / 1024;
            if (kb < 1024) return `${kb.toFixed(2)} KB`;
            const mb = kb / 1024;
            return `${mb.toFixed(2)} MB`;
        };

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "patient_files";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            setUrlData();
            fetchFileStats();

            // Listen for add event from parent
            window.addEventListener('add-patient-file', handleAddEvent);
        });

        onUnmounted(() => {
            window.removeEventListener('add-patient-file', handleAddEvent);
        });

        const handleAddEvent = () => {
            crudVariables.addItem();
        };

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            formatDate,
            downloadS3File,
            getFileIcon,
            bToReadable,
            fileStats,
            loadingStats,
            formatTotalSize,
        };
    },
};
</script>
