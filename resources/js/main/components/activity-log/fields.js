const fields = () => {
    const url = "activity-logs?fields=xid,id,datetime,user,entity,description,action,json_log";
    const hashableColumns = [
        'patient_id'
    ];

    const columns = [
        {
            title: "Fecha y Hora",
            dataIndex: "datetime",
        },
        {
            title: "Usuario",
            dataIndex: "user",
        },
        {
            title: "Tipo",
            dataIndex: "entity",
        },
        {
            title: "Detalles",
            dataIndex: "description",
        },
        {
            title: "Acción",
            dataIndex: "action",
        }
    ];

    const filterableColumns = [
        {
            key: "user",
            value: "nombre usuario",
        },
        {
            key: "json_log",
            value: "log",
        }
    ];

    // Parse user JSON to get the user's name
    const getUserName = (userJson) => {
        try {
            if (!userJson) return "";
            const userData = typeof userJson === 'string' ? JSON.parse(userJson) : userJson;
            return userData.name || "-";
        } catch (error) {
            console.error("Error parsing user data:", error);
            return "";
        }
    };

    // Parse JSON log to extract data for display
    const parseJsonLog = (jsonLog) => {
        try {
            if (!jsonLog) return {};
            const logData = typeof jsonLog === 'string' ? JSON.parse(jsonLog) : jsonLog;
            return logData;
        } catch (error) {
            console.error("Error parsing JSON log:", error);
            return {};
        }
    };

    return {
        url,
        columns,
        filterableColumns,
        hashableColumns,
        getUserName,
        parseJsonLog
    };
};

export default fields;
