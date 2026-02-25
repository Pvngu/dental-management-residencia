import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "clinic_schedules?fields=id,xid,day_of_week,start_time,end_time";
    const addEditUrl = "clinic_schedules/update-schedules";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        day_of_week: "",
        start_time: "",
        end_time: "",
    };

    const columns = [
        {
            title: t("clinic_schedules.day_of_week"),
            dataIndex: "day_of_week",
        },
        {
            title: t("clinic_schedules.start_time"),
            dataIndex: "start_time",
        },
        {
            title: t("clinic_schedules.end_time"),
            dataIndex: "end_time",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "day_of_week",
            value: t("clinic_schedules.day_of_week"),
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
    };
};

export default fields;
