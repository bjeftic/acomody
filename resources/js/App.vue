<template>
    <div>
        <div class="min-h-screen">
            <navbar></navbar>
            <div class="max-w-[1280px] mx-auto">
                <router-view></router-view>
                <modal-index />
            </div>
            <Footer class="max-w-[1280px] mx-auto"></Footer>
        </div>
    </div>
</template>

<script>
import { mapActions } from "vuex";
import Navbar from "@/src/components/Navbar.vue";
import ModalIndex from "@/src/modals/ModalIndex.vue";
import Footer from "@/src/components/Footer.vue";

export default {
    name: "App",
    components: {
        Navbar,
        ModalIndex,
        Footer,
    },
    methods: {
        ...mapActions(["initializeAuth", "getCsrfCookie"]),
    },
    async created() {
        await this.getCsrfCookie();
        await this.initializeAuth();
    },
};
</script>

<style></style>
