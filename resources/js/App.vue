<template>
    <div>
        <div class="min-h-screen">
            <navbar></navbar>
            <template v-if="mainLoading">
                <main-skeleton />
            </template>
            <template v-else>
                <router-view></router-view>
                <modal-index />
                <hr />
            </template>
            <Footer class="max-w-[1280px] mx-auto"></Footer>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
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
    computed: {
        ...mapState({
            mainLoading: (state) => state.mainLoading,
        }),
    },
};
</script>
