import apiClient from "@/services/apiClient";
import * as types from "./mutation-types";

export const initializeAuth = async ({ commit, dispatch }) => {
    try {
        // On local dev, Google OAuth callback runs on localhost:8000 while SPA runs on
        // acomody.local:8000. A short-lived token in the URL is exchanged for a real session.
        const urlParams = new URLSearchParams(window.location.search);
        const oauthToken = urlParams.get("oauth_token");

        if (oauthToken) {
            urlParams.delete("oauth_token");
            const newSearch = urlParams.toString();
            const newUrl =
                window.location.pathname +
                (newSearch ? "?" + newSearch : "") +
                window.location.hash;
            window.history.replaceState({}, "", newUrl);

            try {
                await dispatch("exchangeGoogleToken", oauthToken);
            } catch (e) {
                // Token exchange failed — fall through to normal auth check
            }
        }

        if (typeof window !== "undefined" && window.initialAuthState) {
            if (
                window.initialAuthState.isAuthenticated &&
                window.initialAuthState.user
            ) {
                commit(
                    "user/SET_CURRENT_USER",
                    window.initialAuthState.user,
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

export const exchangeGoogleToken = async ({}, token) => {
    await apiClient.auth.googleExchange.noAuth().post({ token });
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

    // Koristi apiClient umesto direktnog axios poziva
    return apiClient.logIn
        .noAuth()
        .post({ email, password, remember_me: rememberMe })
        .then((response) => {
            commit(types.SET_AUTHENTICATED, true);
            commit(types.SET_INITIALIZED, true);

            return dispatch("user/fetchUser", null, { root: true }).then(() => {
                return Promise.resolve(response);
            });
        })
        .catch((error) => {
            return Promise.reject(error);
        });
};

export const forgotPassword = async ({}, email) => {
    try {
        return apiClient.forgotPassword
            .noAuth()
            .post({ email })
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
        return apiClient.resetPassword
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
        const response = await apiClient.logOut.post();

        if (response.success) {
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
