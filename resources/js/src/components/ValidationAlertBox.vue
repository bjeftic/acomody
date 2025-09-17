<template>
    <n-alert
        v-if="Object.keys(errors).length > 0"
        type="error"
        :show-icon="false"
        style="margin-bottom: 16px"
    >
        <span v-for="(error, errorKey) in errors" :key="errorKey">
            <span> - {{ error.message }} </span
            ><span v-if="errorKey < Object.keys(errors).length - 1"
                >, <br
            /></span>
        </span>
    </n-alert>
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
