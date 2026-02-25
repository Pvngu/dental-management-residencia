import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "appointments?fields=id,xid,patient_id,doctor_id,appointment_date,duration,treatment_details,status,patient,patient:user,doctor,doctor:user,doctor:doctorDepartment,room_id,treatment_type_id,room,treatmentType,created_at";
    const addEditUrl = "appointments";
    const { t } = useI18n();
    const hashableColumns = ["patient_id", "doctor_id", "room_id", "treatment_type_id"];
    
    // Add refs for rooms and treatment types
    const rooms = ref([]);
    const treatmentTypes = ref([]);

    const initData = {
        patient_id: undefined,
        doctor_id: undefined,
        appointment_date: "",
        duration: "",
        treatment_details: "",
        status: "pending",
        room_id: undefined,
        treatment_type_id: undefined,
    };

    const statusOptions = [
        { label: t("appointments.pending"), value: "pending" },
        { label: t("appointments.confirmed"), value: "confirmed" },
        { label: t("appointments.cancelled"), value: "cancelled" },
        { label: t("appointments.completed"), value: "completed" },
        { label: t("appointments.delayed"), value: "delayed" },
    ];

    const columns = [
        {
            title: t("common.time"),
            dataIndex: "time",
            align: "center",
        },
        {
            title: t("common.date"),
            dataIndex: "date",
            sorter: true,
        },
        {
            title: t("common.patient"),
            dataIndex: "patient",
        },
        {
            title: t("common.doctor"),
            dataIndex: "doctor",
        },
        {
            title: t("common.room"),
            dataIndex: "room",
        },
        {
            title: t("common.treatment_type"),
            dataIndex: "treatment_type",
        },
        {
            title: t("common.status"),
            dataIndex: "status",
        },
        {
            title: t("common.created_at"),
            dataIndex: "created_at",
            sorter: true,
        },
        {
            title: t("common.action"),
            dataIndex: "action",
            align: "center",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name"),
        },
        {
            key: "phone_number",
            value: t("common.phone_number"),
        },
        {
            key: "email",
            value: t("common.email"),
        },
        {
            key: "SSN",
            value: t("common.SSN"),
        },
    ];

    // Function to fetch rooms and treatment types
    const getPrefetchData = () => {
        const roomsPromise = axiosAdmin.get("rooms?fields=id,xid,name");
        const treatmentTypesPromise = axiosAdmin.get("treatment_types?fields=id,xid,name,duration_minutes");
        
        return Promise.all([roomsPromise, treatmentTypesPromise])
            .then(([roomsResponse, treatmentTypesResponse]) => {
                rooms.value = roomsResponse.data;
                treatmentTypes.value = treatmentTypesResponse.data;
            });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        statusOptions,
        rooms,
        treatmentTypes,
        getPrefetchData,
    };
};

export default fields;
