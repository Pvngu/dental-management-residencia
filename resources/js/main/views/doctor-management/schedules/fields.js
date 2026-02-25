import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "doctor-schedules?fields=id,xid,doctor_id,x_doctor_id,per_patient_time,doctor:user,schedule";
    const addEditUrl = "doctor-schedules";
    const { t } = useI18n();
    const hashableColumns = ["doctor_id"];
    const doctors = ref([]);

    const initData = {
        doctor_id: undefined,
        per_patient_time: "",
        schedule: [],
    };

    const columns = [
        {
            title: t("doctor_schedules.doctor"),
            dataIndex: "doctor",
        },
        {
            title: t("doctor_schedules.per_patient_time"),
            dataIndex: "per_patient_time",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const scheduleColumns = [
        {
            title: t("doctor_schedules.available_on"),
            dataIndex: "day",
            width: "20%",
        },
        {
            title: t("doctor_schedules.available_from"),
            dataIndex: "from",
            width: "30%",
        },
        {
            title: t("doctor_schedules.available_to"),
            dataIndex: "to",
            width: "30%",
        },
        {
            title: t("common.status"),
            dataIndex: "status",
            width: "20%",
        },
    ];

    const filterableColumns = [
        {
            key: "doctor_id",
            value: t("doctor_schedules.doctor"),
        },
        {
            key: "per_patient_time",
            value: t("doctor_schedules.per_patient_time"),
        },
    ];

    const getPrefetchData = () => {
        const doctorsPromise = axiosAdmin.get("doctors?fields=id,xid,name&limit=10000");
        return Promise.all([doctorsPromise]).then(([doctorsResponse]) => {
            doctors.value = doctorsResponse.data;
        });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        scheduleColumns,
        filterableColumns,
        hashableColumns,
        doctors,
        getPrefetchData,
    };
};

export default fields;
