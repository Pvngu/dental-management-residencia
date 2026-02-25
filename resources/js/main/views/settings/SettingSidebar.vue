<template>
    <div class="setting-sidebar">
        <perfect-scrollbar
            :options="{
                wheelSpeed: 1,
                swipeEasing: true,
                suppressScrollX: true,
            }"
        >
            <a-menu
                v-model:selectedKeys="selectedKeys"
                v-model:openKeys="openKeys"
                mode="inline"
            >
                <a-menu-item
                    key="company"
                    v-if="
                        permsArray.includes('companies_edit') ||
                        permsArray.includes('admin')
                    "
                    @click="
                        isModal
                            ? handleMenuClick('company')
                            : $router.push({
                                  name: 'admin.settings.company.index',
                              })
                    "
                >
                    <template #icon>
                        <LaptopOutlined />
                    </template>
                    {{ $t("menu.company") }}
                </a-menu-item>
                <a-menu-item
                    key="profile"
                    @click="
                        isModal
                            ? handleMenuClick('profile')
                            : $router.push({
                                  name: 'admin.settings.profile.index',
                              })
                    "
                >
                    <template #icon>
                        <UserOutlined />
                    </template>
                    {{ $t("menu.profile") }}
                </a-menu-item>
                <a-menu-item
                    key="preferences"
                    @click="
                        isModal
                            ? handleMenuClick('preferences')
                            : $router.push({
                                  name: 'admin.settings.preferences.index',
                              })
                    "
                >
                    <template #icon>
                        <SettingOutlined />
                    </template>
                    {{ $t("menu.preferences") }}
                </a-menu-item>
                <a-sub-menu
                    key="clinic_schedules"
                    v-if="
                        permsArray.includes('clinic_schedules_view') ||
                        permsArray.includes('admin')
                    "
                >
                    <template #title>
                        <span>
                            <ScheduleOutlined />
                            <span>{{ $t("menu.clinic_management") }}</span>
                        </span>
                    </template>
                    <a-menu-item
                        @click="
                            isModal
                                ? handleMenuClick('clinic_schedules')
                                : $router.push({
                                      name: 'admin.clinic_schedules.index',
                                  })
                        "
                        key="clinic_schedules"
                    >
                        <ScheduleOutlined />
                        <span>{{ $t("menu.clinic_schedules") }}</span>
                    </a-menu-item>
                    <a-menu-item
                        key="treatment_types"
                        v-if="
                            permsArray.includes('treatment_types_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('treatment_types')
                                : $router.push({
                                      name: 'admin.treatment_types.index',
                                  })
                        "
                    >
                        <AppstoreAddOutlined />
                        <span>{{ $t("menu.treatment_types") }}</span>
                    </a-menu-item>
                    <a-menu-item
                        key="rooms"
                        v-if="
                            permsArray.includes('rooms_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('rooms')
                                : $router.push({ name: 'admin.rooms.index' })
                        "
                    >
                        <ApartmentOutlined />
                        <span>{{ $t("menu.rooms") }}</span>
                    </a-menu-item>
                    <a-menu-item
                        key="clinic_locations"
                        v-if="
                            permsArray.includes('clinic_locations_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('clinic_locations')
                                : $router.push({
                                      name: 'admin.clinic_locations.index',
                                  })
                        "
                    >
                        <ShopOutlined />
                        <span>{{ $t("menu.clinic_locations") }}</span>
                    </a-menu-item>
                    <a-menu-item
                        key="insurance_providers"
                        v-if="
                            permsArray.includes('insurance_providers_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('insurance_providers')
                                : $router.push({
                                      name: 'admin.settings.insurance_providers.index',
                                  })
                        "
                    >
                        <SafetyOutlined />
                        <span>{{ $t("menu.insurance_providers") }}</span>
                    </a-menu-item>
                </a-sub-menu>
                <a-sub-menu
                    key="system_catalog"
                    v-if="
                        permsArray.includes('patient_file_types_view') ||
                        permsArray.includes('admin')
                    "
                >
                    <template #title>
                        <span>
                            <DatabaseOutlined />
                            <span>{{ $t("menu.system_catalog") }}</span>
                        </span>
                    </template>
                    <a-menu-item
                        key="patient_file_types"
                        v-if="
                            permsArray.includes('patient_file_types_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('patient_file_types')
                                : $router.push({
                                      name: 'admin.settings.patient_file_types.index',
                                  })
                        "
                    >
                        <TagsOutlined />
                        <span>{{ $t("menu.patient_file_types") }}</span>
                    </a-menu-item>
                    <a-menu-item
                        key="item_categories"
                        v-if="
                            permsArray.includes('item_categories_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('item_categories')
                                : $router.push({
                                      name: 'admin.settings.item_categories.index',
                                  })
                        "
                    >
                        <AppstoreOutlined />
                        <span>{{ $t("menu.item_categories") }}</span>
                    </a-menu-item>
                    <a-menu-item
                        key="item_brands"
                        v-if="
                            permsArray.includes('item_brands_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('item_brands')
                                : $router.push({
                                      name: 'admin.settings.item_brands.index',
                                  })
                        "
                    >
                        <StarOutlined />
                        <span>{{ $t("menu.item_brands") }}</span>
                    </a-menu-item>
                    <a-menu-item
                        key="item_manufactures"
                        v-if="
                            permsArray.includes('item_manufactures_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            isModal
                                ? handleMenuClick('item_manufactures')
                                : $router.push({
                                      name: 'admin.settings.item_manufactures.index',
                                  })
                        "
                    >
                        <BuildOutlined />
                        <span>{{ $t("menu.item_manufactures") }}</span>
                    </a-menu-item>
                </a-sub-menu>
                <a-menu-item
                    key="roles"
                    v-if="
                        permsArray.includes('roles_view') ||
                        permsArray.includes('admin')
                    "
                    @click="
                        isModal
                            ? handleMenuClick('roles')
                            : $router.push({
                                  name: 'admin.settings.roles.index',
                              })
                    "
                >
                    <template #icon>
                        <SolutionOutlined />
                    </template>
                    {{ $t("menu.roles") }}
                </a-menu-item>
                <a-menu-item
                    key="currencies"
                    v-if="
                        permsArray.includes('currencies_view') ||
                        permsArray.includes('admin')
                    "
                    @click="
                        isModal
                            ? handleMenuClick('currencies')
                            : $router.push({
                                  name: 'admin.settings.currencies.index',
                              })
                    "
                >
                    <template #icon>
                        <DollarOutlined />
                    </template>
                    {{ $t("menu.currencies") }}
                </a-menu-item>
                <a-menu-item
                    key="email_templates"
                    v-if="
                        permsArray.includes('email_templates_view') ||
                        permsArray.includes('admin')
                    "
                    @click="
                        isModal
                            ? handleMenuClick('email_templates')
                            : $router.push({
                                  name: 'admin.settings.email_templates.index',
                              }) // Note: Route might not exist, but pattern is followed
                    "
                >
                    <template #icon>
                        <MailOutlined />
                    </template>
                    {{ $t("menu.email_templates") }}
                </a-menu-item>
                <a-menu-item
                    key="email_settings"
                    v-if="
                        permsArray.includes('email_edit') ||
                        permsArray.includes('admin')
                    "
                    @click="
                        isModal
                            ? handleMenuClick('email_settings')
                            : $router.push({
                                  name: 'admin.settings.email.index',
                              })
                    "
                >
                    <template #icon>
                        <MailOutlined />
                    </template>
                    {{ $t("menu.email_settings") }}
                </a-menu-item>
                <a-menu-item
                    key="landing_page"
                    v-if="
                        appSetting.enable_landing_page &&
                        (permsArray.includes('companies_edit') ||
                            permsArray.includes('admin'))
                    "
                    @click="
                        isModal
                            ? handleMenuClick('landing_page')
                            : $router.push({
                                  name: 'admin.settings.landing_page.index',
                              })
                    "
                >
                    <template #icon>
                        <GlobalOutlined />
                    </template>
                    {{ $t("menu.landing_page_settings") }}
                </a-menu-item>
            </a-menu>
        </perfect-scrollbar>
    </div>
</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import {
    LaptopOutlined,
    UserOutlined,
    SettingOutlined,
    ShopOutlined,
    SolutionOutlined,
    ScheduleOutlined,
    DollarOutlined,
    AccountBookOutlined,
    AppstoreAddOutlined,
    ApartmentOutlined,
    FolderOpenOutlined,
    MailOutlined,
    HistoryOutlined,
    FormOutlined,
    DatabaseOutlined,
    GlobalOutlined,
    SafetyOutlined,
    TagsOutlined,
    AppstoreOutlined,
    StarOutlined,
    BuildOutlined,
} from "@ant-design/icons-vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import common from "../../../common/composable/common";

export default defineComponent({
    props: {
        isModal: {
            type: Boolean,
            default: false,
        },
        selectedKey: {
            type: String,
            default: "",
        },
    },
    emits: ["menu-click"],
    components: {
        LaptopOutlined,
        UserOutlined,
        SettingOutlined,
        ShopOutlined,
        SolutionOutlined,
        ScheduleOutlined,
        DollarOutlined,
        AccountBookOutlined,
        AppstoreAddOutlined,
        ApartmentOutlined,
        FolderOpenOutlined,
        MailOutlined,
        HistoryOutlined,
        FormOutlined,
        DatabaseOutlined,
        SafetyOutlined,
        GlobalOutlined,
        TagsOutlined,
        AppstoreOutlined,
        StarOutlined,
        BuildOutlined,
    },
    setup(props, { emit }) {
        const { permsArray, appSetting } = common();
        const route = useRoute();
        const selectedKeys = ref([]);
        const openKeys = ref([]);

        onMounted(() => {
            if (props.isModal) {
                selectedKeys.value = [props.selectedKey];
            } else {
                const menuKey =
                    typeof route.meta.menuKey == "function"
                        ? route.meta.menuKey(route)
                        : route.meta.menuKey;

                selectedKeys.value = [menuKey.replace("-", "_")];
            }

            const menuOpenKeys = localStorage.getItem(
                "settings_sidebar_open_keys",
            );
            if (menuOpenKeys) {
                openKeys.value = JSON.parse(menuOpenKeys);
            }
        });

        watch(route, (newVal, oldVal) => {
            if (!props.isModal) {
                const menuKey =
                    typeof newVal.meta.menuKey == "function"
                        ? newVal.meta.menuKey(newVal)
                        : newVal.meta.menuKey;

                selectedKeys.value = [menuKey.replace("-", "_")];
            }
        });

        watch(
            () => props.selectedKey,
            (newVal) => {
                if (props.isModal) {
                    selectedKeys.value = [newVal];
                }
            },
        );

        watch(openKeys, (newVal, oldVal) => {
            localStorage.setItem(
                "settings_sidebar_open_keys",
                JSON.stringify(newVal),
            );
        });

        const handleMenuClick = (key) => {
            if (props.isModal) {
                emit("menu-click", key);
            }
        };

        return {
            permsArray,
            appSetting,
            selectedKeys,
            openKeys,
            handleMenuClick,
        };
    },
});
</script>

<style lang="less">
.setting-sidebar .ps {
    height: calc(100vh - 145px);
}
</style>
