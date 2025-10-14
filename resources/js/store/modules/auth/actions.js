import apiClient from "@/services/apiClient";
import * as types from "./mutation-types";
import axios from "axios";

export const initializeAuth = async ({ commit, dispatch, getters }) => {
    try {
        if (typeof window !== "undefined" && window.initialAuthState) {
            if (
                window.initialAuthState.isAuthenticated &&
                window.initialAuthState.user
            ) {
                commit("SET_USER", window.initialAuthState.user);
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

export const signUp = async ({}, { email, password, confirmPassword }) => {
    return apiClient.signUp
        .noAuth()
        .post({
            email,
            password,
            confirm_password: confirmPassword,
        })
        .then((response) => {
            return Promise.resolve(response);
        })
        .catch((error) => {
            return Promise.reject(error);
        });
};

export const logIn = (
    { commit, dispatch },
    { email, password, rememberMe }
) => {
    commit(types.CLEAR_AUTH);

    return axios
        .post("/log-in", { email, password, remember_me: rememberMe })
        .then((response) => {
            commit(types.SET_AUTHENTICATED, true);
            commit(types.SET_INITIALIZED, true);

            // Fetch user data
            return dispatch("fetchUser").then(() => {
                return Promise.resolve(response);
            });
        })
        .catch((error) => {
            return Promise.reject(error);
        });
};

export const forgotPassword = async ({}, email) => {
    try {
        apiClient.forgotPassword
            .noAuth()
            .post(email)
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

export const resetPassword = async (
    {},
    { email, password, confirmPassword, token }
) => {
    try {
        await apiClient.resetPassword
            .noAuth()
            .post({
                email,
                password,
                password_confirmation: confirmPassword,
                token,
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

export const resendVerificationEmail = async ({}) => {
    return apiClient.resend
        .post()
        .then((response) => {
            if (response.success) {
                return Promise.resolve(response);
            }
            return Promise.reject(response);
        })
        .catch((error) => {
            return Promise.reject(error);
        });
};

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
