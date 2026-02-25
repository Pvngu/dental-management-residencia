<template>
    <a-modal
        :open="visible"
        :title="$t('appointments.assign_room') || 'Assign Room'"
        :width="1200"
        @ok="handleRoomSelection"
        @cancel="handleCancel"
        centered
    >   
        <div v-if="selectedRoom" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2">
                <EnvironmentOutlined class="text-blue-600" />
                <span class="font-medium">{{ $t('appointments.selected_room') || 'Selected Room' }}:</span>
                <a-tag :color="getRoomStatusColor(selectedRoom.status)">
                    {{ selectedRoom.name }}
                </a-tag>
            </div>
        </div>
        <RoomLayout 
            :selectable="true" 
            :preSelectedRoomId="currentAppointment?.room?.xid"
            @roomSelected="selectRoom" 
        />
        <template #footer>
            <a-button @click="handleCancel">
                {{ $t('common.cancel') }}
            </a-button>
            <a-button 
                type="primary"
                @click="handleRoomSelection"
                :disabled="!selectedRoom"
                :loading="loading"
            >
                {{ $t('appointments.assign_room') || 'Assign Room' }}
            </a-button>
        </template>
    </a-modal>
</template>

<script>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { message } from "ant-design-vue";
import { EnvironmentOutlined } from "@ant-design/icons-vue";
import RoomLayout from "./RoomLayout.vue";

export default {
    name: "RoomSelectionModal",
    components: {
        EnvironmentOutlined,
        RoomLayout
    },
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        currentAppointment: {
            type: Object,
            default: null
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:visible', 'roomAssigned', 'cancel'],
    setup(props, { emit }) {
        const { t } = useI18n();
        const selectedRoom = ref(null);

        // Watch for modal visibility changes to reset selected room
        watch(() => props.visible, (newVisible) => {
            if (newVisible) {
                // Pre-select the current room if it exists
                if (props.currentAppointment?.room) {
                    selectedRoom.value = props.currentAppointment.room;
                } else {
                    selectedRoom.value = null;
                }
            } else {
                // Reset selected room when modal closes
                selectedRoom.value = null;
            }
        });

        const selectRoom = (room) => {
            // Allow selection if room is available, or if it's the currently assigned room
            const isCurrentRoom = props.currentAppointment?.room?.xid === room.xid;
            const isAvailable = room.status === 'available' || room.status?.toLowerCase() === 'available';
            
            if (isAvailable || isCurrentRoom) {
                selectedRoom.value = room;
            } else {
                message.warning(
                    `Room ${room.name} is currently ${room.status}. Please select an available room.`
                );
            }
        };

        const handleRoomSelection = () => {
            if (selectedRoom.value && props.currentAppointment) {
                emit('roomAssigned', {
                    room: selectedRoom.value,
                    appointment: props.currentAppointment
                });
                // Close the modal after emitting the assignment
                emit('update:visible', false);
                // Reset selected room
                selectedRoom.value = null;
            }
        };

        const handleCancel = () => {
            selectedRoom.value = null;
            emit('cancel');
            emit('update:visible', false);
        };

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

        return {
            selectedRoom,
            selectRoom,
            handleRoomSelection,
            handleCancel,
            getRoomStatusColor
        };
    }
};
</script>