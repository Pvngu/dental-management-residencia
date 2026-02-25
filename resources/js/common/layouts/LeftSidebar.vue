<template>
    <a-layout-sider
        :width="240"
        :style="{
            margin: '0 0 0 0',
            overflowY: 'auto',
            position: 'fixed',
            paddingTop: '8px',
            zIndex: 998,
            backgroundColor: '#f7fafd',
        }"
        :trigger="null"
        :collapsed="menuCollapsed"
        theme="light"
        class="sidebar-right-border"
    >
        <div v-if="menuCollapsed" class="logo">
            <img
                :style="{
                    height: '32px',
                }"
                :src="appSetting.small_light_logo_url"
            />
        </div>
        <div v-else>
            <!-- <img
                :style="{
                    height: '60px',
                    paddingLeft: appSetting.rtl ? '0px' : '30px',
                    paddingRight: appSetting.rtl ? '30px' : '0px',
                    paddingTop: '5px',
                    paddingBottom: '20px',
                    marginLeft: appSetting.rtl ? '0px' : '10px',
                    marginRight: appSetting.rtl ? '10px' : '0px',
                }"
                :src="appSetting.light_logo_url"
            /> -->
            <h1 class="text-xl font-bold mx-6">Sistema Clinico</h1>
            <CloseOutlined
                v-if="innerWidth <= 991"
                :style="{
                    marginLeft: appSetting.rtl ? '0px' : '45px',
                    marginRight: appSetting.rtl ? '45px' : '0px',
                    verticalAlign: 'super',
                    color: '#000000',
                }"
                @click="menuSelected"
            />
        </div>

        <div class="main-sidebar">
            <perfect-scrollbar
                :options="{
                    wheelSpeed: 1,
                    swipeEasing: true,
                    suppressScrollX: true,
                }"
            >
                <a-menu
                    theme="light"
                    :openKeys="openKeys"
                    v-model:selectedKeys="selectedKeys"
                    :mode="mode"
                    @openChange="onOpenChange"
                    :style="{
                        borderRight: 'none',
                        backgroundColor: '#f7fafd',
                    }"
                >
                    <a-menu-item
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.dashboard.index' });
                            }
                        "
                        key="dashboard"
                    >
                        <HomeOutlined />
                        <span>{{ $t("menu.dashboard") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        v-if="
                            permsArray.includes('calendar_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.calendar.index' });
                            }
                        "
                        key="calendar"
                    >
                        <CalendarOutlined />
                        <span>{{ $t("menu.calendar") }}</span>
                    </a-menu-item>

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('admin') ||
                            permsArray.includes('message_center_view')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.message_center.index',
                                });
                            }
                        "
                        key="message_center"
                    >
                        <MessageOutlined />
                        <span>{{ $t("menu.message_center") }}</span>
                    </a-menu-item> -->

                    <LeftSideBarMainHeading
                        v-if="
                            permsArray.includes('doctors_view') ||
                            permsArray.includes('patients_view') ||
                            permsArray.includes('appointments_view') ||
                            permsArray.includes('open_cases_view') ||
                            permsArray.includes('medicine_view') ||
                            permsArray.includes('rooms_view') ||
                            permsArray.includes('admin')
                        "
                        :title="$t('menu.clinic_management')"
                        :visible="menuCollapsed"
                    />

                    <a-menu-item
                        v-if="
                            permsArray.includes('doctors_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.doctors.index' });
                            }
                        "
                        key="doctors"
                    >
                        <Icon>
                            <template #component>
                                <UserDoctor />
                            </template>
                        </Icon>
                        <span>{{ $t("menu.doctors") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        v-if="
                            permsArray.includes('patients_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.patients.index' });
                            }
                        "
                        key="patients"
                    >
                        <UserOutlined />
                        <span>{{ $t("menu.patients") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        v-if="
                            permsArray.includes('appointments_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.appointments.table',
                                });
                            }
                        "
                        key="appointments"
                    >
                        <CalendarOutlined />
                        <span>{{ $t("menu.appointments") }}</span>
                    </a-menu-item>

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('open_cases_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.open_cases.index',
                                });
                            }
                        "
                        key="open_cases"
                    >
                        <AlertOutlined />
                        <span>{{ $t("menu.open_cases") }}</span>
                    </a-menu-item> -->

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('medicine_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.medicines.index',
                                });
                            }
                        "
                        key="medicines"
                    >
                        <MedicineBoxOutlined />
                        <span>{{ $t("menu.medicines") }}</span>
                    </a-menu-item> -->

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('rooms_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.room_layout.index',
                                });
                            }
                        "
                        key="room_layout"
                    >
                        <LayoutOutlined />
                        <span>{{ $t("menu.rooms") }}</span>
                    </a-menu-item> -->

                    <!-- <LeftSideBarMainHeading
                        v-if="
                            permsArray.includes('admin')
                        "
                        :title="$t('menu.resources_and_logistics')"
                        :visible="menuCollapsed"
                    /> -->

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('inventory_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.items.index' });
                            }
                        "
                        key="inventory"
                    >
                        <AppstoreOutlined />
                        <span>{{ $t("menu.inventory") }}</span>
                    </a-menu-item> -->

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('faxes_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.fax_center.index',
                                });
                            }
                        "
                        key="fax_center"
                    >
                        <PrinterOutlined />
                        <span>{{ $t("menu.fax_center") }}</span>
                    </a-menu-item> -->

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('postal_receive_view') ||
                            permsArray.includes('postal_dispatch_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.postals.index',
                                });
                            }
                        "
                        key="postals"
                    >
                        <MailOutlined />
                        <span>{{ $t("menu.mail_management") }}</span>
                    </a-menu-item> -->

                    <!-- <LeftSideBarMainHeading
                        :title="$t('menu.administration_and_finance')"
                        :visible="menuCollapsed"
                    /> -->

                    <!-- <a-sub-menu
                        key="sales_menu"
                        v-if="
                            permsArray.includes('sales_view') ||
                            permsArray.includes('admin')
                        "
                    >
                        <template #title>
                            <span>
                                <ShoppingOutlined />
                                <span>{{ $t("menu.sales") }}</span>
                            </span>
                        </template>
                        <a-menu-item
                            v-if="
                                permsArray.includes('sales_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.sales.index' });
                                }
                            "
                            key="sales"
                        >
                            <ShoppingCartOutlined />
                            <span>{{ $t("menu.sales") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('sales_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.sales.dashboard',
                                    });
                                }
                            "
                            key="sales_dashboard"
                        >
                            <BarChartOutlined />
                            <span>{{ $t("menu.sales_dashboard") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('sales_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.sales.pos' });
                                }
                            "
                            key="pos"
                        >
                            <ShopOutlined />
                            <span>{{ $t("menu.point_of_sale") }}</span>
                        </a-menu-item>
                    </a-sub-menu> -->

                    <!-- <a-sub-menu
                        key="expenses"
                        v-if="
                            permsArray.includes('expenses_view') ||
                            permsArray.includes('admin')
                        "
                    >
                        <template #title>
                            <WalletOutlined />
                            <span>{{ $t("menu.expense_management") }}</span>
                        </template>
                        <a-menu-item
                            v-if="
                                permsArray.includes('expenses_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.expenses.index',
                                    });
                                }
                            "
                            key="expenses"
                        >
                            <WalletOutlined />
                            <span>{{ $t("menu.expenses") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes(
                                    'expense_categories_view',
                                ) || permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.expense_categories.index',
                                    });
                                }
                            "
                            key="expense_categories"
                        >
                            <TagsOutlined />
                            <span>{{ $t("menu.expense_categories") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('invoices_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.invoices.index',
                                    });
                                }
                            "
                            key="invoices"
                        >
                            <FileTextOutlined />
                            <span>{{ $t("menu.invoices") }}</span>
                        </a-menu-item>
                    </a-sub-menu> -->

                    <!-- <a-menu-item
                        v-if="appSetting.x_admin_id == user.xid"
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.subscription.current_plan',
                                });
                            }
                        "
                        key="subscription"
                    >
                        <DollarCircleOutlined />
                        <span>{{ $t("menu.subscription") }}</span>
                    </a-menu-item> -->

                    <LeftSideBarMainHeading
                        :title="permsArray.includes('admin') || permsArray.includes('users_view') ? $t('menu.staff_and_system') : $t('menu.system')"
                        :visible="menuCollapsed"
                    />

                    <a-menu-item
                        v-if="
                            permsArray.includes('users_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.users.index' });
                            }
                        "
                        key="users"
                    >
                        <SolutionOutlined />
                        <span>{{ $t("menu.staff_members") }}</span>
                    </a-menu-item>

                    <!-- <a-menu-item
                        v-if="
                            permsArray.includes('activity_log_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.activity_log.index',
                                });
                            }
                        "
                        key="activity_log"
                    >
                        <HistoryOutlined />
                        <span>{{ $t("menu.activity_log") }}</span>
                    </a-menu-item> -->

                    <component
                        v-for="(appModule, index) in appModules"
                        :key="index"
                        v-bind:is="appModule + 'Menu'"
                        @menuSelected="menuSelected"
                    />

                    <a-menu-item
                        @click="
                            () => {
                                menuSelected();
                                openSettingsModal();
                                // Restore selected key to current route to prevent highlighting 'settings'
                                const menuKey =
                                    typeof $route.meta.menuKey == 'function'
                                        ? $route.meta.menuKey($route)
                                        : $route.meta.menuKey;
                                selectedKeys = [menuKey.replace('-', '_')];
                            }
                        "
                        key="settings"
                    >
                        <SettingOutlined />
                        <span>{{ $t("menu.settings") }}</span>
                    </a-menu-item>

                    <a-menu-item @click="logout" key="logout">
                        <LogoutOutlined />
                        <span>{{ $t("menu.logout") }}</span>
                    </a-menu-item>
                </a-menu>
            </perfect-scrollbar>
        </div>
    </a-layout-sider>
</template>

<script>
import { defineComponent, ref, watch, onMounted, computed } from "vue";
import { Layout } from "ant-design-vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import Icon, {
    HomeOutlined,
    LogoutOutlined,
    UserOutlined,
    DesktopOutlined,
    SettingOutlined,
    CloseOutlined,
    ShoppingOutlined,
    ShoppingCartOutlined,
    AppstoreOutlined,
    ShopOutlined,
    BarChartOutlined,
    CalculatorOutlined,
    TeamOutlined,
    WalletOutlined,
    BankOutlined,
    RocketOutlined,
    LaptopOutlined,
    CarOutlined,
    DollarCircleOutlined,
    CopyrightCircleOutlined,
    IeOutlined,
    PhoneOutlined,
    FolderOpenOutlined,
    FileTextOutlined,
    SoundOutlined,
    ApartmentOutlined,
    ScheduleOutlined,
    SolutionOutlined,
    MailOutlined,
    FormOutlined,
    InsertRowBelowOutlined,
    CalendarOutlined,
    FileOutlined,
    ProfileOutlined,
    MedicineBoxOutlined,
    TagsOutlined,
    TrademarkOutlined,
    BuildOutlined,
    RestOutlined,
    GiftOutlined,
    TagOutlined,
    LayoutOutlined,
    HistoryOutlined,
    AlertOutlined,
    SafetyCertificateOutlined,
    PrinterOutlined,
    MessageOutlined,
} from "@ant-design/icons-vue";
import UserDoctor from "../components/icons/UserDoctor.vue";
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import common from "../../common/composable/common";
import useSettingsModal from "../../common/composable/useSettingsModal";
import LeftSideBarMainHeading from "../components/common/typography/LeftSideBarMainHeading.vue";
const { Sider } = Layout;

export default defineComponent({
    components: {
        Sider,
        PerfectScrollbar,
        Layout,
        LeftSideBarMainHeading,

        HomeOutlined,
        LogoutOutlined,
        UserOutlined,
        SettingOutlined,
        CloseOutlined,
        ShoppingOutlined,
        ShoppingCartOutlined,
        AppstoreOutlined,
        ShopOutlined,
        BarChartOutlined,
        CalculatorOutlined,
        TeamOutlined,
        WalletOutlined,
        BankOutlined,
        RocketOutlined,
        LaptopOutlined,
        CarOutlined,
        DollarCircleOutlined,
        CopyrightCircleOutlined,
        IeOutlined,
        FolderOpenOutlined,
        FileTextOutlined,
        SoundOutlined,
        PhoneOutlined,
        ApartmentOutlined,
        ScheduleOutlined,
        SolutionOutlined,
        MailOutlined,
        FormOutlined,
        InsertRowBelowOutlined,
        CalendarOutlined,
        Icon,
        UserDoctor,
        FileOutlined,
        ProfileOutlined,
        MedicineBoxOutlined,
        TagsOutlined,
        TrademarkOutlined,
        BuildOutlined,
        RestOutlined,
        DesktopOutlined,
        WalletOutlined,
        GiftOutlined,
        TagOutlined,
        LayoutOutlined,
        HistoryOutlined,
        AlertOutlined,
        SafetyCertificateOutlined,
        PrinterOutlined,
        MessageOutlined
    },
    setup(props, { emit }) {
        const { appSetting, user, permsArray, appModules, menuCollapsed } =
            common();
        const rootSubmenuKeys = [
            "dashboard",
            "users",
            "forms",
            "form_field_names",
            "settings",
            "subscription",
        ];
        const store = useStore();
        const route = useRoute();
        const { openModal } = useSettingsModal();

        const innerWidth = window.innerWidth;
        const openKeys = ref([]);
        const selectedKeys = ref([]);
        const mode = ref("inline");

        const openSettingsModal = () => {
            openModal();
        };

        onMounted(() => {
            var menuKey =
                typeof route.meta.menuKey == "function"
                    ? route.meta.menuKey(route)
                    : route.meta.menuKey;

            if (route.meta.menuParent == "settings") {
                menuKey = "settings";
            }

            if (route.meta.menuParent == "subscription") {
                menuKey = "subscription";
            }

            if (innerWidth <= 991) {
                openKeys.value = [];
            } else {
                openKeys.value = menuCollapsed.value
                    ? []
                    : [route.meta.menuParent];
            }

            selectedKeys.value = [menuKey.replace("-", "_")];
        });

        const logout = () => {
            store.dispatch("auth/logout");
        };

        const menuSelected = () => {
            if (innerWidth <= 991) {
                store.commit("auth/updateMenuCollapsed", true);
            }
        };

        const onOpenChange = (currentOpenKeys) => {
            const latestOpenKey = currentOpenKeys.find(
                (key) => openKeys.value.indexOf(key) === -1,
            );

            if (rootSubmenuKeys.indexOf(latestOpenKey) === -1) {
                openKeys.value = currentOpenKeys;
            } else {
                openKeys.value = latestOpenKey ? [latestOpenKey] : [];
            }
        };

        watch(route, (newVal, oldVal) => {
            const menuKey =
                typeof newVal.meta.menuKey == "function"
                    ? newVal.meta.menuKey(newVal)
                    : newVal.meta.menuKey;

            if (innerWidth <= 991) {
                openKeys.value = [];
            } else {
                openKeys.value = [newVal.meta.menuParent];
            }

            if (newVal.meta.menuParent == "settings") {
                selectedKeys.value = ["settings"];
            } else if (newVal.meta.menuParent == "subscription") {
                selectedKeys.value = ["subscription"];
            } else {
                selectedKeys.value = [menuKey.replace("-", "_")];
            }
        });

        watch(
            () => menuCollapsed.value,
            (newVal, oldVal) => {
                const menuKey =
                    typeof route.meta.menuKey == "function"
                        ? route.meta.menuKey(route)
                        : route.meta.menuKey;

                if (innerWidth <= 991 && menuCollapsed.value) {
                    openKeys.value = [];
                } else {
                    openKeys.value = menuCollapsed.value
                        ? []
                        : [route.meta.menuParent];
                }

                if (route.meta.menuParent == "settings") {
                    selectedKeys.value = ["settings"];
                } else if (route.meta.menuParent == "subscription") {
                    selectedKeys.value = ["subscription"];
                } else {
                    selectedKeys.value = [menuKey.replace("-", "_")];
                }
            },
        );

        return {
            mode,
            selectedKeys,
            openKeys,
            logout,
            innerWidth: window.innerWidth,
            openSettingsModal,
            onOpenChange,
            menuSelected,
            menuCollapsed,
            appSetting,
            user,
            permsArray,
            appModules,
        };
    },
});
</script>

<style lang="less">
.main-sidebar .ps {
    height: calc(100vh - 62px);
}

@media only screen and (max-width: 1150px) {
    .ant-layout-sider.ant-layout-sider-collapsed {
        left: -80px !important;
    }
}
</style>
