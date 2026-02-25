<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.point_of_sale`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <a-button type="primary" @click="newSale" :loading="loading">
                    <template #icon><PlusOutlined /></template>
                    {{ $t("sales.new_sale") }}
                </a-button>
                <a-button 
                    v-if="draftSales.length > 0" 
                    @click="showDraftsModal = true"
                    :loading="loading"
                >
                    <template #icon><FileTextOutlined /></template>
                    {{ $t("sales.draft_sales") }} ({{ draftSales.length }})
                </a-button>
                <a-button @click="handleBackButton">
                    <template #icon><LeftOutlined /></template>
                    {{ route.params.patientId ? $t("patients.back_to_patient") : $t("common.back_to_list") }}
                </a-button>
            </a-space>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.sales.index' }">
                        {{ $t(`menu.sales`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`sales.pos_mode`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <a-row :gutter="[16, 16]">
            <!-- Product Selection Panel -->
            <a-col :lg="16" :md="14" :sm="24" :xs="24">
                <a-card :title="$t('sales.select_products')">
                    <template #extra>
                        <a-space>
                            <a-select
                                v-model:value="itemTypeFilter"
                                style="width: 150px"
                                @change="filterProducts"
                            >
                                <a-select-option value="all">
                                    {{ $t('sales.all_items') }}
                                </a-select-option>
                                <a-select-option value="goods">
                                    {{ $t('sales.goods_only') }}
                                </a-select-option>
                                <a-select-option value="service">
                                    {{ $t('sales.services_only') }}
                                </a-select-option>
                            </a-select>
                            <a-input-search
                                v-model:value="productSearch"
                                :placeholder="$t('sales.search_products')"
                                style="width: 300px"
                                @search="searchProducts"
                                @input="searchProducts"
                                allowClear
                            />
                        </a-space>
                    </template>
                    
                    <!-- Search Results Info -->
                    <div v-if="productSearch" class="search-info mb-3">
                        <a-tag color="blue">
                            {{ filteredProducts.length }} {{ $t('sales.products_found') }}
                        </a-tag>
                        <a-button size="small" @click="clearSearch" style="margin-left: 8px">
                            {{ $t('common.clear_search') }}
                        </a-button>
                    </div>
                    
                    <div class="products-grid">
                        <div v-if="loadingProducts" class="text-center p-4">
                            <a-spin size="large" />
                            <div class="mt-2">{{ $t('sales.loading_products') }}</div>
                        </div>
                        
                        <div v-else-if="filteredProducts.length === 0 && !loadingProducts" class="text-center p-4">
                            <a-empty :description="productSearch ? $t('sales.no_products_found') : $t('sales.no_products_available')" />
                        </div>
                        
                        <a-row :gutter="[8, 8]" v-else>
                            <a-col 
                                v-for="product in filteredProducts" 
                                :key="product.xid"
                                :lg="6" :md="8" :sm="12" :xs="12"
                            >
                                <a-card 
                                    hoverable 
                                    class="product-card"
                                    :class="{ 'out-of-stock': product.available_quantity <= 0 }"
                                >
                                    <template #cover>
                                        <div class="product-image">
                                            <img v-if="product.image" :src="product.image" :alt="product.name" />
                                            <div v-else class="no-image">
                                                <PictureOutlined />
                                            </div>
                                            <!-- Stock indicator -->
                                            <div 
                                                v-if="product.type === 'goods'" 
                                                class="stock-indicator" 
                                                :class="{ 
                                                    'low-stock': product.available_quantity <= 5 && product.available_quantity > 0, 
                                                    'out-of-stock': product.available_quantity <= 0 
                                                }"
                                            >
                                                {{ product.available_quantity || 0 }}
                                            </div>
                                            <div 
                                                v-else 
                                                class="stock-indicator service-indicator"
                                            >
                                                ∞
                                            </div>
                                        </div>
                                    </template>
                                    <a-card-meta>
                                        <template #title>
                                            <div class="product-name">{{ product.name }}</div>
                                        </template>
                                        <template #description>
                                            <div class="product-price">${{ getProductPrice(product).toFixed(2) }}</div>
                                            <div class="product-stock">
                                                <span v-if="product.type === 'goods'">
                                                    {{ $t('sales.stock') }}: {{ product.available_quantity || 0 }}
                                                </span>
                                                <span v-else>
                                                    {{ $t('sales.service_available') }}
                                                </span>
                                                <span v-if="product.sku" class="barcode">
                                                    | {{ product.sku }}
                                                </span>
                                            </div>
                                            <div class="product-type">
                                                <a-tag :color="product.type === 'service' ? 'blue' : 'green'">
                                                    {{ product.type === 'service' ? $t('sales.service') : $t('sales.product') }}
                                                </a-tag>
                                            </div>
                                        </template>
                                    </a-card-meta>
                                    
                                    <!-- Add to Cart Button -->
                                    <div class="product-actions mt-2">
                                        <a-button 
                                            type="primary" 
                                            size="small" 
                                            block
                                            :disabled="product.type === 'goods' && product.available_quantity <= 0"
                                            @click="addToCart(product)"
                                        >
                                            <template #icon><PlusOutlined /></template>
                                            {{ (product.type === 'goods' && product.available_quantity <= 0) ? $t('sales.out_of_stock') : $t('sales.add_to_cart') }}
                                        </a-button>
                                    </div>
                                </a-card>
                            </a-col>
                        </a-row>
                        
                        <!-- Load More Button -->
                        <div v-if="filteredProducts.length >= 50" class="text-center mt-3">
                            <a-button @click="loadMoreProducts">
                                {{ $t('sales.load_more') }}
                            </a-button>
                        </div>
                    </div>
                </a-card>
            </a-col>

            <!-- Cart Panel -->
            <a-col :lg="8" :md="10" :sm="24" :xs="24">
                <a-card :title="$t('sales.shopping_cart')">
                    <template #extra>
                        <a-button size="small" @click="clearCart" danger>
                            <template #icon><DeleteOutlined /></template>
                            {{ $t('common.clear') }}
                        </a-button>
                    </template>

                    <!-- Customer Selection -->
                    <div v-if="route.params.patientId && selectedPatient" class="mb-3">
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ $t('sales.customer') }}</div>
                                <div class="font-semibold text-base text-gray-800">
                                    {{ selectedPatient.user?.name }} {{ selectedPatient.user?.last_name || '' }}
                                </div>
                                <div v-if="selectedPatient.user?.phone" class="text-xs text-gray-600 mt-1">
                                    <PhoneOutlined class="mr-1" />{{ selectedPatient.user.phone }}
                                </div>
                            </div>
                            <a-button 
                                size="small" 
                                type="link" 
                                @click="clearPatientSelection"
                                danger
                            >
                                {{ $t('common.clear') }}
                            </a-button>
                        </div>
                    </div>
                    <a-form-item v-else :label="$t('sales.customer')" class="mb-3">
                        <a-select
                            v-model:value="currentSale.patient_id"
                            :placeholder="$t('sales.select_customer')"
                            show-search
                            optionFilterProp="title"
                            style="width: 100%"
                            :loading="loadingCustomers"
                        >
                            <a-select-option
                                v-for="customer in customers"
                                :key="customer.xid"
                                :title="customer.user ? customer.user.name : ''"
                                :value="customer.xid"
                            >
                                {{ customer.user ? customer.user.name : '' }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>

                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div v-if="cartItems.length === 0" class="empty-cart">
                            <a-empty :description="$t('sales.cart_empty')" />
                        </div>
                        <div v-else>
                            <div 
                                v-for="(item, index) in cartItems" 
                                :key="item.xid"
                                class="cart-item"
                            >
                                <div class="item-details">
                                    <div class="item-name">{{ item.name }}</div>
                                    <div class="item-price">${{ Number(item.price || 0).toFixed(2) }} {{ $t('sales.each') }}</div>
                                </div>
                                <div class="item-controls">
                                    <a-input-number
                                        v-model:value="item.quantity"
                                        :min="1"
                                        :max="item.type === 'service' ? 100 : item.available_quantity"
                                        size="small"
                                        @change="updateCartItem"
                                    />
                                    <a-button 
                                        size="small" 
                                        danger 
                                        @click="removeFromCart(index)"
                                    >
                                        <template #icon><DeleteOutlined /></template>
                                    </a-button>
                                </div>
                                <div class="item-total">
                                    ${{ (Number(item.price || 0) * item.quantity).toFixed(2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <a-divider />
                        <div class="summary-row">
                            <span>{{ $t('sales.subtotal') }}:</span>
                            <span>${{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>{{ $t('sales.tax') }} (5%):</span>
                            <span>${{ tax.toFixed(2) }}</span>
                        </div>
                        <div class="summary-row" style="align-items: flex-start;">
                            <div style="flex: 1;">
                                <div style="margin-bottom: 8px;">
                                    <strong>{{ $t('sales.discount') }}:</strong>
                                </div>
                                <a-radio-group 
                                    v-model:value="currentSale.discount_type" 
                                    size="small"
                                    @change="calculateTotals"
                                    style="margin-bottom: 8px;"
                                >
                                    <a-radio-button value="fixed">
                                        $
                                    </a-radio-button>
                                    <a-radio-button value="percentage">
                                        %
                                    </a-radio-button>
                                </a-radio-group>
                            </div>
                            <div>
                                <a-input-number
                                    v-model:value="currentSale.discount"
                                    :min="0"
                                    :max="currentSale.discount_type === 'percentage' ? 100 : subtotal + tax"
                                    size="small"
                                    style="width: 100px"
                                    @change="calculateTotals"
                                    :precision="2"
                                    :placeholder="currentSale.discount_type === 'percentage' ? '0-100%' : '$0.00'"
                                />
                                <div v-if="currentSale.discount_type === 'percentage' && currentSale.discount > 0" class="discount-amount-display">
                                    = ${{ discountAmount.toFixed(2) }}
                                </div>
                            </div>
                        </div>
                        <a-divider />
                        <div class="summary-row total">
                            <span>{{ $t('sales.total') }}:</span>
                            <span>${{ total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <div class="checkout-section">
                        <a-space direction="vertical" style="width: 100%">
                            <a-button 
                                type="primary" 
                                size="large" 
                                block
                                :disabled="cartItems.length === 0 || !currentSale.patient_id"
                                :loading="processing"
                                @click="processSale"
                            >
                                <template #icon><ShoppingCartOutlined /></template>
                                {{ currentSale.xid ? $t('sales.complete_sale') : $t('sales.process_sale') }}
                            </a-button>
                            <a-button 
                                size="large" 
                                block
                                :disabled="cartItems.length === 0 || !currentSale.patient_id"
                                :loading="savingDraft"
                                @click="saveAsDraft"
                            >
                                <template #icon><SaveOutlined /></template>
                                {{ $t('sales.save_as_draft') }}
                            </a-button>
                        </a-space>
                    </div>
                </a-card>
            </a-col>
        </a-row>

        <!-- Recent Sales -->
        <a-row class="mt-4">
            <a-col :span="24">
                <a-card :title="$t('sales.recent_sales')">
                    <template #extra>
                        <a-button @click="fetchSales">
                            <template #icon><ReloadOutlined /></template>
                            {{ $t('common.refresh') }}
                        </a-button>
                    </template>
                    
                    <div class="table-responsive">
                        <a-table
                            :columns="salesColumns"
                            :row-key="(record) => record.xid"
                            :data-source="recentSales"
                            :loading="table.loading"
                            :pagination="{ pageSize: 5 }"
                            bordered
                            size="small"
                        >
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.dataIndex === 'status'">
                                    <a-tag :color="getStatusColor(record.status)">
                                        {{ record.status }}
                                    </a-tag>
                                </template>
                                <template v-if="column.dataIndex === 'total'">
                                    ${{ Number(record.total || 0).toFixed(2) }}
                                </template>
                                <template v-if="column.dataIndex === 'sold_at'">
                                    {{ formatDate(record.sold_at) }}
                                </template>
                            </template>
                        </a-table>
                    </div>
                </a-card>
            </a-col>
        </a-row>

        <!-- Draft Sales Modal -->
        <a-modal
            v-model:open="showDraftsModal"
            :title="$t('sales.draft_sales')"
            width="800px"
            :footer="null"
        >
            <div v-if="loadingDrafts" class="text-center p-4">
                <a-spin size="large" />
                <div class="mt-2">{{ $t('common.loading') }}</div>
            </div>
            
            <div v-else-if="draftSales.length === 0" class="text-center p-4">
                <a-empty :description="$t('sales.no_drafts')" />
            </div>
            
            <div v-else>
                <a-list
                    :data-source="draftSales"
                    item-layout="horizontal"
                >
                    <template #renderItem="{ item }">
                        <a-list-item>
                            <template #actions>
                                <a-button 
                                    type="primary" 
                                    size="small"
                                    @click="resumeDraft(item)"
                                >
                                    <template #icon><EditOutlined /></template>
                                    {{ $t('sales.resume_draft') }}
                                </a-button>
                                <a-button 
                                    danger 
                                    size="small"
                                    @click="deleteDraft(item.xid)"
                                >
                                    <template #icon><DeleteOutlined /></template>
                                </a-button>
                            </template>
                            <a-list-item-meta>
                                <template #title>
                                    <span style="font-weight: 600">{{ item.sale_number }}</span>
                                    <a-tag color="orange" style="margin-left: 8px">{{ $t('sales.draft') }}</a-tag>
                                </template>
                                <template #description>
                                    <div>
                                        <strong>{{ $t('sales.customer') }}:</strong> {{ item.patient_name || 'N/A' }}
                                    </div>
                                    <div>
                                        <strong>{{ $t('sales.total') }}:</strong> ${{ Number(item.total || 0).toFixed(2) }}
                                    </div>
                                    <div>
                                        <strong>{{ $t('common.created_at') }}:</strong> {{ formatDate(item.created_at) }}
                                    </div>
                                </template>
                            </a-list-item-meta>
                        </a-list-item>
                    </template>
                </a-list>
            </div>
        </a-modal>
    </admin-page-table-content>
</template>

<script>
import { ref, reactive, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { notification } from "ant-design-vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import { 
    PlusOutlined, 
    LeftOutlined, 
    PictureOutlined,
    DeleteOutlined,
    ShoppingCartOutlined,
    ReloadOutlined,
    PhoneOutlined,
    SaveOutlined,
    FileTextOutlined,
    EditOutlined
} from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import apiAdmin from "../../../../common/composable/apiAdmin";

export default {
    components: {
        AdminPageHeader,
        PlusOutlined,
        LeftOutlined,
        PictureOutlined,
        DeleteOutlined,
        ShoppingCartOutlined,
        ReloadOutlined,
        PhoneOutlined,
        SaveOutlined,
        FileTextOutlined,
        EditOutlined,
    },
    setup() {
        const route = useRoute();
        const { permsArray } = common();
        const { loading, addEditRequestAdmin } = apiAdmin();

        // State
        const productSearch = ref('');
        const itemTypeFilter = ref('all');
        const products = ref([]);
        const customers = ref([]);
        const cartItems = ref([]);
        const loadingProducts = ref(false);
        const loadingCustomers = ref(false);
        const processing = ref(false);
        const selectedPatient = ref(null);
        const savingDraft = ref(false);
        const loadingDrafts = ref(false);
        const showDraftsModal = ref(false);
        const draftSales = ref([]);

        // Current sale data
        const currentSale = ref({
            xid: undefined,
            patient_id: undefined,
            discount: 0,
            discount_type: 'fixed',
            subtotal: 0,
            tax: 0,
            total: 0,
            sale_details: []
        });

        // Recent sales table state
        const table = reactive({
            data: [],
            loading: false,
        });

        const recentSales = ref([]);

        // Computed properties
        const filteredProducts = computed(() => {
            const q = productSearch.value.toLowerCase().trim();
            let filtered = products.value;
            
            // Filter by type first
            if (itemTypeFilter.value !== 'all') {
                filtered = filtered.filter(product => product.type === itemTypeFilter.value);
            }
            
            // Then filter by search term
            if (!q) {
                return filtered.slice(0, 50); // Show first 50 products when no search
            }
            
            return filtered.filter(product => {
                const nameMatch = product.name.toLowerCase().includes(q);
                const skuMatch = product.sku && product.sku.toLowerCase().includes(q);
                const descriptionMatch = product.description && product.description.toLowerCase().includes(q);
                
                return nameMatch || skuMatch || descriptionMatch;
            });
        });

        const subtotal = computed(() => {
            return cartItems.value.reduce((sum, item) => {
                return sum + (Number(item.price || 0) * item.quantity);
            }, 0);
        });

        const tax = computed(() => {
            return subtotal.value * 0.05; // 5% tax
        });

        const discountAmount = computed(() => {
            if (currentSale.value.discount_type === 'percentage') {
                // Apply percentage discount to subtotal (before tax)
                return (subtotal.value * (currentSale.value.discount || 0)) / 100;
            } else {
                // Fixed discount amount
                return currentSale.value.discount || 0;
            }
        });

        const total = computed(() => {
            return subtotal.value + tax.value - discountAmount.value;
        });

        // Sales table columns
        const salesColumns = [
            { 
                title: 'Sale #', 
                dataIndex: 'sale_number',
                width: 120
            },
            { 
                title: 'Customer', 
                dataIndex: 'patient_name',
                width: 150
            },
            { 
                title: 'Total', 
                dataIndex: 'total',
                width: 100
            },
            { 
                title: 'Status', 
                dataIndex: 'status',
                width: 100
            },
            { 
                title: 'Date', 
                dataIndex: 'sold_at',
                width: 150
            }
        ];

        // Methods
        const fetchProducts = async () => {
            if (!window.axiosAdmin) {
                console.error('axiosAdmin is not available');
                notification.error({
                    message: 'Error',
                    description: 'API client not available. Please refresh the page.',
                });
                return;
            }
            
            loadingProducts.value = true;
            try {
                const response = await window.axiosAdmin.get('items?fields=id,xid,name,category_id,unit,description,available_quantity,sku,type,is_sellable,sale_price,cost_price&limit=200');
                // Only show sellable items
                products.value = (response.data || []).filter(item => item.is_sellable !== false);
            } catch (error) {
                console.error('Error fetching products:', error);
                // Show error message to user
                notification.error({
                    message: 'Error',
                    description: 'Failed to load products. Please try again.',
                });
            } finally {
                loadingProducts.value = false;
            }
        };

        const searchProducts = () => {
            // Trigger reactivity - the computed property will handle the filtering
            // This function can be expanded to include server-side search if needed
            const searchTerm = productSearch.value.toLowerCase().trim();
            
            if (searchTerm.length > 0 && filteredProducts.value.length === 0) {
                // If no results found locally, could implement server-side search here
                console.log('No local results found for:', searchTerm);
            }
        };

        const clearSearch = () => {
            productSearch.value = '';
        };

        const loadMoreProducts = async () => {
            // This would typically load more products from the server
            // For now, we'll increase the limit
            try {
                const response = await window.axiosAdmin.get('items?fields=id,xid,name,category_id,unit,description,available_quantity,sku,type,is_sellable,sale_price,cost_price&limit=500');
                // Only show sellable items
                products.value = (response.data || []).filter(item => item.is_sellable !== false);
            } catch (error) {
                console.error('Error loading more products:', error);
            }
        };

        const getProductPrice = (product) => {
            // Use sale_price if available, otherwise fall back to default pricing
            if (product.sale_price !== null && product.sale_price !== undefined && Number(product.sale_price) > 0) {
                return Number(product.sale_price);
            }
            
            // Fallback default pricing for backwards compatibility
            if (product.type === 'service') {
                // Default service prices based on service type
                switch (product.sku) {
                    case 'SERV-CON-015': return 150; // Consulta General
                    case 'SERV-LIM-016': return 200; // Limpieza Dental
                    case 'SERV-EXT-017': return 300; // Extracción Simple
                    default: return 100; // Default service price
                }
            } else {
                // Default product prices based on product category/type
                const name = product.name.toLowerCase();
                if (name.includes('anest') || name.includes('lidocaína')) return 25;
                if (name.includes('resina') || name.includes('amalgama')) return 45;
                if (name.includes('guantes') || name.includes('mascarillas')) return 15;
                if (name.includes('hilo dental')) return 8;
                if (name.includes('pasta')) return 20;
                if (name.includes('alginato') || name.includes('silicona')) return 35;
                if (name.includes('espejo') || name.includes('sonda') || name.includes('excavador')) return 60;
                return 30; // Default product price
            }
        };

        const fetchCustomers = async () => {
            loadingCustomers.value = true;
            try {
                const response = await window.axiosAdmin.get('patients?fields=id,xid,user&limit=100');
                customers.value = response.data || [];
                
                // If patient ID is in the route, fetch and set that patient
                if (route.params.patientId) {
                    const patient = customers.value.find(c => c.xid === route.params.patientId);
                    if (patient) {
                        selectedPatient.value = patient;
                        currentSale.value.patient_id = patient.xid;
                    } else {
                        // If not in the list, fetch the specific patient
                        try {
                            const patientResponse = await window.axiosAdmin.get(`patients/${route.params.patientId}?fields=id,xid,user`);
                            selectedPatient.value = patientResponse.data;
                            currentSale.value.patient_id = patientResponse.data.xid;
                        } catch (err) {
                            console.error('Error fetching specific patient:', err);
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching customers:', error);
                notification.error({
                    message: 'Error',
                    description: 'Failed to load customers. Please try again.',
                });
            } finally {
                loadingCustomers.value = false;
            }
        };

        const fetchSales = async () => {
            table.loading = true;
            try {
                // Get today's date in YYYY-MM-DD format
                const today = new Date().toISOString().split('T')[0];
                
                const response = await window.axiosAdmin.get(
                    `sales?fields=id,xid,sale_number,patient_id,sold_at,status,total&order=id%20desc&limit=50&dates=${today},${today}`
                );
                recentSales.value = response.data || [];
            } catch (error) {
                console.error('Error fetching sales:', error);
                notification.error({
                    message: 'Error',
                    description: 'Failed to load recent sales.',
                });
            } finally {
                table.loading = false;
            }
        };

        const addToCart = (product) => {
            // Services can always be added (no stock limitation)
            if (product.type === 'service') {
                const existingItem = cartItems.value.find(item => item.xid === product.xid);
                if (existingItem) {
                    existingItem.quantity++;
                    updateCartItem();
                    
                 
                } else {
                    cartItems.value.push({
                        ...product,
                        quantity: 1,
                        price: getProductPrice(product)
                    });
                    updateCartItem();
                    
                
                }
                return;
            }

            // For goods, check stock
            if (product.available_quantity <= 0) {
                notification.warning({
                    message: 'Out of Stock',
                    description: `${product.name} is out of stock.`,
                });
                return;
            }

            const existingItem = cartItems.value.find(item => item.xid === product.xid);
            if (existingItem) {
                if (existingItem.quantity < product.available_quantity) {
                    existingItem.quantity++;
                    updateCartItem();
                    
                    // notification.success({
                    //     message: 'Product Added',
                    //     description: `${product.name} quantity updated in cart.`,
                    //     duration: 2,
                    // });
                } else {
                    notification.warning({
                        message: 'Stock Limit',
                        description: `Cannot add more ${product.name}. Stock limit reached.`,
                    });
                }
            } else {
                cartItems.value.push({
                    ...product,
                    quantity: 1,
                    price: getProductPrice(product)
                });
                updateCartItem();
                
                // notification.success({
                //     message: 'Product Added',
                //     description: `${product.name} added to cart.`,
                //     duration: 2,
                // });
            }
        };

        const removeFromCart = (index) => {
            cartItems.value.splice(index, 1);
            updateCartItem();
        };

        const updateCartItem = () => {
            calculateTotals();
        };

        const calculateTotals = () => {
            currentSale.value.subtotal = subtotal.value;
            currentSale.value.tax = tax.value;
            currentSale.value.total = total.value;
        };

        const clearCart = () => {
            cartItems.value = [];
            currentSale.value.discount = 0;
            currentSale.value.discount_type = 'fixed';
            currentSale.value.xid = undefined;
            calculateTotals();
        };

        const newSale = () => {
            clearCart();
            currentSale.value.patient_id = undefined;
        };

        const filterProducts = () => {
            // Trigger reactivity for filtered products
            // The computed property will handle the actual filtering
        };

        // const searchProducts = () => {
        //     // Trigger reactivity - the computed property will handle the filtering
        //     // This function can be expanded to include server-side search if needed
        //     const searchTerm = productSearch.value.toLowerCase().trim();
        //     if (searchTerm.length > 0 && filteredProducts.value.length === 0) {
        //         // If no results found locally, could implement server-side search here
        //         console.log('No local results found for:', searchTerm);
        //     }
        // };

        // const clearSearch = () => {
        //     productSearch.value = '';
        // };

        // const loadMoreProducts = async () => {
        //     // This would typically load more products from the server
        //     // For now, we'll increase the limit
        //     try {
        //         const response = await window.axiosAdmin.get('items?fields=id,xid,name,category_id,unit,description,available_quantity,sku,type&limit=500');
        //         products.value = response.data || [];
        //     } catch (error) {
        //         console.error('Error loading more products:', error);
        //     }
        // };

        const getStatusColor = (status) => {
            switch (status) {
                case 'paid':
                    return 'success';
                case 'pending':
                    return 'warning';
                case 'draft':
                    return 'orange';
                default:
                    return 'default';
            }
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleString();
        };

        const handleBackButton = () => {
            if (route.params.patientId) {
                // If we came from patient details, go back to patient with sales tab
                window.$router.push({
                    name: 'admin.patients.detail',
                    params: { id: route.params.patientId, tab: 'sales' }
                });
            } else {
                // Otherwise go to sales list
                window.$router.push({ name: 'admin.sales.index' });
            }
        };

        const clearPatientSelection = () => {
            selectedPatient.value = null;
            currentSale.value.patient_id = undefined;
        };

        const fetchDraftSales = async () => {
            loadingDrafts.value = true;
            try {
                const response = await window.axiosAdmin.get(
                    'sales?fields=id,xid,sale_number,patient_id,created_at,status,total&status=draft&order=id%20desc&limit=50'
                );
                draftSales.value = response.data || [];
            } catch (error) {
                console.error('Error fetching draft sales:', error);
                notification.error({
                    message: 'Error',
                    description: 'Failed to load draft sales.',
                });
            } finally {
                loadingDrafts.value = false;
            }
        };

        const saveAsDraft = async () => {
            if (cartItems.value.length === 0 || !currentSale.value.patient_id) {
                notification.warning({
                    message: 'Incomplete Draft',
                    description: 'Please select a customer and add items to cart.',
                });
                return;
            }

            savingDraft.value = true;
            
            try {
                const saleData = {
                    patient_id: currentSale.value.patient_id,
                    subtotal: subtotal.value,
                    tax: tax.value,
                    discount: currentSale.value.discount || 0,
                    discount_type: currentSale.value.discount_type || 'fixed',
                    total: total.value,
                    status: 'draft',
                    sold_at: new Date().toISOString().split('T')[0],
                    sale_details: cartItems.value.map(item => ({
                        item_id: item.xid,
                        quantity: item.quantity,
                        price_at_time: item.price,
                        subtotal: item.price * item.quantity
                    }))
                };

                // If updating existing draft
                if (currentSale.value.xid) {
                    await addEditRequestAdmin({
                        url: `sales/${currentSale.value.xid}`,
                        data: saleData,
                        successMessage: 'Draft updated successfully!',
                        success: () => {
                            clearCart();
                            currentSale.value.patient_id = undefined;
                            currentSale.value.xid = undefined;
                            fetchDraftSales();
                        }
                    });
                } else {
                    // Creating new draft
                    await addEditRequestAdmin({
                        url: 'sales',
                        data: saleData,
                        successMessage: 'Draft saved successfully!',
                        success: () => {
                            clearCart();
                            currentSale.value.patient_id = undefined;
                            fetchDraftSales();
                        }
                    });
                }
            } catch (error) {
                console.error('Error saving draft:', error);
                notification.error({
                    message: 'Draft Error',
                    description: 'Failed to save draft. Please try again.',
                });
            } finally {
                savingDraft.value = false;
            }
        };

        const resumeDraft = async (draft) => {
            try {
                // Fetch the full draft details including sale_details
                const response = await window.axiosAdmin.get(`sales/${draft.xid}`);
                const fullDraft = response.data;
                
                // Load draft into current sale
                currentSale.value.xid = fullDraft.xid;
                currentSale.value.patient_id = fullDraft.x_patient_id;
                currentSale.value.discount = fullDraft.discount || 0;
                currentSale.value.discount_type = fullDraft.discount_type || 'fixed';
                
                // Load cart items from sale details
                cartItems.value = [];
                if (fullDraft.details && fullDraft.details.length > 0) {
                    for (const detail of fullDraft.details) {
                        // Find the product in our products list
                        let product = products.value.find(p => p.xid === detail.x_item_id);
                        
                        // If not found, fetch it
                        if (!product) {
                            try {
                                const itemResponse = await window.axiosAdmin.get(`items/${detail.x_item_id}?fields=id,xid,name,category_id,unit,description,available_quantity,sku,type`);
                                product = itemResponse.data;
                            } catch (err) {
                                console.error('Error fetching item:', err);
                                continue;
                            }
                        }
                        
                        if (product) {
                            cartItems.value.push({
                                ...product,
                                quantity: detail.quantity,
                                price: detail.price_at_time
                            });
                        }
                    }
                }
                
                calculateTotals();
                showDraftsModal.value = false;
                
                notification.success({
                    message: 'Success',
                    description: 'Draft resumed successfully!',
                });
            } catch (error) {
                console.error('Error resuming draft:', error);
                notification.error({
                    message: 'Error',
                    description: 'Failed to resume draft. Please try again.',
                });
            }
        };

        const deleteDraft = async (draftId) => {
            try {
                await window.axiosAdmin.delete(`sales/${draftId}`);
                notification.success({
                    message: 'Success',
                    description: 'Draft deleted successfully!',
                });
                fetchDraftSales();
            } catch (error) {
                console.error('Error deleting draft:', error);
                notification.error({
                    message: 'Error',
                    description: 'Failed to delete draft. Please try again.',
                });
            }
        };

        const processSale = async () => {
            if (cartItems.value.length === 0 || !currentSale.value.patient_id) {
                notification.warning({
                    message: 'Incomplete Sale',
                    description: 'Please select a customer and add items to cart.',
                });
                return;
            }

            processing.value = true;
            
            try {
                const saleData = {
                    patient_id: currentSale.value.patient_id,
                    subtotal: subtotal.value,
                    tax: tax.value,
                    discount: currentSale.value.discount || 0,
                    discount_type: currentSale.value.discount_type || 'fixed',
                    total: total.value,
                    status: 'paid',
                    sold_at: new Date().toISOString().split('T')[0],
                    sale_details: cartItems.value.map(item => ({
                        item_id: item.xid,
                        quantity: item.quantity,
                        price_at_time: item.price,
                        subtotal: item.price * item.quantity
                    }))
                };

                // If completing a draft
                if (currentSale.value.xid) {
                    await addEditRequestAdmin({
                        url: `sales/${currentSale.value.xid}`,
                        data: saleData,
                        successMessage: 'Sale completed successfully!',
                        success: () => {
                            clearCart();
                            currentSale.value.patient_id = undefined;
                            currentSale.value.xid = undefined;
                            fetchSales();
                            fetchProducts(); // Refresh to update stock
                            fetchDraftSales();
                        }
                    });
                } else {
                    // Creating new sale
                    await addEditRequestAdmin({
                        url: 'sales',
                        data: saleData,
                        successMessage: 'Sale processed successfully!',
                        success: () => {
                            clearCart();
                            currentSale.value.patient_id = undefined;
                            fetchSales();
                            fetchProducts(); // Refresh to update stock
                            fetchDraftSales();
                        }
                    });
                }
            } catch (error) {
                console.error('Error processing sale:', error);
                notification.error({
                    message: 'Sale Error',
                    description: 'Failed to process sale. Please try again.',
                });
            } finally {
                processing.value = false;
            }
        };

        // Lifecycle
        onMounted(() => {
            fetchProducts();
            fetchCustomers();
            fetchSales();
            fetchDraftSales();
        });

        return {
            route,
            permsArray,
            loading,
            productSearch,
            itemTypeFilter,
            products,
            customers,
            cartItems,
            currentSale,
            loadingProducts,
            loadingCustomers,
            processing,
            table,
            recentSales,
            filteredProducts,
            subtotal,
            tax,
            discountAmount,
            total,
            salesColumns,
            selectedPatient,
            savingDraft,
            loadingDrafts,
            showDraftsModal,
            draftSales,
            fetchProducts,
            fetchCustomers,
            fetchSales,
            fetchDraftSales,
            addToCart,
            removeFromCart,
            updateCartItem,
            calculateTotals,
            clearCart,
            newSale,
            processSale,
            saveAsDraft,
            resumeDraft,
            deleteDraft,
            searchProducts,
            clearSearch,
            loadMoreProducts,
            getProductPrice,
            getStatusColor,
            formatDate,
            handleBackButton,
            clearPatientSelection,
            filterProducts,
        };
    }
}
</script>

<style scoped>
.table-responsive { 
    width: 100%; 
}

/* Product Grid Styles */
.products-grid {
    max-height: 500px;
    overflow-y: auto;
}

.product-card {
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-card.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
}

.product-card.out-of-stock:hover {
    transform: none;
    box-shadow: none;
}

.product-image {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f5f5;
    position: relative;
}

.product-image img {
    max-height: 100%;
    max-width: 100%;
    object-fit: cover;
}

.no-image {
    font-size: 24px;
    color: #ccc;
}

.stock-indicator {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #52c41a;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 10;
    min-width: 24px;
    text-align: center;
}

.stock-indicator.low-stock {
    background: #faad14;
}

.stock-indicator.out-of-stock {
    background: #ff4d4f;
}

.stock-indicator.service-indicator {
    background: #722ed1;
    font-size: 14px;
}

.product-name {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    line-height: 1.3;
    height: 36px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-price {
    font-size: 16px;
    font-weight: bold;
    color: #1890ff;
    margin-bottom: 4px;
}

.product-stock {
    font-size: 11px;
    color: #666;
    margin-bottom: 4px;
}

.product-type {
    margin-top: 4px;
}

.barcode {
    color: #999;
    font-style: italic;
}

.product-actions {
    margin-top: 8px;
}

.search-info {
    border-left: 3px solid #1890ff;
    padding-left: 8px;
    background-color: #f6ffed;
    padding: 8px;
    border-radius: 4px;
}

/* Cart Styles */
.cart-items {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #f0f0f0;
    border-radius: 4px;
    padding: 8px;
    margin: 16px 0;
}

.cart-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-details {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #262626;
}

.item-price {
    font-size: 12px;
    color: #1890ff;
    font-weight: 500;
}

.item-controls {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0 8px;
}

.item-total {
    font-size: 14px;
    font-weight: bold;
    color: #1890ff;
    min-width: 80px;
    text-align: right;
}

.empty-cart {
    padding: 20px;
    text-align: center;
}

/* Cart Summary Styles */
.cart-summary {
    background-color: #fafafa;
    padding: 12px;
    border-radius: 4px;
    margin-top: 16px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 13px;
}

.summary-row.total {
    font-weight: bold;
    font-size: 16px;
    color: #1890ff;
}

.discount-amount-display {
    font-size: 11px;
    color: #52c41a;
    margin-top: 4px;
    text-align: right;
}

.checkout-section {
    margin-top: 16px;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .product-name {
        font-size: 11px;
    }
    
    .product-price {
        font-size: 12px;
    }
    
    .cart-item {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .item-controls {
        order: 3;
        margin: 0;
    }
    
    .item-total {
        order: 2;
        margin-left: auto;
    }
}

/* Animation for adding items */
.cart-item {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>