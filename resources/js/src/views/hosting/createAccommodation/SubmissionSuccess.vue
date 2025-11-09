<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-4">
            Congratulations!
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Your accommodation listing has been successfully submitted and is now under review. We will notify you once it has been approved and is live on our platform.
        </p>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            You can check the status of your listing in your hosting dashboard.
        </p>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Meanwhile, feel free to explore your hosting dashboard to manage your listings and view insights.
        </p>

        <div class="mt-8">
            <router-link
                to="/hosting/dashboard"
                class="inline-block px-6 py-3 bg-blue-600 text-white font-medium rounded hover:bg-blue-700 transition"
            >
                Go to Hosting Dashboard
            </router-link>
        </div>
    </div>
</template>

<script>
export default {
}
</script>

<style>

</style>
