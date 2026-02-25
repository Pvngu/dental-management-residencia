const VIEW_MODE = 'calendar_view_mode';
const SELECTED_DENTISTS_DAY = 'calendar_selected_dentists_day';
const SELECTED_DENTIST_WEEK = 'calendar_selected_dentist_week';

const getFromLocalStorage = (key, defaultValue) => {
    const value = window.localStorage.getItem(key);
    if (value === 'undefined' || value === 'null' || value === undefined || value === null) {
        return defaultValue;
    }
    try {
        return JSON.parse(value);
    } catch (e) {
        return value || defaultValue;
    }
};

const state = {
    viewMode: getFromLocalStorage(VIEW_MODE, 'day'),
    selectedDentistsDay: getFromLocalStorage(SELECTED_DENTISTS_DAY, []),
    selectedDentistWeek: getFromLocalStorage(SELECTED_DENTIST_WEEK, null),
};

const mutations = {
    SET_VIEW_MODE(state, mode) {
        state.viewMode = mode;
        window.localStorage.setItem(VIEW_MODE, mode);
    },
    SET_SELECTED_DENTISTS_DAY(state, dentists) {
        state.selectedDentistsDay = dentists;
        window.localStorage.setItem(SELECTED_DENTISTS_DAY, JSON.stringify(dentists));
    },
    SET_SELECTED_DENTIST_WEEK(state, dentistId) {
        state.selectedDentistWeek = dentistId;
        window.localStorage.setItem(SELECTED_DENTIST_WEEK, JSON.stringify(dentistId));
    },
};

const actions = {
    setViewMode({ commit }, mode) {
        commit('SET_VIEW_MODE', mode);
    },
    setSelectedDentistsDay({ commit }, dentists) {
        commit('SET_SELECTED_DENTISTS_DAY', dentists);
    },
    setSelectedDentistWeek({ commit }, dentistId) {
        commit('SET_SELECTED_DENTIST_WEEK', dentistId);
    },
};

const getters = {
    viewMode: (state) => state.viewMode,
    selectedDentistsDay: (state) => state.selectedDentistsDay,
    selectedDentistWeek: (state) => state.selectedDentistWeek,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
