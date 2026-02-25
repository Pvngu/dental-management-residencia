<template>
    <!-- Log Details Modal -->
    <a-modal
        v-model:open="detailsModalVisible"
        :title="detailsModalTitle"
        :width="900"
        :footer="null"
        @cancel="detailsModalVisible = false"
    >
        <div v-if="selectedLogItem">
            <div class="details-header">
                <a-descriptions bordered>
                    <a-descriptions-item label="Acción" :span="1">
                        <a-tag :color="getActionColor(selectedLogItem.action)">
                            {{ selectedLogItem.action }}
                        </a-tag>
                    </a-descriptions-item>
                    <a-descriptions-item label="Entidad" :span="1">
                        {{ selectedLogItem.entity }}
                    </a-descriptions-item>
                    <a-descriptions-item label="Fecha y Hora" :span="1">
                        {{ formatDateTime(selectedLogItem.datetime) }}
                    </a-descriptions-item>
                    <a-descriptions-item label="Usuario" :span="3">
                        {{ getUserName(selectedLogItem.user) }}
                    </a-descriptions-item>
                    <a-descriptions-item label="Descripción" :span="3">
                        {{ selectedLogItem.description }}
                    </a-descriptions-item>
                </a-descriptions>
            </div>

            <a-divider />

            <div class="json-content">
                <a-tabs>
                    <a-tab-pane key="1" tab="Datos del Usuario">
                        <div v-if="selectedLogItem.user" class="json-view">
                            <JsonViewer 
                                :data="selectedLogItem.user" 
                                :collapsed-paths="collapsedPaths" 
                                @update:collapsed-paths="collapsedPaths = $event" 
                            />
                        </div>
                        <a-empty v-else description="No hay datos de usuario disponibles" />
                    </a-tab-pane>
                    <a-tab-pane key="2" tab="Detalles del Cambio">
                        <div v-if="selectedLogItem.json_log" class="json-view">
                            <JsonViewer 
                                :data="selectedLogItem.json_log" 
                                :collapsed-paths="collapsedPaths" 
                                @update:collapsed-paths="collapsedPaths = $event" 
                            />
                        </div>
                        <a-empty v-else description="No hay detalles disponibles" />
                    </a-tab-pane>
                    <a-tab-pane key="3" tab="Valores Antiguos vs Nuevos" v-if="showChangesTab">
                        <a-empty v-if="!hasChanges" description="No hay cambios para comparar" />
                        <div v-else class="changes-table-container">
                            <a-table 
                                :columns="diffColumns" 
                                :dataSource="diffData" 
                                :pagination="false"
                                :row-key="record => record.field"
                                bordered
                            >
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.dataIndex === 'oldValue'">
                                        <div :class="{'diff-cell': true, 'diff-removed': record.oldValue !== record.newValue}">
                                            {{ formatValue(record.oldValue) }}
                                        </div>
                                    </template>
                                    <template v-if="column.dataIndex === 'newValue'">
                                        <div :class="{'diff-cell': true, 'diff-added': record.oldValue !== record.newValue}">
                                            {{ formatValue(record.newValue) }}
                                        </div>
                                    </template>
                                </template>
                            </a-table>
                        </div>
                    </a-tab-pane>
                </a-tabs>
            </div>
        </div>
    </a-modal>

    <!-- Table content -->
    <a-row class="mt-2">
        <a-col :span="24">
            <div>
                <a-table
                    :columns="columns"
                    :row-key="(record) => record.id"
                    :data-source="table.data"
                    :pagination="table.pagination"
                    :loading="table.loading"
                    @change="handleTableChange"
                    bordered
                    size="middle"
                >
                    <template #title>
                        <a-row justify="space-between" align="center" class="table-header">
                            <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                                <a-select
                                    v-model:value="filters.action"
                                    :placeholder="'Seleccionar entidad'"
                                    :allowClear="true"
                                    optionFilterProp="title"
                                    show-search
                                    @change="onTableSearch"
                                    style="width: 100%"
                                >
                                    <a-select-option value="CREATED">Creado</a-select-option>
                                    <a-select-option value="UPDATED">Actualizado</a-select-option>
                                    <a-select-option value="DELETED">Eliminado</a-select-option>
                                </a-select>
                            </a-col>
                            <a-col 
                                :xs="21"
                                :sm="16"
                                :md="16"
                                :lg="12"
                                :xl="8"
                                v-if="showTableSearch"
                            >
                                <a-input-group compact>
                                    <a-select
                                        style="width: 30%"
                                        v-model:value="table.searchColumn"
                                        :placeholder="'Buscar por'"
                                    >
                                        <a-select-option
                                            v-for="filterableColumn in filterableColumns"
                                            :key="filterableColumn.key"
                                        >
                                            {{ filterableColumn.value }}
                                        </a-select-option>
                                    </a-select>
                                    <a-input-search
                                        style="width: 70%"
                                        v-model:value="table.searchString"
                                        placeholder="Buscar por nombre usuario o campo"
                                        show-search
                                        @search="onTableSearch"
                                        @change="onTableSearch"
                                        :loading="table.loading"
                                    />
                                </a-input-group>
                            </a-col>
                        </a-row>
                    </template>

                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'datetime'">
                            {{ formatDateTime(record.datetime) }}
                        </template>
                        <template v-if="column.dataIndex === 'user'">
                            {{ getUserName(record.user) }}
                        </template>
                        <template v-else-if="column.dataIndex === 'action'">
                            <a-button
                                type="primary"
                                @click="showDetails(record)"
                                style="margin-left: 4px"
                            >
                                <template #icon><EyeOutlined /></template>
                            </a-button>
                        </template>
                        <template v-else-if="column.dataIndex === 'entity'">
                            <a-tag :color="getEntityColor(record.entity)">
                                {{ formatEntity(record.entity) }}
                            </a-tag>
                        </template>
                        <template v-else-if="column.dataIndex === 'description'">
                            <a-typography-paragraph
                                :ellipsis="{
                                    rows: 2,
                                    expandable: true,
                                    symbol: 'más',
                                }"
                                :content="record.description"
                            />
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import { EyeOutlined } from "@ant-design/icons-vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import JsonViewer from "./JsonViewer.vue";

export default {
    props: {
        showTableSearch: {
            type: Boolean,
            default: false
        },
        // Optional: when provided, table will be filtered client-side by this patient id
        patientId: {
            type: [String, Number],
            default: null,
        },
    },
    components: {
        EyeOutlined,
        AdminPageHeader,
        JsonViewer
    },
    setup(props) {
        const {
            url,
            columns,
            filterableColumns,
            hashableColumns,
            getUserName,
            parseJsonLog
        } = fields();

        const { permsArray, formatDateTime } = common();
        const crudVariables = crud();
        
        // For modals
        const detailsModalVisible = ref(false);
        const detailsModalTitle = ref('Detalles del Registro');
        const selectedLogItem = ref(null);
        const collapsedPaths = ref([]);
        const filters = ref({
            action: null,
            // patient_id: props.patientId
        })

        // For the comparison table
        const diffColumns = [
            {
                title: 'Campo',
                dataIndex: 'field',
                width: '20%'
            },
            {
                title: 'Valor Anterior',
                dataIndex: 'oldValue',
                width: '40%'
            },
            {
                title: 'Valor Nuevo',
                dataIndex: 'newValue',
                width: '40%'
            }
        ];

        const diffData = computed(() => {
            if (!selectedLogItem.value || !selectedLogItem.value.json_log) return [];
            
            try {
                const jsonLog = typeof selectedLogItem.value.json_log === 'string' 
                    ? JSON.parse(selectedLogItem.value.json_log) 
                    : selectedLogItem.value.json_log;
                
                if (!jsonLog.data || !jsonLog.data.old || !jsonLog.data.new) return [];
                
                const oldData = jsonLog.data.old;
                const newData = jsonLog.data.new;
                
                // Get all keys from both objects
                const allKeys = [...new Set([...Object.keys(oldData), ...Object.keys(newData)])];
                
                return allKeys.map(key => ({
                    field: key,
                    oldValue: oldData[key],
                    newValue: newData[key]
                }));
            } catch (e) {
                console.error('Error processing diff data:', e);
                return [];
            }
        });

        const hasChanges = computed(() => {
            return diffData.value && diffData.value.length > 0;
        });

        const showChangesTab = computed(() => {
            if (!selectedLogItem.value || !selectedLogItem.value.json_log) return false;
            
            try {
                const jsonLog = typeof selectedLogItem.value.json_log === 'string' 
                    ? JSON.parse(selectedLogItem.value.json_log) 
                    : selectedLogItem.value.json_log;
                
                return jsonLog.action === 'UPDATED' && jsonLog.data && jsonLog.data.old && jsonLog.data.new;
            } catch (e) {
                return false;
            }
        });

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.tableUrl.value = { 
                url,
                filters
            };
            crudVariables.hashableColumns.value = [...hashableColumns];
            crudVariables.fetch({ page: 1 });
        });

        const showDetails = (record) => {
            selectedLogItem.value = record;
            collapsedPaths.value = [];
            detailsModalVisible.value = true;
        };

        const parseJson = (jsonString) => {
            try {
                return typeof jsonString === 'string' ? JSON.parse(jsonString) : jsonString;
            } catch (error) {
                console.error("Error parsing JSON:", error);
                return {};
            }
        };

        const getActionColor = (action) => {
            const colorMap = {
                'CREATE': 'green',
                'UPDATE': 'blue',
                'DELETE': 'red',
                'LOGIN': 'purple',
                'LOGOUT': 'orange',
            };
            return colorMap[action] || 'default';
        };

        const getEntityColor = (entity) => {
            const colorMap = {
                'auditorias': 'cyan',
                'cargos': 'geekblue',
                'docs_acta_inicios': 'purple',
                'docs_acta_notificacions': 'orange',
                'docs_citatorios': 'red',
                'docs_inicio_auditorias': 'green',
                'entes': 'volcano',
                'plan_anual_auditorias': 'magenta',
            };
            return colorMap[entity] || 'default';
        };

        const formatEntity = (entity) => {
            return entity.charAt(0).toUpperCase() + entity.slice(1).replace(/_/g, ' ');
        }

        const formatValue = (value) => {
            if (value === null) return 'null';
            if (value === undefined) return 'undefined';
            if (typeof value === 'object') return JSON.stringify(value);
            if (typeof value === 'string') return value;
            return String(value);
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            getUserName,
            detailsModalVisible,
            detailsModalTitle,
            selectedLogItem,
            showDetails,
            parseJson,
            getActionColor,
            getEntityColor,
            formatValue,
            collapsedPaths,
            diffColumns,
            diffData,
            hasChanges,
            showChangesTab,
            formatDateTime,
            filters,
            formatEntity,
        };
    }
};
</script>

<style scoped>
.json-view {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #f0f0f0;
    border-radius: 4px;
    padding: 12px;
}

.details-header {
    margin-bottom: 20px;
}

.mt-5 {
    margin-top: 5px;
}

.changes-table-container {
    margin-top: 16px;
}

.diff-cell {
    white-space: pre-wrap;
    word-break: break-word;
}

.diff-removed {
    background-color: #ffebee;
}

.diff-added {
    background-color: #e8f5e9;
}

:deep(.ant-descriptions-item-label) {
    font-weight: bold;
    width: 120px;
}
</style>