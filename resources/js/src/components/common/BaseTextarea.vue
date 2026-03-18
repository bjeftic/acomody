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

        <textarea
            :id="inputId"
            v-bind="$attrs"
            :value="modelValue"
            :rows="rows"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :maxlength="maxlength"
            :class="textareaClasses"
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"
        />

        <div class="flex items-start justify-between mt-1.5 gap-2">
            <p v-if="error" class="text-xs text-rose-600 dark:text-rose-400">
                {{ error }}
            </p>
            <p v-else-if="hint" class="text-xs text-gray-500 dark:text-gray-400">
                {{ hint }}
            </p>
            <span v-else class="flex-1" />

            <p v-if="maxlength" class="text-xs text-gray-400 dark:text-gray-500 shrink-0 ml-auto">
                {{ (modelValue || '').length }} / {{ maxlength }}
            </p>
        </div>
    </div>
</template>

<script>
let counter = 0;

export default {
    name: "BaseTextarea",

    inheritAttrs: false,

    props: {
        modelValue: {
            type: String,
            default: "",
        },
        label: {
            type: String,
            default: null,
        },
        placeholder: {
            type: String,
            default: "",
        },
        rows: {
            type: Number,
            default: 4,
        },
        maxlength: {
            type: Number,
            default: null,
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
            inputId: `base-textarea-${++counter}`,
        };
    },

    computed: {
        textareaClasses() {
            return [
                "block w-full rounded-xl border bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-sm transition-all duration-150 resize-y px-3.5 py-2.5",
                "focus:outline-none focus:ring-2 focus:ring-offset-0",
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
