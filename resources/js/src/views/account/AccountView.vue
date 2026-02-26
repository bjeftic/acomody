<template>
    <div class="max-w-2xl mx-auto py-12 px-4">
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
                    <p v-if="avatarUploading" class="text-xs text-blue-500 mt-1">
                        Uploading...
                    </p>
                    <p v-if="avatarError" class="text-xs text-red-500 mt-1">
                        {{ avatarError }}
                    </p>
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
            <fwb-alert
                v-if="profileAlert.message"
                :type="profileAlert.type"
                class="mb-4"
                closable
                @close="profileAlert.message = ''"
            >
                {{ profileAlert.message }}
            </fwb-alert>

            <!-- Profile form -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        First name
                    </label>
                    <fwb-input
                        v-model="profileForm.first_name"
                        placeholder="First name"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Last name
                    </label>
                    <fwb-input
                        v-model="profileForm.last_name"
                        placeholder="Last name"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Phone
                    </label>
                    <fwb-input
                        v-model="profileForm.phone"
                        placeholder="Phone number"
                    />
                </div>

                <fwb-button
                    :loading="profileSaving"
                    :disabled="profileSaving"
                    @click="saveProfile"
                >
                    Save profile
                </fwb-button>
            </div>
        </section>

        <hr class="border-gray-200 dark:border-gray-700 mb-10" />

        <!-- Security Section -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                Security
            </h2>

            <!-- Password alert -->
            <fwb-alert
                v-if="passwordAlert.message"
                :type="passwordAlert.type"
                class="mb-4"
                closable
                @close="passwordAlert.message = ''"
            >
                {{ passwordAlert.message }}
            </fwb-alert>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Current password
                    </label>
                    <fwb-input
                        v-model="passwordForm.current_password"
                        type="password"
                        placeholder="Current password"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        New password
                    </label>
                    <fwb-input
                        v-model="passwordForm.password"
                        type="password"
                        placeholder="New password"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Confirm new password
                    </label>
                    <fwb-input
                        v-model="passwordForm.confirm_password"
                        type="password"
                        placeholder="Confirm new password"
                    />
                </div>

                <fwb-button
                    :loading="passwordSaving"
                    :disabled="passwordSaving"
                    @click="savePassword"
                >
                    Change password
                </fwb-button>
            </div>
        </section>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";

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
        };
    },

    computed: {
        ...mapState({
            currentUser: (state) => state.user.currentUser,
        }),
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
        ...mapActions("user", ["updateProfile", "updatePassword", "uploadAvatar"]),

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
                this.avatarError =
                    error?.error?.message ||
                    "Failed to upload avatar. Please try again.";
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
                this.profileAlert = {
                    type: "success",
                    message: "Profile updated successfully.",
                };
            } catch (error) {
                this.profileAlert = {
                    type: "danger",
                    message:
                        error?.error?.message ||
                        "Failed to update profile. Please try again.",
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
                this.passwordAlert = {
                    type: "success",
                    message: "Password changed successfully.",
                };
                this.passwordForm = {
                    current_password: "",
                    password: "",
                    confirm_password: "",
                };
            } catch (error) {
                const errors = error?.error?.errors;
                const firstError = errors
                    ? Object.values(errors).flat()[0]
                    : null;
                this.passwordAlert = {
                    type: "danger",
                    message:
                        firstError ||
                        error?.error?.message ||
                        "Failed to change password. Please try again.",
                };
            } finally {
                this.passwordSaving = false;
            }
        },
    },
};
</script>
