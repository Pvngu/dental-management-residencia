const state = {
    notesSidebarVisible: false,
    calendarSidebarVisible: true,
};

const mutations = {
    SET_NOTES_SIDEBAR_VISIBLE(state, visible) {
        state.notesSidebarVisible = visible;
    },
    SET_CALENDAR_SIDEBAR_VISIBLE(state, visible) {
        state.calendarSidebarVisible = visible;
    },
};

const actions = {
    toggleNotesSidebar({ commit, state }) {
        commit('SET_NOTES_SIDEBAR_VISIBLE', !state.notesSidebarVisible);
    },
    setNotesSidebarVisible({ commit }, visible) {
        commit('SET_NOTES_SIDEBAR_VISIBLE', visible);
    },
    toggleCalendarSidebar({ commit, state }) {
        commit('SET_CALENDAR_SIDEBAR_VISIBLE', !state.calendarSidebarVisible);
    },
    setCalendarSidebarVisible({ commit }, visible) {
        commit('SET_CALENDAR_SIDEBAR_VISIBLE', visible);
    },
};

const getters = {
    notesSidebarVisible: (state) => state.notesSidebarVisible,
    calendarSidebarVisible: (state) => state.calendarSidebarVisible,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
