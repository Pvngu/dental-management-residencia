import { reactive, toRefs } from "vue";

const state = reactive({
    isPlanExpired: false,
    expirationDate: null,
});

const subscriptionState = () => {
    const setPlanExpired = (expiredInfo) => {
        state.isPlanExpired = true;
        state.expirationDate = expiredInfo.expired_on;
    };

    const clearPlanExpired = () => {
        state.isPlanExpired = false;
        state.expirationDate = null;
    };

    return {
        ...toRefs(state),
        setPlanExpired,
        clearPlanExpired,
    };
};

export default subscriptionState;
