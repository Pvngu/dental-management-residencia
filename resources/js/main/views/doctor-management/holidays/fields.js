import { useI18n } from "vue-i18n";
import { ref } from "vue";
import { useStore } from "vuex";

const fields = () => {
    const url = "doctor-holidays?fields=id,xid,doctor_id,x_doctor_id,start_date,end_date,reason,holiday_type,status,doctor:user{id,xid,name,last_name,profile_image,profile_image_url}";
    const addEditUrl = "doctor-holidays";
    const doctorsWithHolidaysUrl = "doctor-holidays/doctors-with-holidays";
    const calendarEventsUrl = "doctor-holidays/calendar-events";
    const { t } = useI18n();
    const store = useStore();
    const doctors = ref([]);

    const initData = {
        doctor_id: [],
        dateRange: [],
        start_date: "",
        end_date: "",
        reason: "",
        holiday_type: "vacation",
        status: "pending",
    };

    const hashableColumns = ["doctor_id"];

    const columns = [
        {
            title: t("doctor_holidays.doctor"),
            dataIndex: "doctor",
        },
        {
            title: t("doctor_holidays.dates"),
            dataIndex: "dates",
        },
        {
            title: t("doctor_holidays.holiday_type"),
            dataIndex: "holiday_type",
        },
        {
            title: t("doctor_holidays.status"),
            dataIndex: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const holidayTypes = [
        { value: "vacation", label: t("doctor_holidays.vacation") },
        { value: "sick_leave", label: t("doctor_holidays.sick_leave") },
        { value: "personal", label: t("doctor_holidays.personal") },
        { value: "other", label: t("doctor_holidays.other") },
    ];

    const statusOptions = [
        { value: "pending", label: t("doctor_holidays.pending") },
        { value: "approved", label: t("doctor_holidays.approved") },
        { value: "rejected", label: t("doctor_holidays.rejected") },
    ];

    const getPrefetchData = () => {
        const doctorPromise = axiosAdmin.get(doctorsWithHolidaysUrl);
        
        return Promise.all([doctorPromise]).then(([doctorResponse]) => {
            doctors.value = doctorResponse;
        });
    };

    return {
        url,
        addEditUrl,
        doctorsWithHolidaysUrl,
        calendarEventsUrl,
        initData,
        columns,
        hashableColumns,
        holidayTypes,
        statusOptions,
        doctors,
        getPrefetchData,
    }
}

export default fields;
