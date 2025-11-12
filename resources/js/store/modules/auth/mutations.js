import * as types from "./mutation-types";

export default {

[types.SET_AUTHENTICATED](state, status) {
        state.isAuthenticated = status;
    },
    [types.SET_INITIALIZED](state, status) {
        state.isInitialized = status;
    },
    [types.CLEAR_AUTH](state) {
        state.currentUser = null;
        state.isAuthenticated = false;
        state.token = null;
        state.tokenExpiration = null;

        localStorage.removeItem('token');
        localStorage.removeItem('token_expiration');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('token_expiration');
    },
};
