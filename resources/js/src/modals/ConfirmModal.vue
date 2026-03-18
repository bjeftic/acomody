<template>
    <BaseModal v-if="show" @close="cancel" size="md">
        <template #header>
            {{ options.title || "Are you sure?" }}
        </template>
        <template #body>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ options.message || "Do you want to proceed?" }}
            </p>
        </template>
        <template #footer>
            <div class="flex justify-end gap-3">
                <BaseButton variant="secondary" @click="cancel">
                    {{ options.cancelText || "Cancel" }}
                </BaseButton>
                <BaseButton :variant="options.confirmVariant || 'danger'" @click="confirm">
                    {{ options.confirmText || "Confirm" }}
                </BaseButton>
            </div>
        </template>
    </BaseModal>
</template>

<script>
import config from "@/config";
import { toCamelCase } from "@/utils/helpers";
import { mapState, mapActions } from "vuex";

const modalName = config.modals.confirmModal;
const modalNameCamelCase = toCamelCase(modalName);

export default {
    name: "ConfirmModal",

    computed: {
        ...mapState({
            show: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].shown
                    : false,
            resolve: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].resolve
                    : null,
            options: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].options
                    : {},
        }),
    },

    methods: {
        ...mapActions(["initModal", "closeModal"]),

        confirm() {
            if (this.resolve) {
                this.resolve(true);
            }
            this.closeModal({ modalName });
        },

        cancel() {
            if (this.resolve) {
                this.resolve(false);
            }
            this.closeModal({ modalName });
        },
    },

    created() {
        this.initModal({ modalName });
    },
};
</script>
