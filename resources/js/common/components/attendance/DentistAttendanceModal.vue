<template>
  <div>
    <!-- Open Attendance Button + Availability Flag -->
    <div class="flex items-center space-x-3">
      <!-- Small availability flag shown next to the button -->
      <div
        class="flex items-center space-x-2 px-2 py-1 rounded-md"
        :title="t('attendance.current_availability')"
        style="border: 1px solid rgba(0,0,0,0.06);"
      >
        <div
          :class="[
            'w-3 h-3 rounded-full',
            status === 'available' ? 'bg-green-500' : status === 'busy' ? 'bg-red-500' : 'bg-yellow-500'
          ]"
        ></div>
        <span
          :class="[
            'text-sm font-medium',
            status === 'available' ? 'text-green-600' : status === 'busy' ? 'text-red-600' : 'text-yellow-600'
          ]"
        >
          <span class="inline-flex items-center">
            <LoadingOutlined v-if="statusLoading" class="loading-spinner mr-1" />
            {{ statusOptions.find(opt => opt.value === status)?.label }}
          </span>
        </span>
      </div>
      <a-button type="text" @click="openModal" danger class="p-0">
        <template #icon>
          <ClockCircleOutlined />
        </template>
        {{ isClockedIn ? $t('attendance.clock_out') : $t('attendance.clock_in') }}
      </a-button>
    </div>

    <!-- Attendance Modal -->
    <a-modal
      v-model:open="modalVisible"
      :title="$t('attendance.dentist_attendance')"
      :width="700"
      :footer="null"
      @cancel="closeModal"
      centered
    >
      <div class="p-4">
        <a-tabs v-model:activeKey="activeTab" @change="onTabChange">
          <!-- Tab 1: Attendance -->
          <a-tab-pane key="attendance" :tab="$t('attendance.attendance')">
            <div class="space-y-6">
              <!-- Current Status Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-bold mb-3">{{ $t('attendance.current_status') }}</h3>
                
                <!-- Working Status -->
                <div class="flex items-center space-x-4 mb-4">
                  <div class="flex items-center space-x-2">
                    <div
                      :class="[
                        'w-3 h-3 rounded-full',
                        isClockedIn ? 'bg-green-500' : 'bg-red-500'
                      ]"
                    ></div>
                    <span
                      :class="[
                        'font-semibold',
                        isClockedIn ? 'text-green-600' : 'text-red-600'
                      ]"
                    >
                      {{ isClockedIn ? $t('attendance.clocked_in') : $t('attendance.clocked_out') }}
                    </span>
                  </div>
                  
                  <!-- Live Timer (if clocked in) -->
                  <div v-if="isClockedIn && workedDuration" class="text-gray-600">
                    <span class="text-sm">{{ $t('attendance.working_for') }}: </span>
                    <span class="font-mono font-bold text-blue-600">{{ workedDuration }}</span>
                  </div>
                </div>

                <!-- Availability Status -->
                <div class="flex items-center space-x-4">
                  <div class="flex items-center space-x-2">
                    <div
                      :class="[
                        'w-3 h-3 rounded-full',
                        status === 'available' ? 'bg-green-500' : 
                        status === 'busy' ? 'bg-red-500' : 'bg-yellow-500'
                      ]"
                    ></div>
                    <span class="font-medium text-gray-700">{{ $t('attendance.availability_status') }}:</span>
                    <span
                      :class="[
                        'font-semibold',
                        status === 'available' ? 'text-green-600' : 
                        status === 'busy' ? 'text-red-600' : 'text-yellow-600'
                      ]"
                    >
                      <span class="inline-flex items-center">
                        <LoadingOutlined v-if="statusLoading" class="loading-spinner mr-1" />
                        {{ statusOptions.find(opt => opt.value === status)?.label }}
                      </span>
                    </span>
                  </div>
                </div>
                
                <!-- Clock In Time -->
                <div v-if="isClockedIn && clockInTime" class="mt-3 text-sm text-gray-500">
                  {{ $t('attendance.clocked_in_at') }}: {{ formatTime(clockInTime) }}
                </div>
              </div>

              <!-- Toggle Button -->
              <div class="text-center">
                <a-button
                  :type="isClockedIn ? 'default' : 'primary'"
                  :danger="isClockedIn"
                  size="large"
                  :loading="toggleLoading"
                  :disabled="!isClockedIn && hasCompletedDailyAttendance"
                  @click="toggleAttendance"
                  class="px-8 py-2 h-12"
                >
                  <template #icon>
                    <ClockCircleOutlined />
                  </template>
                  {{ isClockedIn ? $t('attendance.clock_out') : $t('attendance.clock_in') }}
                </a-button>
                
                <!-- Warning message when clock-in is disabled -->
                <div v-if="!isClockedIn && hasCompletedDailyAttendance" class="mt-3 text-sm text-orange-600 bg-orange-50 p-3 rounded-lg">
                  <div class="flex items-center justify-center space-x-2">
                    <ExclamationCircleOutlined class="text-orange-500" />
                    <span>{{ $t('attendance.clock_in_disabled_message') || 'Clock-in is disabled. You have already completed your attendance for today. Please wait until tomorrow.' }}</span>
                  </div>
                </div>
              </div>

              <!-- Availability Status Selector -->
              <div class="space-y-3" v-if="isClockedIn">
                <h4 class="text-md font-semibold text-gray-700">{{ $t('attendance.availability_status') }}</h4>
                <div class="flex justify-center">
                  <a-segmented
                    v-model:value="status"
                    :options="statusOptions"
                    :loading="statusLoading"
                    @change="(val) => { if (!isClockedIn) { message.warning(t('attendance.must_be_clocked_in_to_change_status') || 'You must be clocked in to change availability'); return; } debouncedUpdateStatus(val) }"
                    size="large"
                    class="custom-segmented"
                  />
                </div>
              </div>

              <!-- Summary Section -->
              <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-bold mb-3">{{ $t('attendance.summary') }}</h3>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ summary.today || '0h 0m' }}</div>
                    <div class="text-sm text-gray-600">{{ $t('attendance.today') }}</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ summary.week || '0h 0m' }}</div>
                    <div class="text-sm text-gray-600">{{ $t('attendance.this_week') }}</div>
                  </div>
                </div>
              </div>
            </div>
          </a-tab-pane>

          <!-- Tab 2: History -->
          <a-tab-pane key="history" :tab="$t('attendance.history')">
            <div class="space-y-4">
              <h3 class="text-lg font-bold">{{ $t('attendance.attendance_history') }}</h3>
              
              <a-table
                :columns="historyColumns"
                :data-source="crudVariables.table.data"
                :loading="historyLoading"
                :pagination="{ pageSize: 10 }"
                size="middle"
                class="mt-4"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.dataIndex === 'clock_time'">
                    <div>
                      <div class="text-sm font-medium">{{ formatDate(record.clock_time) }}</div>
                      <div class="text-xs text-gray-500">{{ formatTime(record.clock_time) }}</div>
                    </div>
                  </template>

                  <template v-if="column.dataIndex === 'status'">
                    <a-tag :color="record.status === 'clock_in' ? 'green' : record.status === 'clock_out' ? 'red' : 'default'">
                      {{ statusLabel(record.status) }}
                    </a-tag>
                  </template>

                  <template v-if="column.dataIndex === 'notes'">
                    <div>
                      <a-typography-paragraph
                        v-model:content="editableNotes[record.xid]"
                        :editable="{ onChange: (val) => onNoteChange(record, val) }"
                        class="text-sm text-gray-700"
                      />
                    </div>
                  </template>
                </template>
              </a-table>
            </div>
          </a-tab-pane>
        </a-tabs>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, h, watch, reactive } from 'vue'
import { debounce } from 'lodash-es'
import { useI18n } from 'vue-i18n'
import { message, Modal } from 'ant-design-vue'
import { ClockCircleOutlined, LoadingOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue'
import apiAdmin from '../../composable/apiAdmin'
import crud from '../../composable/crud'
import common from '../../composable/common'

// Translation hook
const { t } = useI18n()

// API composable
const { } = apiAdmin()

const { user, dayjs, appSetting } = common()

// Reactive state
const modalVisible = ref(false)
const activeTab = ref('attendance')
const isClockedIn = ref(false)
const clockInTime = ref(null)
const workedDuration = ref('')
const summary = ref({ today: '', week: '' })
const recentRecords = ref([])
const toggleLoading = ref(false)
const historyLoading = ref(false)
const status = ref('available')
const statusLoading = ref(false)
const crudVariables = crud();
const hasCompletedDailyAttendance = ref(false)

// Timer for live duration update
let durationTimer = null

// Availability status options
const statusOptions = [
  {
    label: t('attendance.available'),
    value: 'available',
    class: 'text-green-600 bg-green-50 border-green-200'
  },
  {
    label: t('attendance.busy'),
    value: 'busy',
    class: 'text-red-600 bg-red-50 border-red-200'
  },
  {
    label: t('attendance.break'),
    value: 'break',
    class: 'text-yellow-600 bg-yellow-50 border-yellow-200'
  }
]

// History table columns (match API: clock_time, status, notes, doctor.user.name)
const historyColumns = [
  {
    title: t('common.date'),
    dataIndex: 'clock_time',
    key: 'clock_time',
    width: '25%'
  },
  {
    title: t('attendance.status'),
    dataIndex: 'status',
    key: 'status',
    width: '20%'
  },
  {
    title: t('common.notes'),
    dataIndex: 'notes',
    key: 'notes',
    width: '25%'
  }
]

// Modal management
const openModal = () => {
  modalVisible.value = true
}

const closeModal = () => {
  modalVisible.value = false
  activeTab.value = 'attendance'
}

// Tab change handler
const onTabChange = (key) => {
  if (key === 'history') {
    fetchAttendanceHistory()
  }
}

// Fetch initial attendance summary
const fetchAttendanceSummary = async () => {
  try {
    const res = await axiosAdmin.get('/attendance/summary')
    
    isClockedIn.value = res.isClockedIn
    clockInTime.value = res.clock_in_time
    summary.value = res.summary || { today: '', week: '' }
    hasCompletedDailyAttendance.value = res.has_completed_daily_attendance || false

    // Start live timer if clocked in
    if (isClockedIn.value && clockInTime.value) {
      startDurationTimer()
    }
  } catch (error) {
    console.error('Error fetching attendance summary:', error)
    message.error(t('attendance.failed_to_load_data'))
  }
}

// Toggle attendance (clock in/out)
const toggleAttendance = async () => {
  // Check if user has completed daily attendance (clocked out) and prevent clock-in
  if (!isClockedIn.value && hasCompletedDailyAttendance.value) {
    message.warning(t('attendance.cannot_clock_in_after_clock_out') || 'You cannot clock in again after clocking out for today. Please wait until tomorrow.')
    return
  }

  // Show confirmation modal
  const action = isClockedIn.value ? t('attendance.clock_out') : t('attendance.clock_in')
  const confirmMessage = isClockedIn.value 
    ? t('attendance.confirm_clock_out') || 'Are you sure you want to clock out?' 
    : t('attendance.confirm_clock_in') || 'Are you sure you want to clock in?'

  Modal.confirm({
    title: t('attendance.confirm_action') || 'Confirm Action',
    content: confirmMessage,
    icon: h(ExclamationCircleOutlined),
    okText: t('common.yes') || 'Yes',
    cancelText: t('common.cancel') || 'Cancel',
    onOk: async () => {
      await performToggleAttendance()
    }
  })
}

// Actual toggle attendance function
const performToggleAttendance = async () => {
  toggleLoading.value = true
  
  try {
    const res = await axiosAdmin.post('/attendance/toggle')

    isClockedIn.value = res.status === 'clocked_in'
    clockInTime.value = res.clock_in_time
    summary.value = res.summary || { today: '', week: '' }
    hasCompletedDailyAttendance.value = res.has_completed_daily_attendance || false

    message.success(res.message || t('attendance.attendance_updated'))

    // Handle timer based on new status
    if (isClockedIn.value) {
      startDurationTimer()
    } else {
      stopDurationTimer()
      workedDuration.value = summary.value.today || ''
    }
  } catch (error) {
    console.error('Error toggling attendance:', error)
    message.error(t('attendance.failed_to_update'))
  } finally {
    toggleLoading.value = false
  }
}

// Fetch attendance history
const fetchAttendanceHistory = async () => {
  historyLoading.value = true;
  try {
    crudVariables.tableUrl.value = {
      url: '/attendances?fields=id,xid,user_id,clock_time,status,notes',
      filters: {
        user_id: user.value.xid,
      }
    }

    crudVariables.fetch({
      page: 1,
    })
  } catch (error) {
    console.error('Error fetching attendance history:', error)
    message.error(t('attendance.failed_to_load_history'))
  } finally {
    historyLoading.value = false
  }
}

// Editable notes state: keyed by record.xid
const editableNotes = reactive({})

// Map to store per-record debounced save functions
const noteSaveDebouncers = {}

const saveNote = async (recordXid, noteValue) => {
  try {
    // POST or PATCH to update notes for the attendance record
    await axiosAdmin.put(`/attendances/${recordXid}`, { notes: noteValue })
    message.success(t('attendance.notes_saved') || 'Note saved')
  } catch (error) {
    console.error('Error saving note:', error)
    message.error(t('attendance.failed_to_save_note') || 'Failed to save note')
  }
}

const getDebouncedSave = (recordXid) => {
  if (!noteSaveDebouncers[recordXid]) {
    noteSaveDebouncers[recordXid] = debounce((val) => saveNote(recordXid, val), 800)
  }
  return noteSaveDebouncers[recordXid]
}

const onNoteChange = (record, newVal) => {
  // Keep local editable value
  editableNotes[record.xid] = newVal
  // Call debounced save
  getDebouncedSave(record.xid)(newVal)
}

// When table data changes (history fetched), populate editableNotes
watch(() => crudVariables.table.data, (newData) => {
  if (Array.isArray(newData)) {
    newData.forEach((r) => {
      editableNotes[r.xid] = r.notes || ''
    })
  }
})

// Fetch dentist status
const fetchDentistStatus = async () => {
  try {
    statusLoading.value = true
    const response = await axiosAdmin.get('doctors/status')
    status.value = response.data.status || 'available'
  } catch (error) {
    console.error('Error fetching doctor status:', error)
    // Set default status if fetch fails
    status.value = 'available'
  } finally {
    statusLoading.value = false
  }
}

// Update dentist status
// Update dentist status (debounced wrapper will call this)
const updateStatus = async (newStatus) => {
  // Prevent status change if user is not clocked in
  if (!isClockedIn.value) {
    message.warning(t('attendance.must_be_clocked_in_to_change_status') || 'You must be clocked in to change availability')
    // Revert segmented control to current status (no-op because v-model will remain)
    return
  }

  statusLoading.value = true

  try {
    await axiosAdmin.post('doctors/status', { status: newStatus })
    status.value = newStatus
    message.success(t('attendance.status_updated'))
  } catch (error) {
    console.error('Error updating doctor status:', error)
    message.error(t('attendance.failed_to_update_status'))
    // Revert status on error
    const currentStatus = status.value
    status.value = currentStatus
  } finally {
    statusLoading.value = false
  }
}

// Debounced version to avoid rapid API calls when user toggles quickly
const debouncedUpdateStatus = debounce((newStatus) => {
  // call the async function but ignore the returned promise here
  updateStatus(newStatus)
}, 600)

// Duration timer management
const startDurationTimer = () => {
  stopDurationTimer() // Clear any existing timer
  
  const updateDuration = () => {
    if (clockInTime.value) {
      // Use dayjs with app timezone to compute duration
      const now = dayjs().tz(appSetting.value.timezone)
      // Parse clockIn time as being in the app timezone (since DB stores naive datetime)
      const clockIn = dayjs.tz(clockInTime.value, appSetting.value.timezone)
      const diffSeconds = now.diff(clockIn, 'second')

      const hours = Math.floor(diffSeconds / 3600)
      const minutes = Math.floor((diffSeconds % 3600) / 60)
      const seconds = diffSeconds % 60

      workedDuration.value = `${hours}h ${minutes}m ${seconds}s`
    }
  }
  
  updateDuration() // Initial update
  durationTimer = setInterval(updateDuration, 1000)
}

const stopDurationTimer = () => {
  if (durationTimer) {
    clearInterval(durationTimer)
    durationTimer = null
  }
}

// Utility functions
const formatTime = (timeString) => {
  if (!timeString) return '-'
  // Use appSetting time format and timezone
  try {
    return dayjs(timeString).tz(appSetting.value.timezone).format(appSetting.value.time_format)
  } catch (e) {
    return '-'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    return dayjs(dateString).tz(appSetting.value.timezone).format(appSetting.value.date_format)
  } catch (e) {
    return '-'
  }
}

// Convert status code to human readable label
const statusLabel = (statusCode) => {
  if (!statusCode) return '-'
  const map = {
    clock_in: t('attendance.clock_in'),
    clock_out: t('attendance.clock_out'),
    clocked_in: t('attendance.clocked_in'),
    clocked_out: t('attendance.clocked_out')
  }

  return map[statusCode] || statusCode
}

// Lifecycle hooks
onMounted(() => {
  crudVariables.hashableColumns.value = [
    'user_id'
  ];
  fetchAttendanceSummary()
  fetchDentistStatus()
})

onUnmounted(() => {
  stopDurationTimer()
})
</script>

<style scoped>
/* Additional custom styles if needed */
.ant-modal-body {
  padding: 0;
}

.ant-tabs-content-holder {
  padding: 0;
}

/* Custom segmented control styles */
.custom-segmented :deep(.ant-segmented-item) {
  transition: all 0.3s ease;
}

.custom-segmented :deep(.ant-segmented-item:nth-child(1).ant-segmented-item-selected) {
  background-color: #f0f9ff;
  border-color: #10b981;
  color: #059669;
}

.custom-segmented :deep(.ant-segmented-item:nth-child(2).ant-segmented-item-selected) {
  background-color: #fef2f2;
  border-color: #ef4444;
  color: #dc2626;
}

.custom-segmented :deep(.ant-segmented-item:nth-child(3).ant-segmented-item-selected) {
  background-color: #fffbeb;
  border-color: #f59e0b;
  color: #d97706;
}

.custom-segmented :deep(.ant-segmented-item-label) {
  font-weight: 500;
}

/* Loading spinner next to status labels */
.loading-spinner {
  font-size: 14px;
  line-height: 1;
  display: inline-block;
  -webkit-animation: spin 1s linear infinite;
          animation: spin 1s linear infinite;
}

@-webkit-keyframes spin {
  100% { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes spin {
  100% { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
</style>
