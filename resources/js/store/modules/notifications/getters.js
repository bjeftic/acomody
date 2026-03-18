export const unreadBadge = (state) => {
    return state.unreadCount > 99 ? "99+" : state.unreadCount;
};

export const hasUnread = (state) => state.unreadCount > 0;
