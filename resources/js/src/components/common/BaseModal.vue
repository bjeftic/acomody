<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-gray-900/60 dark:bg-gray-900/80"
                @click="$emit('close')"
            ></div>

            <!-- Panel -->
            <div
                class="relative z-10 w-full bg-white dark:bg-gray-800 rounded-xl shadow-dropdown overflow-hidden"
                :class="sizeClass"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700"
                >
                    <div class="text-base font-semibold text-gray-900 dark:text-white">
                        <slot name="header"></slot>
                    </div>
                    <button
                        @click="$emit('close')"
                        class="ml-4 flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="px-6 py-5 overflow-y-auto max-h-[calc(100vh-10rem)]">
                    <slot name="body"></slot>
                </div>

                <!-- Footer -->
                <div
                    v-if="$slots.footer"
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700"
                >
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script>
export default {
    name: "BaseModal",
    props: {
        size: {
            type: String,
            default: "md",
        },
    },
    emits: ["close"],
    computed: {
        sizeClass() {
            return (
                { sm: "max-w-sm", md: "max-w-md", lg: "max-w-lg", xl: "max-w-xl" }[
                    this.size
                ] || "max-w-md"
            );
        },
    },
};
</script>
