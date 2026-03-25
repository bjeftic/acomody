<template>
    <BaseDropdown ref="dropdown" align="end">
        <!-- Bell trigger -->
        <template #trigger>
            <button
                class="relative p-2 text-primary-700 dark:text-primary-400 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-950 transition-all duration-150 focus:outline-none"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    />
                </svg>
                <span
                    v-if="hasUnread"
                    class="absolute top-1 right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full leading-none"
                >
                    {{ unreadBadge }}
                </span>
            </button>
        </template>

        <!-- Panel -->
        <div class="w-80 max-w-[calc(100vw-1rem)] bg-white dark:bg-gray-800 rounded-xl shadow-dropdown border border-gray-100 dark:border-gray-700">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h6 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $t('title') }}</h6>
                <button
                    v-if="hasUnread"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium"
                    @click.stop="markAllRead"
                >
                    {{ $t('mark_all_read') }}
                </button>
            </div>

            <!-- Notification list -->
            <ul class="max-h-96 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700">
                <li v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                    {{ $t('empty') }}
                </li>
                <li
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="flex gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    :class="{ 'bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30': !notification.read }"
                    @click="handleClick(notification)"
                >
                    <span class="mt-0.5 flex-shrink-0">
                        <svg
                            class="w-5 h-5"
                            :class="colorFor(notification.type)"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPathFor(notification.type)" />
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
                            {{ titleFor(notification) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                            {{ subtitleFor(notification) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ timeAgo(notification.created_at) }}
                        </p>
                    </div>
                    <span
                        v-if="!notification.read"
                        class="flex-shrink-0 mt-1.5 w-2 h-2 rounded-full bg-primary-500"
                    />
                </li>
            </ul>
        </div>
    </BaseDropdown>
</template>

<script>
import { mapActions, mapGetters, mapState } from "vuex";
export default {
    name: "NotificationDropdown",

    computed: {
        ...mapState("notifications", ["notifications"]),
        ...mapGetters("notifications", ["unreadBadge", "hasUnread"]),
    },

    methods: {
        ...mapActions("notifications", ["markAsRead", "markAllRead"]),

        async handleClick(notification) {
            if (!notification.read) {
                await this.markAsRead(notification.id);
            }
            this.$refs.dropdown.close();
            this.navigateTo(notification);
        },

        navigateTo(notification) {
            const { type, data } = notification;
            if (type === "accommodation_under_review") {
                // No draft-specific page yet — go to hosting dashboard
                this.$router.push({ name: "page-hosting-home" });
            } else if (type === "accommodation_approved") {
                this.$router.push({ name: "accommodation-detail", params: { id: data.accommodation_id } });
            } else if (type === "booking_received") {
                // No host booking detail route yet — go to hosting dashboard
                this.$router.push({ name: "page-hosting-home" });
            } else if (type === "booking_confirmed" || type === "booking_cancelled") {
                this.$router.push({ name: "booking-detail", params: { id: data.booking_id } });
            }
        },

        titleFor(notification) {
            const key = `types.${notification.type}`;
            return this.$te(key) ? this.$t(key) : this.$t('types.default');
        },

        subtitleFor(notification) {
            const { type, data } = notification;
            if (type === "accommodation_under_review") {
                return data.title;
            }
            if (type === "accommodation_approved") {
                return data.title;
            }
            if (type === "booking_received") {
                return `${data.guest_name} · ${data.accommodation_title}`;
            }
            if (type === "booking_confirmed" || type === "booking_cancelled") {
                return `${data.accommodation_title} · ${data.check_in} – ${data.check_out}`;
            }
            return "";
        },

        iconPathFor(type) {
            const paths = {
                accommodation_under_review: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
                accommodation_approved: "M3 9.75L12 3l9 6.75V21a1 1 0 01-1 1H4a1 1 0 01-1-1V9.75z M9 22V12h6v10",
                booking_received: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
                booking_confirmed: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
                booking_cancelled: "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z",
            };
            return paths[type] ?? paths.booking_received;
        },

        colorFor(type) {
            const colors = {
                accommodation_under_review: "text-yellow-500",
                accommodation_approved: "text-green-500",
                booking_received: "text-primary-500",
                booking_confirmed: "text-green-500",
                booking_cancelled: "text-red-500",
            };
            return colors[type] ?? "text-gray-500";
        },

        timeAgo(isoString) {
            const diff = Math.floor((Date.now() - new Date(isoString)) / 1000);
            if (diff < 60) return this.$t('time.just_now');
            if (diff < 3600) return this.$t('time.minutes_ago', { n: Math.floor(diff / 60) });
            if (diff < 86400) return this.$t('time.hours_ago', { n: Math.floor(diff / 3600) });
            return this.$t('time.days_ago', { n: Math.floor(diff / 86400) });
        },
    },
};
</script>

<i18n lang="yml">
en:
  title: Notifications
  mark_all_read: Mark all as read
  empty: No notifications yet
  types:
    accommodation_under_review: Accommodation under review
    accommodation_approved: Accommodation approved
    booking_received: New booking received
    booking_confirmed: Booking confirmed
    booking_cancelled: Booking cancelled
    default: Notification
  time:
    just_now: Just now
    minutes_ago: "{n}m ago"
    hours_ago: "{n}h ago"
    days_ago: "{n}d ago"
sr:
  title: Obaveštenja
  mark_all_read: Označi sve kao pročitano
  empty: Nema obaveštenja
  types:
    accommodation_under_review: Smeštaj na pregledu
    accommodation_approved: Smeštaj odobren
    booking_received: Nova rezervacija primljena
    booking_confirmed: Rezervacija potvrđena
    booking_cancelled: Rezervacija otkazana
    default: Obaveštenje
  time:
    just_now: Upravo sada
    minutes_ago: "pre {n}min"
    hours_ago: "pre {n}h"
    days_ago: "pre {n}d"
hr:
  title: Obavijesti
  mark_all_read: Označi sve kao pročitano
  empty: Nema obavijesti
  types:
    accommodation_under_review: Smještaj na pregledu
    accommodation_approved: Smještaj odobren
    booking_received: Nova rezervacija primljena
    booking_confirmed: Rezervacija potvrđena
    booking_cancelled: Rezervacija otkazana
    default: Obavijest
  time:
    just_now: Upravo sada
    minutes_ago: "prije {n}min"
    hours_ago: "prije {n}h"
    days_ago: "prije {n}d"
mk:
  title: Известувања
  mark_all_read: Означи ги сите како прочитани
  empty: Нема известувања
  types:
    accommodation_under_review: Сместувањето е на преглед
    accommodation_approved: Сместувањето е одобрено
    booking_received: Примена нова резервација
    booking_confirmed: Резервацијата е потврдена
    booking_cancelled: Резервацијата е откажана
    default: Известување
  time:
    just_now: Токму сега
    minutes_ago: "пред {n}мин"
    hours_ago: "пред {n}ч"
    days_ago: "пред {n}д"
sl:
  title: Obvestila
  mark_all_read: Označi vse kot prebrano
  empty: Ni obvestil
  types:
    accommodation_under_review: Nastanitev v pregledu
    accommodation_approved: Nastanitev odobrena
    booking_received: Nova rezervacija prejeta
    booking_confirmed: Rezervacija potrjena
    booking_cancelled: Rezervacija preklicana
    default: Obvestilo
  time:
    just_now: Ravnokar
    minutes_ago: "pred {n}min"
    hours_ago: "pred {n}h"
    days_ago: "pred {n}d"
</i18n>

<style scoped>
ul {
    scrollbar-width: none;
}
ul::-webkit-scrollbar {
    display: none;
}
</style>
