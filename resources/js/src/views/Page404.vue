<template>
    <div class="min-h-screen flex items-center justify-center bg-white dark:bg-gray-900 px-4">
        <div class="text-center">
            <p class="text-8xl font-bold text-primary-600 dark:text-primary-400 mb-4">
                404
            </p>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-3">
                {{ $t('errors.not_found') }}
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto">
                {{ $t('errors.not_found_desc') }}
            </p>
            <router-link
                :to="{ name: 'page-welcome' }"
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>
                {{ $t('errors.go_home') }}
            </router-link>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Page404',
};
</script>
