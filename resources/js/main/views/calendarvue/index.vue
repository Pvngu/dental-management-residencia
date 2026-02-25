<template>
    <CalendarReservations />
</template>

<script>
import { defineComponent } from "vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import { CalendarReservations } from "../../../common/components/common/calendarvue";

export default defineComponent({
    name: "CalendarView",
    components: {
        AdminPageHeader,
        CalendarReservations,
    },
    setup() {
        return {};
    },
});
</script>
