import apiClient from "@/services/apiClient";
import * as types from "./mutation-types";
import axios from "axios";

export const getCsrfCookie = async () => {
    await axios.get("/sanctum/csrf-cookie");
};

export const initializeAuth = async ({ commit, dispatch, getters }) => {
    try {
        if (typeof window !== 'undefined' && window.initialAuthState) {
            if (window.initialAuthState.isAuthenticated && window.initialAuthState.user) {
                commit('SET_USER', window.initialAuthState.user);
                return;
            }
        }

        await dispatch("fetchUser");
        
    } catch (error) {
        //
    } finally {
        commit("SET_INITIALIZED", true);
    }
};

export const fetchUser = ({ commit }) => {
    return new Promise((resolve, reject) => {
        apiClient.user
            .get()
            .then((response) => {
                if (response.success) {
                    commit(types.SET_CURRENT_USER, response.data);
                    commit(types.SET_AUTHENTICATED, true);
                    resolve(response);
                } else {
                    reject("Unauthorized");
                }
            })
            .catch((error) => {
                console.error("Error fetching user:", error);
                reject(error);
            });
    });
};

export const signUp = async ({}, { email, password, confirmPassword }) => {
    try {
        await apiClient.signUp
            .noAuth()
            .post({
                email,
                password,
                confirm_password: confirmPassword,
            })
            .then((response) => {
                if (response.success) {
                    return Promise.resolve(response);
                }
                return Promise.reject(response);
            })
            .catch((error) => {
                return Promise.reject(error);
            });
    } catch (error) {
        return Promise.reject(error);
    }
};

export const logIn = async ({ commit, dispatch }, { email, password, rememberMe }) => {
  try {
    commit(types.CLEAR_AUTH)

    await axios.post("/log-in", {
      email,
      password,
      remember_me: rememberMe
    }).then((response) => {
      if (response.data.success) {
        commit('SET_AUTHENTICATED', true)
        dispatch('fetchUser')
        return Promise.resolve(response)
      } else {
        return Promise.reject(
          new Error(response.error?.message || 'Login failed')
        )
      }
    })
  } catch (error) {
    console.error('Login failed:', error)
    return Promise.reject(error)
  }
}

export const logOut = async ({ commit, rootState }) => {
    try {
        const response = await axios.post("/log-out");

        if (response.data.success) {
            // Clear all auth data
            commit("CLEAR_AUTH");

            window.location.href = "/";
            return response;
        } else {
            throw new Error(response.error?.message || "Logout failed");
        }
    } catch (error) {
        commit("CLEAR_AUTH");
        return Promise.reject(error);
    }
};

/*
 * **********************************
 * Modals
 * **********************************
 */
export const openModal = ({ commit }, { modalName, options = false }) => {
    return new Promise((resolve, reject) => {
        document.body.style.overflow = "hidden";
        commit(types.OPEN_MODAL, { modalName, resolve, reject, options });
    });
};
export const closeModal = ({ commit }, { modalName }) => {
    document.body.removeAttribute("style");
    commit(types.CLOSE_MODAL, { modalName });
};
export const initModal = ({ commit }, { modalName }) => {
    commit(types.INIT_MODAL, { modalName });
};

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
