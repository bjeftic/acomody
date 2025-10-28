<template>
    <div class="max-w-4xl mx-auto pb-12">
        <!-- Main Content -->
        <div class="mx-auto">
            <template v-if="loading">
                <form-skeleton />
            </template>
            <template v-else>
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
                                    v-for="type in accommodationTypes"
                                    :key="type.id"
                                    :id="type.id"
                                    :title="type.name"
                                    :icon="type.icon"
                                    :tooltip="type.description"
                                    :selected="
                                        formData.propertyType === type.id
                                    "
                                    @select="selectPropertyType"
                                >
                                <template #icon>
                                    <component
                                        :is="type.icon + 'Icon'"
                                    ></component>
                                </template>
                                </select-action-card>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Occupation Type -->
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
                                v-for="occupation in accommodationOccupations"
                                :key="occupation.id"
                                :title="occupation.name"
                                :description="occupation.description"
                                :selected="
                                    formData.accommodationOccupation === occupation.id
                                "
                                @click="selectAccommodationOccupation(occupation.id)"
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
            </template>
        </div>

        <!-- Footer Navigation -->
        <div v-if="!loading" class="border-t border-gray-200 dark:border-gray-800">
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
    name: "CreateAccommodation",
    components: {
        BaseWrapper,
    },
    data() {
        return {
            loading: true,
            totalSteps: 8,
            formData: {
                propertyType: null,
                accommodationOccupation: null,
                address: {
                    country: "",
                    street: "",
                    city: "",
                    state: "",
                    zipCode: "",
                },
            },
            createAccommodationErrors: {},
        };
    },
    computed: {
        ...mapState("ui", ["countries"]),
        ...mapState("hosting", [
            "accommodationDraft",
        ]),
        ...mapState("hosting/createAccommodation", [
            "accommodationTypes",
            "currentStep",
        ]),
        accommodationOccupations() {
            return this.accommodationTypes.find(
                (type) => type.id === this.formData.propertyType
            )?.available_occupations || [];
        },
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
                    return this.formData.accommodationOccupation !== null;
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
    watch: {
        accommodationDraft: {
            immediate: true,
            deep: true,
            handler(newDraft) {
                if (newDraft) {
                    this.loadDraftData(newDraft);
                }
            },
        },
    },
    methods: {
        ...mapActions("hosting/createAccommodation", [
            "fetchAccommodationTypes",
            "updateAccommodationDraft",
            "incrementCurrentStep",
            "decrementCurrentStep",
        ]),
        ...mapActions("hosting", ["fetchAccommodationDraft"]),
        selectPropertyType(typeId) {
            this.formData.propertyType = typeId;
            this.formData.accommodationOccupation = null;
        },
        selectAccommodationOccupation(occupationId) {
            this.formData.accommodationOccupation = occupationId;
        },
        async nextStep() {
            if (this.canProceed) {
                if (this.currentStep < this.totalSteps) {
                    const draftData = {
                        property_type: this.formData.propertyType,
                        accommodation_occupation: this.formData.accommodationOccupation,
                        address: {
                            country: this.formData.address.country,
                            street: this.formData.address.street,
                            city: this.formData.address.city,
                            state: this.formData.address.state,
                            zip_code: this.formData.address.zipCode,
                        }
                    };

                    try {
                        await this.updateAccommodationDraft({
                            draftData,
                            currentStep: this.currentStep + 1
                        });
                        this.incrementCurrentStep();
                    } catch (error) {
                        console.error('Error updating draft:', error);
                        if (error.response?.data?.errors) {
                            this.createAccommodationErrors = error.response.data.errors;
                        }
                    }
                } else {
                    this.submitListing();
                }
            }
        },
        previousStep() {
            if (this.currentStep > 1) {
                this.decrementCurrentStep();
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
        async handleSaveAndExit() {
            try {
                const draftData = {
                    property_type: this.formData.propertyType,
                    accommodation_occupation: this.formData.accommodationOccupation,
                    address: {
                        country: this.formData.address.country,
                        street: this.formData.address.street,
                        city: this.formData.address.city,
                        state: this.formData.address.state,
                        zip_code: this.formData.address.zipCode,
                    }
                };

                await this.updateAccommodationDraft({
                    draftData,
                    currentStep: this.currentStep
                });
                this.$router.push({ name: "page-hosting-home" });
            } catch (error) {
                console.error('Error saving draft:', error);
            }
        },
        submitListing() {
            console.log("Submitting listing:", this.formData);
            // Implement submit logic here
            this.$router.push({ name: "page-hosting-listings" });
        },
        loadDraftData(draft) {
            this.formData = {
                propertyType: draft.property_type || null,
                accommodationOccupation: draft.accommodation_occupation || null,
                address: {
                    country: draft.address?.country || "",
                    street: draft.address?.street || "",
                    city: draft.address?.city || "",
                    state: draft.address?.state || "",
                    zipCode: draft.address?.zip_code || "",
                },
            };
        },
    },
    async created() {
        await this.fetchAccommodationTypes();
        if (!this.accommodationDraft) {
            await this.fetchAccommodationDraft()
            .finally(() => {
                this.loading = false;
            });
        }
        this.loading = false;
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
