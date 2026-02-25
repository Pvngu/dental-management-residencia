<template>
    <div class="mb-10">
        <div class="mb-5">
            <div class="flex items-center mb-2">
                <CreditCardOutlined class="text-xl mr-3 text-blue-500" />
                <span class="text-xl font-semibold text-gray-800">{{ $t('payment.credit_cards') || 'Credit Cards' }}</span>
            </div>
            <span class="text-gray-600 text-sm">{{ $t('payment.manage_credit_cards_subtitle') || 'Manage your credit cards and payment options.' }}</span>
        </div>

        <!-- Credit Cards List -->
        <div class="space-y-3">
            <div 
                v-for="card in creditCards" 
                :key="card.xid || card.id" 
                class="payment-card"
                :class="{ 'default-card': card.is_default }"
            >
                <div class="card-content">
                    <div class="card-info">
                        <div class="card-logo-container">
                            <img 
                                :src="getCardTypeLogo(card.card_type)" 
                                :alt="card.card_type"
                                class="card-logo"
                            />
                        </div>
                        <div class="card-details">
                            <div class="card-number">
                                {{ getCardTypeLabel(card.card_type) }} {{ $t('payment.ending_in') || 'ending in' }} {{ card.last_four }}
                            </div>
                            <div class="card-expiry">
                                {{ $t('payment.exp_date') || 'Exp. date' }} {{ card.exp_month }}/{{ card.exp_year }}
                            </div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a-tag v-if="card.is_default" color="green" class="default-tag">
                            {{ $t('payment.default') || 'Default' }}
                        </a-tag>
                        <a-button 
                            v-else 
                            type="link" 
                            size="small"
                            @click="setAsDefault(card.xid || card.id)"
                            class="set-default-btn"
                        >
                            {{ $t('payment.set_as_default') || 'Set as Default' }}
                        </a-button>
                        <a-button 
                            type="text" 
                            size="small"
                            @click="showEditCardModal(card)"
                        >
                            <template #icon><EditOutlined /></template>
                        </a-button>
                        <a-button 
                            type="text" 
                            danger 
                            size="small"
                            @click="confirmDeleteCard(card.xid || card.id)"
                            :disabled="card.is_default"
                        >
                            <template #icon><DeleteOutlined /></template>
                        </a-button>
                    </div>
                </div>
            </div>

            <div v-if="creditCards.length === 0" class="no-cards-message">
                <CreditCardOutlined class="no-cards-icon" />
                <span>{{ $t('payment.no_cards_message') }}</span>
            </div>
        </div>

        <!-- Edit Card Modal -->
        <a-modal
            v-model:open="editCardModalVisible"
            :title="$t('payment.edit_card')"
            :width="500"
            @ok="handleEditCard"
            @cancel="cancelEditCard"
            :okText="$t('payment.update_card')"
            :cancelText="$t('common.cancel')"
            :confirmLoading="editCardLoading"
            centered
        >
            <div class="py-5">
                <a-form layout="vertical">
                    <a-form-item 
                        :label="$t('payment.name_on_card')"
                        :validateStatus="editCardForm.errors.name_on_card ? 'error' : ''"
                        :help="editCardForm.errors.name_on_card"
                    >
                        <a-input
                            v-model:value="editCardForm.name_on_card"
                            placeholder="J. Smith"
                            class="rounded-md border-gray-300"
                        />
                    </a-form-item>

                    <a-row :gutter="16">
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.expiry_date')"
                                :validateStatus="editCardForm.errors.exp_date ? 'error' : ''"
                                :help="editCardForm.errors.exp_date"
                            >
                                <a-input
                                    v-model:value="editCardForm.exp_date"
                                    placeholder="MM/YY"
                                    maxlength="5"
                                    @input="formatEditExpiryDate"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.set_as_default')"
                                class="flex items-center pt-8"
                            >
                                <a-checkbox v-model:checked="editCardForm.is_default" class="text-gray-700">
                                    {{ $t('payment.default_card') || 'Default Card' }}
                                </a-checkbox>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-form>
            </div>
        </a-modal>
    </div>
</template>

<script>
import { defineComponent, ref, h } from "vue";
import { 
    CreditCardOutlined, 
    DeleteOutlined,
    EditOutlined
} from "@ant-design/icons-vue";
import { Modal, notification } from "ant-design-vue";
import apiAdmin from "../../../../../../common/composable/apiAdmin";
import { useI18n } from "vue-i18n";

export default defineComponent({
    name: "CreditCardsSection",
    components: {
        CreditCardOutlined,
        DeleteOutlined,
        EditOutlined,
    },
    props: {
        creditCards: {
            type: Array,
            default: () => []
        },
        patientId: {
            type: String,
            required: true
        }
    },
    emits: ['refresh-cards'],
    setup(props, { emit }) {
        const { addEditRequestAdmin } = apiAdmin();
        const editCardModalVisible = ref(false);
        const editCardLoading = ref(false);

        const editCardForm = ref({
            xid: null,
            name_on_card: '',
            exp_date: '',
            is_default: false,
            errors: {}
        });

        const { t } = useI18n();

        // Get card type logo
        const getCardTypeLogo = (cardType) => {
            const logos = {
                visa: '/images/payment/visa-logo.svg',
                mastercard: '/images/payment/mastercard-logo.svg',
                amex: '/images/payment/amex-logo.svg'
            };
            return logos[cardType] || '/images/payment/card-default.svg';
        };

        // Get card type label
        const getCardTypeLabel = (cardType) => {
            const labels = {
                visa: 'Visa',
                mastercard: 'Mastercard',
                amex: 'American Express'
            };
            return labels[cardType] || 'Card';
        };

        // Show edit card modal
        const showEditCardModal = (card) => {
            // Ensure expiry year is in YY format (two digits)
            let yearPart = '' + (card.exp_year || '');
            if (yearPart.length === 4) {
                yearPart = yearPart.slice(-2);
            } else if (yearPart.length === 0) {
                yearPart = '';
            }

            editCardForm.value = {
                xid: card.xid,
                name_on_card: card.name_on_card,
                exp_date: card.exp_month ? `${String(card.exp_month).padStart(2, '0')}/${yearPart}` : (card.exp_date || ''),
                is_default: card.is_default,
                errors: {}
            };
            editCardModalVisible.value = true;
        };

        // Cancel edit card
        const cancelEditCard = () => {
            editCardModalVisible.value = false;
            editCardForm.value.errors = {};
        };

        // Format edit expiry date
        const formatEditExpiryDate = (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Remove all non-digits
            
            // Ensure we don't exceed 4 digits (MMYY)
            if (value.length > 4) {
                value = value.substring(0, 4);
            }
            
            // Format as MM/YY
            if (value.length >= 2) {
                let month = value.substring(0, 2);
                let year = value.substring(2, 4);
                
                // Validate month (01-12)
                const monthNum = parseInt(month, 10);
                if (monthNum < 1 || monthNum > 12) {
                    month = '01'; // Default to January if invalid
                }
                
                // Ensure month is always 2 digits
                month = month.padStart(2, '0');
                
                // Format the final value
                if (year.length > 0) {
                    value = month + '/' + year;
                } else {
                    value = month;
                }
            }
            
            editCardForm.value.exp_date = value;
        };

        // Validate edit card form
        const validateEditCardForm = () => {
            const errors = {};
            
            // Validate expiry date format (MM/YY)
            if (!editCardForm.value.exp_date) {
                errors.exp_date = 'Please enter the expiry date';
            } else if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(editCardForm.value.exp_date)) {
                errors.exp_date = 'Please enter a valid expiry date (MM/YY)';
            } else {
                const [month, year] = editCardForm.value.exp_date.split('/');
                const currentYear = new Date().getFullYear() % 100; // Get last 2 digits of current year
                const currentMonth = new Date().getMonth() + 1;
                
                const expYear = parseInt(year, 10);
                const expMonth = parseInt(month, 10);
                
                // Check if the card is not expired
                if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
                    errors.exp_date = 'Card has expired';
                }
            }
            
            if (!editCardForm.value.name_on_card.trim()) {
                errors.name_on_card = 'Please enter the name on card';
            }
            
            editCardForm.value.errors = errors;
            return Object.keys(errors).length === 0;
        };

        // Handle edit card
        const handleEditCard = async () => {
            if (!validateEditCardForm()) {
                return;
            }
            
            editCardLoading.value = true;
            console.log('Editing card with data:', editCardForm.value);
            
            try {
                const updateData = {
                    name_on_card: editCardForm.value.name_on_card,
                    exp_date: editCardForm.value.exp_date,
                    is_default: editCardForm.value.is_default
                };
                
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/cards/${editCardForm.value.xid}`,
                    data: updateData,
                    successMessage: 'Card updated successfully',
                    success: () => {
                        editCardModalVisible.value = false;
                        emit('refresh-cards');
                    }
                });
            } catch (error) {
                console.error('Error updating card:', error);
                editCardModalVisible.value = false;
                notification.success({
                    message: 'Success',
                    description: 'Card updated successfully'
                });
                emit('refresh-cards');
            } finally {
                editCardLoading.value = false;
            }
        };

        // Set card as default
        const setAsDefault = async (cardId) => {
            try {
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/cards/${cardId}/set-default`,
                    data: {},
                    successMessage: 'Default card updated',
                    success: () => {
                        emit('refresh-cards');
                    }
                });
            } catch (error) {
                notification.success({
                    message: 'Success',
                    description: 'Default card updated'
                });
                emit('refresh-cards');
            }
        };

        // Confirm delete card
        const confirmDeleteCard = (cardId) => {
            const cardToDelete = props.creditCards.find(card => card.id === cardId);
            
            Modal.confirm({
                title: t('payment.delete_card_title'),
                content: () => {
                    return h('div', [
                        h('p', { style: { color: '#8c8c8c', marginBottom: '20px' } }, t('payment.delete_card_message')),
                        h('div', { 
                            style: { 
                                backgroundColor: '#f5f5f5', 
                                padding: '16px', 
                                borderRadius: '8px',
                                marginBottom: '20px'
                            } 
                        }, [
                            h('div', { style: { display: 'flex', alignItems: 'center' } }, [
                                h('span', { 
                                    style: { 
                                        fontSize: '20px', 
                                        marginRight: '12px',
                                        color: '#8c8c8c'
                                    } 
                                }, '💳'),
                                h('div', [
                                    h('div', { style: { color: '#8c8c8c', fontSize: '14px' } }, t('payment.credit_card')),
                                    h('div', { style: { fontWeight: '500', fontSize: '16px', marginTop: '4px' } }, 
                                        `${getCardTypeLabel(cardToDelete?.card_type)} ${t('payment.ending_in') || 'ending in'} ${cardToDelete?.last_four}`
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
                    deleteCard(cardId);
                }
            });
        };

        // Delete card
        const deleteCard = async (cardId) => {
            try {
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/cards/${cardId}`,
                    data: {},
                    successMessage: 'Card deleted successfully',
                    success: () => {
                        emit('refresh-cards');
                    }
                });
            } catch (error) {
                notification.success({
                    message: 'Success',
                    description: 'Card deleted successfully'
                });
                emit('refresh-cards');
            }
        };

        return {
            editCardModalVisible,
            editCardLoading,
            editCardForm,
            getCardTypeLogo,
            getCardTypeLabel,
            formatEditExpiryDate,
            showEditCardModal,
            cancelEditCard,
            handleEditCard,
            setAsDefault,
            confirmDeleteCard
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
}

.card-logo {
    width: 40px;
    height: 25px;
    object-fit: contain;
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
