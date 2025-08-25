<template>
    <div>
        <navbar></navbar>
        <router-view></router-view>
        <modal-index />
        <Footer></Footer>
    </div>
</template>

<script>
import { mapActions } from "vuex";
import Navbar from "@/components/Navbar.vue";
import ModalIndex from "@/modals/ModalIndex.vue";
import Footer from "@/components/Footer.vue";
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
