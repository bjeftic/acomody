import * as actions from "@/store/modules/search/actions";
import mutations from "@/store/modules/search/mutations";
import state from "@/store/modules/search/state";
import * as getters from "@/store/modules/search/getters";

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
