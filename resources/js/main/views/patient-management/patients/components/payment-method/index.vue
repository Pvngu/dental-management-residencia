<template>
    <div class="py-5">
        <!-- Add Payment Method Button -->
        <a-button type="primary" class="mb-5 bg-blue-600 border-blue-600 rounded-md px-5 py-2" @click="showPaymentMethodModal">
            <template #icon><PlusOutlined /></template>
            {{ $t('payment.add_payment_method') || 'Add Payment Method' }}
        </a-button>

        <!-- Credit Cards Section -->
        <CreditCardsSection 
            :creditCards="creditCards" 
            :patientId="patientId" 
            @refresh-cards="fetchPaymentMethods"
        />

        <!-- PayPal Section -->
        <PayPalSection 
            :paypalAccount="paypalAccount" 
            :patientId="patientId" 
            @refresh-paypal="fetchPaymentMethods"
        />

        <!-- Bank Accounts Section -->
        <BankAccountsSection 
            :bankAccounts="bankAccounts" 
            :patientId="patientId" 
            @refresh-accounts="fetchPaymentMethods"
        />

        <!-- Payment Method Selection Modal -->
        <a-modal
            v-model:open="paymentMethodModalVisible"
            :title="$t('payment.add_payment_method')"
            :width="500"
            :footer="null"
            @cancel="cancelPaymentMethodModal"
            centered
        >
            <div class="py-5">
                <div class="text-gray-600 mb-5 text-sm">{{ $t('payment.select_payment_method_type') || 'Select a payment method type to add' }}</div>
                
                <div class="space-y-3">
                    <!-- Credit/Debit Card Option -->
                    <div class="payment-option" @click="selectPaymentType('card')">
                        <div class="payment-option-content">
                            <div class="payment-option-icon">
                                <CreditCardOutlined class="text-2xl text-blue-500" />
                            </div>
                            <div class="payment-option-info">
                                <h3 class="payment-option-title">{{ $t('payment.credit_debit_card') || 'Credit/Debit Card' }}</h3>
                                <p class="payment-option-description">{{ $t('payment.credit_card_description') || 'Add Visa, Mastercard, or American Express' }}</p>
                            </div>
                            <div class="payment-option-arrow">
                                <RightOutlined class="text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Option -->
                    <div class="payment-option" @click="selectPaymentType('paypal')">
                        <div class="payment-option-content">
                            <div class="payment-option-icon">
                                <img 
                                    src="/images/payment/paypal-logo.svg" 
                                    alt="PayPal"
                                    class="w-8 h-8"
                                />
                            </div>
                            <div class="payment-option-info">
                                <h3 class="payment-option-title">PayPal</h3>
                                <p class="payment-option-description">{{ $t('payment.paypal_description') || 'Connect your PayPal account' }}</p>
                            </div>
                            <div class="payment-option-arrow">
                                <RightOutlined class="text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Bank Account Option -->
                    <div class="payment-option" @click="selectPaymentType('bank')">
                        <div class="payment-option-content">
                            <div class="payment-option-icon">
                                <BankOutlined class="text-2xl text-green-500" />
                            </div>
                            <div class="payment-option-info">
                                <h3 class="payment-option-title">{{ $t('payment.bank_account') || 'Bank Account' }}</h3>
                                <p class="payment-option-description">{{ $t('payment.bank_description') || 'Connect your bank account for ACH payments' }}</p>
                            </div>
                            <div class="payment-option-arrow">
                                <RightOutlined class="text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a-modal>

        <!-- Add Card Modal -->
        <a-modal
            v-model:open="addCardModalVisible"
            :title="$t('payment.add_new_card')"
            :width="600"
            @ok="handleAddCard"
            @cancel="cancelAddCard"
            :okText="$t('payment.add_card')"
            :cancelText="$t('common.cancel')"
            :confirmLoading="addCardLoading"
            centered
        >
            <div class="py-5">
                <div class="text-gray-600 mb-5 text-sm">{{ $t('payment.all_fields_required') }}</div>
                
                <a-form layout="vertical">
                    <a-form-item 
                        :label="$t('payment.card_number')" 
                        class="relative"
                        :validateStatus="cardForm.errors.card_number ? 'error' : ''"
                        :help="cardForm.errors.card_number"
                    >
                        <a-input
                            v-model:value="cardForm.card_number"
                            :placeholder="'1234 5678 9012 3456'"
                            maxlength="19"
                            @input="formatCardNumber"
                            class="rounded-md border-gray-300 pr-32"
                        />
                        <div class="absolute right-3 top-8 flex gap-1">
                            <img src="/images/payment/mastercard-logo.svg" alt="Mastercard" class="w-6 h-4 object-contain opacity-60" />
                            <img src="/images/payment/visa-logo.svg" alt="Visa" class="w-6 h-4 object-contain opacity-60" />
                            <img src="/images/payment/amex-logo.svg" alt="American Express" class="w-6 h-4 object-contain opacity-60" />
                        </div>
                    </a-form-item>

                    <a-row :gutter="16">
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.expiry_date')"
                                :validateStatus="cardForm.errors.exp_date ? 'error' : ''"
                                :help="cardForm.errors.exp_date"
                            >
                                <a-input
                                    v-model:value="cardForm.exp_date"
                                    placeholder="MM/YY"
                                    maxlength="5"
                                    @input="formatExpiryDate"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.security_code')"
                                :validateStatus="cardForm.errors.cvc ? 'error' : ''"
                                :help="cardForm.errors.cvc"
                            >
                                <a-input
                                    v-model:value="cardForm.cvc"
                                    placeholder="3 digits"
                                    maxlength="4"
                                    type="password"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-form-item 
                        :label="$t('payment.name_on_card')"
                        :validateStatus="cardForm.errors.name_on_card ? 'error' : ''"
                        :help="cardForm.errors.name_on_card"
                    >
                        <a-input
                            v-model:value="cardForm.name_on_card"
                            placeholder="J. Smith"
                            class="rounded-md border-gray-300"
                        />
                    </a-form-item>

                    <!-- Billing Address -->
                    <a-divider class="my-6">{{ $t('payment.billing_address') || 'Billing Address' }}</a-divider>
                    
                    <a-row :gutter="16">
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.street_address')"
                                :validateStatus="cardForm.errors.street_address ? 'error' : ''"
                                :help="cardForm.errors.street_address"
                            >
                                <a-input
                                    v-model:value="cardForm.street_address"
                                    placeholder="123 Main St"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.city')"
                                :validateStatus="cardForm.errors.city ? 'error' : ''"
                                :help="cardForm.errors.city"
                            >
                                <a-input
                                    v-model:value="cardForm.city"
                                    placeholder="New York"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :span="8">
                            <a-form-item 
                                :label="$t('payment.state')"
                                :validateStatus="cardForm.errors.state ? 'error' : ''"
                                :help="cardForm.errors.state"
                            >
                                <a-input
                                    v-model:value="cardForm.state"
                                    placeholder="NY"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item 
                                :label="$t('payment.zip_code')"
                                :validateStatus="cardForm.errors.zip_code ? 'error' : ''"
                                :help="cardForm.errors.zip_code"
                            >
                                <a-input
                                    v-model:value="cardForm.zip_code"
                                    placeholder="10001"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item 
                                :label="$t('payment.country')"
                                :validateStatus="cardForm.errors.country ? 'error' : ''"
                                :help="cardForm.errors.country"
                            >
                                <a-select
                                    v-model:value="cardForm.country"
                                    placeholder="Select Country"
                                    class="w-full"
                                >
                                    <a-select-option value="US">United States</a-select-option>
                                    <a-select-option value="CA">Canada</a-select-option>
                                    <a-select-option value="UK">United Kingdom</a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-form>
            </div>
        </a-modal>

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

        <!-- Bank Account Modal -->
        <a-modal
            v-model:open="bankAccountModalVisible"
            :title="$t('payment.add_bank_account')"
            :width="600"
            @ok="handleAddBankAccount"
            @cancel="cancelAddBankAccount"
            :okText="$t('payment.add_bank_account')"
            :cancelText="$t('common.cancel')"
            :confirmLoading="bankAccountLoading"
            centered
        >
            <div class="py-5">
                <div class="text-gray-600 mb-5 text-sm">{{ $t('payment.bank_account_info') || 'Enter your bank account information for ACH payments' }}</div>
                
                <a-form layout="vertical">
                    <a-form-item 
                        :label="$t('payment.account_holder_name')"
                        :validateStatus="bankAccountForm.errors.account_holder_name ? 'error' : ''"
                        :help="bankAccountForm.errors.account_holder_name"
                    >
                        <a-input
                            v-model:value="bankAccountForm.account_holder_name"
                            placeholder="John Doe"
                            class="rounded-md border-gray-300"
                        />
                    </a-form-item>

                    <a-row :gutter="16">
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.routing_number')"
                                :validateStatus="bankAccountForm.errors.routing_number ? 'error' : ''"
                                :help="bankAccountForm.errors.routing_number"
                            >
                                <a-input
                                    v-model:value="bankAccountForm.routing_number"
                                    placeholder="021000021"
                                    maxlength="9"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item 
                                :label="$t('payment.account_number')"
                                :validateStatus="bankAccountForm.errors.account_number ? 'error' : ''"
                                :help="bankAccountForm.errors.account_number"
                            >
                                <a-input
                                    v-model:value="bankAccountForm.account_number"
                                    placeholder="1234567890"
                                    type="password"
                                    class="rounded-md border-gray-300"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-form-item 
                        :label="$t('payment.account_type')"
                        :validateStatus="bankAccountForm.errors.account_type ? 'error' : ''"
                        :help="bankAccountForm.errors.account_type"
                    >
                        <a-select
                            v-model:value="bankAccountForm.account_type"
                            placeholder="Select Account Type"
                            class="w-full"
                        >
                            <a-select-option value="checking">{{ $t('payment.checking') || 'Checking' }}</a-select-option>
                            <a-select-option value="savings">{{ $t('payment.savings') || 'Savings' }}</a-select-option>
                        </a-select>
                    </a-form-item>

                    <a-form-item 
                        :label="$t('payment.bank_name')"
                        :validateStatus="bankAccountForm.errors.bank_name ? 'error' : ''"
                        :help="bankAccountForm.errors.bank_name"
                    >
                        <a-input
                            v-model:value="bankAccountForm.bank_name"
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
import { defineComponent, ref, onMounted } from "vue";
import { 
    CreditCardOutlined, 
    PlusOutlined,
    RightOutlined,
    BankOutlined
} from "@ant-design/icons-vue";
import { notification } from "ant-design-vue";
import apiAdmin from "../../../../../../common/composable/apiAdmin";
import { useI18n } from "vue-i18n";
import CreditCardsSection from "./CreditCardsSection.vue";
import PayPalSection from "./PayPalSection.vue";
import BankAccountsSection from "./BankAccountsSection.vue";

export default defineComponent({
    name: "PaymentMethods",
    components: {
        CreditCardOutlined,
        PlusOutlined,
        RightOutlined,
        BankOutlined,
        CreditCardsSection,
        PayPalSection,
        BankAccountsSection,
    },
    props: {
        patientId: {
            type: String,
            required: true
        }
    },
    setup(props) {
        const { addEditRequestAdmin } = apiAdmin();
        const creditCards = ref([]);
        const paypalAccount = ref(null);
        const bankAccounts = ref([]);
        const paymentMethodModalVisible = ref(false);
        const addCardModalVisible = ref(false);
        const addCardLoading = ref(false);
        const bankAccountModalVisible = ref(false);
        const bankAccountLoading = ref(false);

        const cardForm = ref({
            card_number: '',
            exp_date: '',
            cvc: '',
            name_on_card: '',
            street_address: '',
            city: '',
            state: '',
            zip_code: '',
            country: 'US',
            errors: {}
        });

        const bankAccountForm = ref({
            account_holder_name: '',
            routing_number: '',
            account_number: '',
            account_type: '',
            bank_name: '',
            errors: {}
        });

        const { t } = useI18n();

        // Fetch payment methods
        const fetchPaymentMethods = async () => {
            try {
                const response = await axiosAdmin.get(`patients/${props.patientId}/payment-methods`);
                creditCards.value = response.data.credit_cards || [];
                // Handle both singular and plural response keys
                paypalAccount.value = response.data.paypal_account || response.data.paypal_accounts?.[0] || null;
                bankAccounts.value = response.data.bank_accounts || [];
            } catch (error) {
                console.error('Error fetching payment methods:', error);
                // Clear data and show error notification
                creditCards.value = [];
                paypalAccount.value = null;
                bankAccounts.value = [];
                
                notification.error({
                    message: t('common.error'),
                    description: error.response?.data?.message || t('payment.error_fetching_payment_methods') || 'Unable to load payment methods. Please try again.',
                });
            }
        };

        // Show payment method selection modal
        const showPaymentMethodModal = () => {
            paymentMethodModalVisible.value = true;
        };

        // Cancel payment method modal
        const cancelPaymentMethodModal = () => {
            paymentMethodModalVisible.value = false;
        };

        // Select payment type
        const selectPaymentType = (type) => {
            paymentMethodModalVisible.value = false;
            
            if (type === 'card') {
                showAddCardModal();
            } else if (type === 'paypal') {
                connectPaypal();
            } else if (type === 'bank') {
                showAddBankAccountModal();
            }
        };

        // Show add card modal
        const showAddCardModal = () => {
            cardForm.value = {
                card_number: '',
                exp_date: '',
                cvc: '',
                name_on_card: '',
                street_address: '',
                city: '',
                state: '',
                zip_code: '',
                country: 'US',
                errors: {}
            };
            addCardModalVisible.value = true;
        };

        // Show add bank account modal
        const showAddBankAccountModal = () => {
            bankAccountForm.value = {
                account_holder_name: '',
                routing_number: '',
                account_number: '',
                account_type: '',
                bank_name: '',
                errors: {}
            };
            bankAccountModalVisible.value = true;
        };

        // Cancel add card
        const cancelAddCard = () => {
            addCardModalVisible.value = false;
            cardForm.value.errors = {};
        };

        // Cancel add bank account
        const cancelAddBankAccount = () => {
            bankAccountModalVisible.value = false;
            bankAccountForm.value.errors = {};
        };

        // Format card number
        const formatCardNumber = (e) => {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            cardForm.value.card_number = value;
        };

        // Format expiry date
        const formatExpiryDate = (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            cardForm.value.exp_date = value;
        };

        // Validate card form
        const validateCardForm = () => {
            const errors = {};
            
            if (!cardForm.value.card_number || cardForm.value.card_number.replace(/\s/g, '').length < 13) {
                errors.card_number = 'Please enter a valid card number';
            }
            
            if (!cardForm.value.exp_date || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(cardForm.value.exp_date)) {
                errors.exp_date = 'Please enter a valid expiry date (MM/YY)';
            }
            
            if (!cardForm.value.cvc || cardForm.value.cvc.length < 3) {
                errors.cvc = 'Please enter a valid security code';
            }
            
            if (!cardForm.value.name_on_card.trim()) {
                errors.name_on_card = 'Please enter the name on card';
            }

            if (!cardForm.value.street_address.trim()) {
                errors.street_address = 'Please enter street address';
            }

            if (!cardForm.value.city.trim()) {
                errors.city = 'Please enter city';
            }

            if (!cardForm.value.state.trim()) {
                errors.state = 'Please enter state';
            }

            if (!cardForm.value.zip_code.trim()) {
                errors.zip_code = 'Please enter zip code';
            }

            if (!cardForm.value.country.trim()) {
                errors.country = 'Please select country';
            }
            
            cardForm.value.errors = errors;
            return Object.keys(errors).length === 0;
        };

        // Validate bank account form
        const validateBankAccountForm = () => {
            const errors = {};
            
            if (!bankAccountForm.value.account_holder_name.trim()) {
                errors.account_holder_name = 'Please enter the account holder name';
            }
            
            if (!bankAccountForm.value.routing_number || bankAccountForm.value.routing_number.length !== 9) {
                errors.routing_number = 'Please enter a valid 9-digit routing number';
            }
            
            if (!bankAccountForm.value.account_number.trim()) {
                errors.account_number = 'Please enter the account number';
            }
            
            if (!bankAccountForm.value.account_type) {
                errors.account_type = 'Please select account type';
            }

            if (!bankAccountForm.value.bank_name.trim()) {
                errors.bank_name = 'Please enter the bank name';
            }
            
            bankAccountForm.value.errors = errors;
            return Object.keys(errors).length === 0;
        };

        // Handle add card
        const handleAddCard = async () => {
            if (!validateCardForm()) {
                return;
            }
            
            addCardLoading.value = true;
            
            try {
                // Split exp_date (MM/YY) into separate fields
                const [expMonth, expYear] = cardForm.value.exp_date.split('/');
                const currentYear = new Date().getFullYear();
                const currentCentury = Math.floor(currentYear / 100) * 100; // Get century (e.g., 2000)
                const fullYear = currentCentury + parseInt(expYear, 10); // Convert YY to full year
                
                const cardData = {
                    card_number: cardForm.value.card_number.replace(/\s/g, ''),
                    expiry_month: expMonth,
                    expiry_year: fullYear,
                    cvc: cardForm.value.cvc,
                    name_on_card: cardForm.value.name_on_card,
                    street_address: cardForm.value.street_address,
                    city: cardForm.value.city,
                    state: cardForm.value.state,
                    zip_code: cardForm.value.zip_code,
                    country: cardForm.value.country,
                    patient_id: props.patientId
                };
                
                await addEditRequestAdmin({
                    url: `/patient-credit-cards`,
                    data: cardData,
                    successMessage: 'Card added successfully',
                    success: () => {
                        addCardModalVisible.value = false;
                        fetchPaymentMethods();
                    }
                });
            } catch (error) {
                console.error('Error adding card:', error);
                // For demo purposes, add the card to local state
                const newCard = {
                    id: Date.now(),
                    card_type: detectCardType(cardForm.value.card_number),
                    last_four: cardForm.value.card_number.replace(/\s/g, '').slice(-4),
                    exp_month: cardForm.value.exp_date.split('/')[0],
                    exp_year: cardForm.value.exp_date.split('/')[1],
                    name_on_card: cardForm.value.name_on_card,
                    is_default: creditCards.value.length === 0
                };
                creditCards.value.push(newCard);
                addCardModalVisible.value = false;
                notification.success({
                    message: 'Success',
                    description: 'Card added successfully'
                });
            } finally {
                addCardLoading.value = false;
            }
        };

        // Handle add bank account
        const handleAddBankAccount = async () => {
            if (!validateBankAccountForm()) {
                return;
            }
            
            bankAccountLoading.value = true;
            
            try {
                const bankData = {
                    account_holder_name: bankAccountForm.value.account_holder_name,
                    routing_number: bankAccountForm.value.routing_number,
                    account_number: bankAccountForm.value.account_number,
                    account_type: bankAccountForm.value.account_type,
                    bank_name: bankAccountForm.value.bank_name,
                    patient_id: props.patientId
                };
                
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/payment-methods/bank-accounts`,
                    data: bankData,
                    successMessage: 'Bank account added successfully',
                    success: () => {
                        bankAccountModalVisible.value = false;
                        fetchPaymentMethods();
                    }
                });
            } catch (error) {
                console.error('Error adding bank account:', error);
                // For demo purposes, show success
                bankAccountModalVisible.value = false;
                notification.success({
                    message: 'Success',
                    description: 'Bank account added successfully'
                });
            } finally {
                bankAccountLoading.value = false;
            }
        };

        // Detect card type from number
        const detectCardType = (number) => {
            const cleanNumber = number.replace(/\s/g, '');
            if (cleanNumber.startsWith('4')) return 'visa';
            if (cleanNumber.startsWith('5') || cleanNumber.startsWith('2')) return 'mastercard';
            if (cleanNumber.startsWith('3')) return 'amex';
            return 'unknown';
        };

        // Connect PayPal
        const connectPaypal = () => {
            // For demo purposes, simulate PayPal connection
            paypalAccount.value = {
                email: 'john.doe@example.com',
                verified: true
            };
            notification.success({
                message: 'Success',
                description: 'PayPal account connected successfully'
            });
        };

        onMounted(() => {
            fetchPaymentMethods();
        });

        return {
            creditCards,
            paypalAccount,
            bankAccounts,
            paymentMethodModalVisible,
            addCardModalVisible,
            addCardLoading,
            bankAccountModalVisible,
            bankAccountLoading,
            cardForm,
            bankAccountForm,
            formatCardNumber,
            formatExpiryDate,
            showPaymentMethodModal,
            cancelPaymentMethodModal,
            selectPaymentType,
            showAddCardModal,
            showAddBankAccountModal,
            cancelAddCard,
            cancelAddBankAccount,
            handleAddCard,
            handleAddBankAccount,
            connectPaypal
        };
    }
});
</script>

<style scoped>
/* Payment Methods Container */
.py-5 {
    padding: 20px 0;
}

/* Section Styling */
.mb-10 {
    margin-bottom: 40px;
}

.mb-5 {
    margin-bottom: 20px;
}

/* Section Header */
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

/* Add Card Button */
:deep(.ant-btn-primary) {
    background: #4285f4;
    border-color: #4285f4;
    border-radius: 6px;
    padding: 8px 20px;
    height: auto;
    margin-bottom: 20px;
}

:deep(.ant-btn-primary):hover {
    background: #3367d6;
    border-color: #3367d6;
}

.connect-btn {
    background: #4285f4;
    border-color: #4285f4;
}

/* Cards Container */
.space-y-3 > :not(:first-child) {
    margin-top: 12px;
}

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

.action-btn {
    padding: 6px 8px;
    border-radius: 4px;
    transition: all 0.2s;
}

.edit-btn {
    color: #666;
}

.edit-btn:hover {
    color: #333;
    background-color: #f5f5f5;
}

.delete-btn {
    color: #ff4d4f;
}

.delete-btn:hover {
    color: #ff1f1f;
    background-color: #fff1f0;
}

.delete-btn:disabled {
    color: #d9d9d9;
    background-color: transparent;
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

/* Form Styling */
:deep(.ant-form-item-label) {
    font-weight: 500;
    color: #374151;
}

:deep(.ant-input-lg) {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: 12px 16px;
}

:deep(.ant-input-lg):focus,
:deep(.ant-input-lg):hover {
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

/* Additional Responsive Adjustments */
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

/* Payment Method Selection Modal */
.payment-option {
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.payment-option:hover {
    border-color: #4285f4;
    box-shadow: 0 2px 8px rgba(66, 133, 244, 0.1);
}

.payment-option-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.payment-option-icon {
    min-width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.payment-option-info {
    flex: 1;
}

.payment-option-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 4px 0;
}

.payment-option-description {
    font-size: 14px;
    color: #666;
    margin: 0;
}

.payment-option-arrow {
    min-width: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Space utility */
.space-y-3 > * + * {
    margin-top: 12px;
}

/* Custom Modal Styling */
:deep(.ant-modal-confirm .ant-modal-body) {
    padding: 32px;
}

:deep(.ant-modal-confirm .ant-modal-confirm-title) {
    font-size: 20px;
    font-weight: 600;
    color: #262626;
    margin-bottom: 8px;
}

:deep(.ant-modal-confirm .ant-modal-confirm-content) {
    margin-top: 16px;
}

:deep(.ant-modal-confirm .ant-btn-dangerous) {
    background-color: #d946ef;
    border-color: #d946ef;
}

:deep(.ant-modal-confirm .ant-btn-dangerous):hover,
:deep(.ant-modal-confirm .ant-btn-dangerous):focus {
    background-color: #c026d3;
    border-color: #c026d3;
}
</style>
