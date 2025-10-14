import apiClient from "@/services/apiClient";
import * as types from "./mutation-types";

export const fetchUser = ({ commit }) => {
    return new Promise((resolve, reject) => {
        apiClient.user
            .get()
            .then((response) => {
                commit(types.SET_CURRENT_USER, response.data);
                commit(types.SET_AUTHENTICATED, true);
                resolve(response);
            })
            .catch((error) => {
                reject(error);
            });
    });
};
