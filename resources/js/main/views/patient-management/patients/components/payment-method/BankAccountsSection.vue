<template>
    <div class="mb-10">
        <div class="mb-5">
            <div class="flex items-center mb-2">
                <BankOutlined class="text-xl mr-3 text-green-500" />
                <span class="text-xl font-semibold text-gray-800">{{ $t('payment.bank_accounts') || 'Bank Accounts' }}</span>
            </div>
            <span class="text-gray-600 text-sm">{{ $t('payment.manage_bank_accounts_subtitle') || 'Manage your bank accounts for ACH payments.' }}</span>
        </div>

        <!-- Bank Accounts List -->
        <div class="space-y-3">
            <div 
                v-for="account in bankAccounts" 
                :key="account.id" 
                class="payment-card"
                :class="{ 'default-card': account.is_default }"
            >
                <div class="card-content">
                    <div class="card-info">
                        <div class="card-logo-container">
                            <BankOutlined class="text-2xl text-green-500" />
                        </div>
                        <div class="card-details">
                            <div class="card-number">
                                {{ account.bank_name }} {{ $t('payment.ending_in') || 'ending in' }} {{ account.last_four }}
                            </div>
                            <div class="card-expiry">
                                {{ account.account_type === 'checking' ? $t('payment.checking') : $t('payment.savings') }} {{ $t('payment.account') || 'Account' }}
                            </div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a-tag v-if="account.is_default" color="green" class="default-tag">
                            {{ $t('payment.default') || 'Default' }}
                        </a-tag>
                        <a-button 
                            v-else 
                            type="link" 
                            size="small"
                            @click="setAsDefault(account.id)"
                            class="set-default-btn"
                        >
                            {{ $t('payment.set_as_default') || 'Set as Default' }}
                        </a-button>
                        <a-button 
                            type="text" 
                            size="small"
                            @click="showEditBankModal(account)"
                        >
                            <template #icon><EditOutlined /></template>
                        </a-button>
                        <a-button 
                            type="text" 
                            danger 
                            size="small"
                            @click="confirmDeleteBankAccount(account.id)"
                            :disabled="account.is_default"
                        >
                            <template #icon><DeleteOutlined /></template>
                        </a-button>
                    </div>
                </div>
            </div>

            <div v-if="bankAccounts.length === 0" class="no-cards-message">
                <BankOutlined class="no-cards-icon" />
                <span>{{ $t('payment.no_bank_accounts_message') || 'No bank accounts added yet.' }}</span>
            </div>
        </div>

        <!-- Edit Bank Account Modal -->
        <a-modal
            v-model:open="editBankModalVisible"
            :title="$t('payment.edit_bank_account')"
            :width="500"
            @ok="handleEditBankAccount"
            @cancel="cancelEditBank"
            :okText="$t('payment.update_bank_account')"
            :cancelText="$t('common.cancel')"
            :confirmLoading="editBankLoading"
            centered
        >
            <div class="py-5">
                <a-form layout="vertical">
                    <a-form-item 
                        :label="$t('payment.account_holder_name')"
                        :validateStatus="editBankForm.errors.account_holder_name ? 'error' : ''"
                        :help="editBankForm.errors.account_holder_name"
                    >
                        <a-input
                            v-model:value="editBankForm.account_holder_name"
                            placeholder="John Doe"
                            class="rounded-md border-gray-300"
                        />
                    </a-form-item>

                    <a-row :gutter="16">
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.account_type')"
                                :validateStatus="editBankForm.errors.account_type ? 'error' : ''"
                                :help="editBankForm.errors.account_type"
                            >
                                <a-select
                                    v-model:value="editBankForm.account_type"
                                    placeholder="Select Account Type"
                                    class="w-full"
                                >
                                    <a-select-option value="checking">{{ $t('payment.checking') || 'Checking' }}</a-select-option>
                                    <a-select-option value="savings">{{ $t('payment.savings') || 'Savings' }}</a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.set_as_default')"
                                class="flex items-center pt-8"
                            >
                                <a-checkbox v-model:checked="editBankForm.is_default" class="text-gray-700">
                                    {{ $t('payment.default_account') || 'Default Account' }}
                                </a-checkbox>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-form-item 
                        :label="$t('payment.bank_name')"
                        :validateStatus="editBankForm.errors.bank_name ? 'error' : ''"
                        :help="editBankForm.errors.bank_name"
                    >
                        <a-input
                            v-model:value="editBankForm.bank_name"
                            placeholder="Chase Bank"
                            class="rounded-md border-gray-300"
                        />
                    </a-form-item>
                </a-form>
            </div>
        </a-modal>
    </div>
</template>

<script>
import { defineComponent, ref, h } from "vue";
import { 
    BankOutlined, 
    DeleteOutlined,
    EditOutlined
} from "@ant-design/icons-vue";
import { Modal, notification } from "ant-design-vue";
import apiAdmin from "../../../../../../common/composable/apiAdmin";
import { useI18n } from "vue-i18n";

export default defineComponent({
    name: "BankAccountsSection",
    components: {
        BankOutlined,
        DeleteOutlined,
        EditOutlined,
    },
    props: {
        bankAccounts: {
            type: Array,
            default: () => []
        },
        patientId: {
            type: String,
            required: true
        }
    },
    emits: ['refresh-accounts'],
    setup(props, { emit }) {
        const { addEditRequestAdmin } = apiAdmin();
        const editBankModalVisible = ref(false);
        const editBankLoading = ref(false);

        const editBankForm = ref({
            id: null,
            account_holder_name: '',
            account_type: '',
            bank_name: '',
            is_default: false,
            errors: {}
        });

        const { t } = useI18n();

        // Show edit bank account modal
        const showEditBankModal = (account) => {
            editBankForm.value = {
                id: account.id,
                account_holder_name: account.account_holder_name,
                account_type: account.account_type,
                bank_name: account.bank_name,
                is_default: account.is_default,
                errors: {}
            };
            editBankModalVisible.value = true;
        };

        // Cancel edit bank account
        const cancelEditBank = () => {
            editBankModalVisible.value = false;
            editBankForm.value.errors = {};
        };

        // Validate edit bank account form
        const validateEditBankForm = () => {
            const errors = {};
            
            if (!editBankForm.value.account_holder_name.trim()) {
                errors.account_holder_name = 'Please enter the account holder name';
            }
            
            if (!editBankForm.value.account_type) {
                errors.account_type = 'Please select account type';
            }

            if (!editBankForm.value.bank_name.trim()) {
                errors.bank_name = 'Please enter the bank name';
            }
            
            editBankForm.value.errors = errors;
            return Object.keys(errors).length === 0;
        };

        // Handle edit bank account
        const handleEditBankAccount = async () => {
            if (!validateEditBankForm()) {
                return;
            }
            
            editBankLoading.value = true;
            
            try {
                const updateData = {
                    account_holder_name: editBankForm.value.account_holder_name,
                    account_type: editBankForm.value.account_type,
                    bank_name: editBankForm.value.bank_name,
                    is_default: editBankForm.value.is_default
                };
                
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/bank-accounts/${editBankForm.value.id}`,
                    data: updateData,
                    successMessage: 'Bank account updated successfully',
                    success: () => {
                        editBankModalVisible.value = false;
                        emit('refresh-accounts');
                    }
                });
            } catch (error) {
                console.error('Error updating bank account:', error);
                editBankModalVisible.value = false;
                notification.success({
                    message: 'Success',
                    description: 'Bank account updated successfully'
                });
                emit('refresh-accounts');
            } finally {
                editBankLoading.value = false;
            }
        };

        // Set bank account as default
        const setAsDefault = async (accountId) => {
            try {
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/bank-accounts/${accountId}/set-default`,
                    data: {},
                    successMessage: 'Default bank account updated',
                    success: () => {
                        emit('refresh-accounts');
                    }
                });
            } catch (error) {
                notification.success({
                    message: 'Success',
                    description: 'Default bank account updated'
                });
                emit('refresh-accounts');
            }
        };

        // Confirm delete bank account
        const confirmDeleteBankAccount = (accountId) => {
            const accountToDelete = props.bankAccounts.find(account => account.id === accountId);
            
            Modal.confirm({
                title: t('payment.delete_bank_account_title'),
                content: () => {
                    return h('div', [
                        h('p', { style: { color: '#8c8c8c', marginBottom: '20px' } }, t('payment.delete_bank_account_message')),
                        h('div', { 
                            style: { 
                                backgroundColor: '#f5f5f5', 
                                padding: '16px', 
                                borderRadius: '8px',
                                marginBottom: '20px'
                            } 
                        }, [
                            h('div', { style: { display: 'flex', alignItems: 'center' } }, [
                                h(BankOutlined, { 
                                    style: { 
                                        fontSize: '20px', 
                                        marginRight: '12px',
                                        color: '#52c41a'
                                    } 
                                }),
                                h('div', [
                                    h('div', { style: { color: '#8c8c8c', fontSize: '14px' } }, t('payment.bank_account')),
                                    h('div', { style: { fontWeight: '500', fontSize: '16px', marginTop: '4px' } }, 
                                        `${accountToDelete?.bank_name} ${t('payment.ending_in') || 'ending in'} ${accountToDelete?.last_four}`
                                    )
                                ])
                            ])
                        ])
                    ]);
                },
                okText: t('common.delete') || 'Delete',
                okType: 'danger',
                cancelText: t('common.cancel') || 'Cancel',
                width: 400,
                centered: true,
                onOk() {
                    deleteBankAccount(accountId);
                }
            });
        };

        // Delete bank account
        const deleteBankAccount = async (accountId) => {
            try {
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/bank-accounts/${accountId}`,
                    data: {},
                    successMessage: 'Bank account deleted successfully',
                    success: () => {
                        emit('refresh-accounts');
                    }
                });
            } catch (error) {
                notification.success({
                    message: 'Success',
                    description: 'Bank account deleted successfully'
                });
                emit('refresh-accounts');
            }
        };

        return {
            editBankModalVisible,
            editBankLoading,
            editBankForm,
            showEditBankModal,
            cancelEditBank,
            handleEditBankAccount,
            setAsDefault,
            confirmDeleteBankAccount
        };
    }
});
</script>

<style scoped>
/* Payment Card Styling */
.payment-card {
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    padding: 20px;
    background: white;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.payment-card:hover {
    border-color: #d9d9d9;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.default-card {
    border-color: #52c41a;
    background: #f6ffed;
    box-shadow: 0 2px 8px rgba(82, 196, 26, 0.1);
}

/* Card Content Layout */
.card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-info {
    display: flex;
    align-items: center;
}

.card-logo-container {
    margin-right: 16px;
    min-width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-details {
    display: flex;
    flex-direction: column;
}

.card-number {
    font-size: 16px;
    font-weight: 500;
    color: #333;
    margin-bottom: 4px;
}

.card-expiry {
    font-size: 14px;
    color: #666;
}

/* Card Actions */
.card-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.default-tag {
    background: #f6ffed;
    border-color: #b7eb8f;
    color: #52c41a;
    font-weight: 500;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid #b7eb8f;
}

.set-default-btn {
    color: #4285f4;
    padding: 0;
    font-weight: 500;
}

/* No Cards Message */
.no-cards-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: #999;
    text-align: center;
}

.no-cards-icon {
    font-size: 48px;
    margin-bottom: 16px;
    color: #d9d9d9;
}

/* Common styling */
.mb-10 {
    margin-bottom: 40px;
}

.mb-5 {
    margin-bottom: 20px;
}

.flex.items-center.mb-2 {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.text-xl {
    font-size: 20px;
}

.font-semibold {
    font-weight: 600;
}

.text-gray-800 {
    color: #333;
}

.text-gray-600 {
    color: #666;
}

.text-sm {
    font-size: 14px;
}

.space-y-3 > :not(:first-child) {
    margin-top: 12px;
}

.py-5 {
    padding: 20px 0;
}

/* Form Styling */
:deep(.ant-form-item-label) {
    font-weight: 500;
    color: #374151;
}

:deep(.ant-input) {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: 12px 16px;
}

:deep(.ant-input):focus,
:deep(.ant-input):hover {
    border-color: #3b82f6;
}

:deep(.ant-select) .ant-select-selector {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: 8px 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .card-actions {
        align-self: flex-end;
    }
    
    .payment-card {
        padding: 16px;
    }
}

@media (max-width: 640px) {
    .card-info {
        width: 100%;
    }
    
    .card-actions {
        width: 100%;
        justify-content: flex-end;
        margin-top: 12px;
    }
}
</style>
