<template>
    <div>
        <fwb-navbar class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo/Brand -->
                    <div class="flex items-center">
                        <h3 class="text-2xl font-bold text-green-600 m-0">
                            TuristApp
                        </h3>
                    </div>

                    <!-- Navigation Items -->
                    <div class="flex items-center gap-4">
                        <!-- Become a host button -->
                        <fwb-button
                            v-if="!isLoggedIn && $route.name !== 'page-become-a-host'"
                            color="default"
                            @click="
                                $router.push({ name: 'page-become-a-host' })
                            "
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
                            <fwb-dropdown text="Account" size="lg">
                                <fwb-list-group-item
                                    v-for="option in accountOptions"
                                    :key="option.key"
                                    @click="handleSelect(option.key)"
                                    class="cursor-pointer hover:bg-gray-100"
                                >
                                    {{ option.label }}
                                </fwb-list-group-item>
                            </fwb-dropdown>
                        </template>
                    </div>
                </div>
            </div>
        </fwb-navbar>
    </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import { FwbButton, FwbDropdown, FwbListGroupItem } from "flowbite-vue";

export default {
    name: "Navbar",
    components: {
        FwbButton,
        FwbDropdown,
        FwbListGroupItem,
    },
    data() {
        return {
            selectedDestination: "",

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
            if (key === "log-out") {
                this.logOut();
            } else {
                // Handle other account options
                console.log("Selected:", key);
            }
        },
    },
};
</script>
