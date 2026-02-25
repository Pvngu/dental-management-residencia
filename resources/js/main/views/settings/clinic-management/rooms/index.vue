<template>
    <div class="rooms-container">
        <a-tabs v-model:activeKey="activeTab" type="card">
            <a-tab-pane key="rooms" :tab="$t('menu.rooms')">
                <RoomsTab />
            </a-tab-pane>
            <a-tab-pane key="room_types" :tab="$t('menu.room_types')">
                <RoomTypesTab />
            </a-tab-pane>
        </a-tabs>
    </div>
</template>

<script>
import { ref } from "vue";
import RoomsTab from "./RoomsTab.vue";
import RoomTypesTab from "./RoomTypesTab.vue";

export default {
    components: {
        RoomsTab,
        RoomTypesTab,
    },
    setup() {
        const activeTab = ref("rooms");

        return {
            activeTab,
        };
    },
};
</script>
