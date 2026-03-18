import apiClient from "@/services/apiClient";
import * as types from "./mutation-types";

export const fetchNotifications = async ({ commit }) => {
    try {
        const response = await apiClient.notifications.get();
        commit(types.SET_NOTIFICATIONS, response.data.data);
        commit(types.SET_UNREAD_COUNT, response.data.unread_count);
    } catch (error) {
        console.error("Failed to fetch notifications:", error);
    }
};

export const markAsRead = async ({ commit }, id) => {
    try {
        await apiClient.notifications[id].read.post();
        commit(types.MARK_READ, id);
    } catch (error) {
        console.error("Failed to mark notification as read:", error);
    }
};

export const markAllRead = async ({ commit }) => {
    try {
        await apiClient.notifications.readAll.post();
        commit(types.MARK_ALL_READ);
    } catch (error) {
        console.error("Failed to mark all notifications as read:", error);
    }
};
