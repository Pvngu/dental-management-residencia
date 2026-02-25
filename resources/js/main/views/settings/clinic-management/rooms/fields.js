import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "rooms?fields=id,xid,name,room_type_id,floor,capacity,status,notes,roomType,room_type_id,x_room_type_id";
    const addEditUrl = "rooms";
    const { t } = useI18n();
    const hashableColumns = ["room_type_id"];
    const roomTypes = ref([]);

    const initData = {
        name: "",
        room_type_id: undefined,
        floor: 1,
        capacity: 1,
        status: "Available",
        notes: "",
    };

    const columns = [
        {
            title: t("common.name"),
            dataIndex: "name",
        },
        {
            title: t("room.room_type"),
            dataIndex: "room_type_id",
        },
        {
            title: t("room.floor"),
            dataIndex: "floor",
        },
        {
            title: t("room.capacity"),
            dataIndex: "capacity",
        },
        {
            title: t("common.status"),
            dataIndex: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name"),
        },
        {
            key: "floor",
            value: t("room.floor"),
        },
    ];

    const getPrefetchData = () => {
        const roomTypesPromise = axiosAdmin.get("room-types/all");
        return Promise.all([roomTypesPromise]).then(([roomTypesResponse]) => {
            roomTypes.value = roomTypesResponse.data;
        });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        roomTypes,
        getPrefetchData,
    };
};

export default fields;
