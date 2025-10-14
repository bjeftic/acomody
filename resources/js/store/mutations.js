import * as types from "./mutation-types";
import { toCamelCase } from "@/utils/helpers";

export default {

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
