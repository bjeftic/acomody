<template>
    <div>
        <n-config-provider :theme="theme">
            <navbar class="max-w-[1280px] mx-auto"></navbar>
            <div class="max-w-[1280px] mx-auto">
                <router-view></router-view>
                <modal-index />
            </div>
            <Footer class="max-w-[1280px] mx-auto"></Footer>
        </n-config-provider>
    </div>
</template>

<script>
import { ref, h } from "vue";
import { mapActions } from "vuex";
import Navbar from "@/components/Navbar.vue";
import ModalIndex from "@/modals/ModalIndex.vue";
import Footer from "@/components/Footer.vue";

// Theme
const theme = ref(null);

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

<style>
.destinations-section {
    padding: 40px 0;
    background: #f8f9fa;
}
</style>
