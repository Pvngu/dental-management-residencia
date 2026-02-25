<template>
    <a-layout-header
        :style="{
            padding: '0 16px',
            background: 'white',
            position: 'sticky',
            top: 0,
            zIndex: 999,
        }"
    >
        <a-row>
            <a-col :span="4">
                <a-space>
                    <MenuOutlined class="trigger" @click="showHideMenu" />
                </a-space>
            </a-col>
            <a-col :span="20">
                <HeaderRightIcons>
                    <a-space>
                        <!-- Clinic Selector - Only show if multiple clinicLocations -->
                        <template v-if="clinicLocations.length > 1">
                            <a-select
                                v-model:value="selectedClinicId"
                                style="width: 200px"
                                :placeholder="
                                    $t('common.select_default_text', [
                                        $t('clinic_locations.clinic'),
                                    ])
                                "
                                @change="onClinicChange"
                                show-search
                                optionFilterProp="title"
                            >
                                <a-select-option
                                    key="all"
                                    value="all"
                                    v-if="
                                        user.is_superadmin ||
                                        permsArray.includes(
                                            'clinic_locations_view_all',
                                        ) ||
                                        permsArray.includes('admin')
                                    "
                                >
                                    {{
                                        $t("common.all_clinics") ||
                                        "All Clinics"
                                    }}
                                </a-select-option>
                                <a-select-option
                                    v-for="clinic in clinicLocations"
                                    :key="clinic.xid"
                                    :value="clinic.xid"
                                    :title="clinic.name"
                                >
                                    {{ clinic.name }}
                                </a-select-option>
                            </a-select>
                            <a-divider type="vertical" />
                        </template>

                        <!-- Current Clinic Display -->
                        <!-- <a-tag v-if="currentClinicName" color="blue">
                            {{ currentClinicName }}
                        </a-tag>

                        <template v-if="clinicLocations.length > 1">
                            <a-divider type="vertical" />
                        </template>

                        <a-button
                            v-if="permsArray.includes('doctors_available_view') || permsArray.includes('admin')"
                            type="link"
                            @click="showDoctorsDrawer"
                            title="View Available Doctors"
                        >
                            <template #icon>
                                <TeamOutlined />
                            </template>
                        </a-button>
                        <DentistAttendanceModal v-if="selectedClinicId && selectedClinicId !== 'all'" />
                        <Notifications />
                        <a-divider type="vertical" /> -->
                        <a-button
                            type="link"
                            @click="
                                () => {
                                    $router.push({
                                        name: 'admin.settings.profile.index',
                                    });
                                }
                            "
                            class="p-0!"
                        >
                            <a-avatar
                                size="small"
                                :src="user.profile_image_url"
                            />
                        </a-button>
                    </a-space>
                </HeaderRightIcons>
            </a-col>
        </a-row>

        <!-- Available Doctors Drawer -->
        <AvailableDoctorsModal
            :visible="doctorsDrawerVisible"
            @close="doctorsDrawerVisible = false"
            @book-appointment="handleBookAppointment"
        />
    </a-layout-header>
</template>

<script>
import { ref, reactive, computed, onMounted } from "vue";
import { useStore } from "vuex";
import {
    MenuOutlined,
    DownOutlined,
    ScheduleOutlined,
    TeamOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { loadLocaleMessages } from "../i18n";
import { HeaderRightIcons } from "./style";
import common from "../../common/composable/common";
import MenuMode from "./MenuMode.vue";
import AffixButton from "./AffixButton.vue";
import DentistAttendanceModal from "../components/attendance/DentistAttendanceModal.vue";
import AvailableDoctorsModal from "../components/receptionist/AvailableDoctorsModal.vue";
import Notifications from "../../main/components/notification/index.vue";

export default {
    components: {
        MenuOutlined,
        DownOutlined,
        ScheduleOutlined,
        TeamOutlined,
        HeaderRightIcons,
        MenuMode,
        AffixButton,
        DentistAttendanceModal,
        AvailableDoctorsModal,
        Notifications,
    },
    setup(props, { emit }) {
        const {
            user,
            clinicLocations,
            permsArray,
            menuCollapsed,
            selectedClinicId,
        } = common();
        const store = useStore();
        const selectedLang = ref(store.state.auth.lang);
        const { locale, t } = useI18n();
        const doctorsDrawerVisible = ref(false);

        // const selectedClinicId = ref(store.getters["clinic/currentClinicId"]); // Removed local ref

        const currentClinicName = computed(() => {
            if (!selectedClinicId.value) return null;
            if (selectedClinicId.value === "all") return "All Clinics";
            const clinic = clinicLocations.value.find(
                (c) => c.xid === selectedClinicId.value,
            );
            return clinic ? clinic.name : null;
        });

        console.log("clinic locations:", clinicLocations.value);

        const validateAndSetClinic = () => {
            const storedClinicId = selectedClinicId.value;
            const canViewAll =
                user.value.is_superadmin ||
                permsArray.value.includes("clinic_locations_view_all") ||
                permsArray.value.includes("admin");

            // Check if stored clinic exists in available clinicLocations
            // 'all' is only valid if user has permission
            const clinicExists =
                (storedClinicId === "all" && canViewAll) ||
                (storedClinicId &&
                    clinicLocations.value.some(
                        (c) => c.xid === storedClinicId,
                    ));

            if (!clinicExists && clinicLocations.value.length > 0) {
                // If stored clinic doesn't exist or no clinic selected, select the first one
                selectedClinicId.value = clinicLocations.value[0].xid;
                store.dispatch("clinic/selectClinic", selectedClinicId.value);
            } else if (!storedClinicId && clinicLocations.value.length > 0) {
                // If no clinic selected but clinicLocations available, select first one
                selectedClinicId.value = clinicLocations.value[0].xid;
                store.dispatch("clinic/selectClinic", selectedClinicId.value);
            }
        };

        const onClinicChange = (clinicId) => {
            store.dispatch("clinic/selectClinic", clinicId);
            // Reload current page to refresh data with new clinic context
            window.location.reload();
        };

        onMounted(() => {
            validateAndSetClinic();
        });

        const langSelected = async (lang) => {
            store.commit("auth/updateLang", lang);
            await loadLocaleMessages(i18n, lang);

            selectedLang.value = lang;
            locale.value = lang;
        };

        const showHideMenu = () => {
            store.commit("auth/updateMenuCollapsed", !menuCollapsed.value);
        };

        const logout = () => {
            store.dispatch("auth/logout");
        };

        const showDoctorsDrawer = () => {
            doctorsDrawerVisible.value = true;
        };

        const handleBookAppointment = (doctor) => {
            console.log("Booking appointment with:", doctor);
            // TODO: Navigate to appointment booking or open booking modal
        };

        return {
            permsArray,
            logout,
            showHideMenu,
            langSelected,
            selectedLang,
            langs: computed(() => store.state.auth.allLangs),
            user,
            doctorsDrawerVisible,
            showDoctorsDrawer,
            handleBookAppointment,
            // Clinic management
            clinicLocations,
            selectedClinicId,
            currentClinicName,
            onClinicChange,
        };
    },
};
</script>

<style lang="less">
.trigger {
    font-size: 18px;
    line-height: 64px;
    padding-top: 4px;
    cursor: pointer;
    transition: color 0.3s;
}

.trigger:hover {
    color: #1890ff;
}
</style>
