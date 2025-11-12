<template>
    <base-wrapper>
        <template #content>
            <router-view></router-view>
        </template>
    </base-wrapper>
</template>

<script>
import { mapActions } from "vuex";
export default {
    name: "HostingHome",
};
</script>
