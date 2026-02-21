<template>
    <div class="relative" ref="wrapper">
        <slot name="trigger" :toggle="toggle" :displayText="displayText">
            <button
                type="button"
                @click="toggle"
                class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-sm"
            >
                <span>{{ displayText }}</span>
                <svg
                    class="w-4 h-4 text-gray-400 transition-transform"
                    :class="{ 'rotate-180': isOpen }"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path d="M7.41 8.84L12 13.42l4.59-4.58L18 10.25l-6 6-6-6z" />
                </svg>
            </button>
        </slot>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                ref="dropdown"
                class="absolute z-30 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4"
                :class="dropdownClass"
                @click.stop
            >
                <!-- Adults -->
                <div class="flex items-center justify-between py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <div class="font-semibold text-gray-800 dark:text-white">Adults</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Ages 13 or above</div>
                    </div>
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            @click="decrement('adults', 1)"
                            :disabled="modelValue.adults <= 1"
                            class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-500 flex items-center justify-center hover:border-gray-900 dark:hover:border-white transition disabled:opacity-30 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                        >
                            −
                        </button>
                        <span class="w-5 text-center font-semibold text-gray-800 dark:text-white">
                            {{ modelValue.adults }}
                        </span>
                        <button
                            type="button"
                            @click="increment('adults', maxAdults)"
                            :disabled="modelValue.adults >= maxAdults"
                            class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-500 flex items-center justify-center hover:border-gray-900 dark:hover:border-white transition disabled:opacity-30 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Children -->
                <div class="flex items-center justify-between py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <div class="font-semibold text-gray-800 dark:text-white">Children</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Ages 2–12</div>
                    </div>
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            @click="decrement('children', 0)"
                            :disabled="modelValue.children <= 0"
                            class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-500 flex items-center justify-center hover:border-gray-900 dark:hover:border-white transition disabled:opacity-30 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                        >
                            −
                        </button>
                        <span class="w-5 text-center font-semibold text-gray-800 dark:text-white">
                            {{ modelValue.children }}
                        </span>
                        <button
                            type="button"
                            @click="increment('children', maxChildren)"
                            :disabled="modelValue.children >= maxChildren"
                            class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-500 flex items-center justify-center hover:border-gray-900 dark:hover:border-white transition disabled:opacity-30 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Infants -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <div class="font-semibold text-gray-800 dark:text-white">Infants</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Under 2</div>
                    </div>
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            @click="decrement('infants', 0)"
                            :disabled="modelValue.infants <= 0"
                            class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-500 flex items-center justify-center hover:border-gray-900 dark:hover:border-white transition disabled:opacity-30 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                        >
                            −
                        </button>
                        <span class="w-5 text-center font-semibold text-gray-800 dark:text-white">
                            {{ modelValue.infants }}
                        </span>
                        <button
                            type="button"
                            @click="increment('infants', maxInfants)"
                            :disabled="modelValue.infants >= maxInfants"
                            class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-500 flex items-center justify-center hover:border-gray-900 dark:hover:border-white transition disabled:opacity-30 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'GuestsDropdown',

    model: {
        prop: 'modelValue',
        event: 'update:modelValue',
    },

    props: {
        modelValue: {
            type: Object,
            default: () => ({ adults: 2, children: 0, infants: 0 }),
        },
        maxAdults: {
            type: Number,
            default: 10,
        },
        maxChildren: {
            type: Number,
            default: 10,
        },
        maxInfants: {
            type: Number,
            default: 5,
        },
        /**
         * Tailwind classes to control dropdown width and position.
         * Override depending on context (e.g. right-0 for SearchBar, left-0 for BookingCard).
         */
        dropdownClass: {
            type: String,
            default: 'right-0 w-80',
        },
    },

    data() {
        return {
            isOpen: false,
        };
    },

    computed: {
        displayText() {
            const total = this.modelValue.adults + this.modelValue.children;
            let text = `${total} ${total === 1 ? 'guest' : 'guests'}`;
            if (this.modelValue.infants > 0) {
                text += `, ${this.modelValue.infants} ${this.modelValue.infants === 1 ? 'infant' : 'infants'}`;
            }
            return text;
        },
    },

    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },

    beforeDestroy() {
        document.removeEventListener('click', this.handleClickOutside);
    },

    methods: {
        toggle() {
            this.isOpen = !this.isOpen;
        },

        open() {
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
        },

        increment(field, max) {
            if (this.modelValue[field] < max) {
                this.$emit('update:modelValue', {
                    ...this.modelValue,
                    [field]: this.modelValue[field] + 1,
                });
            }
        },

        decrement(field, min) {
            if (this.modelValue[field] > min) {
                this.$emit('update:modelValue', {
                    ...this.modelValue,
                    [field]: this.modelValue[field] - 1,
                });
            }
        },

        handleClickOutside(event) {
            if (
                this.isOpen &&
                this.$refs.wrapper &&
                !this.$refs.wrapper.contains(event.target)
            ) {
                this.isOpen = false;
            }
        },
    },
};
</script>
