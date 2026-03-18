<template>
    <div :class="full ? 'w-full' : ''">
        <label
            v-if="label"
            :for="inputId"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
        >
            {{ label }}
            <span v-if="required" class="text-rose-500 ml-0.5">*</span>
        </label>

        <div class="relative">
            <div
                v-if="$slots.prefix"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
            >
                <slot name="prefix" />
            </div>

            <input
                :id="inputId"
                v-bind="$attrs"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :class="inputClasses"
                @input="$emit('update:modelValue', $event.target.value)"
                @blur="$emit('blur', $event)"
                @focus="$emit('focus', $event)"
            />

            <div
                v-if="$slots.suffix"
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
            >
                <slot name="suffix" />
            </div>
        </div>

        <p v-if="error" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 flex items-center gap-1">
            {{ error }}
        </p>
        <p v-else-if="hint" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            {{ hint }}
        </p>
    </div>
</template>

<script>
let counter = 0;

export default {
    name: "BaseInput",

    inheritAttrs: false,

    props: {
        modelValue: {
            type: [String, Number],
            default: "",
        },
        label: {
            type: String,
            default: null,
        },
        type: {
            type: String,
            default: "text",
        },
        placeholder: {
            type: String,
            default: "",
        },
        error: {
            type: String,
            default: null,
        },
        hint: {
            type: String,
            default: null,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        required: {
            type: Boolean,
            default: false,
        },
        full: {
            type: Boolean,
            default: true,
        },
    },

    emits: ["update:modelValue", "blur", "focus"],

    data() {
        return {
            inputId: `base-input-${++counter}`,
        };
    },

    computed: {
        inputClasses() {
            return [
                "block w-full rounded-xl border bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-sm transition-all duration-150",
                "focus:outline-none focus:ring-2 focus:ring-offset-0",
                this.$slots.prefix ? "pl-9" : "px-3.5",
                this.$slots.suffix ? "pr-9" : "px-3.5",
                "py-2.5",
                this.error
                    ? "border-rose-400 focus:border-rose-400 focus:ring-rose-200 dark:border-rose-500 dark:focus:ring-rose-900"
                    : "border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-100 dark:focus:border-primary-500 dark:focus:ring-primary-900",
                this.disabled
                    ? "opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-900"
                    : "",
            ];
        },
    },
};
</script>
