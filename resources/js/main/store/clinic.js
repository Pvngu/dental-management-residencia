const CURRENT_CLINIC_ID = 'current_clinic_id';

const getJSONFromLocalStorage = (key) => {
    const value = window.localStorage.getItem(key);

    if (value === 'undefined' || value === 'null' || value === undefined || value === null) {
        return null;
    }
    else {
        return value;
    }
};

export default {
    namespaced: true,
    state() {
        return {
            currentClinicId: getJSONFromLocalStorage(CURRENT_CLINIC_ID),
        }
    },

    mutations: {
        setClinic(state, clinicId) {
            state.currentClinicId = clinicId;
            if (clinicId) {
                window.localStorage.setItem(CURRENT_CLINIC_ID, clinicId);
            } else {
                window.localStorage.removeItem(CURRENT_CLINIC_ID);
            }
        },
    },

    actions: {
        selectClinic(context, clinicId) {
            context.commit('setClinic', clinicId);

            // Optionally persist the choice to the backend
            // if (clinicId) {
            //     axiosAdmin.post('/user/select-clinic', {
            //         clinic_id: clinicId
            //     }).catch(error => {
            //         console.error('Failed to persist clinic selection:', error);
            //     });
            // }

            // Trigger a refresh of the current page data
            // You can emit an event or use event bus if needed
            if (window.dispatchEvent) {
                window.dispatchEvent(new CustomEvent('clinic-changed', { detail: { clinicId } }));
            }
        },
    },

    getters: {
        currentClinicId: (state) => state.currentClinicId,
    }
}
