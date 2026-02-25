<template>
    <a-space
        :style="isClickable ? 'cursor: pointer;' : ''"
        @click="isClickable ? handleClick() : null"
    >
        <a-avatar :size="avatarSize" :src="user.profile_image_url" />
        <div class="flex flex-col">
            <span class="capitalize">
                <span
                    class="font-semibold"
                    :style="isClickable ? 'color: #1890ff; display: inline-flex; align-items: center;' : ''"
                >
                    {{ user.name }} {{ user.last_name }}
                    <template v-if="isClickable">
                        <EyeOutlined
                            style="font-size:10px; margin-left:2px;"
                            aria-hidden="true"
                            title="Clickable"
                        />
                    </template>
                </span>
                <br v-if="user && user.role && user.role.name && showRole" />
                <small v-if="user && user.role && user.role.display_name && showRole">{{
                    user && user.role.display_name
                }}</small>
            </span>
            <span v-if="showEmail">
                <small v-if="user && user.email">{{ user.email }}</small>
            </span>
        </div>
    </a-space>
</template>

<script setup>
import { EyeOutlined } from "@ant-design/icons-vue";

const emit = defineEmits(['onClick']);
const props = defineProps({
    user: { type: Object, required: true },
    avatarSize: { type: [Number, String], default: 40 },
    showEmail: { type: Boolean, default: true },
    showRole: { type: Boolean, default: false },
    isClickable: { type: Boolean, default: false },
});

function handleClick() {
    emit('onClick', props.user);
}
</script>
