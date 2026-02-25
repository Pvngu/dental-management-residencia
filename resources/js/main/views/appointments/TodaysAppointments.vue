<template>
    <!-- Appointment Add/Edit Modal -->
    <Appointment
        :visible="crudVariables.addEditVisible.value"
        @closed="crudVariables.onCloseAddEdit"
        :formData="crudVariables.formData.value"
        :data="crudVariables.viewData.value"
        :successMessage="crudVariables.successMessage.value"
        :url="addEditUrl"
        :addEditType="crudVariables.addEditType.value"
        @addEditSuccess="handleAddEditSuccess"
        :rooms="fieldRooms"
        :treatmentTypes="treatmentTypes"
    />

    <!-- Appointment Details Modal -->
    <AppointmentDetails
        :visible="appointmentViewModalVisible"
        :appointmentData="selectedAppointmentForView"
        @closed="onCloseAppointmentViewModal"
    />

    <!-- Statistics Cards -->
    <a-row :gutter="[16, 16]" class="mb-4 w-full">
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="t('appointments.total_appointments')"
                :value="totalCount"
                :loading="loading"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="t('appointments.waiting')"
                :value="checkedInCount"
                :loading="loading"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="t('appointments.in_progress')"
                :value="inProgressCount"
                :loading="loading"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="t('appointments.completed')"
                :value="completedCount"
                :loading="loading"
            />
        </a-col>
    </a-row>
    <!-- Main Content -->
    <div class="pb-6">
        <a-card class="shadow-sm border border-gray-200">
            <!-- Tabs -->
            <a-tabs v-model:activeKey="activeTab" type="card" size="medium">
                <template #rightExtra>
                    <div class="flex items-center gap-4 text-sm text-gray-600">
                        <CalendarOutlined />
                        <span>{{ flowDate }}</span>
                        <!-- Refresh button with tooltip; uses page loading state so it shows the same animation as fetching -->
                        <a-tooltip :title="t('common.refresh') || 'Refresh'">
                            <a-button
                                type="text"
                                :loading="loading"
                                @click="fetchTodaysAppointments"
                            >
                                <template #icon>
                                    <ReloadOutlined />
                                </template>
                            </a-button>
                        </a-tooltip>
                        <!-- Search input for patient name / phone -->
                        <a-input-search
                            style="width: 240px"
                            v-model:value="searchString"
                            :placeholder="t('common.search') + '...'"
                            enter-button
                            allowClear
                            @search="onSearch"
                        />
                    </div>
                </template>
                <a-tab-pane key="flow">
                    <template #tab>
                        <span class="flex items-center gap-2">
                            <UnorderedListOutlined />
                            {{
                                t("appointments.flow_view") ||
                                t("appointments.appointment_flow")
                            }}
                            <a-badge :count="totalCount" />
                        </span>
                    </template>
                </a-tab-pane>

                <a-tab-pane key="checked_in">
                    <template #tab>
                        <span class="flex items-center gap-2">
                            <CheckCircleOutlined />
                            {{ t("appointments.checked_in") }}
                            <a-badge :count="checkedInCount" />
                        </span>
                    </template>
                </a-tab-pane>

                <a-tab-pane key="in_progress">
                    <template #tab>
                        <span class="flex items-center gap-2">
                            <ClockCircleOutlined />
                            {{ t("appointments.in_progress") }}
                            <a-badge :count="inProgressCount" />
                        </span>
                    </template>
                </a-tab-pane>

                <a-tab-pane key="completed">
                    <template #tab>
                        <span class="flex items-center gap-2">
                            <CheckCircleOutlined />
                            {{ t("appointments.completed") }}
                            <a-badge
                                :count="completedCount"
                                :number-style="{ backgroundColor: '#1890ff' }"
                            />
                        </span>
                    </template>
                </a-tab-pane>

                <a-tab-pane key="checked_out">
                    <template #tab>
                        <span class="flex items-center gap-2">
                            <CheckOutlined />
                            {{ t("appointments.checked_out") }}
                            <a-badge
                                :count="checkedOutCount"
                                :number-style="{ backgroundColor: '#52c41a' }"
                            />
                        </span>
                    </template>
                </a-tab-pane>
            </a-tabs>

            <!-- Appointment List -->
            <div class="mt-4">
                <div v-if="loading">
                    <a-skeleton
                        class="mb-12"
                        avatar
                        :paragraph="{ rows: 4 }"
                        v-for="i in 3"
                        :key="i"
                    />
                </div>
                <a-list
                    :data-source="groupedAppointments"
                    item-layout="vertical"
                    size="medium"
                    class="appointment-list"
                    v-else
                >
                    <template #renderItem="{ item: group }">
                        <!-- Render the current appointment -->
                        <div
                            v-for="(item, index) in (isPatientExpanded(group.patientXid) ? group.appointments : [group.currentAppointment])"
                            :key="item.xid"
                            :class="{
                                'mt-4': index > 0,
                                'ml-8 border-l-4 border-blue-300 pl-4': index > 0
                            }"
                        >
                            <a-list-item
                                :data-appointment-xid="item.xid"
                                class="appointment-item"
                                :class="{
                                    'waiting-too-long':
                                        getStatusForItem(item) === 'checked_in' &&
                                        isWaitingTooLong(item),
                                    'highlight-update': highlightedAppointments.has(item.xid),
                                    '!mb-0': group.hasMultiple && index === 0
                                }"
                            >
                            <template #actions>
                                <div class="flex gap-2">
                                    <a-button
                                        v-if="!isCompleted(item)"
                                        @click="onActionClick(item)"
                                        :loading="item.loading"
                                        :style="getActionButtonStyle(item)"
                                    >
                                        <template #icon>
                                            <component
                                                :is="getActionIcon(item)"
                                            />
                                        </template>
                                        {{ getActionLabel(item) }}
                                    </a-button>
                                    <a-button
                                        v-if="
                                            getStatusForItem(item) ===
                                            'in_progress'
                                        "
                                        type="default"
                                        @click="handleOpenItemsModal(item)"
                                        style="
                                            border-color: #722ed1;
                                            color: #722ed1;
                                        "
                                    >
                                        <template #icon>
                                            <ShoppingCartOutlined />
                                        </template>
                                        {{
                                            t("appointments.add_items") ||
                                            "Add Items"
                                        }}
                                    </a-button>
                                    <a-button
                                        v-if="item.completed_datetime"
                                        type="primary"
                                        @click="navigateToPOS(item)"
                                        :disabled="!item.patient?.xid"
                                    >
                                        <template #icon>
                                            <ShoppingCartOutlined />
                                        </template>
                                        {{ t("sales.pos_mode") || "POS" }}
                                    </a-button>
                                    <a-button
                                        v-if="
                                            (permsArray.includes(
                                                'prescriptions_view'
                                            ) ||
                                                permsArray.includes('admin')) &&
                                            hasPrescriptions(item)
                                        "
                                        type="default"
                                        @click="
                                            openPrescriptionPreviewModal(item)
                                        "
                                        style="
                                            border-color: #52c41a;
                                            color: #52c41a;
                                        "
                                    >
                                        <template #icon>
                                            <MedicineBoxOutlined />
                                        </template>
                                        {{
                                            t("prescriptions.view_prescription")
                                        }}
                                    </a-button>
                                </div>
                            </template>

                            <!-- <template #extra>
                            <div class="flex flex-col items-end">
                                <a-tag :color="getStatusColor(item.status)" class="font-medium mb-2">
                                    {{ getStatusText(item.status) }}
                                </a-tag>
                                <div v-if="item.room" class="text-xs text-gray-600">
                                    {{ item.room }}
                                </div>
                            </div>
                        </template> -->

                            <a-list-item-meta>
                                <template #avatar>
                                    <a-avatar
                                        :size="124"
                                        :src="
                                            item.patient?.user
                                                ?.profile_image_url
                                        "
                                        class="border-2 border-gray-200"
                                    >
                                        <template #icon>
                                            <UserOutlined />
                                        </template>
                                    </a-avatar>
                                </template>

                                <template #title>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <a
                                                    class="text-lg font-semibold text-gray-900 cursor-pointer"
                                                    @click.prevent="
                                                        navigateToPatientDetails(
                                                            item
                                                        )
                                                    "
                                                >
                                                    {{ item.patient?.user?.name }}
                                                    {{
                                                        item.patient?.user
                                                            ?.last_name
                                                    }}
                                                </a>
                                                <!-- Badge showing multiple appointments -->
                                                <a-badge
                                                    v-if="group.hasMultiple && index === 0"
                                                    :count="group.count"
                                                    :number-style="{ backgroundColor: '#1890ff' }"
                                                    :title="$t('appointments.total_appointments_today') || 'Total appointments today'"
                                                >
                                                    <CalendarOutlined class="text-blue-500" />
                                                </a-badge>
                                            </div>
                                            <div
                                                class="text-sm text-gray-600 mt-1"
                                            >
                                                <PhoneOutlined />
                                                <span class="ml-1">{{
                                                    item.patient?.user?.phone
                                                }}</span>
                                            </div>
                                            <div
                                                class="text-sm text-gray-500 mt-1"
                                            >
                                                {{ item.patient?.user?.gender }}
                                                |
                                                {{ item.patient?.user?.age }}
                                                years
                                            </div>
                                        </div>
                                        <div
                                            class="text-right flex flex-col items-end"
                                        >
                                            <!-- Status tag shown top-right -->
                                            <div class="mb-2">
                                                <a-tag
                                                    class="!m-0"
                                                    :color="
                                                        getStatusColor(item)
                                                    "
                                                    >{{
                                                        getStatusText(item)
                                                    }}</a-tag
                                                >
                                                <!-- DELAY tag for patients waiting too long -->
                                                <a-tag
                                                    v-if="
                                                        getStatusForItem(
                                                            item
                                                        ) === 'checked_in' &&
                                                        isWaitingTooLong(item)
                                                    "
                                                    class="!m-0 !ml-1 animate-pulse"
                                                    color="red"
                                                >
                                                    {{
                                                        t(
                                                            "appointments.delay"
                                                        ) || "DELAY"
                                                    }}
                                                </a-tag>
                                                <!-- Waiting time for checked-in patients -->
                                                <div
                                                    v-if="
                                                        getStatusForItem(
                                                            item
                                                        ) === 'checked_in' &&
                                                        calculateWaitingTime(
                                                            item
                                                        )
                                                    "
                                                    class="mt-1 text-xs font-medium"
                                                    :class="
                                                        isWaitingTooLong(item)
                                                            ? 'text-red-600 font-bold'
                                                            : 'text-blue-600'
                                                    "
                                                >
                                                    {{
                                                        t(
                                                            "appointments.waiting"
                                                        ) || "Waiting"
                                                    }}:
                                                    {{
                                                        calculateWaitingTime(
                                                            item
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                            <div
                                                class="font-medium text-gray-900"
                                            >
                                                {{ item.time }}
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                {{ formatTimeRange(item) }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ item.treatment_type?.name }}
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template #description>
                                    <div
                                        class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-3"
                                    >
                                        <div>
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div
                                                    class="font-medium text-gray-900 mb-1"
                                                >
                                                    {{
                                                        t(
                                                            "appointments.reason_for_visit"
                                                        )
                                                    }}
                                                </div>
                                                <div>
                                                    <a-button
                                                        type="text"
                                                        size="small"
                                                        @click="
                                                            startEdit(
                                                                item,
                                                                'reason_visit'
                                                            )
                                                        "
                                                    >
                                                        <template #icon
                                                            ><EditOutlined
                                                        /></template>
                                                    </a-button>
                                                </div>
                                            </div>

                                            <div
                                                v-if="
                                                    editing[item.xid] &&
                                                    Object.prototype.hasOwnProperty.call(
                                                        editing[item.xid],
                                                        'reason_visit'
                                                    )
                                                "
                                                class="mb-2"
                                            >
                                                <a-textarea
                                                    v-model:value="
                                                        editing[item.xid]
                                                            .reason_visit
                                                    "
                                                    rows="3"
                                                />
                                                <div class="mt-2">
                                                    <a-button
                                                        type="primary"
                                                        size="small"
                                                        :loading="
                                                            editLoading[
                                                                item.xid
                                                            ] &&
                                                            editLoading[
                                                                item.xid
                                                            ].reason_visit
                                                        "
                                                        @click.prevent="
                                                            saveEdit(
                                                                item,
                                                                'reason_visit'
                                                            )
                                                        "
                                                        style="
                                                            margin-right: 8px;
                                                        "
                                                    >
                                                        {{ t("common.save") }}
                                                    </a-button>
                                                    <a-button
                                                        size="small"
                                                        @click.prevent="
                                                            cancelEdit(
                                                                item,
                                                                'reason_visit'
                                                            )
                                                        "
                                                        >{{
                                                            t("common.cancel")
                                                        }}</a-button
                                                    >
                                                </div>
                                            </div>

                                            <div v-else>
                                                <div
                                                    v-if="item.reason_visit"
                                                    class="text-sm text-gray-700 bg-gray-50 p-2 rounded mb-2"
                                                >
                                                    {{ item.reason_visit }}
                                                </div>
                                                <span
                                                    v-else
                                                    class="text-gray-400 italic"
                                                    >{{
                                                        t(
                                                            "appointments.no_reason_provided"
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div>
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div
                                                    class="font-medium text-gray-900 mb-1"
                                                >
                                                    {{
                                                        t(
                                                            "appointments.appointment_notes"
                                                        )
                                                    }}
                                                </div>
                                                <div>
                                                    <a-button
                                                        type="text"
                                                        size="small"
                                                        @click="
                                                            startEdit(
                                                                item,
                                                                'appointment_notes'
                                                            )
                                                        "
                                                    >
                                                        <template #icon
                                                            ><EditOutlined
                                                        /></template>
                                                    </a-button>
                                                </div>
                                            </div>

                                            <div
                                                v-if="
                                                    editing[item.xid] &&
                                                    Object.prototype.hasOwnProperty.call(
                                                        editing[item.xid],
                                                        'appointment_notes'
                                                    )
                                                "
                                                class="mb-2"
                                            >
                                                <a-textarea
                                                    v-model:value="
                                                        editing[item.xid]
                                                            .appointment_notes
                                                    "
                                                    rows="3"
                                                />
                                                <div class="mt-2">
                                                    <a-button
                                                        type="primary"
                                                        size="small"
                                                        :loading="
                                                            editLoading[
                                                                item.xid
                                                            ] &&
                                                            editLoading[
                                                                item.xid
                                                            ].appointment_notes
                                                        "
                                                        @click.prevent="
                                                            saveEdit(
                                                                item,
                                                                'appointment_notes'
                                                            )
                                                        "
                                                        style="
                                                            margin-right: 8px;
                                                        "
                                                    >
                                                        {{ t("common.save") }}
                                                    </a-button>
                                                    <a-button
                                                        size="small"
                                                        @click.prevent="
                                                            cancelEdit(
                                                                item,
                                                                'appointment_notes'
                                                            )
                                                        "
                                                        >{{
                                                            t("common.cancel")
                                                        }}</a-button
                                                    >
                                                </div>
                                            </div>

                                            <div v-else>
                                                <div
                                                    v-if="
                                                        item.appointment_notes
                                                    "
                                                    class="text-sm text-gray-700 bg-yellow-50 p-2 rounded border-l-4 border-yellow-400"
                                                >
                                                    {{ item.appointment_notes }}
                                                </div>
                                                <span
                                                    v-else
                                                    class="text-gray-400 italic"
                                                    >{{
                                                        t(
                                                            "appointments.no_notes"
                                                        )
                                                    }}</span
                                                >
                                            </div>

                                            <!-- Doctor Information - More Prominent -->
                                            <div
                                                v-if="item.doctor"
                                                class="mt-3"
                                            >
                                                <div class="font-medium text-gray-900 text-sm mb-2">
                                                    {{ $t('common.doctor') || 'Doctor' }}
                                                </div>
                                                <a-card
                                                    size="small"
                                                    class="cursor-pointer hover:shadow-md transition-shadow border-blue-200 hover:border-blue-400"
                                                    :body-style="{ padding: '8px 12px' }"
                                                    @click="openDoctorInfoModal(item.doctor)"
                                                >
                                                    <div class="flex items-center gap-3">
                                                        <a-avatar
                                                            :size="40"
                                                            class="bg-blue-500 flex-shrink-0"
                                                            :src="item.doctor.user?.profile_image_url"
                                                        >
                                                            <template #icon>
                                                                <UserOutlined />
                                                            </template>
                                                        </a-avatar>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="font-semibold text-gray-900 truncate">
                                                                {{ item?.doctor?.user?.name }}
                                                                {{ item?.doctor?.user?.last_name }}
                                                            </div>
                                                            <div class="text-xs text-gray-500 truncate">
                                                                <MedicineBoxOutlined class="mr-1" />
                                                                {{ item?.doctor?.doctor_department?.name || $t('doctors.general') }}
                                                            </div>
                                                        </div>
                                                        <a-tag
                                                            :color="item?.doctor?.current_status === 'available' ? 'green' : 'orange'"
                                                            class="flex-shrink-0 text-xs"
                                                        >
                                                            <div
                                                                class="inline-block w-2 h-2 rounded-full mr-1"
                                                                :style="{ backgroundColor: item?.doctor?.current_status === 'available' ? '#52c41a' : '#faad14' }"
                                                            ></div>
                                                            {{ item?.doctor?.current_status === 'available' ? ($t('doctors.available') || 'Available') : ($t('doctors.busy') || 'Busy') }}
                                                        </a-tag>
                                                    </div>
                                                </a-card>
                                            </div>
                                            <div
                                                v-else
                                                class="mt-3"
                                            >
                                                <div class="font-medium text-gray-900 text-sm mb-2">
                                                    {{ $t('common.doctor') || 'Doctor' }}
                                                </div>
                                                <div class="text-gray-400 italic text-sm flex items-center gap-2 p-2 bg-gray-50 rounded">
                                                    <UserOutlined />
                                                    {{ $t('appointments.no_doctor_assigned') || 'No doctor assigned' }}
                                                </div>
                                            </div>

                                            <!-- Room Information -->
                                            <div class="mt-2">
                                                <div
                                                    class="flex items-center justify-between"
                                                >
                                                    <div
                                                        class="font-medium text-gray-900 text-sm"
                                                    >
                                                        {{
                                                            $t(
                                                                "appointments.room"
                                                            ) || "Room"
                                                        }}
                                                    </div>
                                                    <a-button
                                                        type="text"
                                                        size="small"
                                                        @click="
                                                            handleSelectRoom(
                                                                item
                                                            )
                                                        "
                                                    >
                                                        <template #icon
                                                            ><EnvironmentOutlined
                                                        /></template>
                                                    </a-button>
                                                </div>
                                                <div
                                                    v-if="item.room"
                                                    class="flex items-center gap-2 mt-1"
                                                >
                                                    <a-tag
                                                        :color="
                                                            getRoomStatusColor(
                                                                item.room.status
                                                            )
                                                        "
                                                        class="text-xs"
                                                    >
                                                        {{ item.room.name }}
                                                    </a-tag>
                                                    <span
                                                        class="text-xs text-gray-500"
                                                        >{{
                                                            $t(
                                                                "appointments.floor"
                                                            ) || "Floor"
                                                        }}
                                                        {{
                                                            item.room.floor
                                                        }}</span
                                                    >
                                                </div>
                                                <span
                                                    v-else
                                                    class="text-gray-400 italic text-xs"
                                                    >{{
                                                        $t(
                                                            "appointments.no_room_assigned"
                                                        ) || "No room assigned"
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </a-list-item-meta>
                        </a-list-item>
                        </div>
                        
                        <!-- Expand/Collapse Button for Multiple Appointments -->
                        <div 
                            v-if="group.hasMultiple && !isPatientExpanded(group.patientXid)"
                            class="mt-2 mb-4 text-center"
                        >
                            <a-button
                                type="dashed"
                                size="small"
                                @click="togglePatientExpand(group.patientXid)"
                                class="w-full sm:w-auto"
                            >
                                <template #icon><DownOutlined /></template>
                                {{ $t('appointments.show_more') || 'Show More' }} 
                                ({{ group.count - 1 }} {{ $t('appointments.more_appointments') || 'more' }})
                            </a-button>
                        </div>
                        
                        <!-- Collapse Button -->
                        <div 
                            v-if="group.hasMultiple && isPatientExpanded(group.patientXid)"
                            class="mt-2 mb-4 text-center"
                        >
                            <a-button
                                type="dashed"
                                size="small"
                                @click="togglePatientExpand(group.patientXid)"
                                class="w-full sm:w-auto"
                            >
                                <template #icon><UpOutlined /></template>
                                {{ $t('appointments.show_less') || 'Show Less' }}
                            </a-button>
                        </div>
                    </template>
                </a-list>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal
                v-model:open="modalVisible"
                :title="modalTitle"
                :action="modalAction"
                :patient="modalPatient"
                @confirm="onConfirm"
                @cancel="onCancel"
                @editPatient="openPatientDrawerForAppointment(modalItem)"
            />
            <!-- Patient Add/Edit Drawer -->
            <AddEditPatient
                :visible="patientDrawerVisible"
                :formData="patientFormData"
                :url="patientUrl"
                addEditType="edit"
                :pageTitle="patientDrawerTitle"
                @addEditSuccess="fetchTodaysAppointments"
                @closed="closePatientDrawer"
            />

            <!-- Room Selection Modal -->
            <RoomSelectionModal
                :visible="roomSelectionVisible"
                :currentAppointment="currentAppointmentForRoom"
                @roomAssigned="handleRoomAssignment"
                @cancel="roomSelectionVisible = false"
            />

            <!-- Appointment Items Modal -->
            <AppointmentItemsModal
                :visible="itemsModalVisible"
                :appointment="currentAppointmentForItems"
                @saved="handleItemsSaved"
                @closed="handleItemsModalClosed"
            />

            <!-- Prescription Details Modal -->
            <PrescriptionDetailsModal
                :visible="prescriptionPreviewVisible"
                :prescription="currentAppointmentForPrescription?.prescription"
                @update:visible="(val) => (prescriptionPreviewVisible = val)"
                @deleted="onPrescriptionDeleted"
            />

            <!-- Doctor Info Modal -->
            <DoctorInfoModal
                :visible="doctorInfoModalVisible"
                :doctor="selectedDoctor"
                @update:visible="(val) => (doctorInfoModalVisible = val)"
                @closed="closeDoctorInfoModal"
            />
        </a-card>
    </div>
</template>

<script setup>
import { onMounted, ref, reactive, computed, onUnmounted } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import common from "../../../common/composable/common";
import StateWidget from "../../../common/components/common/card/StateWidget.vue";
import {
    UnorderedListOutlined,
    CheckCircleOutlined,
    CheckOutlined,
    ClockCircleOutlined,
    LoginOutlined,
    LogoutOutlined,
    EditOutlined,
    EyeOutlined,
    MoreOutlined,
    BellOutlined,
    CalendarOutlined,
    ReloadOutlined,
    DeleteOutlined,
    UserOutlined,
    PhoneOutlined,
    ShoppingCartOutlined,
    EnvironmentOutlined,
    MedicineBoxOutlined,
    DownOutlined,
    UpOutlined,
} from "@ant-design/icons-vue";
import { message, notification } from "ant-design-vue";
import AddEditPatient from "../patient-management/patients/AddEdit.vue";
import RoomSelectionModal from "../room-management/rooms/components/RoomSelectionModal.vue";
import AppointmentItemsModal from "./components/AppointmentItemsModal.vue";
import PrescriptionDetailsModal from "../patient-management/patients/components/modals/PrescriptionDetailsModal.vue";
import DoctorInfoModal from "./components/DoctorInfoModal.vue";
import Appointment from "../../components/appointment/index.vue";
import AppointmentDetails from "../../components/appointment/AppointmentDetails.vue";
import ConfirmationModal from "../../components/appointment/ConfirmationModal.vue";
import crud from "../../../common/composable/crud";
import fields from "./fields";

// WebSocket
const echo = window.Echo;

// Import crud and fields for appointment add/edit
const {
    addEditUrl,
    initData,
    rooms: fieldRooms,
    treatmentTypes,
    getPrefetchData,
} = fields();

const crudVariables = crud();

// Appointment viewing state
const appointmentViewModalVisible = ref(false);
const selectedAppointmentForView = ref({});

// Handler for appointment add/edit success
const handleAddEditSuccess = (xid) => {
    fetchTodaysAppointments();
    message.success(t("common.success"));
};

// Function to view appointment details
const viewAppointment = (appointment) => {
    selectedAppointmentForView.value = appointment;
    appointmentViewModalVisible.value = true;
};

// Function to close appointment view modal
const onCloseAppointmentViewModal = () => {
    appointmentViewModalVisible.value = false;
    selectedAppointmentForView.value = {};
};

// Function to edit appointment
const editAppointment = (appointment) => {
    // Parse appointment_date to get day, time, month, year
    const dateObj = new Date(appointment.appointment_date);
    const day = dateObj.getDate();
    const month = dateObj.getMonth(); // 0-based
    const year = dateObj.getFullYear();
    // Format time as h:mma (e.g., 9:30am)
    let hours = dateObj.getHours();
    let minutes = dateObj.getMinutes();
    const ampm = hours >= 12 ? "pm" : "am";
    let displayHours = hours % 12;
    displayHours = displayHours ? displayHours : 12; // 0 => 12
    const displayMinutes = minutes.toString().padStart(2, "0");
    const timeString = `${displayHours}:${displayMinutes}${ampm}`;

    crudVariables.formData.value = {
        appointment_date: appointment.appointment_date,
        duration: appointment.duration,
        treatment_details: appointment.treatment_details,
        status: appointment.status,
        xid: appointment.xid,
        patient_id: appointment.patient?.xid,
        doctor_id: appointment.doctor?.xid,
        selectedDate: day,
        selectedTimeSlot: timeString,
        currentMonth: month,
        currentYear: year,
        room_id: appointment.room?.xid,
        treatment_type_id: appointment.treatment_type?.xid,
    };
    crudVariables.addEditType.value = "edit";
    crudVariables.addEditVisible.value = true;
};

// Initialize crud variables
crudVariables.crudUrl.value = addEditUrl;
crudVariables.langKey.value = "appointments";
crudVariables.initData.value = { ...initData };
crudVariables.formData.value = { ...initData };

// Local edit state for inline editing (reactive so property additions are tracked)
const editing = reactive({});
const editLoading = reactive({});

const startEdit = (item, field) => {
    if (!item || !field) return;
    // initialize editing object for this xid
    if (!editing[item.xid]) {
        // assign a new object for this xid
        editing[item.xid] = {};
    }
    editing[item.xid][field] = item[field] || "";
};

const cancelEdit = (item, field) => {
    if (!item || !field) return;
    if (editing[item.xid]) {
        delete editing[item.xid][field];
        // remove object if empty
        if (Object.keys(editing[item.xid]).length === 0) {
            delete editing[item.xid];
        }
    }
};

const saveEdit = async (item, field) => {
    if (!item || !field) return;
    const value = editing[item.xid] && editing[item.xid][field];
    if (value === undefined) return;

    try {
        if (!editLoading[item.xid]) editLoading[item.xid] = {};
        editLoading[item.xid][field] = true;

        // Mark as being updated locally to prevent WebSocket notifications
        updatingLocally.value.add(item.xid);
        console.log('🔄 Starting local edit for:', item.xid, field);

        const payload = { [field]: value };
        const url = `appointments/${item.xid}`;
        const res = await axiosAdmin.put(url, payload);

        // Normalize response body
        const resBody = res && res.data ? res.data : res;

        // Try to find an updated object in common response shapes
        const updated = resBody && (resBody.data || resBody);

        // If updated is an object with more than just xid, merge it into the item and sync the appointments array
        if (
            updated &&
            typeof updated === "object" &&
            Object.keys(updated).length > 1
        ) {
            Object.keys(updated).forEach((k) => (item[k] = updated[k]));
            const idx = appointments.value.findIndex((a) => a.xid === item.xid);
            if (idx >= 0) {
                Object.keys(updated).forEach((k) => {
                    appointments.value[idx][k] = updated[k];
                });
            }
        } else {
            // Backend returned only minimal info (e.g. { xid: '...' }) — apply the edited field locally as fallback
            item[field] = value;
            const idx = appointments.value.findIndex((a) => a.xid === item.xid);
            if (idx >= 0) {
                appointments.value[idx][field] = value;
            }
        }

        message.success(t("appointments.updated"));
        cancelEdit(item, field);
    } catch (e) {
        console.error("Error saving appointment edit", e);
        message.error(t("common.error"));
    } finally {
        if (editLoading[item.xid]) {
            editLoading[item.xid][field] = false;
        }
        
        // Remove from local update tracking after a delay
        // to ensure WebSocket event has been processed
        setTimeout(() => {
            updatingLocally.value.delete(item.xid);
            console.log('✅ Removed from local update tracking:', item.xid);
        }, 2000);
    }
};

const loading = ref(false);
const activeTab = ref("flow");
const appointments = ref([]); // This should be populated with actual appointment data
const { formatTime, formatDate, dayjsObject, permsArray, user } = common();
const { t } = useI18n();
const patientUrl = ref("");

// Real-time timer for waiting times
const currentTime = ref(Date.now());
const timerInterval = ref(null);

// Router for navigation to patient details
const router = useRouter();
const navigateToPatientDetails = (item) => {
    const patientXid = item?.patient?.xid || item?.xid;
    if (!patientXid) return;
    router.push({ name: "admin.patients.detail", params: { id: patientXid } });
};

const navigateToPOS = (item) => {
    const patientXid = item?.patient?.xid;
    if (!patientXid) return;
    router.push({
        name: "admin.sales.pos.patient",
        params: { patientId: patientXid },
    });
};

// Compute a flow date to display on the right side of tabs
const flowDate = computed(() => {
    const list = appointments.value || [];
    let dateToUse = null;

    if (list.length > 0) {
        const first = list[0];
        dateToUse =
            first.appointment_date ||
            first.next_appointment?.appointment_date ||
            first.date;
    }

    if (!dateToUse) {
        // fallback to today
        return dayjsObject().format("DD-MM-YYYY");
    }

    try {
        return dayjsObject(dateToUse).format("DD-MM-YYYY");
    } catch (e) {
        return dayjsObject().format("DD-MM-YYYY");
    }
});

// Calculate waiting time for checked-in patients
const calculateWaitingTime = (item) => {
    if (!item || !item.checkin_datetime) return null;

    try {
        const checkinTime = dayjsObject(item.checkin_datetime);
        const now = dayjsObject(currentTime.value);
        const diffMinutes = now.diff(checkinTime, "minute");
        const diffSeconds = now.diff(checkinTime, "second");

        // If waiting too long, show more precise time with seconds
        if (isWaitingTooLong(item)) {
            if (diffMinutes < 60) {
                const remainingSeconds = diffSeconds % 60;
                return `${diffMinutes}m ${remainingSeconds}s`;
            } else {
                const hours = Math.floor(diffMinutes / 60);
                const minutes = diffMinutes % 60;
                const remainingSeconds = diffSeconds % 60;
                return `${hours}h ${minutes}m ${remainingSeconds}s`;
            }
        } else {
            // Normal display without seconds
            if (diffMinutes < 60) {
                return `${diffMinutes}m`;
            } else {
                const hours = Math.floor(diffMinutes / 60);
                const minutes = diffMinutes % 60;
                return `${hours}h ${minutes}m`;
            }
        }
    } catch (e) {
        return null;
    }
};

// Check if patient has been waiting longer than appointment duration
const isWaitingTooLong = (item) => {
    if (!item || !item.checkin_datetime) return false;

    try {
        const checkinTime = dayjsObject(item.checkin_datetime);
        const now = dayjsObject(currentTime.value);
        const waitingMinutes = now.diff(checkinTime, "minute");

        // Get appointment duration from treatment type or default to 60 minutes
        const appointmentDuration =
            parseInt(item.duration) ||
            parseInt(item.treatment_type?.duration_minutes) ||
            60;

        return waitingMinutes > appointmentDuration;
    } catch (e) {
        return false;
    }
};

const formatTimeRange = (item) => {
    if (!item) return "";
    const apptDate =
        item.appointment_date ||
        item.next_appointment?.appointment_date ||
        item.date;
    if (!apptDate) return item.duration ? `${item.duration} min` : "";

    try {
        const start = dayjsObject(apptDate);
        const duration =
            parseInt(item.duration) ||
            parseInt(item.treatment_type?.duration_minutes) ||
            60;
        const end = start.add(duration, "minute");

        return `${formatTime(start)} - ${formatTime(end)}`;
    } catch (e) {
        return item.duration ? `${item.duration} min` : "";
    }
};

// Helpers for action button
const isCompleted = (item) => {
    return item && item.checkout_datetime;
};

const getNextStatus = (item) => {
    // Flow: checked_in -> in_progress -> completed -> checked_out
    // Derive current status from datetime fields
    if (!item) return "checked_in";
    if (item.checkout_datetime) return null; // Already checked out, no next status
    if (item.completed_datetime) return "checked_out";
    if (item.in_progress_datetime) return "completed";
    if (item.checkin_datetime || item.arrive_datetime) return "in_progress";
    return "checked_in"; // Not yet checked in
};

// Determine a normalized status for an item (reuse in filters and UI)
const getStatusForItem = (item) => {
    if (!item) return "scheduled";
    // Status is derived from datetime fields in order of progression
    if (item.checkout_datetime) return "checked_out";
    if (item.completed_datetime) return "completed";
    if (item.in_progress_datetime) return "in_progress";
    if (item.checkin_datetime) return "checked_in";
    if (item.arrive_datetime) return "checked_in";
    return "scheduled";
};

const getStatusText = (item) => {
    const s = getStatusForItem(item);
    switch (s) {
        case "not_arrived":
            return t("appointments.not_arrived") || "Not arrived";
        case "scheduled":
            return t("appointments.scheduled") || "Scheduled";
        case "checked_in":
            return t("appointments.checked_in") || "Checked in";
        case "checked_out":
            return t("appointments.checked_out") || "Checked out";
        case "in_progress":
            return t("appointments.in_progress") || "In Progress";
        case "completed":
            return t("appointments.completed") || "Completed";
        case "cancelled":
            return t("appointments.cancelled") || "Cancelled";
        default:
            return s;
    }
};

const getStatusColor = (item) => {
    const s = getStatusForItem(item);
    // Ant tag colors: success, processing, warning, error, default
    switch (s) {
        case "not_arrived":
            return "orange";
        case "scheduled":
            return "default";
        case "checked_in":
            return "processing";
        case "in_progress":
            return "cyan";
        case "completed":
            return "blue";
        case "checked_out":
            return "green";
        case "cancelled":
            return "red";
        default:
            return "default";
    }
};

const getActionLabel = (item) => {
    // Derive status from datetime fields
    if (!item) return t("appointments.check_in");
    if (item.checkout_datetime) return t("appointments.checked_out");
    if (item.completed_datetime) return t("appointments.check_out");
    if (item.in_progress_datetime) return t("appointments.finish_appointment");
    if (item.checkin_datetime || item.arrive_datetime)
        return t("appointments.start_appointment");
    return t("appointments.check_in");
};

const getActionIcon = (item) => {
    // Derive status from datetime fields
    if (!item) return LoginOutlined;
    if (item.checkout_datetime) return CheckOutlined;
    if (item.completed_datetime) return LogoutOutlined;
    if (item.in_progress_datetime) return CheckCircleOutlined;
    if (item.checkin_datetime || item.arrive_datetime)
        return ClockCircleOutlined;
    return LoginOutlined;
};

// Search state for client-side filtering
const searchString = ref("");

const onSearch = (value) => {
    // value may be empty when cleared; we already bind v-model to searchString
    searchString.value = typeof value === "string" ? value : searchString.value;
};

// Track which patient groups are expanded to show all appointments
const expandedPatients = ref(new Set());

const togglePatientExpand = (patientXid) => {
    if (expandedPatients.value.has(patientXid)) {
        expandedPatients.value.delete(patientXid);
    } else {
        expandedPatients.value.add(patientXid);
    }
};

const isPatientExpanded = (patientXid) => {
    return expandedPatients.value.has(patientXid);
};

// Track appointments being updated locally to skip notifications
const updatingLocally = ref(new Set());

const toggleNextStatus = async (item) => {
    // don't toggle if already checked out
    if (item && item.checkout_datetime) return;
    const next = getNextStatus(item);
    if (!next) return;

    try {
        // ensure loading flag exists on the item
        item.loading = true;
        
        // Mark as being updated locally BEFORE making the request
        updatingLocally.value.add(item.xid);
        console.log('🔄 Starting local update for:', item.xid);

        const url = `appointments/${item.xid}/update-status`;
        const payload = { flow_status: next };

        const res = await axiosAdmin.put(url, payload);

        if (res && res.data && res.status === "success") {
            // update the item in-place with fresh data from server
            const updated = res.data;
            // copy properties from updated to item
            Object.keys(updated).forEach((key) => {
                item[key] = updated[key];
            });
            // show success message based on target status
            try {
                if (next === "checked_in")
                    message.success(t("appointments.patient_checked_in"));
                else if (next === "in_progress")
                    message.success(t("appointments.in_progress_started"));
                else if (next === "completed")
                    message.success(t("appointments.appointment_completed"));
                else if (next === "checked_out")
                    message.success(t("appointments.patient_checked_out"));
                else message.success(t("common.success"));
            } catch (e) {
                /* ignore if message not available */
            }
        } else {
            console.error(
                "Unexpected response updating appointment status",
                res
            );
            try {
                message.error(t("common.error"));
            } catch (e) {}
        }
    } catch (e) {
        console.error("Error updating appointment status:", e);
        try {
            message.error(t("common.error"));
        } catch (er) {}
    } finally {
        item.loading = false;
        
        // Remove from local update tracking after a longer delay
        // to ensure WebSocket event has been processed
        setTimeout(() => {
            updatingLocally.value.delete(item.xid);
            console.log('✅ Removed from local update tracking:', item.xid);
        }, 2000); // Increased to 2 seconds
    }
};

// Returns inline style object for action button based on appointment status
const getActionButtonStyle = (item) => {
    // Derive status from datetime fields
    // default: blue primary
    const styles = {
        background: "#1890ff",
        borderColor: "#1890ff",
        color: "#fff",
    };

    if (!item) return styles;

    // If already checked out, make it neutral/disabled-looking
    if (item.checkout_datetime) {
        return {
            background: "#d9d9d9",
            borderColor: "#d9d9d9",
            color: "#8c8c8c",
        };
    }

    // Completed: green for Check Out
    if (item.completed_datetime) {
        return {
            background: "#52c41a",
            borderColor: "#52c41a",
            color: "#fff",
        };
    }

    // In Progress: blue for Complete
    if (item.in_progress_datetime) {
        return styles;
    }

    // Checked In: cyan for Start Progress
    if (item.checkin_datetime || item.arrive_datetime) {
        return {
            background: "#13c2c2",
            borderColor: "#13c2c2",
            color: "#fff",
        };
    }

    // Not checked in yet: amber/warning for Check In
    return {
        background: "#faad14",
        borderColor: "#faad14",
        color: "#fff",
    };
};

const fetchTodaysAppointments = () => {
    loading.value = true;
    axiosAdmin
        .get("appointments/today")
        .then((response) => {
            const data = response.data;

            if (data) {
                appointments.value = data;
            }
        })
        .catch((error) => {
            console.error("Error fetching today's appointment stats:", error);
        })
        .finally(() => {
            loading.value = false;
        });
};

// Handle real-time appointment updates from WebSocket
const handleAppointmentUpdate = (data) => {
    console.log('📡 Received appointment update:', data);
    
    const { appointment, action } = data;
    
    if (!appointment || !appointment.xid) {
        console.warn('⚠️ Invalid appointment data received');
        return;
    }
    
    // Skip if this appointment is being updated by current user
    if (updatingLocally.value.has(appointment.xid)) {
        console.log('⏭️ Skipping notification - local update in progress');
        // Still update the data, just skip the notification
        const index = appointments.value.findIndex(
            (a) => a.xid === appointment.xid
        );
        if (index !== -1 && action !== 'deleted') {
            Object.keys(appointment).forEach((key) => {
                appointments.value[index][key] = appointment[key];
            });
        }
        return;
    }
    
    console.log(`🔄 Processing ${action} for appointment:`, appointment.xid);
    
    const index = appointments.value.findIndex(
        (a) => a.xid === appointment.xid
    );
    
    if (action === 'deleted') {
        // Remove appointment from list
        if (index !== -1) {
            appointments.value.splice(index, 1);
            console.log('✅ Appointment removed from list');
            
            // Show notification
            notification.info({
                message: t('appointments.appointment_deleted'),
                description: `${appointment.patient?.user?.name || 'Patient'} - ${formatTime(appointment.appointment_date)}`,
                duration: 4,
            });
        }
    } else if (action === 'created') {
        // Add new appointment if not already in list
        if (index === -1) {
            appointments.value.push(appointment);
            console.log('✅ New appointment added to list');
            
            // Show notification
            notification.success({
                message: t('appointments.new_appointment'),
                description: `${appointment.patient?.user?.name || 'Patient'} - ${formatTime(appointment.appointment_date)}`,
                duration: 4,
                onClick: () => scrollToAppointment(appointment.xid),
            });
        }
    } else {
        // Update existing appointment (updated or status_changed)
        if (index !== -1) {
            // Preserve reactivity by updating properties individually
            Object.keys(appointment).forEach((key) => {
                appointments.value[index][key] = appointment[key];
            });
            console.log('✅ Appointment updated in list');
            
            // Add highlight effect
            highlightAppointment(appointment.xid);
            
            // Show notification
            const statusText = getStatusText(appointment);
            notification.info({
                message: action === 'status_changed' 
                    ? t('appointments.status_updated') 
                    : t('appointments.appointment_updated'),
                description: `${appointment.patient?.user?.name || 'Patient'} - ${statusText}\n (${t('messages.click_to_view')} )`,
                duration: 4,
                onClick: () => scrollToAppointment(appointment.xid),
            });
        } else {
            // Appointment not in current list, might be newly relevant
            appointments.value.push(appointment);
            console.log('✅ Appointment added to list (was not present)');
            
            // Show notification
            notification.success({
                message: t('appointments.new_appointment'),
                description: `${appointment.patient?.user?.name || 'Patient'} - ${formatTime(appointment.appointment_date)}\n${t('common.click_to_view') || 'Click to view'}`,
                duration: 4,
                onClick: () => scrollToAppointment(appointment.xid),
            });
        }
    }
};

// Highlight appointment with animation
const highlightedAppointments = ref(new Set());

const highlightAppointment = (xid) => {
    highlightedAppointments.value.add(xid);
    
    // Remove highlight after 3 seconds
    setTimeout(() => {
        highlightedAppointments.value.delete(xid);
    }, 3000);
};

// Scroll to specific appointment
const scrollToAppointment = (xid) => {
    const element = document.querySelector(`[data-appointment-xid="${xid}"]`);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Highlight after scrolling
        highlightAppointment(xid);
    }
};

onMounted(() => {
    getPrefetchData();
    fetchTodaysAppointments();

    // Start real-time timer for waiting times
    // Update every second to show seconds for delayed patients
    timerInterval.value = setInterval(() => {
        currentTime.value = Date.now();
    }, 1000); // Update every second

    // Listen for add appointment event from parent
    window.addEventListener("add-appointment", () => {
        crudVariables.addItem();
    });
    
    // Subscribe to WebSocket channel for real-time appointment updates
    const companyId = user.value?.company_id;
    console.log('🔌 Setting up WebSocket subscription for company:', companyId);
    
    if (echo && companyId) {
        const channel = echo.private(`company.${companyId}`);
        console.log('✅ Subscribed to channel: company.' + companyId);
        
        channel.listen('.appointment.updated', (data) => {
            console.log('📨 Event received on channel');
            handleAppointmentUpdate(data);
        });
    } else {
        console.error('❌ Failed to subscribe: Echo or companyId not available', { echo: !!echo, companyId });
    }
});

// Cleanup timer on unmount
onUnmounted(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
    
    // Cleanup WebSocket subscription
    const companyId = user.value?.company_id;
    if (echo && companyId) {
        echo.leave(`company.${companyId}`);
    }
});

// Confirmation modal state and handlers
const modalVisible = ref(false);
const modalAction = ref(null);
const modalItem = ref(null);
const modalPatient = reactive({
    fullName: "",
    phone: "",
    address: "",
    ssn: "",
});

// Patient AddEdit drawer state
const patientDrawerVisible = ref(false);
const patientDrawerTitle = ref("");
const patientFormData = reactive({});

// Room selection state
const roomSelectionVisible = ref(false);
const currentAppointmentForRoom = ref(null);

// Items modal state
const itemsModalVisible = ref(false);
const currentAppointmentForItems = ref(null);

// Prescription preview modal state
const prescriptionPreviewVisible = ref(false);
const currentAppointmentForPrescription = ref(null);

// Doctor info modal state
const doctorInfoModalVisible = ref(false);
const selectedDoctor = ref(null);

const openPatientDrawerForAppointment = (appt) => {
    if (!appt) return;
    modalItem.value = appt; // keep reference

    // set drawer title
    patientUrl.value = `patients/${appt.patient?.xid}`;
    patientDrawerTitle.value = t("patients.edit");
    patientDrawerVisible.value = true;
};

const closePatientDrawer = () => {
    patientDrawerVisible.value = false;
    patientUrl.value = "";
};

// Room selection handlers
const handleSelectRoom = (appointment) => {
    currentAppointmentForRoom.value = appointment;
    roomSelectionVisible.value = true;
};

const handleRoomAssignment = async ({ room, appointment }) => {
    try {
        loading.value = true;

        // Mark as being updated locally to prevent WebSocket notifications
        updatingLocally.value.add(appointment.xid);
        console.log('🔄 Starting room assignment for:', appointment.xid);

        const url = `appointments/${appointment.xid}`;
        const payload = {
            room_id: room.xid,
        };

        const response = await axiosAdmin.put(url, payload);

        if (response && response.data) {
            // Update the appointment data in the list
            const appointmentIndex = appointments.value.findIndex(
                (appt) => appt.xid === appointment.xid
            );

            if (appointmentIndex !== -1) {
                appointments.value[appointmentIndex].room = room;
                appointments.value[appointmentIndex].x_room_id = room.xid;
            }

            message.success(
                t("appointments.room_assigned_successfully") ||
                    "Room assigned successfully"
            );
            roomSelectionVisible.value = false;
            currentAppointmentForRoom.value = null;
        }
    } catch (error) {
        console.error("Error updating appointment room:", error);
        message.error(t("common.error") || "Failed to assign room");
    } finally {
        loading.value = false;
        
        // Remove from local update tracking after a delay
        setTimeout(() => {
            updatingLocally.value.delete(appointment.xid);
            console.log('✅ Removed from local update tracking (room assignment):', appointment.xid);
        }, 2000);
    }
};

const getRoomStatusColor = (status) => {
    const mapping = {
        available: "default",
        occupied: "blue",
        reserved: "red",
        maintenance: "orange",
        cleaning: "yellow",
    };
    return mapping[status?.toLowerCase()] || "default";
};

// Items modal handlers
const handleOpenItemsModal = (appointment) => {
    currentAppointmentForItems.value = appointment;
    itemsModalVisible.value = true;
};

const handleItemsSaved = (data) => {
    console.log("Items saved for appointment:", data);
    // Update the appointment with items info
    // Items will be stored and retrieved when modal reopens
    // Items will be finalized when appointment status reaches 'completed'
    fetchTodaysAppointments(); // Refresh the list
};

const handleItemsModalClosed = () => {
    itemsModalVisible.value = false;
    currentAppointmentForItems.value = null;
};

// Prescription preview modal handlers
const hasPrescriptions = (appointment) => {
    // Check if appointment has prescription data
    return appointment?.prescription && appointment.prescription !== null;
};

const openPrescriptionPreviewModal = (appointment) => {
    if (!appointment?.patient?.xid || !appointment?.prescription) return;

    currentAppointmentForPrescription.value = appointment;
    prescriptionPreviewVisible.value = true;
};

const onPrescriptionDeleted = () => {
    prescriptionPreviewVisible.value = false;
    currentAppointmentForPrescription.value = null;
    // Refresh appointments to update prescription status
    fetchTodaysAppointments();
};

// Doctor info modal handlers
const openDoctorInfoModal = (doctor) => {
    selectedDoctor.value = doctor;
    doctorInfoModalVisible.value = true;
};

const closeDoctorInfoModal = () => {
    doctorInfoModalVisible.value = false;
    selectedDoctor.value = null;
};

const modalTitle = computed(() => {
    if (!modalAction.value) return "";
    if (modalAction.value === "check_in")
        return t("appointments.confirm_check_in") || "Confirm check in";
    if (modalAction.value === "check_out")
        return t("appointments.confirm_check_out") || "Confirm check out";
    return t("common.confirm") || "Confirm";
});

const onActionClick = (item) => {
    // Determine action based on next status
    const next = getNextStatus(item);
    if (!next) return;

    modalAction.value = next;
    modalItem.value = item;
    // For check-in, populate modalPatient with real data if available, otherwise use dummy
    modalPatient.fullName = item.patient?.user?.name
        ? `${item.patient.user.name} ${
              item.patient.user.last_name || ""
          }`.trim()
        : modalPatient.fullName;
    modalPatient.phone = item.patient?.user?.phone || modalPatient.phone;
    modalPatient.address = item.patient?.user?.address || modalPatient.address;
    modalPatient.ssn = item.patient?.user?.ssn || modalPatient.ssn;

    modalVisible.value = true;
};

const onConfirm = async () => {
    modalVisible.value = false;
    if (modalItem.value) {
        await toggleNextStatus(modalItem.value);
    }
    modalItem.value = null;
    modalAction.value = null;
};

const onCancel = () => {
    modalVisible.value = false;
    modalItem.value = null;
    modalAction.value = null;
};

// Compute filtered appointments based on selected tab and search string
const filteredAppointments = computed(() => {
    const list = appointments.value || [];

    // First, apply the tab filter to get a base list
    let baseList = list;

    // Flow/tab: keep all (baseList remains list)
    if (activeTab.value && activeTab.value !== "flow") {
        if (activeTab.value === "checked_in") {
            baseList = list.filter((i) => getStatusForItem(i) === "checked_in");
        } else if (activeTab.value === "in_progress") {
            baseList = list.filter(
                (i) => getStatusForItem(i) === "in_progress"
            );
        } else if (activeTab.value === "completed") {
            baseList = list.filter((i) => getStatusForItem(i) === "completed");
        } else if (activeTab.value === "checked_out") {
            baseList = list.filter(
                (i) => getStatusForItem(i) === "checked_out"
            );
        }
    }

    // Then apply search filter if present (applies on top of tab filter)
    const q = (searchString.value || "").toString().trim().toLowerCase();
    if (!q) return baseList;

    const matchesQuery = (i) => {
        // patient may be nested under patient.user
        const user = i?.patient?.user || i?.patient || {};
        const fullName = [user.first_name, user.name, user.last_name]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();
        const phone = (user.phone || "").toString().toLowerCase();

        return fullName.includes(q) || phone.includes(q);
    };

    return baseList.filter(matchesQuery);
});

// Group appointments by patient and sort within each group
const groupedAppointments = computed(() => {
    const list = filteredAppointments.value || [];
    const groups = new Map();
    
    // Helper to determine priority (lower number = higher priority)
    const getAppointmentPriority = (appt) => {
        const status = getStatusForItem(appt);
        // Priority order: in_progress > checked_in > scheduled > completed > checked_out
        if (status === 'in_progress') return 1;
        if (status === 'checked_in') return 2;
        if (status === 'scheduled') return 3;
        if (status === 'completed') return 4;
        if (status === 'checked_out') return 5;
        return 6;
    };
    
    // Group by patient
    list.forEach(appt => {
        const patientXid = appt.patient?.xid;
        if (!patientXid) return;
        
        if (!groups.has(patientXid)) {
            groups.set(patientXid, []);
        }
        groups.get(patientXid).push(appt);
    });
    
    // Sort appointments within each group
    const result = [];
    groups.forEach((appts, patientXid) => {
        // Sort by priority first, then by appointment_date
        const sorted = [...appts].sort((a, b) => {
            const priorityDiff = getAppointmentPriority(a) - getAppointmentPriority(b);
            if (priorityDiff !== 0) return priorityDiff;
            
            // If same priority, sort by appointment_date
            return new Date(a.appointment_date) - new Date(b.appointment_date);
        });
        
        result.push({
            patientXid,
            patient: sorted[0].patient,
            appointments: sorted,
            currentAppointment: sorted[0], // First one is always current
            hasMultiple: sorted.length > 1,
            count: sorted.length
        });
    });
    
    // Sort groups by the priority of their current appointment
    return result.sort((a, b) => {
        const priorityDiff = getAppointmentPriority(a.currentAppointment) - getAppointmentPriority(b.currentAppointment);
        if (priorityDiff !== 0) return priorityDiff;
        return new Date(a.currentAppointment.appointment_date) - new Date(b.currentAppointment.appointment_date);
    });
});

// Counts for tabs
const totalCount = computed(() => (appointments.value || []).length);
const checkedInCount = computed(
    () =>
        (appointments.value || []).filter(
            (i) => getStatusForItem(i) === "checked_in"
        ).length
);
const inProgressCount = computed(
    () =>
        (appointments.value || []).filter(
            (i) => getStatusForItem(i) === "in_progress"
        ).length
);
const completedCount = computed(
    () =>
        (appointments.value || []).filter(
            (i) => getStatusForItem(i) === "completed"
        ).length
);
const checkedOutCount = computed(
    () =>
        (appointments.value || []).filter(
            (i) => getStatusForItem(i) === "checked_out"
        ).length
);
</script>

<style scoped>
.appointment-manager {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
        "Helvetica Neue", Arial, sans-serif;
}

/* Custom appointment list styling */
:deep(.appointment-list) {
    background: white;
    border-radius: 8px;
}

:deep(.appointment-item) {
    border: 1px solid #f0f0f0;
    border-radius: 8px;
    margin-bottom: 16px;
    padding: 16px;
    transition: all 0.3s ease;
}

:deep(.appointment-item:hover) {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #1890ff;
}

/* Highlight patients waiting too long */
:deep(.appointment-item.waiting-too-long) {
    border-left: 4px solid #ff4d4f;
    background: linear-gradient(90deg, #fff1f0 0%, #ffffff 100%);
}

:deep(.appointment-item.waiting-too-long:hover) {
    border-color: #ff4d4f;
    box-shadow: 0 4px 12px rgba(255, 77, 79, 0.15);
}

/* Highlight updated appointments */
:deep(.appointment-item.highlight-update) {
    animation: highlightPulse 1s ease-in-out 3;
    border-left: 4px solid #52c41a;
}

@keyframes highlightPulse {
    0%, 100% {
        background-color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
    }
    50% {
        background-color: #f6ffed;
        box-shadow: 0 4px 16px rgba(82, 196, 26, 0.25);
        transform: scale(1.01);
    }
}

/* Animate pulse for delay tag */
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

:deep(.appointment-item .ant-list-item-meta-title) {
    margin-bottom: 8px;
}

:deep(.appointment-item .ant-list-item-meta-description) {
    color: #666;
}

/* Custom scrollbar for lists */
:deep(.ant-list) {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f9fafb;
}

:deep(.ant-list::-webkit-scrollbar) {
    width: 8px;
}

:deep(.ant-list::-webkit-scrollbar-track) {
    background: #f9fafb;
}

:deep(.ant-list::-webkit-scrollbar-thumb) {
    background-color: #d1d5db;
    border-radius: 4px;
}

/* Custom tab styling */
:deep(.ant-tabs-tab) {
    font-weight: 500;
}

/* Custom card styling */
:deep(.ant-card) {
    border-radius: 8px;
}

/* Custom button styling */
:deep(.ant-btn-primary) {
    background: #1890ff;
    border-color: #1890ff;
}

/* Status-specific styling */
.status-not-arrived {
    border-left: 4px solid #faad14;
}

.status-checked-in {
    border-left: 4px solid #1890ff;
}

.status-in-progress {
    border-left: 4px solid #13c2c2;
}

.status-completed {
    border-left: 4px solid #1890ff;
}

.status-checked-out {
    border-left: 4px solid #52c41a;
}

.status-waitlist {
    border-left: 4px solid #722ed1;
}

.status-cancelled {
    border-left: 4px solid #ff4d4f;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .appointment-manager {
        padding: 0 16px;
    }

    :deep(.appointment-item) {
        padding: 12px;
    }

    :deep(.ant-list-item-action) {
        margin-top: 12px;
    }
}

/* Animation for check-in/check-out actions */
.appointment-action-loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Highlight urgent appointments */
.priority-urgent {
    background: linear-gradient(90deg, #fff2e8 0%, #ffffff 100%);
    border-left: 4px solid #ff7a45;
}

.priority-high {
    background: linear-gradient(90deg, #fff7e6 0%, #ffffff 100%);
    border-left: 4px solid #ffa940;
}
</style>
