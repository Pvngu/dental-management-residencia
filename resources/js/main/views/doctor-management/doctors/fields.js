import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "doctors?fields=id,xid,doctor_department_id,x_doctor_department_id,specialties,designation,qualification,appointment_charge,practice_id,description,professional_id,professional_id_url,color,user{id,xid,user_type,name,last_name,email,profile_image,profile_image_url,phone,country_code,status,gender,date_of_birth,created_at,role_id,x_role_id},doctorSchedules{id,xid,doctor_id,x_doctor_id,per_patient_time},user:addresses,doctorSchedules:schedule,specialties";
    const addEditUrl = "doctors";
    const { t } = useI18n();
    const statuses = ref([
        { value: 'active', label: t('common.active') },
        { value: 'inactive', label: t('common.inactive') }
    ]);
    const hashableColumns = ['doctor_department_id']
    const departments = ref([]);
    const specialists = ref([]);

    const initData = {
        xid: undefined,
        qualification: "",
        specialties: "",
        specialist: "",
        designation: "",
        practice_id: "",
        appointment_charge: "",
        description: "",
        professional_id: undefined,
        professional_id_url: undefined,
        color: "#3B82F6",
        x_doctor_department_id: undefined,
        phone: "",
        country_code: "US",
        specialties: {
            id: undefined,
            name: "",
            description: ""
        },
        user: {
            xid: undefined,
            first_name: "",
            last_name: "",
            email: "",
            password: "",
            profile_image: undefined,
            profile_image_url: undefined,
            phone: "",
            country_code: "US",
            addresses: [],
            date_of_birth: "",
            status: "enabled",
            user_type: "doctors",
            role_id: undefined,
        },
        doctor_schedules: {
            xid: undefined,
            per_patient_time: undefined,
            schedule: []
        }
    };

    const getPrefetchData = () => {
        const departmentsPromise = axiosAdmin.get("doctor-departments/all");
        const specialistsPromise = axiosAdmin.get("doctor-specialty/all");
        return Promise.all([departmentsPromise, specialistsPromise]).then(([departmentsResponse, specialistsResponse]) => {
            departments.value = departmentsResponse.data;
            specialists.value = specialistsResponse.data;
        });
    }

    const columns = [
        {
            title: t("common.doctor"),
            dataIndex: "doctor",
        },
        {
            title: t("doctors.qualification"),
            dataIndex: "qualification",
        },
        {
            title: t("doctors.color"),
            dataIndex: "color",
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
            key: "doctor",
            value: t("common.doctor"),
        },
        {
            key: "status",
            value: t("common.status"),
        }
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        statuses,
        getPrefetchData,
        departments,
        specialists,
        hashableColumns
    }
}

export default fields;
