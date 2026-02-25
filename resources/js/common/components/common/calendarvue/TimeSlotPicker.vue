<template>
    <div class="time-slot-picker">
        <div class="time-slot-header">
            <h4>{{ $t('calendar.available_time_slots') }}</h4>
            <span class="date-display">{{ formatDate(selectedDate) }}</span>
        </div>
        
        <div class="time-slots-grid">
            <div 
                v-for="slot in availableTimeSlots" 
                :key="slot.time"
                class="time-slot-item"
                :class="{
                    'selected': selectedSlot === slot.time,
                    'unavailable': !slot.available,
                    'occupied': slot.occupied
                }"
                @click="selectTimeSlot(slot)"
            >
                <div class="slot-time">{{ slot.time }}</div>
                <div class="slot-status">
                    <span v-if="slot.occupied" class="occupied-text">
                        {{ $t('calendar.occupied') }}
                    </span>
                    <span v-else-if="!slot.available" class="unavailable-text">
                        {{ $t('calendar.unavailable') }}
                    </span>
                    <span v-else class="available-text">
                        {{ $t('calendar.available') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="time-slot-legend">
            <div class="legend-item">
                <div class="legend-color available"></div>
                <span>{{ $t('calendar.available') }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-color occupied"></div>
                <span>{{ $t('calendar.occupied') }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-color unavailable"></div>
                <span>{{ $t('calendar.unavailable') }}</span>
            </div>
        </div>

        <div class="time-slot-actions" v-if="selectedSlot">
            <a-space>
                <a-button @click="clearSelection">
                    {{ $t('common.clear') }}
                </a-button>
                <a-button type="primary" @click="confirmSelection">
                    {{ $t('calendar.confirm_time_slot') }}
                </a-button>
            </a-space>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import moment from "moment";

export default defineComponent({
    name: "TimeSlotPicker",
    props: {
        selectedDate: {
            type: [String, Object],
            default: () => moment().format("YYYY-MM-DD"),
        },
        dentistId: {
            type: String,
            default: "",
        },
        existingAppointments: {
            type: Array,
            default: () => [],
        },
        workingHours: {
            type: Object,
            default: () => ({
                start: "09:00",
                end: "17:00",
                lunchBreak: {
                    start: "12:00",
                    end: "13:00",
                },
                slotDuration: 60, // minutes
            }),
        },
    },
    emits: ["time-selected", "time-cleared"],
    setup(props, { emit }) {
        const { t } = useI18n();
        const selectedSlot = ref(null);

        const formatDate = (date) => {
            return moment(date).format("dddd, MMMM Do YYYY");
        };

        const generateTimeSlots = () => {
            const slots = [];
            const startTime = moment(props.workingHours.start, "HH:mm");
            const endTime = moment(props.workingHours.end, "HH:mm");
            const lunchStart = moment(props.workingHours.lunchBreak.start, "HH:mm");
            const lunchEnd = moment(props.workingHours.lunchBreak.end, "HH:mm");
            const slotDuration = props.workingHours.slotDuration;

            let currentTime = startTime.clone();

            while (currentTime.isBefore(endTime)) {
                const slotTime = currentTime.format("HH:mm");
                const displayTime = currentTime.format("hh:mm A");
                
                // Check if this time slot is during lunch break
                const isLunchTime = currentTime.isBetween(lunchStart, lunchEnd, null, '[)');
                
                // Check if this time slot is already occupied
                const isOccupied = props.existingAppointments.some(appointment => {
                    const appointmentStart = moment(appointment.start_time, "hh:mm A");
                    return appointmentStart.format("HH:mm") === slotTime;
                });

                // Check if the dentist is available (this would come from backend)
                const isDentistAvailable = true; // This should be determined by dentist's schedule

                slots.push({
                    time: slotTime,
                    displayTime: displayTime,
                    available: !isLunchTime && isDentistAvailable,
                    occupied: isOccupied,
                    isLunchTime: isLunchTime,
                });

                currentTime.add(slotDuration, "minutes");
            }

            return slots;
        };

        const availableTimeSlots = computed(() => {
            return generateTimeSlots();
        });

        const selectTimeSlot = (slot) => {
            if (slot.available && !slot.occupied) {
                selectedSlot.value = slot.time;
                emit("time-selected", {
                    time: slot.time,
                    displayTime: slot.displayTime,
                    date: props.selectedDate,
                });
            }
        };

        const clearSelection = () => {
            selectedSlot.value = null;
            emit("time-cleared");
        };

        const confirmSelection = () => {
            if (selectedSlot.value) {
                const slot = availableTimeSlots.value.find(s => s.time === selectedSlot.value);
                emit("time-selected", {
                    time: slot.time,
                    displayTime: slot.displayTime,
                    date: props.selectedDate,
                    confirmed: true,
                });
            }
        };

        // Watch for changes in date or dentist to reset selection
        watch([() => props.selectedDate, () => props.dentistId], () => {
            clearSelection();
        });

        return {
            selectedSlot,
            availableTimeSlots,
            formatDate,
            selectTimeSlot,
            clearSelection,
            confirmSelection,
        };
    },
});
</script>

<style scoped>
.time-slot-picker {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.time-slot-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.time-slot-header h4 {
    margin: 0;
    color: #262626;
    font-weight: 600;
}

.date-display {
    color: #595959;
    font-size: 14px;
    font-weight: 500;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.time-slot-item {
    padding: 12px;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.time-slot-item:hover:not(.unavailable):not(.occupied) {
    border-color: #1890ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(24, 144, 255, 0.2);
}

.time-slot-item.selected {
    border-color: #1890ff;
    background: #e6f7ff;
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.time-slot-item.unavailable {
    background: #f5f5f5;
    border-color: #d9d9d9;
    cursor: not-allowed;
    opacity: 0.6;
}

.time-slot-item.occupied {
    background: #fff2e8;
    border-color: #ffbb96;
    cursor: not-allowed;
}

.slot-time {
    font-weight: 600;
    font-size: 14px;
    color: #262626;
    margin-bottom: 4px;
}

.slot-status {
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.available-text {
    color: #52c41a;
}

.occupied-text {
    color: #fa8c16;
}

.unavailable-text {
    color: #8c8c8c;
}

.time-slot-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
    padding: 12px;
    background: #fafafa;
    border-radius: 6px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #595959;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}

.legend-color.available {
    background: #52c41a;
}

.legend-color.occupied {
    background: #fa8c16;
}

.legend-color.unavailable {
    background: #8c8c8c;
}

.time-slot-actions {
    display: flex;
    justify-content: center;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

/* Responsive design */
@media (max-width: 768px) {
    .time-slot-picker {
        padding: 16px;
    }
    
    .time-slots-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
    }
    
    .time-slot-header {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
    
    .time-slot-legend {
        flex-direction: column;
        gap: 8px;
    }
    
    .legend-item {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .time-slots-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .time-slot-item {
        padding: 8px;
    }
    
    .slot-time {
        font-size: 13px;
    }
    
    .slot-status {
        font-size: 11px;
    }
}
</style>
