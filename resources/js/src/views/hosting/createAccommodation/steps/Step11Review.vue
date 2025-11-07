<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Review your listing
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Here's what we'll show to guests. Make sure everything looks good!
        </p>

        <hr />

        <div class="max-w-4xl mx-auto py-4 space-y-6">
            <!-- Listing Preview Card -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden"
            >
                <!-- Photos Preview -->
                <div class="relative aspect-video bg-gray-100 dark:bg-gray-900">
                    <img
                        v-if="formData.photos.length > 0"
                        :src="formData.photos[0].urls.large"
                        alt="Cover photo"
                        class="w-full h-full object-cover"
                    />
                    <div
                        v-else
                        class="w-full h-full flex items-center justify-center text-gray-400"
                    >
                        <span>No photos uploaded</span>
                    </div>
                    <div
                        class="absolute bottom-4 right-4 px-3 py-1 bg-white dark:bg-gray-900 rounded-lg shadow-lg"
                    >
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ formData.photos.length }} photos
                        </span>
                    </div>
                </div>

                <!-- Listing Details -->
                <div class="p-6">
                    <!-- Title & Location -->
                    <div class="mb-4">
                        <h2
                            class="text-2xl font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            {{ formData.title || "Untitled listing" }}
                        </h2>
                        <p class="text-base text-gray-600 dark:text-gray-400">
                            {{ formData.address.city }},
                            {{ formData.address.country }}
                        </p>
                    </div>

                    <!-- Quick Stats -->
                    <div
                        class="flex items-center space-x-6 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ formData.floorPlan.guests }} guests
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ formData.floorPlan.bedrooms }} bedrooms
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ formData.floorPlan.beds }} beds
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ formData.floorPlan.bathrooms }} bathrooms
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <p
                            class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3"
                        >
                            {{
                                formData.description ||
                                "No description provided"
                            }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div
                        class="pt-4 border-t border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-baseline">
                            <span
                                class="text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                ${{ formData.pricing.basePrice }}
                            </span>
                            <span
                                class="text-base text-gray-600 dark:text-gray-400 ml-1"
                            >
                                / night
                            </span>
                        </div>
                        <p
                            v-if="formData.pricing.hasWeekendPrice"
                            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                        >
                            Weekend: ${{ formData.pricing.weekendPrice }} /
                            night
                        </p>
                    </div>
                </div>
            </div>

            <!-- Editable Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Property Info -->
                <edit-section title="Property info" @edit="goToStep(1)">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Type:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ propertyTypeName }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Occupation:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ occupationTypeName }}
                            </span>
                        </div>
                    </div>
                </edit-section>

                <!-- Location -->
                <edit-section title="Location" @edit="goToStep(3)">
                    <div
                        class="space-y-1 text-sm text-gray-700 dark:text-gray-300"
                    >
                        <p>{{ formData.address.street }}</p>
                        <p>
                            {{ formData.address.city }},
                            {{ formData.address.state }}
                            {{ formData.address.zipCode }}
                        </p>
                        <p>{{ formData.address.country }}</p>
                    </div>
                </edit-section>

                <!-- Amenities -->
                <edit-section title="Amenities" @edit="goToStep(5)">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ formData.amenities.length }} amenities selected
                    </p>
                </edit-section>

                <!-- House Rules -->
                <edit-section title="House rules" @edit="goToStep(10)">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Check-in:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.houseRules.checkInFrom }} -
                                {{ formData.houseRules.checkInUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Check-out:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.houseRules.checkOutUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Cancellation:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium capitalize"
                            >
                                {{ formData.houseRules.cancellationPolicy }}
                            </span>
                        </div>
                    </div>
                </edit-section>
            </div>

            <!-- Legal Agreement -->
            <div
                class="p-6 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
            >
                <div class="flex items-start space-x-3">
                    <input
                        v-model="agreedToTerms"
                        type="checkbox"
                        id="terms-checkbox"
                        class="mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <label
                        for="terms-checkbox"
                        class="text-sm text-gray-700 dark:text-gray-300"
                    >
                        I agree to the
                        <a href="#" class="text-blue-600 hover:underline">
                            Host Terms of Service</a
                        >,
                        <a href="#" class="text-blue-600 hover:underline">
                            Cancellation Policy</a
                        >, and
                        <a href="#" class="text-blue-600 hover:underline"
                            >House Rules</a
                        >. I also acknowledge that I will comply with all
                        applicable laws and regulations.
                    </label>
                </div>
            </div>

            <!-- Publish Notice -->
            <div
                class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                >
                    <svg
                        class="w-5 h-5 mr-2 text-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    What happens next?
                </h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <span class="mr-2">1.</span>
                        <span>
                            Your listing will be reviewed by our team (usually
                            within 24 hours)
                        </span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">2.</span>
                        <span>Once approved, it will be visible to guests</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">3.</span>
                        <span>
                            You'll receive notifications when guests inquire or
                            book
                        </span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">4.</span>
                        <span>
                            You can edit your listing anytime from your
                            dashboard
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import EditSection from "@/src/views/hosting/createAccommodation/components/EditSection.vue";
import { mapActions } from "vuex/dist/vuex.cjs.js";

export default {
    name: "Step11Review",
    components: {
        EditSection,
    },
    props: {
        formData: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ["update:form-data", "go-to-step"],
    data() {
        return {
            agreedToTerms: false,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", [
            "accommodationDraftId",
            "accommodationTypes",
        ]),

        propertyTypeName() {
            if (!this.formData.propertyType) return "";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.propertyType
            );
            return type ? type.name : "";
        },

        occupationTypeName() {
            if (!this.formData.accommodationOccupation) return "";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.propertyType
            );
            const occupation = type?.available_occupations?.find(
                (o) => o.id === this.formData.accommodationOccupation
            );
            return occupation ? occupation.name : "";
        },
    },
    watch: {
        agreedToTerms(value) {
            this.$emit("update:form-data", {
                ...this.formData,
                agreedToTerms: value,
            });
        },
    },
    methods: {
        ...mapActions("hosting/createAccommodation", [
            "fetchPhotos",
            "goToStep",
        ]),
    },
    created() {
        this.fetchPhotos(this.accommodationDraftId);
    },
};
</script>
