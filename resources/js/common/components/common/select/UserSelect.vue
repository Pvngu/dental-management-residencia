<template>
    <a-select
        v-model:value="selectOption"
        :placeholder="$t('common.select_default_text', [$t('user.user')])"
        :allowClear="true"
        style="width: 100%"
        :dropdown-match-select-width="false"
        optionFilterProp="title"
        show-search
        @change="onChange"
        @search="onSearch"
        :filterOption="false"
        :disabled="disabled"
        :mode="mode"
        :loading="loading"
        :notFoundContent="loading ? undefined : null"
        :getPopupContainer="(triggerNode) => triggerNode.parentNode"
    >
        <a-select-opt-group
            v-for="(users, role) in allUsers"
            :key="role"
            :label="role"
        >
            <a-select-option
                v-for="user in users"
                :key="user.xid"
                :title="`${user.last_name} ${user.name} ${user.phone} ${user.email}`"
                :value="user.xid"
                :disabled="
                    (disableDisabledUsers && user.status === 'disabled') ||
                    user.xid === currentUserId
                "
                :class="{
                    warningSelect:
                        showDisabledUserWarning && user.status === 'disabled',
                }"
            >
                <a-row :gutter="12">
                    <a-col flex="20px">
                        <a-avatar :src="user.profile_image_url" :size="20" />
                    </a-col>
                    <a-col flex="1" class="ellipsis" style="width: 100%">
                        <div>{{ user.last_name }} {{ user.name }}</div>
                        <small v-if="user.phone && user.phone">
                            {{ user.phone }}
                        </small>
                    </a-col>
                </a-row>
            </a-select-option>
        </a-select-opt-group>
    </a-select>
</template>

<script>
import { defineComponent, onMounted, ref, watch } from "vue";

export default defineComponent({
    props: {
        value: {
            default: null,
        },
        disabled: {
            default: false,
        },
        showDisabledUserWarning: {
            default: false,
        },
        disableDisabledUsers: {
            default: false,
        },
        currentUserId: {
            default: undefined,
        },
        data: {
            default: null,
        },
        mode: {
            default: null,
        },
        roles: {
            default: "",
        },
        showPhone: {
            default: false,
        },
        userType: {
            default: "",
        },
    },
    setup(props, { emit }) {
        const usersUrl = "all-users";
        const allUsers = ref({});
        const selectOption = ref(null);
        const loading = ref(false);
        let searchTimeout = null;

        const fetchUsers = (searchString = "") => {
            loading.value = true;

            // Build query params
            let params = [];
            if (props.roles) params.push(`roles=${props.roles}`);
            if (props.userType) params.push(`log_type=${props.userType}`);
            if (searchString)
                params.push(`searchString=${encodeURIComponent(searchString)}`);

            const url =
                usersUrl + (params.length > 0 ? "?" + params.join("&") : "");

            axiosAdmin
                .get(url)
                .then((res) => {
                    allUsers.value = res.data.users;
                    loading.value = false;
                })
                .catch(() => {
                    loading.value = false;
                });
        };

        const onSearch = (searchValue) => {
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Debounce search by 300ms
            searchTimeout = setTimeout(() => {
                fetchUsers(searchValue);
            }, 300);
        };

        const onChange = (id) => {
            emit("onChange", id);
        };

        onMounted(() => {
            if (props.value) {
                selectOption.value = props.value;
            }

            if (props.data) {
                allUsers.value = props.data;
            } else {
                fetchUsers();
            }
        });

        // Reset select option when value is null
        watch(
            () => props.value,
            (newValue) => {
                if (newValue === null) {
                    selectOption.value = newValue;
                }
            },
        );

        watch(
            () => props.data,
            (newValue) => {
                if (newValue) {
                    allUsers.value = newValue;
                }
            },
        );

        watch(
            () => props.value,
            (newValue) => {
                if (newValue) {
                    selectOption.value = newValue;
                }
            },
        );

        return {
            allUsers,
            onChange,
            onSearch,
            selectOption,
            loading,
        };
    },
});
</script>

<style>
.warningSelect {
    background-color: #fffbe6;
    color: #faad14 !important;
}
.ellipsis {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
