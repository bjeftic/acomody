import apiClient from "@/services/apiClient";
import * as types from "./mutation-types";
import axios from "axios";

export const initializeAuth = async ({ commit, dispatch }) => {
    try {
        if (typeof window !== "undefined" && window.initialAuthState) {
            if (
                window.initialAuthState.isAuthenticated &&
                window.initialAuthState.user
            ) {
                commit(
                    "user/SET_CURRENT_USER",
                    window.initialAuthState.user,
                    null,
                    { root: true }
                );
                commit("SET_AUTHENTICATED", true);
                return;
            }
        }

        await dispatch("user/fetchUser", null, { root: true });
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

export const logIn = async (
    { commit, dispatch },
    { email, password, rememberMe = false }
) => {
    // Commit to root store
    commit("CLEAR_AUTH");

    try {
        const response = await axios.post("/log-in", {
            email,
            password,
            remember_me: rememberMe,
        });

        commit("SET_AUTHENTICATED", true);
        commit("SET_INITIALIZED", true);

        await dispatch("user/fetchUser", null, { root: true });

        return response;
    } catch (error) {
        throw error;
    }
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

export const logOut = async ({ commit }) => {
    try {
        const response = await axios.post("/log-out");

        // Clear auth data regardless of response
        commit("CLEAR_AUTH");
        window.location.href = "/";
        return response;
    } catch (error) {
        console.error("Logout failed:", error);
        // Clear auth even on error
        commit("CLEAR_AUTH");
        // Redirect anyway
        window.location.href = "/";
        return Promise.reject(error);
    }
};
