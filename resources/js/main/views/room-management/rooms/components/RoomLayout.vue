<template>
    <div class="flex flex-col h-[calc(100svh-200px)] overflow-hidden">
        <!-- Floor tabs -->
        <a-row class="bg-white px-4 mt-2 rounded-lg">
            <a-col :span="24">
                <div class="flex justify-start mt-4 mb-2">
                    <a-radio-group v-model:value="selectedFloor" button-style="solid" @change="handleFloorChange">
                        <a-radio-button v-for="floor in uniqueFloors" :key="floor" :value="floor">
                            {{ getFloorOrdinal(floor) }} Floor
                        </a-radio-button>
                    </a-radio-group>
                </div>
            </a-col>
        </a-row>
        
        <!-- Room layout by room type -->
        <div class="mt-2 bg-white p-4 shadow-sm h-full overflow-y-scroll rounded-lg">
            <a-skeleton :loading="loading" active :paragraph="{ rows: 8 }">
                <template #default>
                    <template v-for="(roomGroup, roomType) in groupedRooms" :key="roomType">
                        <div class="mb-8" v-if="roomGroup.rooms.length > 0">
                            <!-- Room type header -->
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-base text-gray-700">{{ roomGroup.name }}</span>
                                <div>
                                    <a-tag color="blue">
                                        {{ getPatientRange(roomGroup.rooms) }}
                                    </a-tag>
                                </div>
                            </div>
                            
                            <!-- Room cards in horizontal layout -->
                            <div class="flex flex-wrap gap-4">
                                <div 
                                    v-for="room in roomGroup.rooms"
                                    :key="room.xid"
                                    class="bg-white rounded-lg border border-gray-200 p-4 w-80 shadow-sm hover:shadow-md transition-all duration-200"
                                    :class="{ 
                                        'cursor-pointer': selectable,
                                        'ring-2 ring-blue-500 border-blue-500 bg-blue-50': selectedRoomId === room.xid && selectable,
                                        'opacity-50 cursor-not-allowed': selectable && room.status !== 'available' && room.status?.toLowerCase() !== 'available' && preSelectedRoomId !== room.xid
                                    }"
                                    @click="selectable && handleRoomSelect(room)"
                                >
                                    <!-- Room number badge -->
                                    <div class="mb-3">
                                        <a-tag
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white"
                                            :color="getRoomStatusColor(room.status)"
                                        >
                                            {{ room.name }}
                                        </a-tag>
                                    </div>
                                    
                                    <!-- Patient and Doctor information -->
                                    <div class="mb-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <UserOutlined class="text-gray-400" />
                                            <span class="text-sm font-medium" v-if="room?.current_appointment?.patient">{{ room.current_appointment.patient.user.name }} {{ room.current_appointment.patient.user.last_name }}</span>
                                            <span class="text-sm font-medium" v-else>No Patient</span>
                                        </div>
                                        <div class="flex items-center gap-2 mb-1 ml-5">
                                            <UserDoctor class="text-gray-400" />
                                            <span class="text-sm font-medium" v-if="room?.current_appointment?.doctor">{{ room.current_appointment.doctor.user.name }}{{ room.current_appointment.doctor.user.last_name ? ' ' + room.current_appointment.doctor.user.last_name : '' }}</span>
                                            <span class="text-sm font-medium" v-else>No Doctor</span>
                                        </div>
                                        <div class="text-sm text-gray-600 ml-5">
                                            {{ room?.current_appointment?.treatment_type?.name || '----' }}
                                        </div>
                                    </div>
                                    
                                    <!-- Time and equipment -->
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <ClockCircleOutlined />
                                            <span>
                                                {{ room?.current_appointment?.appointment_date ? new Date(room.current_appointment.appointment_date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false }) : '----' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <ToolOutlined />
                                            <span>
                                                {{ (() => {
                                                    const equipmentMap = { 1: 'Dental Chair, LED Light +1', 2: 'Dental Chair, LED Light +2', 3: 'Dental Chair, LED Light +3' };
                                                    const eq = equipmentMap[room.capacity] || `Dental Chair, LED Light +${room.capacity}`;
                                                    return eq.length > 15 ? eq.substring(0, 15) + '...' : eq;
                                                })() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <div v-if="Object.keys(groupedRooms).length === 0" class="py-8 text-center">
                        <a-empty :description="$t('room.no_rooms_found')" />
                    </div>
                </template>
            </a-skeleton>
        </div>
        
        <!-- Room status legend -->
        <div class="p-4 bg-white"> 
            <div class="flex items-center gap-6 text-sm">
                <span class="font-medium text-gray-700">Room Status:</span>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-gray-400 rounded-full"></span>
                    <span>Available</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <span>Occupied</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                    <span>Reserved</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-orange-500 rounded-full"></span>
                    <span>Maintenance</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                    <span>Cleaning</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import { 
    UserOutlined, 
    ClockCircleOutlined,
    ToolOutlined
} from "@ant-design/icons-vue";
import UserDoctor from "../../../../../common/components/icons/UserDoctor.vue";

export default {
    name: "RoomLayout",
    components: {
        UserOutlined,
        ClockCircleOutlined,
        ToolOutlined,
        UserDoctor
    },
    props: {
        selectable: {
            type: Boolean,
            default: false
        },
        preSelectedRoomId: {
            type: String,
            default: null
        }
    },
    emits: ['roomSelected'],
    setup(props, { emit }) {
        const roomTypes = ref([]);
        const selectedFloor = ref('1');
        const uniqueFloors = ref([]);
        const roomsData = ref([]);
        const loading = ref(false);
        const selectedRoomId = ref(props.preSelectedRoomId);

        onMounted(() => {
            loadRoomTypes();
            loadRooms();
        });

        const loadRoomTypes = () => {
            window.axiosAdmin.get("room-types?fields=id,xid,name,description").then((response) => {
                roomTypes.value = response.data;
            });
        };

        const loadRooms = () => {
            loading.value = true;
            window.axiosAdmin.get("rooms?fields=id,xid,name,floor,capacity,status,notes,roomType,currentAppointment{xid,appointment_date,duration},currentAppointment:treatmentType,currentAppointment:patient,currentAppointment:doctor,currentAppointment:patient:user,currentAppointment:doctor:user", {
                params: {
                    limit: 1000
                }
            }).then((response) => {
                roomsData.value = response.data;
                extractUniqueFloors();
                
                // If there's a pre-selected room, emit it after loading
                if (props.preSelectedRoomId && props.selectable) {
                    const preSelectedRoom = roomsData.value.find(room => room.xid === props.preSelectedRoomId);
                    if (preSelectedRoom) {
                        selectedRoomId.value = props.preSelectedRoomId;
                        emit('roomSelected', preSelectedRoom);
                    }
                }
                
                loading.value = false;
            }).catch(() => {
                loading.value = false;
            });
        };

        const extractUniqueFloors = () => {
            const floors = [...new Set(roomsData.value.map(room => room.floor))];
            uniqueFloors.value = floors.sort((a, b) => a - b);
            
            if (!uniqueFloors.value.includes(selectedFloor.value) && uniqueFloors.value.length > 0) {
                selectedFloor.value = uniqueFloors.value[0];
            }
        };

        const handleFloorChange = (e) => {
            selectedFloor.value = e.target.value;
        };

        const handleRoomSelect = (room) => {
            if (props.selectable) {
                // Allow selection if room is available or if it's the pre-selected room
                const isPreSelected = props.preSelectedRoomId === room.xid;
                const isAvailable = room.status === 'available' || room.status?.toLowerCase() === 'available';
                
                if (isAvailable || isPreSelected) {
                    selectedRoomId.value = room.xid;
                    emit('roomSelected', room);
                } else {
                    // Optionally show a message that room is not available
                    console.log('Room is not available for selection');
                }
            }
        };

        const groupedRooms = computed(() => {
            const groups = {};
            
            const floorRooms = roomsData.value.filter(room => room.floor === selectedFloor.value);
            
            floorRooms.forEach(room => {
                const typeId = room.room_type?.name || 'Other';
                
                if (!groups[typeId]) {
                    groups[typeId] = {
                        name: room.room_type?.name || 'Other',
                        rooms: []
                    };
                }
                
                groups[typeId].rooms.push(room);
            });
            
            return groups;
        });

        const getRoomStatusColor = (status) => {
            const mapping = {
                'available': 'default',
                'occupied': 'blue',
                'reserved': 'red',
                'maintenance': 'orange',
                'cleaning': 'yellow'
            };

            return mapping[status?.toLowerCase()] || 'default';
        };

        // Removed helper functions that read room info; template now accesses properties directly on `room`.

        const getPatientRange = (rooms) => {
            const totalCapacity = rooms.reduce((sum, room) => sum + (room.capacity || 0), 0);
            const occupiedRooms = rooms.filter(room => room.status === 'Occupied').length;
            
            if (totalCapacity === 0) return '0 Patients';
            if (occupiedRooms === 0) return `0-${totalCapacity} Patients`;
            
            return `${occupiedRooms}-${totalCapacity} Patients`;
        };

        const getFloorOrdinal = (floor) => {
            const ordinals = {
                1: '1st',
                2: '2nd', 
                3: '3rd',
                4: '4th',
                5: '5th',
                6: '6th',
                7: '7th',
                8: '8th',
                9: '9th',
                10: '10th'
            };
            return ordinals[floor] || `${floor}th`;
        };

        return {
            roomTypes,
            selectedFloor,
            uniqueFloors,
            roomsData,
            loading,
            selectedRoomId,
            loadRoomTypes,
            loadRooms,
            extractUniqueFloors,
            handleFloorChange,
            handleRoomSelect,
            groupedRooms,
            getRoomStatusColor,
            getPatientRange,
            getFloorOrdinal
        };
    }
};
</script>
