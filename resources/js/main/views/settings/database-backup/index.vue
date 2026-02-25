<template>
    <div class="database-backup-container">
        <div class="mb-4 flex justify-end gap-2">
            <a-space>
                <GenerateDbBackup @success="backupCreated" />
                <BackupCommandSetting />
            </a-space>
        </div>
        <DatabaseSettings ref="settingRef"> </DatabaseSettings>
    </div>
</template>

<script>
import { ref } from "vue";
import DatabaseSettings from "../../common/settings/database-backup/index.vue";
import GenerateDbBackup from "../../common/settings/database-backup/GenerateDbBackup.vue";
import BackupCommandSetting from "../../common/settings/database-backup/BackupCommandSetting.vue";

export default {
    components: {
        DatabaseSettings,
        GenerateDbBackup,
        BackupCommandSetting,
    },
    setup() {
        const settingRef = ref(null);

        const backupCreated = (response) => {
            settingRef.value.backupCreated(response);
        };

        return {
            backupCreated,
            settingRef,
        };
    },
};
</script>
