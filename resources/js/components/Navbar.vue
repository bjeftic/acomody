<template>
    <div>
        <n-layout>
            <n-layout-header
                class="h-16 m-auto"
            >
                <div
                    class="flex items-center justify-between h-full"
                >
                    <!-- Logo/Brand -->
                    <div class="flex items-center">
                        <h3 class="m-0 text-[#18a058]">TuristApp</h3>
                    </div>

                    <!-- Navigation Items -->
                    <div class="flex items-center gap-6">
                        <n-button strong secondary type="primary" size="large">
                            <template #icon>
                                <n-icon>
                                    <HomeIcon />
                                </n-icon>
                            </template>
                            Become a host
                        </n-button>

                        <!-- Destinations dropdown -->
                        <n-dropdown
                            size="large"
                            :options="destinationOptions"
                            trigger="click"
                        >
                            <n-button strong text size="large">
                                Destinations
                                <template #icon>
                                    <n-icon class="ml-1">
                                        <ChevronDownIcon />
                                    </n-icon>
                                </template>
                            </n-button>
                        </n-dropdown>

                        <template v-if="!isLoggedIn">
                            <n-button
                                strong
                                secondary
                                size="large"
                                @click="openLogInModal"
                            >
                                <template #icon>
                                    <n-icon>
                                        <UserIcon />
                                    </n-icon>
                                </template>
                                Log in
                            </n-button>

                            <n-button
                                strong
                                secondary
                                size="large"
                                @click="openSignUpModal"
                            >
                                <template #icon>
                                    <n-icon>
                                        <UserIcon />
                                    </n-icon>
                                </template>
                                Sign up
                            </n-button>
                        </template>
                        <template v-else>
                            <n-dropdown
                                size="large"
                                :options="accountOptions"
                                trigger="click"
                                @select="handleSelect"
                            >
                                <n-button strong text size="large">
                                    Destinations
                                    <template #icon>
                                        <n-icon class="ml-1">
                                            <ChevronDownIcon />
                                        </n-icon>
                                    </template>
                                </n-button>
                            </n-dropdown>
                        </template>
                    </div>
                </div>
            </n-layout-header>
        </n-layout>
    </div>
</template>

<script>
import { h } from "vue";
import { mapActions } from "vuex";
const HomeIcon = {
    render() {
        return h(
            "svg",
            {
                width: "16",
                height: "16",
                viewBox: "0 0 24 24",
                fill: "currentColor",
            },
            [
                h("path", {
                    d: "M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z",
                }),
            ]
        );
    },
};

const UserIcon = {
    render() {
        return h(
            "svg",
            {
                width: "16",
                height: "16",
                viewBox: "0 0 24 24",
                fill: "currentColor",
            },
            [
                h("path", {
                    d: "M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z",
                }),
            ]
        );
    },
};

const ChevronDownIcon = {
    render() {
        return h(
            "svg",
            {
                width: "16",
                height: "16",
                viewBox: "0 0 24 24",
                fill: "currentColor",
            },
            [
                h("path", {
                    d: "M7.41 8.84L12 13.42l4.59-4.58L18 10.25l-6 6-6-6z",
                }),
            ]
        );
    },
};

const LanguageIcon = {
    render() {
        return h(
            "svg",
            {
                width: "16",
                height: "16",
                viewBox: "0 0 24 24",
                fill: "currentColor",
            },
            [
                h("path", {
                    d: "M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95a15.65 15.65 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.92 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2c0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.987 7.987 0 0 1 5.08 16zm2.95-8H5.08a7.987 7.987 0 0 1 4.33-3.56A15.65 15.65 0 0 0 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2c0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2c0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2c0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z",
                }),
            ]
        );
    },
};

import { mapGetters } from "vuex";
import { logOut } from "../store/actions";
export default {
    name: "Navbar",
    components: {
        HomeIcon,
        UserIcon,
        ChevronDownIcon,
        LanguageIcon,
    },
    data() {
        return {
            selectedDestination: "",
            destinationOptions: [
                {
                    label: "Popularne destinacije",
                    key: "popular",
                    type: "group",
                    children: [
                        {
                            label: "Beograd",
                            key: "beograd",
                        },
                        {
                            label: "Novi Sad",
                            key: "novi-sad",
                        },
                        {
                            label: "Niš",
                            key: "nis",
                        },
                    ],
                },
                {
                    label: "Planinske destinacije",
                    key: "mountains",
                    type: "group",
                    children: [
                        {
                            label: "Kopaonik",
                            key: "kopaonik",
                        },
                        {
                            label: "Zlatibor",
                            key: "zlatibor",
                        },
                        {
                            label: "Tara",
                            key: "tara",
                        },
                    ],
                },
                {
                    label: "Spa destinacije",
                    key: "spa",
                    type: "group",
                    children: [
                        {
                            label: "Vrnjačka Banja",
                            key: "vrnjacka-banja",
                        },
                        {
                            label: "Sokobanja",
                            key: "sokobanja",
                        },
                        {
                            label: "Bukovička Banja",
                            key: "bukovicka-banja",
                        },
                    ],
                },
            ],
            accountOptions: [
                {
                    label: "My account",
                    key: "my-account",
                },
                {
                    label: "Dashboard",
                    key: "dashboard",
                },
                {
                    label: "Bookings",
                    key: "bookings",
                },
                {
                    label: "Wishlist",
                    key: "wishlist",
                },
                {
                    label: "Reviews",
                    key: "reviews",
                },
                {
                    label: "Log out",
                    key: "log-out",
                },
            ],
        };
    },
    computed: {
        ...mapGetters(["isLoggedIn"]),
    },
    methods: {
        ...mapActions(["openModal", "logOut"]),
        openLogInModal() {
            this.openModal({
                modalName: "logInModal",
            });
        },
        openSignUpModal() {
            this.openModal({
                modalName: "signUpModal",
            });
        },
        handleSelect(key) {
            if(key === "log-out") {
                this.logOut();
            }
        },
    },
};
</script>

<style scoped>
/* Dodatni stilovi ako su potrebni */
</style>
