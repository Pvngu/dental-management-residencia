import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();
    
    // Minimal fields for index/listing view - only what's needed for the table
    const indexFields = "xid,ssn,updated_at,unread_messages_count,user{xid,name,last_name,email,phone,date_of_birth,profile_image,profile_image_url,default_address}";
    const indexUrl = `patients?fields=${indexFields}`;
    
    // Complete fields for AddEdit/detail view - all patient data
    const addEditUrl = "patients";
    const hashableColumns = ["role_id", "preferred_doctor_id", "provider_id", "secondary_provider_id"];

    const initData = {
        xid: "",
        pharmacy_name: "",
        pharmacy_phone: "",
        blood_type: undefined,
        ssn: "",
        allergies: [],
        media_channels: {},
        preferred_doctor_id: undefined,
        // Insurance fields
        has_secondary_insurance: false,
        primary_insurance: "primary",
        provider_id: undefined,
        policy_holder_name: "",
        relationship_to_holder: "self",
        member_id: "",
        group_number: "",
        plan_type: "",
        verified_at: undefined,
        // Secondary insurance fields
        secondary_provider_id: undefined,
        secondary_policy_holder_name: "",
        secondary_relationship_to_holder: "self",
        secondary_member_id: "",
        secondary_group_number: "",
        secondary_plan_type: "",
        secondary_verified_at: undefined,
        user: {
            first_name: "",
            last_name: "",
            email: "",
            password: "",
            profile_image: undefined,
            profile_image_url: undefined,
            phone: "",
            address: "",
            date_of_birth: "",
            status: "enabled",
            user_type: "doctors", 
            role_id: undefined,
        },
        emergency_contacts: {
            name: "",
            phone: "",
            xid: "",
        },
        addresses: [],
    };

    const columns = [
        {
            title: t("user.name"),
            dataIndex: "patient",
            key: "patient",
        },
        {
            title: t("user.phone"),
            dataIndex: "phone",
        },
        {
            title: t("user.address"),
            dataIndex: "address",
            width: "20%",
        },
        {
            title: t("user.date_of_birth"),
            dataIndex: "date_of_birth",
        },
        {
            title: t("common.last_activity"),
            dataIndex: "updated_at",
        },
        {
            title: t("user.registration"),
            dataIndex: "created_at",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("user.name"),
        },
        {
            key: "email",
            value: t("user.email"),
        },
        {
            key: "phone",
            value: t("user.phone"),
        },
        {
            key: "address",
            value: t("user.address"),
        },
        {
            key: "date_of_birth",
            value: t("user.date_of_birth"),
        },
    ];

    return {
        indexUrl,
        url: indexUrl, // Default to index URL for backwards compatibility
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
    };
};

export default fields;
