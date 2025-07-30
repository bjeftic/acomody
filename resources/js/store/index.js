import { createStore } from 'vuex';
import * as actions from "@/store/actions.js";
import state from "@/store/state.js";
import * as getters from "@/store/getters.js";
import mutations from "@/store/mutations.js";
// import guidebooks from "@/pages/guidebooks/store";
// import settings from "@/pages/settings/store";

const store = createStore({
    strict: true,
    actions,
    getters,
    state,
    mutations,
    modules: {
        // guidebooks,
        // settings,
    }
});

export default store;
