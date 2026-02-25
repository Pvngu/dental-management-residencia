<template>
    <a-modal
        :open="isPlanExpired"
        :maskClosable="false"
        :closable="false"
        :title="
            $t('subscription_plans.plan_expired_title') ||
            'Subscription Plan Expired'
        "
        :footer="null"
        width="80%"
        :keyboard="false"
    >
        <a-alert
            :message="
                $t('subscription_plans.plan_expired_message') ||
                'Your subscription plan has expired. Please upgrade or renew your plan to continue accessing the application.'
            "
            type="error"
            show-icon
            class="mb-4"
        >
            <template #description v-if="expirationDate">
                {{ $t("subscription_plans.expired_on") }}:
                {{ formatDate(expirationDate) }}
            </template>
        </a-alert>

        <PaymentMethodsModal
            v-if="responseData && responseData.currency"
            :visible="paymentMethodsModalVisible"
            :subscribePlan="currentSelectedPlan"
            :currency="responseData.currency"
            :planType="packageType"
            @closed="onPaymentMethodsModalClose"
            @offlinePaymentSuccess="offlinePaymentSuccess"
        />

        <div class="mt-5" v-if="!paymentMethodsModalVisible">
            <a-row :gutter="16" class="mb-5">
                <a-col :span="24" class="text-center">
                    <a-radio-group v-model:value="packageType">
                        <a-radio-button value="monthly">
                            {{ $t("subscription_plans.monthly") }}
                        </a-radio-button>
                        <a-radio-button value="annual">
                            {{ $t("subscription_plans.yearly") }}
                        </a-radio-button>
                    </a-radio-group>
                </a-col>
            </a-row>
            <a-row
                :gutter="[16, 16]"
                v-if="
                    responseData &&
                    responseData.all_subscription_plans &&
                    (responseData.currency || appSetting.currency)
                "
                justify="center"
            >
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="12"
                    :lg="8"
                    :xl="8"
                    v-for="allSubscriptionPlan in responseData.all_subscription_plans"
                    :key="allSubscriptionPlan.xid"
                >
                    <a-card hoverable class="h-full">
                        <template #title>
                            <div class="text-center">
                                <a-typography-title :level="4">
                                    {{ allSubscriptionPlan.name }}
                                </a-typography-title>
                            </div>
                        </template>
                        <div class="text-center mb-4">
                            <a-typography-title :level="3" type="secondary">
                                {{
                                    packageType == "monthly"
                                        ? formatAmountUsingCurrencyObject(
                                              allSubscriptionPlan.monthly_price,
                                              responseData.currency ||
                                                  appSetting.currency
                                          )
                                        : formatAmountUsingCurrencyObject(
                                              allSubscriptionPlan.annual_price,
                                              responseData.currency ||
                                                  appSetting.currency
                                          )
                                }}
                            </a-typography-title>
                            <span>
                                {{
                                    packageType == "monthly"
                                        ? $t("subscription_plans.per_month")
                                        : $t("subscription_plans.per_year")
                                }}
                            </span>
                        </div>

                        <ul class="list-disc pl-5 mb-4">
                            <li
                                v-for="feature in allSubscriptionPlan.features"
                                :key="feature"
                            >
                                {{ feature }}
                            </li>
                        </ul>

                        <div class="text-center mt-auto">
                            <a-button
                                type="primary"
                                @click="subscribeThisPlan(allSubscriptionPlan)"
                                block
                                size="large"
                            >
                                {{ $t("payment_transaction.subscribe") }}
                                <DoubleRightOutlined />
                            </a-button>
                        </div>
                    </a-card>
                </a-col>
            </a-row>
            <div v-else class="text-center">
                <a-spin />
            </div>
        </div>
    </a-modal>
</template>

<script>
import { ref, onMounted, defineComponent, watch } from "vue";
import { DoubleRightOutlined } from "@ant-design/icons-vue";
import common from "../../common/composable/common";
import subscriptionState from "../composable/subscriptionState";
import PaymentMethodsModal from "../../superadmin/views/admin/PaymentMethodsModal.vue";

export default defineComponent({
    components: {
        DoubleRightOutlined,
        PaymentMethodsModal,
    },
    setup() {
        const { isPlanExpired, expirationDate, clearPlanExpired } =
            subscriptionState();
        const { formatAmountUsingCurrencyObject, formatDate, appSetting } =
            common();
        const responseData = ref([]);
        const packageType = ref("monthly");
        const currentSelectedPlan = ref({});
        const paymentMethodsModalVisible = ref(false);

        const fetchAllPlans = () => {
            axiosAdmin.get("all-subscription-plans").then((response) => {
                responseData.value = response.data;
                if (appSetting.value && appSetting.value.package_type) {
                    packageType.value = appSetting.value.package_type;
                }
            });
        };

        const subscribeThisPlan = (selectedPlan) => {
            currentSelectedPlan.value = selectedPlan;
            paymentMethodsModalVisible.value = true;
        };

        const onPaymentMethodsModalClose = (reloadPage) => {
            paymentMethodsModalVisible.value = false;
            if (reloadPage) {
                // If payment successful, we can clear the expired state
                // Ideally, we might want to refresh the app or specific data
                clearPlanExpired();
                window.location.reload();
            }
        };

        const offlinePaymentSuccess = (reloadPage) => {
            paymentMethodsModalVisible.value = false;
            // For offline payment, we might still want to show the modal or a message
            // but usually it requires admin approval.
            // For now, let's treat it as a success-like flow but maybe show a message?
            // Or reload to let the backend decide if it's still expired (pending status might still be "expired" until approved)
            // But the user expects some feedback.
            // If the backend allows access with pending request, validation will pass.
            // If not, the modal will pop up again after reload.
            window.location.reload();
        };

        // Watch for expiration state to load plans if needed
        // But since this is a global modal component, we can load on mount if we expect it to be used.
        // Or better, load when it becomes visible.

        watch(isPlanExpired, (newVal) => {
            if (newVal) {
                fetchAllPlans();
            }
        });

        // Also check on mount if it's already true (unlikely unless persistent state)
        onMounted(() => {
            if (isPlanExpired.value) {
                fetchAllPlans();
            }
        });

        return {
            isPlanExpired,
            expirationDate,
            formatAmountUsingCurrencyObject,
            formatDate,
            responseData,
            packageType,
            subscribeThisPlan,
            currentSelectedPlan,
            paymentMethodsModalVisible,
            onPaymentMethodsModalClose,
            offlinePaymentSuccess,
        };
    },
});
</script>
