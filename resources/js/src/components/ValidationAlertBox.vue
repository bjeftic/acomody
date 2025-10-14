<template>
    <fwb-alert v-if="Object.keys(errors).length > 0" type="danger" class="mb-4">
        <span v-for="(error, errorKey) in errors" :key="errorKey">
            <span> - {{ error.message }} </span
            ><span v-if="errorKey < Object.keys(errors).length - 1"
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
            type: Object,
            required: true,
        },
    },
};
</script>

<style></style>
