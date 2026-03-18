<template>
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
                Host Profile
            </h1>
            <p class="text-base text-gray-600 dark:text-gray-400">
                Update your host profile information visible to guests. Display name, contact email, and phone are
                required before your listings appear in search.
            </p>
        </div>

        <!-- Avatar -->
        <section class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Profile photo
            </h2>
            <div class="flex items-center gap-6">
                <div class="relative w-24 h-24 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 cursor-pointer flex-shrink-0"
                    @click="triggerAvatarUpload">
                    <img v-if="avatarPreview || form.avatar_url" :src="avatarPreview || form.avatar_url"
                        alt="Host avatar" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
                        </svg>
                    </div>
                    <div
                        class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Click the avatar to upload a new photo. This photo will appear on your listings.
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        JPEG, PNG or WebP — max 5MB
                    </p>
                    <p v-if="avatarUploading" class="text-xs text-primary-500 mt-1">
                        Uploading...
                    </p>
                    <p v-if="avatarError" class="text-xs text-red-500 mt-1">
                        {{ avatarError }}
                    </p>
                </div>
                <input ref="avatarInput" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden"
                    @change="onAvatarSelected" />
            </div>
        </section>

        <!-- Alert -->
        <div
            v-if="alert.message"
            class="mb-6 px-4 py-3 rounded-xl text-sm border"
            :class="alert.type === 'success'
                ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300'
                : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'"
        >
            {{ alert.message }}
        </div>

        <!-- Form -->
        <form @submit.prevent="submit">
            <!-- Required fields -->
            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Contact information
                    <span class="text-sm font-normal text-gray-400 ml-1">* required</span>
                </h2>
                <div class="flex flex-col gap-4">
                    <BaseInput
                        v-model="form.display_name"
                        label="Display name"
                        placeholder="Your name or business name shown to guests"
                        :error="errors.display_name ? errors.display_name[0] : null"
                        required
                    />
                    <BaseInput
                        v-model="form.contact_email"
                        type="email"
                        label="Contact email"
                        placeholder="Email for guest communication"
                        :error="errors.contact_email ? errors.contact_email[0] : null"
                        required
                    />
                    <BaseInput
                        v-model="form.phone"
                        type="tel"
                        label="Phone"
                        placeholder="+381 60 000 0000"
                        :error="errors.phone ? errors.phone[0] : null"
                        required
                    />
                </div>
            </section>

            <!-- Optional fields -->
            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Business details
                </h2>
                <div class="flex flex-col gap-4">
                    <BaseInput
                        v-model="form.business_name"
                        label="Business name"
                        placeholder="Optional — if you operate as a company"
                    />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <BaseInput
                            v-model="form.tax_id"
                            label="Tax ID"
                            placeholder="Optional"
                        />
                        <BaseInput
                            v-model="form.vat_number"
                            label="VAT number"
                            placeholder="Optional"
                        />
                    </div>
                </div>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Location
                </h2>
                <div class="flex flex-col gap-4">
                    <BaseInput
                        v-model="form.address"
                        label="Address"
                        placeholder="Street address (optional)"
                    />
                    <BaseInput
                        v-model="form.city"
                        label="City"
                        placeholder="City (optional)"
                    />
                </div>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    About you
                </h2>
                <BaseTextarea
                    v-model="form.bio"
                    label="Bio"
                    placeholder="Tell guests a bit about yourself as a host (optional)"
                    :rows="4"
                    :maxlength="2000"
                />
            </section>

            <div class="flex items-center gap-3">
                <BaseButton type="submit" size="lg" :loading="submitting" :disabled="submitting">
                    {{ submitting ? 'Saving...' : 'Save changes' }}
                </BaseButton>
                <BaseButton
                    v-if="isEditing"
                    variant="secondary"
                    size="lg"
                    @click="$router.push({ name: 'page-dashboard' })"
                >
                    Cancel
                </BaseButton>
                <BaseButton
                    v-if="$route.query.next === 'listing-create'"
                    variant="link"
                    size="sm"
                    @click="$router.push({ name: 'page-listing-create' })"
                >
                    Skip — create accommodation first
                </BaseButton>
            </div>
        </form>

        <!-- Danger Zone -->
        <template v-if="isEditing && !$route.query.next">
            <hr class="border-gray-200 dark:border-gray-700 my-10" />

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

                <div class="flex items-start justify-between gap-6 p-4 border border-rose-200 dark:border-rose-900 rounded-xl">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            Delete hosting account
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Removes your host profile and all accommodation listings. Your user account stays active.
                        </p>
                    </div>
                    <BaseButton variant="danger" size="sm" @click="deletionModal = true">
                        Request deletion
                    </BaseButton>
                </div>

                <!-- Confirmation Modal -->
                <fwb-modal v-if="deletionModal" @close="closeDeletionModal">
                    <template #header>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Delete hosting account
                        </h3>
                    </template>
                    <template #body>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            This will request the deletion of your host profile and all accommodation listings. Your
                            user account will remain active. An admin will review and process your request.
                        </p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type <span class="font-bold text-rose-600">DELETE</span> to confirm:
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
        </template>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from "vuex";
import apiClient from "@/services/apiClient"; // TODO: move profile submit/avatar calls to actions

export default {
    name: "HostProfilePage",
    data() {
        return {
            form: {
                display_name: "",
                business_name: "",
                contact_email: "",
                phone: "",
                address: "",
                city: "",
                tax_id: "",
                vat_number: "",
                bio: "",
                avatar_url: null,
            },
            errors: {},
            initializing: false,
            submitting: false,
            alert: { message: "", type: "success" },
            avatarUploading: false,
            avatarError: "",
            avatarPreview: null,
            deletionAlert: { message: "", type: "success" },
            deletionModal: false,
            deletionConfirmWord: "",
            deletionRequesting: false,
        };
    },
    computed: {
        ...mapState({ currentUser: (state) => state.user.currentUser }),
        ...mapGetters("user", ["isHost"]),
        isEditing() {
            return this.isHost;
        },
    },
    async mounted() {
        if (!this.isEditing) {
            this.initializing = true;
            try {
                await this.initializeHostProfile();
                await this.fetchUser();
            } finally {
                this.initializing = false;
            }
        }
        this.populateForm();
    },
    methods: {
        ...mapActions("user", ["fetchUser", "initializeHostProfile", "requestHostAccountDeletion"]),
        populateForm() {
            const hp = this.currentUser?.host_profile;
            if (!hp) return;
            this.form.display_name = hp.display_name || "";
            this.form.business_name = hp.business_name || "";
            this.form.contact_email = hp.contact_email || "";
            this.form.phone = hp.phone || "";
            this.form.address = hp.address || "";
            this.form.city = hp.city || "";
            this.form.tax_id = hp.tax_id || "";
            this.form.vat_number = hp.vat_number || "";
            this.form.bio = hp.bio || "";
            this.form.avatar_url = hp.avatar_url || null;
        },
        async submit() {
            this.errors = {};
            this.submitting = true;
            try {
                const payload = {
                    display_name: this.form.display_name,
                    business_name: this.form.business_name || null,
                    contact_email: this.form.contact_email,
                    phone: this.form.phone,
                    address: this.form.address || null,
                    city: this.form.city || null,
                    tax_id: this.form.tax_id || null,
                    vat_number: this.form.vat_number || null,
                    bio: this.form.bio || null,
                };

                await apiClient.host.profile.put(payload);

                await this.fetchUser();

                if (this.$route.query.next === "listing-create") {
                    this.$router.push({ name: "page-listing-create" });
                    return;
                }

                this.alert = { message: "Host profile updated successfully.", type: "success" };
            } catch (error) {
                if (error.status === 422) {
                    this.errors = error.error?.errors || {};
                } else {
                    this.alert = {
                        message: error.error?.message || "Something went wrong. Please try again.",
                        type: "danger",
                    };
                }
            } finally {
                this.submitting = false;
            }
        },
        triggerAvatarUpload() {
            if (!this.isEditing) return;
            this.$refs.avatarInput.click();
        },
        async onAvatarSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.avatarError = "";
            this.avatarPreview = URL.createObjectURL(file);
            this.avatarUploading = true;

            try {
                await apiClient.host.profile.avatar.upload(file, "avatar");
                await this.fetchUser();
                this.form.avatar_url = this.currentUser?.host_profile?.avatar_url || null;
            } catch {
                this.avatarError = "Failed to upload avatar. Please try again.";
                this.avatarPreview = null;
            } finally {
                this.avatarUploading = false;
                event.target.value = "";
            }
        },

        closeDeletionModal() {
            this.deletionModal = false;
            this.deletionConfirmWord = "";
        },

        async submitDeletionRequest() {
            this.deletionRequesting = true;
            this.deletionAlert = { message: "", type: "success" };

            try {
                await this.requestHostAccountDeletion();
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
