<template>
    <div class="relative" ref="container">
        <div @click.stop="toggle">
            <slot name="trigger" :is-open="isOpen" />
        </div>

        <div
            v-if="isOpen"
                :class="[
                    'absolute mt-1.5 z-50',
                    align === 'end' ? 'right-0' : 'left-0',
                ]"
                @click="closeInside ? close() : null"
            >
                <slot />
            </div>
    </div>
</template>

<script>
export default {
    name: "BaseDropdown",

    props: {
        align: {
            type: String,
            default: "start",
            validator: (v) => ["start", "end"].includes(v),
        },
        closeInside: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["open", "close"],

    data() {
        return {
            isOpen: false,
        };
    },

    mounted() {
        document.addEventListener("click", this.onOutsideClick);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.onOutsideClick);
    },

    methods: {
        toggle() {
            this.isOpen ? this.close() : this.open();
        },

        open() {
            this.isOpen = true;
            this.$emit("open");
        },

        close() {
            this.isOpen = false;
            this.$emit("close");
        },

        onOutsideClick(event) {
            if (this.$refs.container && !this.$refs.container.contains(event.target)) {
                this.close();
            }
        },
    },
};
</script>
