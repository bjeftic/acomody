<template>
    <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            Booking options
        </h3>

        <div class="space-y-3">
            <label
                v-for="option in bookingOptions"
                :key="option.id"
                class="flex items-start cursor-pointer group"
            >
                <input
                    type="checkbox"
                    :checked="isSelected(option.id)"
                    @change="toggleOption(option.id)"
                    class="mt-1 w-5 h-5 text-blue-600 border-gray-300 dark:border-gray-700 rounded focus:ring-blue-500"
                />
                <div class="ml-3 flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="text-lg">{{ option.icon }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ option.name }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        {{ option.description }}
                    </p>
                </div>
            </label>
        </div>
    </div>
</template>

<script>
import { filtersConfig } from '../../config/filtersConfig';

export default {
    name: 'BookingOptionsFilter',
    props: {
        instantBook: {
            type: Boolean,
            default: false,
        },
        selfCheckin: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            bookingOptions: filtersConfig.bookingOptions,
        };
    },
    methods: {
        isSelected(optionId) {
            if (optionId === 'instant_book') return this.instantBook;
            if (optionId === 'self_checkin') return this.selfCheckin;
            return false;
        },
        toggleOption(optionId) {
            if (optionId === 'instant_book') {
                this.$emit('update:instant-book', !this.instantBook);
            } else if (optionId === 'self_checkin') {
                this.$emit('update:self-checkin', !this.selfCheckin);
            }
        },
    },
};
</script>
