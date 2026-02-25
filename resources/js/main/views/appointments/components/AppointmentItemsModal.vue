<template>
    <a-modal
        :open="visible"
        :title="$t('appointments.items_used') || 'Items Used During Appointment'"
        width="1200px"
        :footer="null"
        @cancel="onClose"
        centered
    >
        <div class="appointment-items-modal">
            <a-row :gutter="[16,16]">
                <!-- Left column: Search/Filter + Available Items -->
                <a-col :xs="24" :sm="24" :md="14" :lg="14">
                    <a-card class="left-card">
                        <template #title>
                            <div class="flex items-center justify-between">
                                <div>{{ $t('appointments.available_items') || 'Available Items' }}</div>
                                <div style="min-width: 320px;">
                                    <a-input-search
                                        v-model:value="searchQuery"
                                        :placeholder="$t('common.search') || 'Search items...'"
                                        @search="handleSearch"
                                        allow-clear
                                        style="width: 55%"
                                    >
                                        <template #prefix>
                                            <SearchOutlined />
                                        </template>
                                    </a-input-search>

                                    <a-select
                                        v-model:value="selectedCategory"
                                        :placeholder="$t('common.select_default_text', [$t('items.category')]) || 'Select category'"
                                        style="width: 40%; margin-left: 8px"
                                        allow-clear
                                        @change="handleCategoryChange"
                                    >
                                        <a-select-option
                                            v-for="category in categories"
                                            :key="category.xid"
                                            :value="category.xid"
                                        >
                                            {{ category.name }}
                                        </a-select-option>
                                    </a-select>
                                </div>
                            </div>
                        </template>

                        <div class="left-scroll" style="margin-top: 12px">
                            <a-table
                                :columns="availableItemsColumns"
                                :data-source="filteredAvailableItems"
                                :row-key="record => record.xid"
                                :pagination="{ pageSize: 6, size: 'small' }"
                                size="small"
                                :loading="loading"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.dataIndex === 'name'">
                                        <div>
                                            <div class="font-medium">{{ record.name }}</div>
                                            <div class="text-xs text-gray-500">{{ record.sku }}</div>
                                        </div>
                                    </template>
                                    <template v-if="column.dataIndex === 'category'">
                                        <a-tag color="blue">{{ record.category?.name || '-' }}</a-tag>
                                    </template>
                                    <template v-if="column.dataIndex === 'available_quantity'">
                                        <a-tag :color="record.available_quantity > 0 ? 'green' : 'red'">
                                            {{ record.available_quantity }} {{ record.unit || '' }}
                                        </a-tag>
                                    </template>
                                    <template v-if="column.dataIndex === 'action'">
                                        <a-button
                                            type="primary"
                                            size="small"
                                            @click="addItemToSelected(record)"
                                            :disabled="record.available_quantity <= 0"
                                        >
                                            <template #icon>
                                                <PlusOutlined />
                                            </template>
                                            {{ $t('common.add') || 'Add' }}
                                        </a-button>
                                    </template>
                                </template>
                            </a-table>
                        </div>
                    </a-card>
                </a-col>

                <!-- Right column: Selected Items + Notes -->
                <a-col :xs="24" :sm="24" :md="10" :lg="10">
                    <a-card class="right-card" :title="$t('appointments.selected_items') + ` (${selectedItems.length})`">
                        <a-spin :spinning="loadingSavedItems" :tip="$t('common.loading') || 'Loading...'">
                            <div class="right-card-body">
                                <div class="selected-list-wrapper">
                                    <transition-group name="slide" tag="div">
                                        <div
                                            v-for="(record, index) in selectedItems"
                                            :key="record.xid"
                                            class="selected-row"
                                        >
                                            <div class="col name-col">
                                                <div class="font-medium">{{ record.name }}</div>
                                                <div class="text-xs text-gray-500">{{ record.sku }}</div>
                                            </div>
                                            <div class="col qty-col">
                                                <a-input-number
                                                    v-model:value="record.quantity"
                                                    :min="1"
                                                    :max="record.available_quantity"
                                                    size="small"
                                                    style="width: 100%"
                                                />
                                            </div>
                                            <div class="col unit-col">{{ record.unit || '-' }}</div>
                                            <div class="col avail-col">{{ record.available_quantity }}</div>
                                            <div class="col action-col">
                                                <a-button
                                                    type="primary"
                                                    danger
                                                    size="small"
                                                    @click="removeSelectedItem(index)"
                                                >
                                                    <template #icon>
                                                        <DeleteOutlined />
                                                    </template>
                                                </a-button>
                                            </div>
                                        </div>
                                    </transition-group>
                                    <a-empty v-if="selectedItems.length === 0" :description="$t('appointments.no_items_selected') || 'No items selected'" />
                                </div>

                                <a-divider />

                                <div class="notes-and-actions">
                                    <h3 class="text-lg font-semibold mb-2">{{ $t('common.notes') || 'Notes' }}</h3>
                                    <a-textarea
                                        v-model:value="notes"
                                        :placeholder="$t('appointments.items_notes_placeholder') || 'Add notes about items used...'"
                                        :rows="4"
                                    />

                                    <div class="actions" style="margin-top: 12px; text-align: right">
                                        <a-button @click="onClose" style="margin-right:8px">
                                            {{ $t('common.cancel') || 'Cancel' }}
                                        </a-button>
                                        <a-button
                                            type="primary"
                                            @click="handleSave"
                                            :loading="saving"
                                            :disabled="selectedItems.length === 0"
                                        >
                                            <template #icon>
                                                <SaveOutlined />
                                            </template>
                                            {{ $t('common.save') || 'Save' }}
                                        </a-button>
                                    </div>
                                </div>
                            </div>
                        </a-spin>
                    </a-card>
                </a-col>
            </a-row>
        </div>
    </a-modal>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { message } from 'ant-design-vue';
import {
    SearchOutlined,
    PlusOutlined,
    DeleteOutlined,
    SaveOutlined,
} from '@ant-design/icons-vue';

const { t } = useI18n();

// Props
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    appointment: {
        type: Object,
        default: () => ({}),
    },
});

// Emits
const emit = defineEmits(['update:visible', 'saved', 'closed']);

// State
const loading = ref(false);
const saving = ref(false);
const loadingSavedItems = ref(false);
const searchQuery = ref('');
const selectedCategory = ref(undefined);
const notes = ref('');
const selectedItems = ref([]);

// Categories and items will be fetched from API
const categories = ref([]);
const availableItems = ref([]);

// Fetch items and categories from API
const fetchItems = async () => {
    loading.value = true;
    try {
        // Fetch items (limit 200 as requested)
        const response = await window.axiosAdmin.get('items?fields=id,xid,name,category_id,unit,description,available_quantity,sku,type&limit=200');
        // The project axiosAdmin returns data directly in many places, but in some places it returns an object with data
        const items = response && response.data ? response.data : response;
        if (Array.isArray(items)) {
            availableItems.value = items;
        } else if (items && items.data) {
            availableItems.value = items.data;
        }

        // Build categories from items (if category data not provided via endpoint)
        const catMap = {};
        availableItems.value.forEach(it => {
            if (it.category && it.category.xid) {
                catMap[it.category.xid] = it.category;
            } else if (it.category_id) {
                // placeholder name if category object absent
                if (!catMap[it.category_id]) {
                    catMap[it.category_id] = { xid: it.category_id, name: it.category_name || it.category_id };
                }
            }
        });
        categories.value = Object.values(catMap);
    } catch (error) {
        console.error('Error fetching items:', error);
        message.error(t('common.error') || 'Failed to fetch items');

        // Fallback: keep availableItems empty (component previously used dummy data)
    } finally {
        loading.value = false;
    }
};

// Fetch saved appointment items
const fetchSavedAppointmentItems = async () => {
    if (!props.appointment || !props.appointment.xid) {
        return;
    }

    loadingSavedItems.value = true;
    try {
        // TODO: Backend endpoint needed - GET appointments/{xid}/items
        // Expected response format:
        // {
        //   items: [
        //     {
        //       item_xid: 'xxx',
        //       item_name: 'Item Name',
        //       quantity: 2,
        //       unit: 'pieces',
        //       item: { ...full item details }
        //     }
        //   ],
        //   notes: 'Some notes'
        // }
        const response = await window.axiosAdmin.get(`appointments/${props.appointment.xid}/items`);
        const data = response && response.data ? response.data : response;

        if (data && data.items && Array.isArray(data.items)) {
            // Map saved items to the format we need
            selectedItems.value = data.items.map(savedItem => {
                // Merge with full item details from availableItems if available
                const fullItem = availableItems.value.find(ai => ai.xid === savedItem.item_xid);
                
                return {
                    xid: savedItem.item_xid,
                    name: savedItem.item_name || fullItem?.name || 'Unknown Item',
                    sku: fullItem?.sku || '',
                    category: fullItem?.category || null,
                    category_id: fullItem?.category_id || null,
                    unit: savedItem.unit || fullItem?.unit || '',
                    available_quantity: fullItem?.available_quantity || 0,
                    description: fullItem?.description || '',
                    quantity: savedItem.quantity || 1,
                };
            });

            // Load notes if provided
            if (data.notes) {
                notes.value = data.notes;
            }
        }
    } catch (error) {
        // If endpoint returns 404, it means no items saved yet - this is ok
        if (error.response && error.response.status === 404) {
            console.log('No saved items found for this appointment');
        } else {
            console.error('Error fetching saved appointment items:', error);
            // Don't show error message as this is not critical - user can still add items
        }
    } finally {
        loadingSavedItems.value = false;
    }
};

// Computed filtered items
const filteredAvailableItems = computed(() => {
    let items = availableItems.value;

    // Filter by category
    if (selectedCategory.value) {
        items = items.filter(item => item.category_id === selectedCategory.value);
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        items = items.filter(item =>
            item.name.toLowerCase().includes(query) ||
            item.sku.toLowerCase().includes(query) ||
            item.description?.toLowerCase().includes(query)
        );
    }

    // Exclude items that are already selected
    const selectedXids = new Set(selectedItems.value.map(i => i.xid));
    items = items.filter(it => !selectedXids.has(it.xid));

    return items;
});

// Table columns for available items
const availableItemsColumns = [
    {
        title: t('items.name') || 'Name',
        dataIndex: 'name',
        key: 'name',
        width: '30%',
    },
    {
        title: t('items.category') || 'Category',
        dataIndex: 'category',
        key: 'category',
        width: '20%',
    },
    {
        title: t('items.available_quantity') || 'Available',
        dataIndex: 'available_quantity',
        key: 'available_quantity',
        width: '20%',
        align: 'center',
    },
    {
        title: t('items.description') || 'Description',
        dataIndex: 'description',
        key: 'description',
        width: '20%',
        ellipsis: true,
    },
    {
        title: t('common.action') || 'Action',
        dataIndex: 'action',
        key: 'action',
        width: '10%',
        align: 'center',
    },
];

// Table columns for selected items
const selectedItemsColumns = [
    {
        title: t('items.name') || 'Name',
        dataIndex: 'name',
        key: 'name',
        width: '35%',
    },
    {
        title: t('items.quantity') || 'Quantity',
        dataIndex: 'quantity',
        key: 'quantity',
        width: '20%',
        align: 'center',
    },
    {
        title: t('items.unit') || 'Unit',
        dataIndex: 'unit',
        key: 'unit',
        width: '15%',
        align: 'center',
    },
    {
        title: t('items.available_quantity') || 'Available',
        dataIndex: 'available_quantity',
        key: 'available_quantity',
        width: '20%',
        align: 'center',
    },
    {
        title: t('common.action') || 'Action',
        dataIndex: 'action',
        key: 'action',
        width: '10%',
        align: 'center',
    },
];

// Methods
const handleSearch = () => {
    // Search is already handled by computed property
};

const handleCategoryChange = () => {
    // Filter is already handled by computed property
};

const addItemToSelected = (item) => {
    // Check if item already in selected
    const exists = selectedItems.value.find(i => i.xid === item.xid);
    if (exists) {
        message.warning(t('appointments.item_already_added') || 'Item already added');
        return;
    }

    // Add item with default quantity of 1
    selectedItems.value.push({
        ...item,
        quantity: 1,
    });
};

const removeSelectedItem = (index) => {
    selectedItems.value.splice(index, 1);
};

const handleSave = async () => {
    if (selectedItems.value.length === 0) {
        message.warning(t('appointments.select_at_least_one_item') || 'Please select at least one item');
        return;
    }

    try {
        saving.value = true;

        // Prepare data to save
        const data = {
            appointment_xid: props.appointment.xid,
            items: selectedItems.value.map(item => ({
                item_xid: item.xid,
                item_name: item.name,
                quantity: item.quantity,
                unit: item.unit,
            })),
            notes: notes.value,
        };

        // POST to backend
        const response = await window.axiosAdmin.post('appointments/items', data);

        // response normalization: many axiosAdmin usages expect response.data or direct data
        const resData = response && response.data ? response.data : response;

        message.success(t('appointments.items_saved') || 'Items saved successfully');

        emit('saved', resData || data);
        onClose();
    } catch (error) {
        console.error('Error saving appointment items:', error);
        message.error(t('common.error') || 'Failed to save items');
    } finally {
        saving.value = false;
    }
};

const onClose = () => {
    emit('update:visible', false);
    emit('closed');
};

// Watch for modal visibility changes to reset form
let _previousBodyOverflow = '';
watch(() => props.visible, async (newVal) => {
    if (newVal) {
        // Reset form when modal opens
        selectedItems.value = [];
        searchQuery.value = '';
        selectedCategory.value = undefined;
        notes.value = '';

        // Fetch latest items when modal opens
        await fetchItems();
        
        // Fetch saved appointment items after fetching available items
        await fetchSavedAppointmentItems();

        // Lock body scroll to avoid page scroll behind modal
        try {
            _previousBodyOverflow = document.body.style.overflow || '';
            document.body.style.overflow = 'hidden';
        } catch (e) {
            // ignore if DOM not available
        }
    } else {
        // Restore body scroll when modal closes
        try {
            document.body.style.overflow = _previousBodyOverflow || '';
        } catch (e) {}
    }
});

onUnmounted(() => {
    // Ensure body overflow restored if component unmounts while modal open
    try {
        document.body.style.overflow = _previousBodyOverflow || '';
    } catch (e) {}
});

// Also fetch on mounted to warm cache if modal opened later
onMounted(() => {
    // optional: don't fetch unless modal opens, but prefetching may help UX
});
</script>

<style scoped>
.appointment-items-modal {
    min-height: calc(80vh);
    display: flex;
    flex-direction: column;
}

/* Ensure left and right cards fill available height */
.appointment-items-modal .ant-row {
    flex: 1 1 auto;
}

.appointment-items-modal .left-card,
.appointment-items-modal .right-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Make the left table scroll within its column */
.appointment-items-modal .left-scroll {
    overflow-y: auto;
    /* subtract space for card header/title */
    max-height: calc(80vh - 140px);
}

/* Right: selected items area should scroll and notes/actions stay visible */
.appointment-items-modal .right-card-body {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.appointment-items-modal .selected-list-wrapper {
    overflow-y: auto;
    flex: 1 1 auto;
    max-height: calc(80vh - 220px);
}

.appointment-items-modal .notes-and-actions {
    flex: 0 0 auto;
}

/* Slight padding adjustments to keep things tidy */
.appointment-items-modal .ant-card-body {
    padding: 12px 16px;
}

/* Selected row layout */
.selected-row {
    display: flex;
    align-items: center;
    padding: 8px 4px;
    border-bottom: 1px solid #f0f0f0;
    background: white;
}
.selected-row .col { padding: 0 8px; }
.selected-row .name-col { flex: 1 1 40%; }
.selected-row .qty-col { flex: 0 0 20%; }
.selected-row .unit-col { flex: 0 0 15%; text-align: center }
.selected-row .avail-col { flex: 0 0 10%; text-align: center }
.selected-row .action-col { flex: 0 0 12%; text-align: right }

/* Slide-in from right on enter, slide-out to left on leave */
.slide-enter-from {
    transform: translateX(30px);
    opacity: 0;
}
.slide-enter-active {
    transition: all 220ms cubic-bezier(.2,.8,.2,1);
}
.slide-enter-to {
    transform: translateX(0);
    opacity: 1;
}
.slide-leave-from {
    transform: translateX(0);
    opacity: 1;
}
.slide-leave-active {
    transition: all 220ms cubic-bezier(.2,.8,.2,1);
}
.slide-leave-to {
    transform: translateX(-30px);
    opacity: 0;
}

</style>
