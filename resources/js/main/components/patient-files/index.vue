<template>
    <!-- Add Edit Component -->
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
    

    <!-- Upload Section -->
    <UploadFileBig
        :acceptFormat="'image/*,.pdf,.doc,.docx'"
        :formData="uploadFormData"
        folder="patient-files"
        uploadField="file"
        @onFileUploaded="onFileUploaded"
    />

    <!-- Files Table -->
    <div class="mt-5">
        <a-table
            :columns="columns"
            :data-source="table.data"
            :row-key="record => record.id"
            bordered
            size="middle"
            :scroll="scrollStyle"
            :loading="table.loading"
        >
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
                <template v-if="column.dataIndex === 'uploaded_by'">
                    <user-info @click="showViewUserDrawer(record)" :user="record.creator" />
                </template>
                <template v-if="column.dataIndex === 'file_size'">
                    {{ bToReadable(record.file_size) }}
                </template>
                <template v-if="column.dataIndex === 'created_at'">
                    {{ formatDate(record.created_at) }}
                </template>
                <template v-if="column.dataIndex === 'action'">
                    <a-dropdown>
                        <a-button type="text" shape="circle" icon>
                            <EllipsisOutlined />
                        </a-button>
                        <template #overlay>
                            <a-menu>
                                <a-menu-item key="view" @click="downloadS3File(record.file, 'patient-files')">
                                    <DownloadOutlined /> {{ $t("common.download") }}
                                </a-menu-item>
                                <a-menu-item key="edit" @click="editItem(record)">
                                    <EditOutlined /> {{ $t("common.edit") }}
                                </a-menu-item>
                                <a-menu-item key="delete" @click="showDeleteConfirm(record.xid)">
                                    <DeleteOutlined /> {{ $t("common.delete") }}
                                </a-menu-item>
                            </a-menu>
                        </template>
                    </a-dropdown>
                </template>
            </template>
        </a-table>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import UploadFileBig from "../../../common/core/ui/file/UploadFileBig.vue";
import { FileOutlined, PictureOutlined, DownloadOutlined, EditOutlined, DeleteOutlined, EllipsisOutlined, FilePdfOutlined, FileWordOutlined } from "@ant-design/icons-vue";
import AddEdit from "./AddEdit.vue";
import fields from "./fields";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";
import { useI18n } from "vue-i18n";
import UserInfo from "../../../common/components/user/UserInfo.vue";

export default {
    props: {
        id: {
            type: String,
            default: "",
        },
        scrollStyle: {
            type: Object,
            default: null,
        }
    },
    components: {
        UploadFileBig,
        FileOutlined,
        PictureOutlined,
        DownloadOutlined,
        EditOutlined,
        DeleteOutlined,
        EllipsisOutlined,
        FilePdfOutlined,
        FileWordOutlined,
        AddEdit,
        UserInfo,
    },
    setup(props, { emit }) {
        // Get data from fields and crud
        const { initData, url, addEditUrl, columns, filterableColumns } = fields();
        const { addEditRequestAdmin } = apiAdmin();
        const crudVariables = crud();
        const { formatDate, downloadS3File } = common();
        const { t } = useI18n();

        // Define uploadFormData for UploadFileBig component
        const uploadFormData = ref({ file: undefined, file_url: undefined });

        // Map file type to icon
        const getFileIcon = (record) => {
            var type = record.file_type;
            var ext = "";
            if (record.file) {
                const parts = record.file.split(".");
                if (parts.length > 1) {
                    ext = parts.pop().toLowerCase();
                }
            }
            if (ext === "pdf") return "FilePdfOutlined";
            if(ext === 'docx' || ext === 'dox') return "FileWordOutlined"
            if (type && type.startsWith("image")) return "PictureOutlined";
            return "FileOutlined";
        };

        const bToReadable = (bytes) => {
            // handle undefined/null but allow 0
            if (bytes === null || bytes === undefined) return "0 KB";

            // bytes less than 1 KB -> show bytes
            if (bytes < 1024) return `${bytes} B`;

            const kb = bytes / 1024;
            // less than 1 MB -> show KB
            if (kb < 1024) return `${kb.toFixed(2)} KB`;

            const mb = kb / 1024;
            return `${mb.toFixed(2)} MB`;
        };

        onMounted(() => {
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "patient_files";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url: url,
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
                limit: 8
            });
        };

        const onFileUploaded = (file) => {
            addEditRequestAdmin({
                url: url,
                data: {
                    file: file.file,
                    name: file.name,
                    file_size: file.size,
                    file_type: file.type,
                    patient_id: props.id,
                },
                successMessage: t('patient_files.created'),
                success: (res) => {
                    setUrlData();
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        return {
            columns,
            uploadFormData,
            getFileIcon,
            onFileUploaded,
            bToReadable,
            formatDate,
            downloadS3File,
            ...crudVariables,
        };
    },
};
</script>
