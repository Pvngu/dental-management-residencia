<template>
    <div class="settings-layout">
        <a-row>
            <a-col
                :xs="24"
                :sm="24"
                :md="24"
                :lg="4"
                :xl="4"
                class="bg-setting-sidebar"
            >
                <SettingSidebar />
            </a-col>
            <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
                <router-view />
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import SettingSidebar from "./SettingSidebar.vue";

export default defineComponent({
    components: {
        SettingSidebar,
    },
});
</script>
