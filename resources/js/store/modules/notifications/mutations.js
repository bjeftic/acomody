import * as types from "./mutation-types";

export default {
    [types.SET_NOTIFICATIONS](state, notifications) {
        state.notifications = notifications;
    },

    [types.SET_UNREAD_COUNT](state, count) {
        state.unreadCount = count;
    },

    [types.MARK_READ](state, id) {
        const notification = state.notifications.find((n) => n.id === id);
        if (notification) {
            notification.read = true;
        }
        state.unreadCount = Math.max(0, state.unreadCount - 1);
    },

    [types.MARK_ALL_READ](state) {
        state.notifications.forEach((n) => {
            n.read = true;
        });
        state.unreadCount = 0;
    },
};
