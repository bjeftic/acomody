<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="classes"
        v-bind="$attrs"
    >
        <FwbSpinner v-if="loading" size="4" class="mr-2 shrink-0" />
        <slot />
    </button>
</template>

<script>
export default {
    name: "BaseButton",

    inheritAttrs: false,

    props: {
        variant: {
            type: String,
            default: "primary",
            validator: (v) =>
                ["primary", "secondary", "ghost", "danger", "link"].includes(v),
        },
        size: {
            type: String,
            default: "md",
            validator: (v) => ["sm", "md", "lg", "xl"].includes(v),
        },
        type: {
            type: String,
            default: "button",
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        full: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        classes() {
            return [
                "inline-flex items-center justify-center font-medium transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 select-none",
                this.sizeClasses,
                this.variantClasses,
                this.full ? "w-full" : "",
                this.disabled || this.loading
                    ? "opacity-50 cursor-not-allowed"
                    : "cursor-pointer",
            ];
        },

        sizeClasses() {
            return {
                sm: "text-sm px-3 py-1.5 rounded-lg gap-1.5",
                md: "text-sm px-4 py-2.5 rounded-xl gap-2",
                lg: "text-base px-6 py-3 rounded-xl gap-2",
                xl: "text-base px-8 py-4 rounded-2xl gap-2.5",
            }[this.size];
        },

        variantClasses() {
            return {
                primary:
                    "bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800 focus-visible:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-600",
                secondary:
                    "bg-white text-primary-700 border border-primary-200 hover:bg-primary-50 hover:border-primary-300 active:bg-primary-100 focus-visible:ring-primary-500 dark:bg-gray-800 dark:text-primary-400 dark:border-primary-800 dark:hover:bg-gray-700",
                ghost:
                    "bg-transparent text-primary-700 hover:bg-primary-50 active:bg-primary-100 focus-visible:ring-primary-500 dark:text-primary-400 dark:hover:bg-primary-950",
                danger:
                    "bg-rose-600 text-white hover:bg-rose-700 active:bg-rose-800 focus-visible:ring-rose-500",
                link: "bg-transparent text-primary-700 underline underline-offset-2 hover:text-primary-800 focus-visible:ring-primary-500 dark:text-primary-400 px-0 py-0 rounded-none",
            }[this.variant];
        },
    },
};
</script>
