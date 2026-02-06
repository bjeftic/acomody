import { createStore } from 'vuex';
import * as actions from "@/store/actions.js";
import state from "@/store/state.js";
import mutations from "@/store/mutations.js";
import auth from "@/store/modules/auth";
import user from "@/store/modules/user";
import hosting from "@/store/modules/hosting";
import search from "@/store/modules/search";
import ui from "@/store/modules/ui";

const store = createStore({
    strict: true,
    actions,
    state,
    mutations,
    modules: {
        auth,
        user,
        hosting,
        search,
        ui,
    }
});

export default store;
