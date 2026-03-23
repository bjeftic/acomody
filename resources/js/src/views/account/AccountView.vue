<template>
    <div class="max-w-4xl mx-auto py-12 px-4">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-8">
            My Account
        </h1>

        <!-- Profile Section -->
        <section class="mb-10">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                Profile
            </h2>

            <!-- Avatar -->
            <div class="flex items-center gap-6 mb-6">
                <div
                    class="relative w-20 h-20 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 cursor-pointer flex-shrink-0"
                    @click="triggerAvatarUpload"
                >
                    <img
                        v-if="currentUser?.avatar_url"
                        :src="currentUser.avatar_url"
                        alt="Avatar"
                        class="w-full h-full object-cover"
                    />
                    <div
                        v-else
                        class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500"
                    >
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Click the avatar to upload a new photo.
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                        JPEG, PNG or WebP — max 5MB
                    </p>
                    <p v-if="avatarUploading" class="text-xs text-primary-500 mt-1">Uploading...</p>
                    <p v-if="avatarError" class="text-xs text-red-500 mt-1">{{ avatarError }}</p>
                </div>
                <input
                    ref="avatarInput"
                    type="file"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    class="hidden"
                    @change="onAvatarSelected"
                />
            </div>

            <!-- Profile alert -->
            <div
                v-if="profileAlert.message"
                class="mb-4 px-4 py-3 rounded-xl text-sm border"
                :class="profileAlert.type === 'success'
                    ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300'
                    : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'"
            >
                {{ profileAlert.message }}
            </div>

            <!-- Profile form -->
            <div class="space-y-4">
                <BaseInput v-model="profileForm.first_name" label="First name" placeholder="First name" />
                <BaseInput v-model="profileForm.last_name" label="Last name" placeholder="Last name" />
                <BaseInput v-model="profileForm.phone" label="Phone" placeholder="Phone number" />

                <BaseButton :loading="profileSaving" :disabled="profileSaving" @click="saveProfile">
                    Save profile
                </BaseButton>
            </div>
        </section>

        <hr class="border-gray-200 dark:border-gray-700 mb-10" />

        <!-- Host Profile Section -->
        <section v-if="isHost" class="mb-10">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Host profile
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Manage the information guests see on your listings.
            </p>
            <BaseButton variant="secondary" @click="$router.push({ name: 'page-host-profile' })">
                Edit host profile
            </BaseButton>
        </section>

        <hr v-if="isHost" class="border-gray-200 dark:border-gray-700 mb-10" />

        <!-- Security Section -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                Security
            </h2>

            <!-- Password alert -->
            <div
                v-if="passwordAlert.message"
                class="mb-4 px-4 py-3 rounded-xl text-sm border"
                :class="passwordAlert.type === 'success'
                    ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300'
                    : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'"
            >
                {{ passwordAlert.message }}
            </div>

            <div class="space-y-4">
                <BaseInput v-model="passwordForm.current_password" type="password" label="Current password" placeholder="Current password" />
                <BaseInput v-model="passwordForm.password" type="password" label="New password" placeholder="New password" />
                <BaseInput v-model="passwordForm.confirm_password" type="password" label="Confirm new password" placeholder="Confirm new password" />

                <BaseButton :loading="passwordSaving" :disabled="passwordSaving" @click="savePassword">
                    Change password
                </BaseButton>
            </div>
        </section>

        <!-- Danger Zone -->
        <hr class="border-gray-200 dark:border-gray-700 mb-10 mt-10" />

        <section>
            <h2 class="text-xl font-semibold text-red-600 mb-6">
                Danger zone
            </h2>

            <div
                v-if="deletionAlert.message"
                class="mb-6 px-4 py-3 rounded-xl text-sm border"
                :class="deletionAlert.type === 'success'
                    ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300'
                    : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'"
            >
                {{ deletionAlert.message }}
            </div>

            <div class="flex flex-wrap items-start justify-between gap-4 p-4 border border-red-200 dark:border-red-900 rounded-xl">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete account</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Permanently deletes your entire account including host profile, listings, and all data.
                    </p>
                </div>
                <BaseButton variant="danger" size="sm" @click="deletionModal = true">
                    Request deletion
                </BaseButton>
            </div>

            <!-- Confirmation Modal -->
            <fwb-modal v-if="deletionModal" @close="closeDeletionModal">
                <template #header>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete account</h3>
                </template>
                <template #body>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        This will request the permanent deletion of your entire account, including any host profile and listings. An admin will review and process your request.
                    </p>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Type <span class="font-bold text-red-600">DELETE</span> to confirm:
                    </p>
                    <BaseInput v-model="deletionConfirmWord" placeholder="Type DELETE" />
                </template>
                <template #footer>
                    <div class="flex gap-3">
                        <BaseButton
                            variant="danger"
                            :disabled="deletionConfirmWord !== 'DELETE' || deletionRequesting"
                            :loading="deletionRequesting"
                            @click="submitDeletionRequest"
                        >
                            Submit deletion request
                        </BaseButton>
                        <BaseButton variant="secondary" @click="closeDeletionModal">
                            Cancel
                        </BaseButton>
                    </div>
                </template>
            </fwb-modal>
        </section>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from "vuex";

export default {
    name: "AccountView",

    data() {
        return {
            profileForm: {
                first_name: "",
                last_name: "",
                phone: "",
            },
            passwordForm: {
                current_password: "",
                password: "",
                confirm_password: "",
            },
            profileSaving: false,
            passwordSaving: false,
            avatarUploading: false,
            avatarError: "",
            profileAlert: { type: "success", message: "" },
            passwordAlert: { type: "success", message: "" },
            deletionAlert: { type: "success", message: "" },
            deletionModal: false,
            deletionConfirmWord: "",
            deletionRequesting: false,
        };
    },

    computed: {
        ...mapState({
            currentUser: (state) => state.user.currentUser,
        }),
        ...mapGetters("user", ["isHost"]),
    },

    watch: {
        currentUser: {
            immediate: true,
            handler(user) {
                if (user) {
                    this.profileForm.first_name = user.first_name || "";
                    this.profileForm.last_name = user.last_name || "";
                    this.profileForm.phone = user.phone || "";
                }
            },
        },
    },

    methods: {
        ...mapActions("user", ["updateProfile", "updatePassword", "uploadAvatar", "requestUserAccountDeletion"]),

        triggerAvatarUpload() {
            this.$refs.avatarInput.click();
        },

        async onAvatarSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.avatarUploading = true;
            this.avatarError = "";

            try {
                await this.uploadAvatar(file);
            } catch (error) {
                this.avatarError = error?.error?.message || "Failed to upload avatar. Please try again.";
            } finally {
                this.avatarUploading = false;
                event.target.value = "";
            }
        },

        async saveProfile() {
            this.profileSaving = true;
            this.profileAlert = { type: "success", message: "" };

            try {
                await this.updateProfile({ ...this.profileForm });
                this.profileAlert = { type: "success", message: "Profile updated successfully." };
            } catch (error) {
                this.profileAlert = {
                    type: "danger",
                    message: error?.error?.message || "Failed to update profile. Please try again.",
                };
            } finally {
                this.profileSaving = false;
            }
        },

        async savePassword() {
            this.passwordSaving = true;
            this.passwordAlert = { type: "success", message: "" };

            try {
                await this.updatePassword({ ...this.passwordForm });
                this.passwordAlert = { type: "success", message: "Password changed successfully." };
                this.passwordForm = { current_password: "", password: "", confirm_password: "" };
            } catch (error) {
                const errors = error?.error?.errors;
                const firstError = errors ? Object.values(errors).flat()[0] : null;
                this.passwordAlert = {
                    type: "danger",
                    message: firstError || error?.error?.message || "Failed to change password. Please try again.",
                };
            } finally {
                this.passwordSaving = false;
            }
        },

        closeDeletionModal() {
            this.deletionModal = false;
            this.deletionConfirmWord = "";
        },

        async submitDeletionRequest() {
            this.deletionRequesting = true;
            this.deletionAlert = { type: "success", message: "" };

            try {
                await this.requestUserAccountDeletion();
                this.closeDeletionModal();
                this.deletionAlert = {
                    type: "success",
                    message: "Your deletion request has been submitted. Our team will review and process it shortly.",
                };
            } catch (error) {
                this.closeDeletionModal();
                this.deletionAlert = {
                    type: "danger",
                    message: error?.error?.message || "Failed to submit deletion request. Please try again.",
                };
            } finally {
                this.deletionRequesting = false;
            }
        },
    },
};
</script>
