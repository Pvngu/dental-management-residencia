<template>
    <div class="clinic-access-manager">
        <!-- Search and Filter -->
        <div class="mb-4">
            <a-input-search
                v-model:value="searchQuery"
                placeholder="Search clinics by name or city..."
                enter-button
                allow-clear
            />
        </div>

        <div class="clinics-list-container space-y-4">
            <div v-if="loading" class="text-center py-4">
                <a-spin />
            </div>

            <div
                v-else-if="filteredClinics.length === 0"
                class="text-center py-4 text-gray-500"
            >
                No clinics found.
            </div>

            <div
                v-else
                v-for="clinic in filteredClinics"
                :key="clinic.id"
                class="clinic-card flex flex-col md:flex-row items-start md:items-center justify-between p-4 border rounded-lg hover:shadow-sm transition-shadow duration-300 bg-white"
                :class="{ 'opacity-70 bg-gray-50': !clinic.has_access }"
            >
                <!-- Clinic Info -->
                <div
                    class="flex items-center space-x-4 mb-4 md:mb-0 w-full md:w-1/3"
                >
                    <div
                        class="w-12 h-12 flex-shrink-0 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden border"
                    >
                        <img
                            v-if="clinic.logo_url"
                            :src="clinic.logo_url"
                            :alt="clinic.name"
                            class="w-full h-full object-cover"
                        />
                        <span
                            v-else
                            class="text-gray-400 text-xs text-center px-1"
                            >{{ getInitials(clinic.name) }}</span
                        >
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 leading-tight">
                            {{ clinic.name }}
                        </h4>
                        <p class="text-xs text-gray-500">{{ clinic.city }}</p>
                    </div>
                </div>

                <!-- Controls -->
                <div
                    class="flex flex-col md:flex-row items-center space-y-3 md:space-y-0 md:space-x-6 w-full md:w-2/3 justify-end"
                >
                    <!-- Access Toggle -->
                    <div class="flex items-center space-x-2">
                        <a-switch
                            v-model:checked="clinic.has_access"
                            @change="handleAccessChange(clinic)"
                            checked-children="Access"
                            un-checked-children="No Access"
                        />
                    </div>

                    <!-- Role Selector -->
                    <div class="w-full md:w-48">
                        <a-select
                            v-model:value="clinic.x_assigned_role_id"
                            :disabled="!clinic.has_access"
                            placeholder="Select Role"
                            class="w-full"
                            :class="{ 'opacity-50': !clinic.has_access }"
                            @change="handleRoleChange(clinic)"
                        >
                            <a-select-option
                                v-for="role in availableRoles"
                                :key="role.xid"
                                :value="role.xid"
                            >
                                <div class="flex items-center">
                                    <span
                                        class="w-2 h-2 rounded-full mr-2"
                                        :style="{
                                            backgroundColor: getRoleColor(
                                                role.name,
                                            ),
                                        }"
                                    ></span>
                                    {{ role.display_name || role.name }}
                                </div>
                            </a-select-option>
                        </a-select>
                    </div>

                    <!-- Default Clinic -->
                    <div
                        class="flex items-center"
                        :title="
                            !clinic.has_access
                                ? 'Enable access to set as default'
                                : 'Set as default login clinic'
                        "
                    >
                        <a-radio
                            :checked="clinic.is_default"
                            :disabled="!clinic.has_access"
                            @click="handleDefaultChange(clinic)"
                        >
                            Default
                        </a-radio>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, computed, watch, onMounted } from "vue";
import { message } from "ant-design-vue";

export default defineComponent({
    name: "ClinicAccessManager",
    props: {
        userId: {
            type: [String, Number],
            required: true, // Pass 'new' for new users
        },
        availableRoles: {
            type: Array,
            default: () => [],
        },
        userRoleId: {
            type: [String, Number],
            default: null, // The user's current role_id (x_role_id)
        },
    },
    emits: ["update:clinics", "update:defaultClinicId"],
    setup(props, { emit }) {
        const loading = ref(false);
        const searchQuery = ref("");
        const clinics = ref([]);

        // Role Colors helper
        const getRoleColor = (roleName) => {
            const colors = {
                admin: "#f5222d",
                doctor: "#1890ff",
                staff: "#52c41a",
                receptionist: "#fa8c16",
                patient: "#722ed1",
            };
            return colors[roleName] || "#d9d9d9";
        };

        const getInitials = (name) => {
            return name
                ? name
                      .split(" ")
                      .map((n) => n[0])
                      .join("")
                      .substring(0, 2)
                      .toUpperCase()
                : "CL";
        };

        const fetchClinicMatrix = async () => {
            loading.value = true;
            try {
                const id = props.userId || "new"; // Handle undefined userId for creation
                const response = await axiosAdmin.get(
                    `users/${id}/clinic-matrix`,
                );
                clinics.value = response.data;
                emitUpdate(); // Emit initial state
            } catch (error) {
                console.error("Failed to load clinic matrix", error);
                message.error("Failed to load clinic access data.");
            } finally {
                loading.value = false;
            }
        };

        const filteredClinics = computed(() => {
            if (!searchQuery.value) return clinics.value;
            const lower = searchQuery.value.toLowerCase();
            return clinics.value.filter(
                (c) =>
                    c.name.toLowerCase().includes(lower) ||
                    (c.city && c.city.toLowerCase().includes(lower)),
            );
        });

        const handleAccessChange = (clinic) => {
            if (clinic.has_access) {
                // If turned ON, assign user's current role if none selected
                if (!clinic.x_assigned_role_id) {
                    // First try to use user's role
                    if (props.userRoleId) {
                        clinic.x_assigned_role_id = props.userRoleId;
                    } else if (props.availableRoles.length > 0) {
                        // Fallback to first available role
                        clinic.x_assigned_role_id = props.availableRoles[0].xid;
                    }
                }
            } else {
                // If turned OFF
                clinic.is_default = false; // Cannot be default if no access
            }
            emitUpdate();
        };

        const handleRoleChange = (clinic) => {
            emitUpdate();
        };

        const handleDefaultChange = (selectedClinic) => {
            if (!selectedClinic.has_access) return;

            // Uncheck others
            clinics.value.forEach((c) => (c.is_default = false));
            selectedClinic.is_default = true;
            emitUpdate();
        };

        const emitUpdate = () => {
            // Prepared payload for the parent
            const payload = clinics.value
                .filter((c) => c.has_access)
                .map((c) => {
                    // Validate role is selected
                    const roleId = c.x_assigned_role_id || props.userRoleId || (props.availableRoles.length > 0 ? props.availableRoles[0].xid : null);
                    
                    if (!roleId) {
                        message.warning(`Role is required for ${c.name}`);
                    }
                    
                    return {
                        id: c.xid, // sending xid as id (backend trait handles this) or send id if needed. The resource usually expects user form data style.
                        // Usually we send xids to API. Backend decodes.
                        clinic_id: c.xid,
                        role_id: roleId,
                    };
                });

            const defaultClinic = clinics.value.find((c) => c.is_default);
            const defaultClinicId = defaultClinic ? defaultClinic.xid : null;

            emit("update:clinics", payload);
            emit("update:defaultClinicId", defaultClinicId);
        };

        onMounted(() => {
            fetchClinicMatrix();
        });

        // Watch if userId changes (e.g. after save and create another)
        watch(
            () => props.userId,
            (newVal) => {
                if (newVal) fetchClinicMatrix();
            },
        );

        return {
            loading,
            searchQuery,
            filteredClinics,
            getRoleColor,
            getInitials,
            handleAccessChange,
            handleRoleChange,
            handleDefaultChange,
        };
    },
});
</script>
