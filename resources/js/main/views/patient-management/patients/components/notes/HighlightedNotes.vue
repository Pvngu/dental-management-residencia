<template>
    <div class="mt-4">
        <a-spin :spinning="loading">
            <a-alert v-if="loading" :message="$t('patient_notes.loading')" />
            <div v-if="allNotes && allNotes.length > 0">
                <div>
                    <a-alert
                        v-for="note in allNotes"
                        :key="note.xid"
                        :type="note.is_highlighted ? getNoteTypeColor(note.note_type) : 'info'"
                        :class="[
                            'mb-4!',
                            note.is_highlighted ? 'border-2 border-yellow-400' : 'opacity-75'
                        ]"
                        :show-icon="false"
                    >
                        <template #message>
                            <div style="margin-right: 44px;">
                                <strong>{{ note.title }}</strong>
                                <a-tag size="small" class="ml-2">{{ getNoteTypeLabel(note.note_type) }}</a-tag>
                                <a-tag v-if="note.is_highlighted" color="gold" size="small" class="ml-1">
                                    <template #icon>
                                        <StarFilled />
                                    </template>
                                    {{ $t('patient_notes.highlighted') }}
                                </a-tag>
                                <a-tag v-if="note.is_private" color="red" size="small" class="ml-1">
                                    {{ $t('patient_notes.private') }}
                                </a-tag>
                            </div>
                        </template>
                        <template #description>
                            <a-typography-paragraph 
                                style="margin-bottom: 0;" 
                                :ellipsis="{
                                    rows: 6,
                                    expandable: true,
                                }"
                                :content="note.content"
                            />
                        </template>
                        <template #closeText>
                            <small class="absolute top-6 right-5">
                                {{ formatDate(note.created_at) }}
                                <br>
                                <span class="text-xs">{{ note.user?.name || 'Unknown' }}</span>
                            </small>
                        </template>
                    </a-alert>
                </div>
            </div>
        </a-spin>
    </div>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue';
import { PushpinOutlined, StarFilled } from '@ant-design/icons-vue';
import common from '../../../../../../common/composable/common';
import fields from "./fields";

export default {
    components: {
        PushpinOutlined,
        StarFilled
    },
    props: {
        patientId: {
            type: String,
            required: true
        },
        refreshTrigger: {
            type: Boolean,
            default: false
        }
    },
    setup(props) {
        const { formatDate } = common();
        const { noteTypes } = fields();
        const loading = ref(true);
        const allNotes = ref([]);
    // const showNotes = ref(true);

        const fetchHighlightedNotes = () => {
            loading.value = true;
            
            // Get all notes for the patient, sorted by highlighted status (highlighted first)
            const url = `patient-notes?fields=id,xid,title,content,note_type,is_private,is_highlighted,created_at,user{xid,name}&filters=patient_id eq "${props.patientId}"&hashable=${props.patientId}&orderBy=is_highlighted desc,created_at desc`;
            
            window.axiosAdmin.get(url)
                .then(response => {
                    allNotes.value = response.data;
                })
                .catch(error => {
                    console.error('Error fetching notes:', error);
                })
                .finally(() => {
                    loading.value = false;
                });
        };

        const getNoteTypeLabel = (key) => {
            const noteType = noteTypes.value.find(type => type.key === key);
            return noteType ? noteType.value : key;
        };

        const getNoteTypeColor = (key) => {
            const colorMap = {
                'general': 'info',
                'medical': 'error',
                'treatment': 'warning',
                'insurance': 'success',
                'administrative': 'info',
                'billing': 'warning'
            };
            return colorMap[key] || 'info';
        };

        onMounted(() => {
            fetchHighlightedNotes();
        });

        // Watch for changes in refreshTrigger to reload data
        watch(() => props.refreshTrigger, (newVal, oldVal) => {
            if (newVal !== oldVal) {
                fetchHighlightedNotes();
            }
        });

        return {
            loading,
            allNotes,
            formatDate,
            // showNotes,
            getNoteTypeLabel,
            getNoteTypeColor
        };
    }
}
</script>
