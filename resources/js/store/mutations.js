import * as types from "./mutation-types";
import { toCamelCase } from "@/utils/helpers";

export default {
    /**
     * **********************************
     * Users
     * **********************************
     */
    [types.SET_CURRENT_USER](state, { data }) {
        state.currentUser = data.user;
    },
    [types.SET_AUTHENTICATED](state, status) {
        state.isAuthenticated = status;
    },
    [types.SET_INITIALIZED](state, status) {
        state.isInitialized = status;
    },
    [types.CLEAR_AUTH](state) {
        state.currentUser = null;
        state.isAuthenticated = false;
    },

    /**
     * **********************************
     * Currencies
     * **********************************
     */
    [types.SET_CURRENCIES](state, { response }) {
        state.currencies = response.data;
    },

    /*
     * **********************************
     * Modals
     * **********************************
     */
    [types.INIT_MODAL](state, { modalName }) {
        const modalNameCamelCase = toCamelCase(modalName);

        state.modals[modalNameCamelCase] = {
            name: modalName,
            shown: false,
            resolve: null,
            reject: null,
            options: {},
        };
    },
    [types.OPEN_MODAL](state, { modalName, resolve, reject, options }) {
        const modalNameCamelCase = toCamelCase(modalName);
        state.modals = {
            ...state.modals,
            [modalNameCamelCase]: {
                ...state.modals[modalNameCamelCase],
                name: modalName,
                shown: true,
                resolve: resolve,
                reject: reject,
                options:
                    typeof options !== "undefined"
                        ? options
                        : state.modals[modalNameCamelCase].options,
            },
        };
    },
    [types.CLOSE_MODAL](state, { modalName }) {
        const modalNameCamelCase = toCamelCase(modalName);

        state.modals = {
            ...state.modals,
            [modalNameCamelCase]: {
                ...state.modals[modalNameCamelCase],
                shown: false,
                resolve: null,
                reject: null,
                options: {},
            },
        };
    },
};
