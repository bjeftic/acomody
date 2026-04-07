<template>
    <div class="border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Back Button -->
                <BaseButton
                    v-if="currentStep > 1"
                    variant="secondary"
                    size="md"
                    @click="$emit('previous')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ $t('common.back') }}
                </BaseButton>
                <div v-else></div>

                <!-- Next/Continue Button -->
                <BaseButton
                    :disabled="!canProceed"
                    size="md"
                    @click="$emit('next')"
                >
                    {{ currentStep === totalSteps ? $t('finish') : $t('common.next') }}
                    <svg v-if="currentStep < totalSteps" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </BaseButton>
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

<i18n lang="yaml">
en:
  finish: Finish
sr:
  finish: Završi
hr:
  finish: Završi
mk:
  finish: Заврши
sl:
  finish: Zaključi
</i18n>
