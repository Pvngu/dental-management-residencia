import { createStore } from 'vuex';
import auth from './auth';
import ui from './modules/ui';
import clinic from './clinic';
import calendar from './modules/calendar';

export default createStore({
    modules: {
        auth,
        ui,
        clinic,
        calendar,
    }
})
