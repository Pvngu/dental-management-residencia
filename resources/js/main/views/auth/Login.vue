<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-white py-12 px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="flex justify-center">
            <h1 class="text-2xl font-bold text-gray-800">Sistema Dental</h1>
        </div>

        <!-- Form Container -->
        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow mt-6">
            <!-- Display errors or success messages -->
            <div class="mb-4">
                <a-alert
                    v-if="onRequestSend.error != ''"
                    :message="onRequestSend.error"
                    type="error"
                    show-icon
                />
                <a-alert
                    v-if="onRequestSend.success"
                    :message="$t('messages.login_success')"
                    type="success"
                    show-icon
                />
            </div>
            
            <form class="space-y-6" @submit.prevent="onSubmit">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        {{ $t('user.email_phone') }}
                    </label>
                    <div class="mt-2">
                        <input
                            id="email"
                            v-model="credentials.email"
                            type="text"
                            autocomplete="email"
                            required
                            class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                        />
                        <p v-if="rules.email" class="mt-1 text-sm text-red-600">{{ rules.email.message }}</p>
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        {{ $t('user.password') }}
                    </label>
                    <div class="mt-2">
                        <a-input-password
                            id="password"
                            v-model:value="credentials.password"
                            autocomplete="current-password"
                            :placeholder="$t('common.placeholder_default_text', [$t('user.password')])"
                            @pressEnter="onSubmit"
                            class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                        />
                        <p v-if="rules.password" class="mt-1 text-sm text-red-600">{{ rules.password.message }}</p>
                    </div>
                </div>

                <!-- Remember Me and Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            {{ $t('auth.remember_me') }}
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="/forgot-password" class="font-medium text-indigo-600 hover:text-indigo-500">
                            {{ $t('auth.forgot_password') }}
                        </a>
                    </div>
                </div>

                <!-- Sign In Button -->
                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="flex w-full justify-center rounded-md border border-transparent bg-blue-600 py-2.5 px-4 text-sm font-medium text-white! shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 login-btn hover:cursor-pointer"
                    >
                        <span v-if="loading">{{ $t('auth.signing_in') }}</span>
                        <span v-else>{{ $t("menu.login") }}</span>
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-6">
                <div class="relative">
                    <!-- <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300" />
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="bg-white px-2 text-gray-500">{{ $t('auth.or_continue_with') }}</span>
                    </div> -->
                    <a-divider>{{ $t('auth.or_continue_with') }}</a-divider>
                </div>

                <!-- Social Logins -->
                <div class="mt-6 grid grid-cols-1 gap-3">
                    <div>
                        <a
                            href="/auth/google"
                            class="inline-flex w-full justify-center items-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-500 shadow-sm hover:bg-gray-50"
                        >
                            <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                            </svg>
                            <span class="ml-2">{{ $t('auth.google') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trial offer -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <span>{{ $t('auth.not_a_member') }} </span>
            <a href="/signup/trial" class="font-medium text-indigo-600 hover:text-indigo-500">
                {{ $t('auth.start_free_trial') }}
            </a>
        </div>
    </div>
</template>

<script>
import { defineComponent, reactive, ref } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";

export default defineComponent({
    setup() {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { globalSetting, appType } = common();
        const loginBackground = globalSetting.value.login_image_url;
        const store = useStore();
        const router = useRouter();
        const credentials = reactive({
            email: null,
            password: null,
        });
        const onRequestSend = ref({
            error: "",
            success: "",
        });

        const onSubmit = () => {
            onRequestSend.value = {
                error: "",
                success: false,
            };

            addEditRequestAdmin({
                url: "auth/login",
                data: credentials,
                success: (response) => {
                    if (response && response.status == "success") {
                        const user = response.user;
                        store.commit("auth/updateUser", user);
                        store.commit("auth/updateToken", response.token);
                        store.commit("auth/updateExpires", response.expires_in);
                        store.commit(
                            "auth/updateVisibleSubscriptionModules",
                            response.visible_subscription_modules
                        );

                        if (appType == "non-saas") {
                            // Fetch app data from API
                            store.dispatch("auth/updateApp");
                            router.push({
                                name: "admin.dashboard.index",
                                params: { success: true },
                            });
                        } else {
                            if (user.is_superadmin && user.user_type == "super_admins") {
                                // Fetch app data from API
                                store.dispatch("auth/updateApp");
                                router.push({
                                    name: "superadmin.dashboard.index",
                                    params: { success: true },
                                });
                            } else {
                                // Fetch app data from API
                                store.dispatch("auth/updateApp");
                                router.push({
                                    name: "admin.dashboard.index",
                                    params: { success: true },
                                });
                            }
                        }
                    } else {
                        onRequestSend.value = {
                            error: response.message ? response.message : "",
                            success: false,
                        };
                    }
                },
                error: (err) => {},
            });
        };

        return {
            loading,
            rules,
            credentials,
            onSubmit,
            onRequestSend,
            globalSetting,
            loginBackground,

            innerWidth: window.innerWidth,
        };
    },
});
</script>