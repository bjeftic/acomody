<template>
    <div class="max-w-4xl mx-auto pb-12">
        <!-- Main Content -->
        <div class="mx-auto">
            <!-- Step Content -->
            <transition name="fade" mode="out-in">
                <div :key="currentStep">
                    <!-- Step 1: Property Type -->
                    <div v-if="currentStep === 1">
                        <h1
                            class="text-3xl font-semibold text-gray-900 dark:text-white mb-8"
                        >
                            Step: {{ currentStep }} - Which of these best
                            describes your place?
                        </h1>

                        <hr />

                        <div class="flex items-center justify-center">
                            <div
                                class="max-w-2xl grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 overflow-auto max-h-[500px] py-4 pr-4"
                            >
                                <select-action-card
                                    v-for="type in propertyTypes"
                                    :key="type.id"
                                    :id="type.id"
                                    :title="type.name"
                                    :icon="type.icon"
                                    :selected="
                                        formData.propertyType === type.id
                                    "
                                    @select="selectPropertyType"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Location Type -->
                    <div v-else-if="currentStep === 2">
                        <h1
                            class="text-3xl font-semibold text-gray-900 dark:text-white mb-8"
                        >
                            Step: {{ currentStep }} - What type of place will
                            guests have?
                        </h1>

                        <hr />

                        <div class="space-y-4 py-4">
                            <action-card
                                v-for="location in locationTypes"
                                :key="location.id"
                                :title="location.name"
                                :description="location.description"
                                :selected="
                                    formData.locationType === location.id
                                "
                                @click="selectLocationType(location.id)"
                            />
                        </div>
                    </div>

                    <!-- Step 3: Address -->
                    <div v-else-if="currentStep === 3">
                        <h1
                            class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            Step: {{ currentStep }} - Where's your place
                            located?
                        </h1>

                        <p
                            class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                        >
                            Your address is only shared with guests after
                            they've made a reservation.
                        </p>

                        <hr />

                        <div class="space-y-6 py-4">
                            <!-- Country -->
                            <fwb-select
                                v-model="formData.address.country"
                                :options="countryOptions"
                                label="Country"
                                placeholder="Select a country"
                            />
                            <p
                                v-if="createListingErrors.address"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ createListingErrors.address.country[0] }}
                            </p>

                            <!-- Street Address -->
                            <fwb-input
                                v-model="formData.address.street"
                                placeholder="Street address"
                                label="Street address"
                            />

                            <!-- City -->
                            <fwb-input
                                v-model="formData.address.city"
                                placeholder="City"
                                label="City"
                            />

                            <!-- State/Province & Zip Code -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <fwb-input
                                        v-model="formData.address.state"
                                        placeholder="State"
                                        label="State"
                                    />
                                </div>
                                <div>
                                    <fwb-input
                                        v-model="formData.address.zipCode"
                                        placeholder="Zip code"
                                        label="Zip code"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add more steps as needed -->
                </div>
            </transition>
        </div>

        <!-- Footer Navigation -->
        <div class="border-t border-gray-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto py-4">
                <div class="flex items-center justify-between">
                    <!-- Back Button -->
                    <fwb-button
                        v-if="currentStep > 1"
                        color="alternative"
                        outline="false"
                        @click="previousStep"
                    >
                        <div class="flex">
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                            <span>Back</span>
                        </div>
                    </fwb-button>
                    <div v-else></div>
                    <!-- Next/Continue Button -->
                    <fwb-button
                        @click="nextStep"
                        :disabled="!canProceed"
                        class="px-6"
                        color="default"
                    >
                        {{ currentStep === totalSteps ? "Finish" : "Next" }}
                    </fwb-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BaseWrapper from "@/src/layouts/BaseWrapper.vue";
import { mapState, mapActions } from "vuex";

export default {
    name: "CreateListing",
    components: {
        BaseWrapper,
    },
    data() {
        return {
            currentStep: 1,
            totalSteps: 3,
            formData: {
                propertyType: null,
                locationType: null,
                address: {
                    country: "",
                    street: "",
                    city: "",
                    state: "",
                    zipCode: "",
                },
            },
            propertyTypes: [
                { id: "house", name: "House", icon: "🏠" },
                { id: "apartment", name: "Apartment", icon: "🏢" },
                { id: "barn", name: "Barn", icon: "🚜" },
                { id: "bed-breakfast", name: "Bed & breakfast", icon: "🛏️" },
                { id: "boat", name: "Boat", icon: "⛵" },
                { id: "cabin", name: "Cabin", icon: "🏕️" },
                { id: "camper", name: "Camper/RV", icon: "🚐" },
                { id: "casa", name: "Casa particular", icon: "🏘️" },
                { id: "castle", name: "Castle", icon: "🏰" },
                { id: "cave", name: "Cave", icon: "⛰️" },
                { id: "container", name: "Container", icon: "📦" },
                { id: "cycladic", name: "Cycladic home", icon: "🏛️" },
            ],
            locationTypes: [
                {
                    id: "entire-place",
                    name: "An entire place",
                    description: "Guests have the whole place to themselves.",
                },
                {
                    id: "private-room",
                    name: "A private room",
                    description:
                        "Guests have their own room in a home, plus access to shared spaces.",
                },
                {
                    id: "shared-room",
                    name: "A shared room",
                    description:
                        "Guests sleep in a room or common area that may be shared with you or others.",
                },
            ],
            createListingErrors: {},
        };
    },
    computed: {
        ...mapState("ui", ["countries"]),
        countryOptions() {
            if (!this.countries) return [];
            return this.countries.map(country => ({
                value: country.iso_code_2,
                name: country.name
            }));
        },
        canProceed() {
            switch (this.currentStep) {
                case 1:
                    return this.formData.propertyType !== null;
                case 2:
                    return this.formData.locationType !== null;
                case 3:
                    return (
                        this.formData.address.country &&
                        this.formData.address.street &&
                        this.formData.address.city
                    );
                default:
                    return true;
            }
        },
    },
    methods: {
        ...mapActions("hosting/createListing", [
            "fetchAccommodationTypes",
        ]),
        selectPropertyType(typeId) {
            this.formData.propertyType = typeId;
        },
        selectLocationType(locationId) {
            this.formData.locationType = locationId;
        },
        nextStep() {
            if (this.canProceed) {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                } else {
                    this.submitListing();
                }
            }
        },
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        handleExit() {
            if (
                confirm(
                    "Are you sure you want to exit? Your progress will be lost."
                )
            ) {
                this.$router.push({ name: "page-hosting-home" });
            }
        },
        handleSaveAndExit() {
            // Implement save draft functionality
            console.log("Saving draft...", this.formData);
            this.$router.push({ name: "page-hosting-home" });
        },
        submitListing() {
            console.log("Submitting listing:", this.formData);
            // Implement submit logic here
            this.$router.push({ name: "page-hosting-listings" });
        },
    },
    created() {
        this.fetchAccommodationTypes();
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
