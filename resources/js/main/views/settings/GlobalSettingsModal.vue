<template>
    <a-modal
        v-model:open="visible"
        width="100%"
        :footer="null"
        :bodyStyle="{
            padding: '0',
            height: '94vh',
            overflow: 'hidden',
            margin: 0,
        }"
        centered
        class="global-settings-modal"
    >
        <div class="flex h-full">
            <!-- Sidebar -->
            <div
                class="w-64 bg-gray-50 border-r border-gray-200 h-full flex flex-col"
            >
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold m-0">
                        {{ $t("menu.settings") }}
                    </h2>
                </div>
                <SettingSidebar
                    :is-modal="true"
                    :selected-key="activeTab"
                    @menu-click="handleMenuClick"
                />
            </div>

            <!-- Content -->
            <div class="flex-1 h-full overflow-hidden flex flex-col bg-white">
                <div class="px-6 pt-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold mb-1">
                        {{ $t(`menu.${activeTab}`) }}
                    </h2>
                </div>
                <perfect-scrollbar ref="scrollbarRef" class="flex-1 px-6 pb-6">
                    <component :is="currentComponent" />
                </perfect-scrollbar>
            </div>
        </div>
    </a-modal>
</template>

<script>
import { defineComponent, ref, computed, watch } from "vue";
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import useSettingsModal from "../../../common/composable/useSettingsModal";
import common from "../../../common/composable/common";
import SettingSidebar from "./SettingSidebar.vue";

// Import Settings Components
import CompanyEdit from "./company/Edit.vue";
import ProfileEdit from "./profile/Edit.vue";
import PreferencesEdit from "./preferences/Edit.vue";
import Roles from "./roles/index.vue";
import Currencies from "./currency/index.vue";
import EmailSettings from "./email/Edit.vue"; // Assuming this is the correct path based on previous edits
import MailSettings from "./mail-settings/Edit.vue";
import LandingPage from "./landing-page/index.vue";
import ClinicSchedules from "./clinic-management/clinic-schedules/index.vue";
import TreatmentTypes from "./clinic-management/treatment-types/index.vue";
import Rooms from "./clinic-management/rooms/index.vue";
import ClinicLocations from "./clinic-management/clinic-locations/index.vue";
import InsuranceProviders from "./clinic-management/insurance-providers/index.vue";
import DatabaseBackup from "./database-backup/index.vue";
import StorageSettings from "./storage/Edit.vue";
import PatientFileTypes from "./system-catalog/patient-file-types/index.vue";
import ItemBrands from "./system-catalog/item-brands/index.vue";
import ItemCategories from "./system-catalog/item-categories/index.vue";
import ItemManufactures from "./system-catalog/item-manufactures/index.vue";
import EmailTemplates from "./email-templates/index.vue";

export default defineComponent({
    components: {
        PerfectScrollbar,
        SettingSidebar,
        CompanyEdit,
        ProfileEdit,
        PreferencesEdit,
        Roles,
        Currencies,
        EmailSettings,
        MailSettings,
        LandingPage,
        ClinicSchedules,
        TreatmentTypes,
        Rooms,
        ClinicLocations,
        InsuranceProviders,
        DatabaseBackup,
        StorageSettings,
        PatientFileTypes,
        ItemBrands,
        ItemCategories,
        ItemManufactures,
        EmailTemplates,
    },
    setup() {
        const { visible, activeTab } = useSettingsModal();
        const scrollbarRef = ref(null);

        watch(activeTab, (newVal) => {
            // Reset scroll position instantly
            if (scrollbarRef.value) {
                scrollbarRef.value.$el.scrollTop = 0;
            }
        });

        const handleMenuClick = (key) => {
            activeTab.value = key;
        };

        const currentComponent = computed(() => {
            switch (activeTab.value) {
                case "company":
                    return "CompanyEdit";
                case "profile":
                    return "ProfileEdit";
                case "preferences":
                    return "PreferencesEdit";
                case "roles":
                    return "Roles";
                case "currencies":
                    return "Currencies";
                case "email_settings":
                    return "MailSettings";
                case "landing_page":
                    return "LandingPage";
                case "clinic_schedules":
                    return "ClinicSchedules";
                case "treatment_types":
                    return "TreatmentTypes";
                case "rooms":
                    return "Rooms";
                case "clinic_locations":
                    return "ClinicLocations";
                case "insurance_providers":
                    return "InsuranceProviders";
                case "database_backup":
                    return "DatabaseBackup";
                case "storage_settings":
                    return "StorageSettings";
                case "patient_file_types":
                    return "PatientFileTypes";
                case "item_brands":
                    return "ItemBrands";
                case "item_categories":
                    return "ItemCategories";
                case "item_manufactures":
                    return "ItemManufactures";
                case "email_templates":
                    return "EmailTemplates";
                default:
                    return "CompanyEdit";
            }
        });

        return {
            visible,
            handleMenuClick,
            currentComponent,
            activeTab,
            scrollbarRef,
        };
    },
});
</script>

<style lang="less">
.global-settings-modal {
    .ant-modal-content {
        border-radius: 12px;
        overflow: hidden;
    }
    .ant-modal-body {
        padding: 0;
    }

    // Hide AdminPageHeader inside modal
    .breadcrumb-header {
        display: none !important;
    }

    // Also hide any other page headers if they use different classes
    .page-header {
        display: none !important;
    }
}
</style>
