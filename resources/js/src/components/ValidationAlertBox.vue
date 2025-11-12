<template>
    <fwb-alert v-if="errors.length > 0" type="danger" class="mb-4">
        <span v-for="(error, errorKey) in errors" :key="errorKey">
            <span> - {{ error }} </span
            ><span v-if="errorKey < errors.length - 1"
                >, <br
            /></span>
        </span>
    </fwb-alert>
</template>

<script>
export default {
    name: "ValidationAlertBox",
    props: {
        errors: {
            type: Array,
            required: true,
        },
    },
};
</script>

<style></style>
