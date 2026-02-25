<template>
    <keep-alive>
        <component key="vertical" v-bind:is="'VerticalStyle'" />
    </keep-alive>
    <GlobalSettingsModal />
    <PlanExpiredModal />
</template>

<script>
import common from "../../common/composable/common";
import VerticalStyle from "./VerticalStyle.vue";
import LoadingApp from "../../main/views/LoadingApp.vue";
import GlobalSettingsModal from "../../main/views/settings/GlobalSettingsModal.vue";
import PlanExpiredModal from "./PlanExpiredModal.vue";

export default {
    components: {
        VerticalStyle,
        LoadingApp,
        GlobalSettingsModal,
        PlanExpiredModal,
    },
    setup() {
        const { cssSettings } = common();

        return {
            cssSettings,
        };
    },
};
</script>
