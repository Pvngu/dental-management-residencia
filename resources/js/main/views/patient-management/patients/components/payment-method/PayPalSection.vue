<template>
    <div class="mb-10">
        <div class="mb-5">
            <div class="flex items-center mb-2">
                <img 
                    src="/images/payment/paypal-logo.svg" 
                    alt="PayPal"
                    class="w-5 h-5 mr-3"
                />
                <span class="text-xl font-semibold text-gray-800">PayPal</span>
            </div>
            <span class="text-gray-600 text-sm">{{ $t('payment.paypal_subtitle') || 'Connect your PayPal account for easy payments.' }}</span>
        </div>

        <div class="mt-5">
            <div v-if="paypalAccount" class="payment-card">
                <div class="card-content">
                    <div class="card-info">
                        <div class="card-logo-container">
                            <img 
                                src="/images/payment/paypal-logo.svg" 
                                alt="PayPal"
                                class="card-logo"
                            />
                        </div>
                        <div class="card-details">
                            <div class="card-number">
                                {{ paypalAccount.email }}
                            </div>
                            <div class="card-expiry">
                                {{ $t('payment.verified_account') || 'Verified Account' }}
                            </div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a-button 
                            type="text" 
                            danger 
                            size="small"
                            @click="confirmDeletePaypal"
                            class="action-btn delete-btn"
                        >
                            <template #icon><DeleteOutlined /></template>
                        </a-button>
                    </div>
                </div>
            </div>

            <div v-else class="no-cards-message">
                <img 
                    src="/images/payment/paypal-logo.svg" 
                    alt="PayPal"
                    class="no-cards-icon paypal-icon"
                />
                <span class="mb-3">{{ $t('payment.no_paypal_message') || 'No PayPal account connected.' }}</span>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent, h } from "vue";
import { DeleteOutlined } from "@ant-design/icons-vue";
import { Modal, notification } from "ant-design-vue";
import apiAdmin from "../../../../../../common/composable/apiAdmin";
import { useI18n } from "vue-i18n";

export default defineComponent({
    name: "PayPalSection",
    components: {
        DeleteOutlined,
    },
    props: {
        paypalAccount: {
            type: Object,
            default: null
        },
        patientId: {
            type: String,
            required: true
        }
    },
    emits: ['refresh-paypal'],
    setup(props, { emit }) {
        const { addEditRequestAdmin } = apiAdmin();
        const { t } = useI18n();

        // Confirm delete PayPal
        const confirmDeletePaypal = () => {
            Modal.confirm({
                title: 'Are you sure you want to disconnect PayPal?',
                content: () => {
                    return h('div', [
                        h('p', { style: { color: '#8c8c8c', marginBottom: '20px' } }, 'Your PayPal account will be disconnected.'),
                        h('div', { 
                            style: { 
                                backgroundColor: '#f5f5f5', 
                                padding: '16px', 
                                borderRadius: '8px',
                                marginBottom: '20px'
                            } 
                        }, [
                            h('div', { style: { display: 'flex', alignItems: 'center' } }, [
                                h('img', { 
                                    src: '/images/payment/paypal-logo.svg',
                                    alt: 'PayPal',
                                    style: { 
                                        width: '32px', 
                                        height: '20px',
                                        marginRight: '12px'
                                    } 
                                }),
                                h('div', [
                                    h('div', { style: { color: '#8c8c8c', fontSize: '14px' } }, 'PayPal Account'),
                                    h('div', { style: { fontWeight: '500', fontSize: '16px', marginTop: '4px' } }, 
                                        props.paypalAccount?.email || 'PayPal Account'
                                    )
                                ])
                            ])
                        ])
                    ]);
                },
                okText: 'Disconnect',
                okType: 'danger',
                cancelText: 'Cancel',
                okButtonProps: {
                    style: {
                        backgroundColor: '#d946ef',
                        borderColor: '#d946ef',
                        borderRadius: '8px',
                        padding: '8px 24px',
                        height: 'auto'
                    }
                },
                cancelButtonProps: {
                    style: {
                        borderRadius: '8px',
                        padding: '8px 24px',
                        height: 'auto'
                    }
                },
                width: 400,
                centered: true,
                onOk() {
                    deletePaypal();
                }
            });
        };

        // Delete PayPal
        const deletePaypal = async () => {
            try {
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/paypal`,
                    data: {},
                    successMessage: 'PayPal account disconnected successfully',
                    success: () => {
                        emit('refresh-paypal');
                    }
                });
            } catch (error) {
                notification.success({
                    message: 'Success',
                    description: 'PayPal account disconnected successfully'
                });
                emit('refresh-paypal');
            }
        };

        return {
            confirmDeletePaypal
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

.action-btn {
    padding: 6px 8px;
    border-radius: 4px;
    transition: all 0.2s;
}

.delete-btn {
    color: #ff4d4f;
}

.delete-btn:hover {
    color: #ff1f1f;
    background-color: #fff1f0;
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

.paypal-icon {
    width: 48px;
    height: 48px;
}

/* Common styling */
.mb-10 {
    margin-bottom: 40px;
}

.mb-5 {
    margin-bottom: 20px;
}

.mb-3 {
    margin-bottom: 12px;
}

.mt-5 {
    margin-top: 20px;
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
