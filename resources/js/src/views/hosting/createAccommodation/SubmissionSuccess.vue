<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-4">
            Congratulations!
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            <template v-if="isColdStart">
                Your accommodation listing has been successfully submitted and is now under review. Once approved, your listing will be ready — and when the platform launches, guests will be able to start booking right away.
            </template>
            <template v-else>
                Your accommodation listing has been successfully submitted and is now under review. We will notify you once it has been approved and is live on our platform.
            </template>
        </p>

        <!-- Host profile incomplete prompt -->
        <div
            v-if="!hostProfileComplete"
            class="flex gap-4 p-5 mb-8 bg-amber-50 border border-amber-200 rounded-lg"
        >
            <div class="flex-shrink-0 text-amber-500 mt-0.5">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-amber-800 mb-1">
                    Complete your host profile to go live
                </h3>
                <p class="text-sm text-amber-700 mb-3">
                    Your listing won't be searchable until you fill in your host profile (display name, contact email, and phone). It only takes a minute.
                </p>
                <router-link
                    :to="{ name: 'page-host-profile' }"
                    class="inline-block px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded hover:bg-amber-700 transition"
                >
                    Complete host profile →
                </router-link>
            </div>
        </div>

        <div class="mt-4">
            <router-link
                to="/hosting/dashboard"
                class="inline-block px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition"
            >
                Go to Hosting Dashboard
            </router-link>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import config from "@/config.js";

export default {
    name: "SubmissionSuccess",
    computed: {
        ...mapGetters("user", ["hostProfileComplete"]),
        isColdStart() {
            return config.features.cold_start === true;
        },
    },
};
</script>
