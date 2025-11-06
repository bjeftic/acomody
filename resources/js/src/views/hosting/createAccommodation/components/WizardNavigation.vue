<template>
    <div class="border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto py-4">
            <div class="flex items-center justify-between">
                <!-- Back Button -->
                <fwb-button
                    v-if="currentStep > 1"
                    color="alternative"
                    outline="false"
                    @click="$emit('previous')"
                >
                    <div class="flex items-center">
                        <svg
                            class="w-5 h-5 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        <span>Back</span>
                    </div>
                </fwb-button>
                <div v-else></div>

                <!-- Next/Continue Button -->
                <fwb-button
                    @click="$emit('next')"
                    :disabled="!canProceed"
                    class="px-6"
                    color="default"
                >
                    {{ currentStep === totalSteps ? "Finish" : "Next" }}
                </fwb-button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "WizardNavigation",
    props: {
        currentStep: {
            type: Number,
            required: true,
        },
        totalSteps: {
            type: Number,
            required: true,
        },
        canProceed: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["previous", "next"],
};
</script>
