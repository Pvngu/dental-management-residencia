import { ref } from 'vue';

const visible = ref(false);
const activeTab = ref('company');

const useSettingsModal = () => {
    const openModal = (tab = 'company') => {
        activeTab.value = tab;
        visible.value = true;
    };

    const closeModal = () => {
        visible.value = false;
    };

    return {
        visible,
        activeTab,
        openModal,
        closeModal,
    };
};

export default useSettingsModal;
