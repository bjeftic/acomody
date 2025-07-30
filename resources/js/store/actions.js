import * as types from "./mutation-types";


export const getAuthUser = ({ commit }) => {
    return new Promise((resolve, reject) => {
        ServiceClientAuth.get("/users")
            .then((response) => {
                commit(types.SET_CURRENT_USER, response.data);
                resolve(response);
            })
            .catch((error) => {
                reject(error);
            });
    });
};

export const logout = ({ rootState, commit }) => {
    ServiceClientAuth.post("/logout")
        .then((response) => {
            commit(types.REMOVE_CURRENT_USER, rootState);
            window.location.href = "/";
            return response;
        })
        .catch((error) => {
            return error;
        });
};

/*
 * **********************************
 * Modals
 * **********************************
 */
// export const openModal = ({ commit }, { modalName, options }) => {
//     return new Promise((resolve, reject) => {
//         document.body.style.overflow = "hidden";
//         commit(types.OPEN_MODAL, { modalName, resolve, reject, options });
//     });
// };
// export const closeModal = ({ commit }, { modalName }) => {
//     document.body.removeAttribute("style");
//     commit(types.CLOSE_MODAL, { modalName });
// };
// export const initModal = ({ commit }, { modalName }) => {
//     commit(types.INIT_MODAL, { modalName });
// };

/**
 * **********************************
 * Fields
 * **********************************
 */

// export const fetchFieldOptions = ({ state }, { field, search }) => {
//     return new Promise((resolve, reject) => {
//         ServiceClientAuth.get(`${state.activeOrganizationId}/fields/options`, {
//             params: { field, search },
//         })
//             .then((response) => {
//                 resolve(response);
//             })
//             .catch((error) => {
//                 reject(error);
//             });
//     });
// };
