import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "doctor-breaks?fields=id,xid,doctor_id,break_from,break_to,every_day,date,doctor:user";
    const addEditUrl = "doctor-breaks";
    const doctorsWithBreaksUrl = "doctor-breaks/doctors-with-breaks";
    const hashableColumns = ["doctor_id"];
    const { t } = useI18n();
    const doctors = ref([]);

    const initData = {
        doctor_id: [],
        break_from: "",
        break_to: "",
        every_day: 1,
        date: "",
    };

    const columns = [
        {
            title: t("doctor_breaks.doctor"),
            dataIndex: "doctor",
        },
        {
            title: t("doctor_breaks.break_from"),
            dataIndex: "break_from",
        },
        {
            title: t("doctor_breaks.break_to"),
            dataIndex: "break_to",
        },
        {
            title: t("common.date"),
            dataIndex: "date",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const getPrefetchData = () => {
        const doctorPromise = axiosAdmin.get(doctorsWithBreaksUrl);
        
        return Promise.all([doctorPromise]).then(([doctorResponse]) => {
            doctors.value = doctorResponse;
        });
    };

    return {
        url,
        addEditUrl,
        doctorsWithBreaksUrl,
        initData,
        columns,
        hashableColumns,
        doctors,
        getPrefetchData,
    }
}

export default fields;
