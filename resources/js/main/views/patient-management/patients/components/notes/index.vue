<template>
    <div class="p-4">
        <div class="flex justify-end items-center mb-6">
            <a-button 
                type="primary" 
                @click="addItem" 
                v-if="permsArray.includes('patient_notes_create') || permsArray.includes('admin')"
            >
                <PlusOutlined /> {{ $t("patient_notes.add") }}
            </a-button>
        </div>

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
            :patientId="patientId"
        />

        <a-list
            :loading="loading"
            :data-source="table.data"
        >
            <template #renderItem="{ item: note }">
                <a-list-item 
                    :key="note.xid" 
                    class="mb-2 rounded-lg shadow-sm p-4"
                    :class="[
                        note.is_highlighted ? 'bg-yellow-50' : '',
                        note.is_private ? 'border-l-4 border-red-500' : 'border-l-4 border-gray-200'
                    ]"
                >
                    <a-list-item-meta>
                        <template #title>
                            <div class="flex items-center gap-2">
                                <h3 v-if="note.title" :class="note.is_highlighted ? 'text-yellow-900' : ''" class="m-0 text-base font-semibold inline">{{ note.title }}</h3>
                                <a-tag color="gold" v-if="note.is_highlighted">
                                    <template #icon>
                                        <StarFilled />
                                    </template>
                                    {{ $t("patient_notes.highlighted") }}
                                </a-tag>
                                <a-tag v-if="note.is_private" color="red">
                                    <template #icon>
                                        <LockOutlined />
                                    </template>
                                    {{ $t("patient_notes.private") }}
                                </a-tag>
                                <a-tag :color="getNoteTypeColor(note.note_type)">{{ getNoteTypeLabel(note.note_type) }}</a-tag>
                            </div>
                        </template>
                        <template #description>
                            <div>
                                <div class="mb-4 whitespace-pre-line" :class="note.is_highlighted ? 'text-yellow-900' : ''">
                                    <a-typography-paragraph 
                                        style="margin-bottom: 0;" 
                                        :ellipsis="{
                                            rows: 3,
                                            expandable: true,
                                        }"
                                        :content="note.content"
                                    />
                                </div>
                                <div class="text-gray-500 text-xs" :class="note.is_highlighted ? 'text-yellow-900' : ''">
                                    <span>{{ $t("patient_notes.added_by") }}: {{ note.user ? note.user.name : '-' }}</span>
                                </div>
                            </div>
                        </template>
                    </a-list-item-meta>
                    <template #actions>
                        <span class="text-gray-500 text-xs mr-4" :class="note.is_highlighted ? 'text-yellow-900' : ''">{{ formatDateTime(note.created_at) }}</span>
                        <a-dropdown :trigger="['click']" v-if="permsArray.includes('patient_notes_edit') || permsArray.includes('patient_notes_delete') || permsArray.includes('admin')">
                            <template #overlay>
                                <a-menu>
                                    <a-menu-item v-if="permsArray.includes('patient_notes_edit') || permsArray.includes('admin')" key="1" @click="editItem(note)">
                                        <EditOutlined /> {{ $t("common.edit") }}
                                    </a-menu-item>
                                    <a-menu-item v-if="permsArray.includes('patient_notes_edit') || permsArray.includes('admin')" key="2" @click="toggleHighlight(note)">
                                        <StarOutlined /> 
                                        {{ note.is_highlighted ? $t("patient_notes.remove_highlight") : $t("patient_notes.highlight") }}
                                    </a-menu-item>
                                    <a-menu-item v-if="permsArray.includes('patient_notes_delete') || permsArray.includes('admin')" key="3" @click="showDeleteConfirm(note.xid)">
                                        <DeleteOutlined /> {{ $t("common.delete") }}
                                    </a-menu-item>
                                </a-menu>
                            </template>
                            <a-button type="text">
                                <template #icon>
                                    <MoreOutlined />
                                </template>
                            </a-button>
                        </a-dropdown>
                    </template>
                </a-list-item>
            </template>
        </a-list>
    </div>
</template>

<script>
import { onMounted, defineComponent } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
    StarOutlined,
    MoreOutlined,
    StarFilled,
    LockOutlined
} from "@ant-design/icons-vue";
import common from "../../../../../../common/composable/common";
import apiAdmin from "../../../../../../common/composable/apiAdmin";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import crud from "../../../../../../common/composable/crud";

export default defineComponent({
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        StarOutlined,
        MoreOutlined,
        StarFilled,
        LockOutlined,
        AddEdit
    },
    props: {
        patientId: {
            type: String,
            required: true
        },
    },
    emits: ['noteUpdated'],
    setup(props, { emit }) {
        const { formatDateTime, permsArray } = common();
        const { addEditRequestAdmin, loading } = apiAdmin();
        const { url, addEditUrl, initData, noteTypes, hashableColumns } = fields();
        const crudVariables = crud();

        const setUrlData = () => {
            crudVariables.hashableColumns.value = [...hashableColumns];

            // Filter notes by patient ID
            const filterUrl = `${url}&filters=patient_id eq "${props.patientId}"&hashable=${props.patientId}`;

            crudVariables.tableUrl.value = {
                url: filterUrl,
            };

            crudVariables.fetch({
                page: 1,
            });
        };

        const getNoteTypeLabel = (key) => {
            const noteType = noteTypes.value.find(type => type.key === key);
            return noteType ? noteType.value : key;
        };

        const getNoteTypeColor = (key) => {
            const colorMap = {
                'general': 'default',
                'medical': 'red',
                'treatment': 'orange',
                'insurance': 'green',
                'administrative': 'blue',
                'billing': 'purple'
            };
            return colorMap[key] || 'default';
        };

        const toggleHighlight = (note) => {
            addEditRequestAdmin({
                url: `${addEditUrl}/${note.xid}/highlight`,
                data: {},
                method: 'POST',
                successMessage: note.is_highlighted
                    ? "Highlight removed successfully"
                    : "Note highlighted successfully",
                success: () => {
                    setUrlData();
                    emit('noteUpdated');
                },
            });
        };

        const addEditSuccess = (data) => {
            crudVariables.addEditSuccess(data);
            emit('noteUpdated');
        };

        onMounted(() => {
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "patient_notes";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.table.sorter.field = "is_highlighted";
            crudVariables.table.sorter.order = "desc";
            
            setUrlData();
        });

        return {
            ...crudVariables,
            permsArray,
            getNoteTypeLabel,
            getNoteTypeColor,
            toggleHighlight,
            formatDateTime,
            loading,
            addEditSuccess
        };
    }
});
</script>
