<template>
    <div>
        <div class="min-h-screen">
            <cold-start-navbar v-if="isColdStart" />
            <navbar v-else></navbar>
            <template v-if="mainLoading">
                <main-skeleton />
            </template>
            <template v-else>
                <router-view></router-view>
                <modal-index />
                <hr v-if="!isColdStart" />
            </template>
            <Footer v-if="!isColdStart" class="max-w-[1280px] mx-auto"></Footer>
        </div>
    </div>
</template>

<script>
import { useHead } from "@unhead/vue";
import { mapState } from "vuex";
import Navbar from "@/src/components/Navbar.vue";
import ColdStartNavbar from "@/src/components/ColdStartNavbar.vue";
import ModalIndex from "@/src/modals/ModalIndex.vue";
import Footer from "@/src/components/Footer.vue";
import config from "@/config.js";

export default {
    name: "App",
    components: {
        Navbar,
        ColdStartNavbar,
        ModalIndex,
        Footer,
    },
    setup() {
        useHead({ titleTemplate: '%s | Acomody' });
    },
    computed: {
        ...mapState({
            mainLoading: (state) => state.mainLoading,
        }),
        isColdStart() {
            return config.features.cold_start === true;
        },
    },
};
</script>
