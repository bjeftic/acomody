<template>
    <fwb-navbar class="bg-white border-b border-gray-200">
        <div class="w-full px-4 sm:px-0 max-w-7xl mx-auto">
            <div class="flex justify-between items-center h-12">
                <!-- Logo/Brand -->
                <div class="flex items-center">
                    <h3 class="text-2xl font-bold text-green-600 m-0">
                        TuristApp
                    </h3>
                </div>
                <!-- Navigation Items -->
                <div class="flex items-center gap-2">
                    <fwb-button
                        v-if="isLoggedIn && $route.name !== 'page-hosting-home'"
                        color="alternative"
                        @click="$router.push({ name: 'page-hosting-home' })"
                    >
                        Hosting
                    </fwb-button>
                    <!-- Become a host button -->
                    <fwb-button
                        v-if="
                            !isLoggedIn && $route.name !== 'page-become-a-host'
                        "
                        color="default"
                        @click="$router.push({ name: 'page-become-a-host' })"
                    >
                        Become a host
                    </fwb-button>

                    <!-- Auth buttons or Account dropdown -->
                    <template v-if="!isLoggedIn">
                        <fwb-button outline @click="openLogInModal">
                            Log in
                        </fwb-button>

                        <fwb-button outline @click="openSignUpModal">
                            Sign up
                        </fwb-button>
                    </template>
                    <template v-else>
                        <fwb-dropdown
                            text="Account"
                            size="lg"
                            align-to-end
                            close-inside
                        >
                            <nav
                                class="py-2 text-sm rounded-lg text-gray-700 border border-gray-200 dark:text-gray-200 dark:border-gray-700 flex flex-col"
                            >
                                <span
                                    class="cursor-pointer px-4 py-2 min-w-36 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                >
                                    My account
                                </span>
                                <span
                                    class="cursor-pointer px-4 py-2 min-w-36 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                >
                                    Dashboard
                                </span>
                                <span
                                    class="cursor-pointer px-4 py-2 min-w-36 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                >
                                    Bookings
                                </span>
                                <span
                                    class="cursor-pointer px-4 py-2 min-w-36 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                >
                                    Wishlists
                                </span>
                                <span
                                    class="cursor-pointer px-4 py-2 min-w-36 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                >
                                    Reviews
                                </span>
                                <span
                                    class="cursor-pointer px-4 py-2 min-w-36 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                    @click="logOut"
                                >
                                    Log out
                                </span>
                            </nav>
                        </fwb-dropdown>
                    </template>
                </div>
            </div>
        </div>
    </fwb-navbar>
</template>

<script>
import { mapActions, mapGetters } from "vuex";

export default {
    name: "Navbar",
    computed: {
        ...mapGetters("auth", ["isLoggedIn"]),
    },
    methods: {
        ...mapActions(["openModal"]),
        ...mapActions("auth", ["logOut"]),
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
    },
};
</script>
