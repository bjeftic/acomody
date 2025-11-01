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
                                Step: {{ currentStep }} - What type of place
                                will guests have?
                            </h1>

                            <hr />

                            <div class="space-y-4 py-4">
                                <action-card
                                    v-for="occupation in accommodationOccupations"
                                    :key="occupation.id"
                                    :title="occupation.name"
                                    :description="occupation.description"
                                    :selected="
                                        formData.accommodationOccupation ===
                                        occupation.id
                                    "
                                    @click="
                                        selectAccommodationOccupation(
                                            occupation.id
                                        )
                                    "
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
                        <!-- Step 4: Floor Plan / Guest Capacity -->
                        <div v-else-if="currentStep === 4">
                            <h1
                                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                            >
                                Share some basics about your place
                            </h1>
                            <p
                                class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                            >
                                You'll add more details later, like bed types.
                            </p>

                            <hr class="mb-8" />

                            <div class="space-y-8 max-w-xl mx-auto">
                                <!-- Guests -->
                                <div
                                    class="flex items-center justify-between py-6 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <div>
                                        <h3
                                            class="text-base font-medium text-gray-900 dark:text-white"
                                        >
                                            Guests
                                        </h3>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <button
                                            @click="decrementGuests"
                                            :disabled="
                                                formData.floorPlan.guests <= 1
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.guests <= 1
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 12H4"
                                                />
                                            </svg>
                                        </button>
                                        <span
                                            class="text-base font-medium text-gray-900 dark:text-white w-8 text-center"
                                        >
                                            {{ formData.floorPlan.guests }}
                                        </span>
                                        <button
                                            @click="incrementGuests"
                                            :disabled="
                                                formData.floorPlan.guests >= 16
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.guests >= 16
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4v16m8-8H4"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Bedrooms -->
                                <div
                                    class="flex items-center justify-between py-6 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <div>
                                        <h3
                                            class="text-base font-medium text-gray-900 dark:text-white"
                                        >
                                            Bedrooms
                                        </h3>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <button
                                            @click="decrementBedrooms"
                                            :disabled="
                                                formData.floorPlan.bedrooms <= 0
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.bedrooms <= 0
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 12H4"
                                                />
                                            </svg>
                                        </button>
                                        <span
                                            class="text-base font-medium text-gray-900 dark:text-white w-8 text-center"
                                        >
                                            {{ formData.floorPlan.bedrooms }}
                                        </span>
                                        <button
                                            @click="incrementBedrooms"
                                            :disabled="
                                                formData.floorPlan.bedrooms >=
                                                50
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.bedrooms >=
                                                50
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4v16m8-8H4"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Beds -->
                                <div
                                    class="flex items-center justify-between py-6 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <div>
                                        <h3
                                            class="text-base font-medium text-gray-900 dark:text-white"
                                        >
                                            Beds
                                        </h3>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <button
                                            @click="decrementBeds"
                                            :disabled="
                                                formData.floorPlan.beds <= 1
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.beds <= 1
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 12H4"
                                                />
                                            </svg>
                                        </button>
                                        <span
                                            class="text-base font-medium text-gray-900 dark:text-white w-8 text-center"
                                        >
                                            {{ formData.floorPlan.beds }}
                                        </span>
                                        <button
                                            @click="incrementBeds"
                                            :disabled="
                                                formData.floorPlan.beds >= 50
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.beds >= 50
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4v16m8-8H4"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Bathrooms -->
                                <div
                                    class="flex items-center justify-between py-6 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <div>
                                        <h3
                                            class="text-base font-medium text-gray-900 dark:text-white"
                                        >
                                            Bathrooms
                                        </h3>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <button
                                            @click="decrementBathrooms"
                                            :disabled="
                                                formData.floorPlan.bathrooms <=
                                                0.5
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.bathrooms <=
                                                0.5
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 12H4"
                                                />
                                            </svg>
                                        </button>
                                        <span
                                            class="text-base font-medium text-gray-900 dark:text-white w-12 text-center"
                                        >
                                            {{ formData.floorPlan.bathrooms }}
                                        </span>
                                        <button
                                            @click="incrementBathrooms"
                                            :disabled="
                                                formData.floorPlan.bathrooms >=
                                                20
                                            "
                                            :class="[
                                                'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                                                formData.floorPlan.bathrooms >=
                                                20
                                                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                                                    : 'border-gray-400 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4v16m8-8H4"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 5: Amenities -->
                        <div v-else-if="currentStep === 5">
                            <h1
                                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                            >
                                Tell guests what your place has to offer
                            </h1>
                            <p
                                class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                            >
                                You can add more amenities after you publish
                                your listing.
                            </p>

                            <hr class="mb-8" />

                            <div class="space-y-8">
                                <!-- Category: General Amenities -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        General
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in generalAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Bathroom -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Bathroom
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in bathroomAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Kitchen & Dining -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Kitchen and dining
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in kitchenAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Heating & Cooling -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Heating and cooling
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in heatingCoolingAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Entertainment -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Entertainment
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in entertainmentAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Internet & Office -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Internet and office
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in internetOfficeAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Parking & Facilities -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Parking and facilities
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in parkingFacilitiesAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Outdoor -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Outdoor
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in outdoorAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>

                                <!-- Category: Safety -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Safety items
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <amenity-checkbox
                                            v-for="amenity in safetyAmenities"
                                            :key="amenity.id"
                                            :amenity="amenity"
                                            :selected="
                                                formData.amenities.includes(
                                                    amenity.id
                                                )
                                            "
                                            @toggle="toggleAmenity(amenity.id)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 6: Photos -->
                        <div v-else-if="currentStep === 6">
                            <h1
                                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                            >
                                Add some photos of your place
                            </h1>
                            <p
                                class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                            >
                                You'll need at least 5 photos to get started.
                                You can add more or make changes later.
                            </p>

                            <hr class="mb-8" />

                            <!-- Upload Area -->
                            <div class="space-y-6">
                                <!-- Drag & Drop Zone -->
                                <div
                                    v-if="formData.photos.length < 20"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    :class="[
                                        'border-2 border-dashed rounded-xl p-12 text-center transition-all duration-200',
                                        isDragging
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                            : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600',
                                    ]"
                                >
                                    <div
                                        class="flex flex-col items-center space-y-4"
                                    >
                                        <!-- Upload Icon -->
                                        <svg
                                            class="w-16 h-16 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>

                                        <!-- Text -->
                                        <div>
                                            <p
                                                class="text-lg font-medium text-gray-900 dark:text-white mb-2"
                                            >
                                                Drag your photos here
                                            </p>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400 mb-4"
                                            >
                                                Choose at least 5 photos
                                            </p>
                                        </div>

                                        <!-- Upload Button -->
                                        <label
                                            for="photo-upload"
                                            class="px-6 py-3 bg-white dark:bg-gray-800 border-2 border-gray-900 dark:border-white text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                                        >
                                            Upload from your device
                                        </label>
                                        <input
                                            id="photo-upload"
                                            type="file"
                                            multiple
                                            accept="image/jpeg,image/png,image/jpg"
                                            @change="handleFileSelect"
                                            class="hidden"
                                        />

                                        <!-- Info Text -->
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            Maximum 20 photos • JPG or PNG • Max
                                            10MB each
                                        </p>
                                    </div>
                                </div>

                                <!-- Photo Grid -->
                                <div
                                    v-if="formData.photos.length > 0"
                                    class="space-y-4"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            {{ formData.photos.length }} / 20
                                            photos
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            Drag photos to reorder
                                        </p>
                                    </div>

                                    <!-- Draggable Photo Grid -->
                                    <draggable
                                        v-model="formData.photos"
                                        item-key="id"
                                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                                        :animation="200"
                                        ghost-class="opacity-50"
                                    >
                                        <template #item="{ element, index }">
                                            <div
                                                class="relative group aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 cursor-move"
                                            >
                                                <!-- Photo -->
                                                <img
                                                    :src="element.preview"
                                                    :alt="`Photo ${index + 1}`"
                                                    class="w-full h-full object-cover"
                                                />

                                                <!-- Cover Photo Badge -->
                                                <div
                                                    v-if="index === 0"
                                                    class="absolute top-2 left-2 px-3 py-1 bg-white dark:bg-gray-900 text-xs font-semibold rounded-full shadow-lg"
                                                >
                                                    Cover photo
                                                </div>

                                                <!-- Overlay on Hover -->
                                                <div
                                                    class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"
                                                >
                                                    <div
                                                        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                                    >
                                                        <!-- Delete Button -->
                                                        <button
                                                            @click="
                                                                removePhoto(
                                                                    index
                                                                )
                                                            "
                                                            class="p-2 bg-white dark:bg-gray-900 rounded-full hover:bg-red-500 hover:text-white transition-colors"
                                                        >
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
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Loading Indicator -->
                                                <div
                                                    v-if="element.uploading"
                                                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center"
                                                >
                                                    <svg
                                                        class="animate-spin h-8 w-8 text-white"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <circle
                                                            class="opacity-25"
                                                            cx="12"
                                                            cy="12"
                                                            r="10"
                                                            stroke="currentColor"
                                                            stroke-width="4"
                                                            fill="none"
                                                        />
                                                        <path
                                                            class="opacity-75"
                                                            fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                        </template>
                                    </draggable>
                                </div>

                                <!-- Error Messages -->
                                <div
                                    v-if="uploadErrors.length > 0"
                                    class="space-y-2"
                                >
                                    <div
                                        v-for="(error, index) in uploadErrors"
                                        :key="index"
                                        class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-start space-x-3"
                                    >
                                        <svg
                                            class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <p
                                            class="text-sm text-red-700 dark:text-red-300"
                                        >
                                            {{ error }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 7: Title -->
                        <div v-else-if="currentStep === 7">
                            <h1
                                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                            >
                                Now, let's give your {{ propertyTypeName }} a
                                title
                            </h1>
                            <p
                                class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                            >
                                Short titles work best. Have fun with it—you can
                                always change it later.
                            </p>

                            <hr class="mb-8" />

                            <div class="max-w-2xl mx-auto space-y-6">
                                <!-- Title Input -->
                                <div class="relative">
                                    <fwb-textarea
                                        v-model="formData.title"
                                        :rows="3"
                                        :maxlength="50"
                                        placeholder="Example: Cozy studio in the heart of the city"
                                        class="text-xl resize-none"
                                    />

                                    <!-- Character Counter -->
                                    <div
                                        class="flex items-center justify-between mt-2"
                                    >
                                        <p
                                            :class="[
                                                'text-sm font-medium',
                                                formData.title.length >= 50
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-gray-500 dark:text-gray-400',
                                            ]"
                                        >
                                            {{ formData.title.length }}/50
                                        </p>
                                    </div>
                                </div>

                                <!-- Quick Suggestions -->
                                <div class="space-y-4">
                                    <h3
                                        class="text-base font-medium text-gray-900 dark:text-white"
                                    >
                                        Need inspiration? Try one of these:
                                    </h3>

                                    <!-- Suggestion Cards -->
                                    <div class="space-y-3">
                                        <button
                                            v-for="(
                                                suggestion, index
                                            ) in titleSuggestions"
                                            :key="index"
                                            @click="
                                                selectTitleSuggestion(
                                                    suggestion
                                                )
                                            "
                                            class="w-full p-4 text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-gray-900 dark:hover:border-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-all group"
                                        >
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <p
                                                    class="text-base text-gray-900 dark:text-white font-medium flex-1"
                                                >
                                                    {{ suggestion }}
                                                </p>
                                                <svg
                                                    class="w-5 h-5 text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white flex-shrink-0 ml-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5l7 7-7 7"
                                                    />
                                                </svg>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tips -->
                                <div
                                    class="p-6 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
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
                                        Tips for a great title
                                    </h4>
                                    <ul
                                        class="space-y-2 text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Highlight what makes your place
                                                special</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Include the location or a
                                                unique feature</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Keep it clear and
                                                descriptive</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-red-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Avoid all caps or excessive
                                                punctuation!!!</span
                                            >
                                        </li>
                                    </ul>
                                </div>

                                <!-- Examples based on property type -->
                                <div
                                    class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
                                    >
                                        Popular {{ propertyTypeName }} titles
                                    </h4>
                                    <div class="space-y-2">
                                        <p
                                            v-for="(
                                                example, index
                                            ) in titleExamples"
                                            :key="index"
                                            class="text-sm text-gray-700 dark:text-gray-300 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-2 text-blue-500 flex-shrink-0"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            "{{ example }}"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 8: Description -->
                        <div v-else-if="currentStep === 8">
                            <h1
                                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                            >
                                Create your description
                            </h1>
                            <p
                                class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                            >
                                Share what makes your place special.
                            </p>

                            <hr class="mb-8" />

                            <div class="max-w-2xl mx-auto space-y-6">
                                <!-- Description Textarea -->
                                <div class="relative">
                                    <fwb-textarea
                                        v-model="formData.description"
                                        :rows="10"
                                        :maxlength="500"
                                        placeholder="Describe your space, the neighborhood, and what guests will love about staying here..."
                                        class="resize-none"
                                        @input="clearDescriptionError"
                                    />

                                    <!-- Character Counter -->
                                    <div
                                        class="flex items-center justify-between mt-2"
                                    >
                                        <p
                                            :class="[
                                                'text-sm font-medium',
                                                formData.description.length >=
                                                500
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-gray-500 dark:text-gray-400',
                                            ]"
                                        >
                                            {{
                                                formData.description.length
                                            }}/500
                                        </p>
                                    </div>
                                </div>

                                <!-- Quick Templates -->
                                <div class="space-y-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <h3
                                            class="text-base font-medium text-gray-900 dark:text-white"
                                        >
                                            Start with a template
                                        </h3>
                                        <button
                                            @click="
                                                showTemplates = !showTemplates
                                            "
                                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            {{
                                                showTemplates
                                                    ? "Hide templates"
                                                    : "Show templates"
                                            }}
                                        </button>
                                    </div>

                                    <!-- Template Cards -->
                                    <div v-if="showTemplates" class="space-y-3">
                                        <button
                                            v-for="(
                                                template, index
                                            ) in descriptionTemplates"
                                            :key="index"
                                            @click="
                                                selectDescriptionTemplate(
                                                    template
                                                )
                                            "
                                            class="w-full p-4 text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-gray-900 dark:hover:border-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-all group"
                                        >
                                            <div
                                                class="flex items-start justify-between mb-2"
                                            >
                                                <h4
                                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                                >
                                                    {{ template.title }}
                                                </h4>
                                                <svg
                                                    class="w-5 h-5 text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white flex-shrink-0 ml-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5l7 7-7 7"
                                                    />
                                                </svg>
                                            </div>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3"
                                            >
                                                {{ template.content }}
                                            </p>
                                        </button>
                                    </div>
                                </div>

                                <!-- Writing Tips -->
                                <div
                                    class="p-6 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
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
                                        Tips for a great description
                                    </h4>
                                    <ul
                                        class="space-y-2 text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Describe the atmosphere and
                                                vibe of your space</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Mention what's special about
                                                the location</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Highlight unique features or
                                                amenities</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Include nearby attractions or
                                                transportation</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Be honest and set clear
                                                expectations</span
                                            >
                                        </li>
                                    </ul>
                                </div>

                                <!-- Structure Guide -->
                                <div
                                    class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
                                    >
                                        Suggested structure
                                    </h4>
                                    <div class="space-y-4">
                                        <div>
                                            <h5
                                                class="text-sm font-medium text-gray-900 dark:text-white mb-1"
                                            >
                                                1. The Space
                                            </h5>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                Describe the rooms, layout, and
                                                overall feel of your place.
                                            </p>
                                        </div>
                                        <div>
                                            <h5
                                                class="text-sm font-medium text-gray-900 dark:text-white mb-1"
                                            >
                                                2. Guest Access
                                            </h5>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                Explain which areas guests can
                                                use (entire place, private room,
                                                etc.).
                                            </p>
                                        </div>
                                        <div>
                                            <h5
                                                class="text-sm font-medium text-gray-900 dark:text-white mb-1"
                                            >
                                                3. The Neighborhood
                                            </h5>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                Share what makes the area
                                                special and mention nearby
                                                spots.
                                            </p>
                                        </div>
                                        <div>
                                            <h5
                                                class="text-sm font-medium text-gray-900 dark:text-white mb-1"
                                            >
                                                4. Getting Around
                                            </h5>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                Note transportation options and
                                                parking information.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Word Count Helper -->
                                <div
                                    class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                                >
                                    <div class="flex items-center space-x-2">
                                        <svg
                                            class="w-5 h-5 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            Word count:
                                            <strong
                                                class="text-gray-900 dark:text-white"
                                                >{{ wordCount }}</strong
                                            >
                                        </span>
                                    </div>
                                    <div
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Recommended: 50-200 words
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 9: Pricing -->
                        <div v-else-if="currentStep === 9">
                            <h1
                                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                            >
                                Now, set your price
                            </h1>
                            <p
                                class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                            >
                                You can change it anytime.
                            </p>

                            <hr class="mb-8" />

                            <div class="max-w-2xl mx-auto space-y-8">
                                <!-- Base Price Input Section -->
                                <div class="text-center space-y-6">
                                    <!-- Large Price Display -->
                                    <div class="relative inline-block">
                                        <div
                                            class="flex items-baseline justify-center"
                                        >
                                            <span
                                                class="text-6xl font-semibold text-gray-900 dark:text-white mr-2"
                                            >
                                                {{ currency }}
                                            </span>
                                            <input
                                                v-model.number="
                                                    formData.pricing.basePrice
                                                "
                                                type="number"
                                                min="0"
                                                step="1"
                                                @input="clearPricingError"
                                                @focus="$event.target.select()"
                                                class="text-6xl font-semibold text-gray-900 dark:text-white bg-transparent border-0 border-b-4 border-gray-300 dark:border-gray-700 focus:border-gray-900 dark:focus:border-white focus:ring-0 w-48 text-center outline-none"
                                                placeholder="00"
                                            />
                                        </div>
                                        <p
                                            class="text-lg text-gray-600 dark:text-gray-400 mt-2"
                                        >
                                            base price per night
                                        </p>
                                    </div>

                                    <!-- Guest Price Preview -->
                                    <div
                                        class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg inline-block"
                                    >
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400 mb-1"
                                        >
                                            Guest price before taxes (weekday)
                                        </p>
                                        <p
                                            class="text-2xl font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ currency
                                            }}{{ guestPriceBeforeTaxes }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Weekend Pricing -->
                                <div
                                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                >
                                    <div
                                        class="flex items-start justify-between mb-4"
                                    >
                                        <div class="flex-1">
                                            <h3
                                                class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                                            >
                                                <svg
                                                    class="w-5 h-5 mr-2 text-purple-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                                Weekend Pricing
                                            </h3>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                Set a different price for Friday
                                                and Saturday nights
                                            </p>
                                        </div>
                                        <label
                                            class="relative inline-flex items-center cursor-pointer ml-4"
                                        >
                                            <input
                                                v-model="
                                                    formData.pricing
                                                        .hasWeekendPrice
                                                "
                                                type="checkbox"
                                                class="sr-only peer"
                                            />
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                                            ></div>
                                        </label>
                                    </div>

                                    <!-- Weekend Price Input -->
                                    <div
                                        v-if="formData.pricing.hasWeekendPrice"
                                        class="mt-4"
                                    >
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                        >
                                            Weekend price per night
                                        </label>
                                        <div class="relative max-w-xs">
                                            <span
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-xl font-semibold text-gray-500"
                                            >
                                                {{ currency }}
                                            </span>
                                            <input
                                                v-model.number="
                                                    formData.pricing
                                                        .weekendPrice
                                                "
                                                type="number"
                                                min="0"
                                                step="1"
                                                class="pl-12 w-full px-4 py-3 text-lg font-semibold border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                placeholder="0"
                                            />
                                        </div>
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400 mt-2"
                                        >
                                            Applies to Friday and Saturday
                                            nights
                                        </p>

                                        <!-- Weekend Price Preview -->
                                        <div
                                            class="mt-4 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg"
                                        >
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400 mb-1"
                                            >
                                                Guest price on weekends (before
                                                taxes)
                                            </p>
                                            <p
                                                class="text-xl font-semibold text-purple-600 dark:text-purple-400"
                                            >
                                                {{ currency
                                                }}{{ weekendGuestPrice }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Info Note -->
                                    <div
                                        v-else
                                        class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                    >
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            💡 If not set, the base price will
                                            apply to all nights including
                                            weekends
                                        </p>
                                    </div>
                                </div>

                                <!-- Long Stay Discounts -->
                                <div
                                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                >
                                    <div class="mb-4">
                                        <h3
                                            class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                                        >
                                            <svg
                                                class="w-5 h-5 mr-2 text-green-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Long Stay Discounts
                                        </h3>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            Offer discounts to guests who book
                                            longer stays
                                        </p>
                                    </div>

                                    <div class="space-y-4">
                                        <!-- Weekly Discount -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            >
                                                Weekly discount (7+ nights)
                                            </label>
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <div
                                                    class="relative flex-1 max-w-xs"
                                                >
                                                    <input
                                                        v-model.number="
                                                            formData.pricing
                                                                .weeklyDiscount
                                                        "
                                                        type="number"
                                                        min="0"
                                                        max="99"
                                                        step="1"
                                                        class="w-full px-4 py-2 pr-12 border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                        placeholder="0"
                                                    />
                                                    <span
                                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                                                    >
                                                        %
                                                    </span>
                                                </div>
                                                <div
                                                    v-if="
                                                        formData.pricing
                                                            .weeklyDiscount > 0
                                                    "
                                                    class="text-sm text-green-600 dark:text-green-400 font-medium"
                                                >
                                                    -{{ currency
                                                    }}{{ weeklyDiscountAmount }}
                                                    per night
                                                </div>
                                            </div>
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                            >
                                                Recommended: 10-15%
                                            </p>
                                        </div>

                                        <!-- Monthly Discount -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            >
                                                Monthly discount (28+ nights)
                                            </label>
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <div
                                                    class="relative flex-1 max-w-xs"
                                                >
                                                    <input
                                                        v-model.number="
                                                            formData.pricing
                                                                .monthlyDiscount
                                                        "
                                                        type="number"
                                                        min="0"
                                                        max="99"
                                                        step="1"
                                                        class="w-full px-4 py-2 pr-12 border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                        placeholder="0"
                                                    />
                                                    <span
                                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                                                    >
                                                        %
                                                    </span>
                                                </div>
                                                <div
                                                    v-if="
                                                        formData.pricing
                                                            .monthlyDiscount > 0
                                                    "
                                                    class="text-sm text-green-600 dark:text-green-400 font-medium"
                                                >
                                                    -{{ currency
                                                    }}{{
                                                        monthlyDiscountAmount
                                                    }}
                                                    per night
                                                </div>
                                            </div>
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                            >
                                                Recommended: 20-30%
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Discount Preview -->
                                    <div
                                        v-if="
                                            formData.pricing.weeklyDiscount >
                                                0 ||
                                            formData.pricing.monthlyDiscount > 0
                                        "
                                        class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg"
                                    >
                                        <h4
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
                                        >
                                            Pricing examples with discounts
                                        </h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span
                                                    class="text-gray-600 dark:text-gray-400"
                                                    >1 night</span
                                                >
                                                <span
                                                    class="font-medium text-gray-900 dark:text-white"
                                                    >{{ currency
                                                    }}{{
                                                        formData.pricing
                                                            .basePrice
                                                    }}</span
                                                >
                                            </div>
                                            <div
                                                v-if="
                                                    formData.pricing
                                                        .weeklyDiscount > 0
                                                "
                                                class="flex justify-between"
                                            >
                                                <span
                                                    class="text-gray-600 dark:text-gray-400"
                                                    >7 nights (with
                                                    {{
                                                        formData.pricing
                                                            .weeklyDiscount
                                                    }}% off)</span
                                                >
                                                <span
                                                    class="font-medium text-green-600 dark:text-green-400"
                                                    >{{ currency
                                                    }}{{ weeklyStayTotal }}
                                                    <span class="text-xs"
                                                        >({{ currency
                                                        }}{{
                                                            priceAfterWeeklyDiscount
                                                        }}/night)</span
                                                    ></span
                                                >
                                            </div>
                                            <div
                                                v-if="
                                                    formData.pricing
                                                        .monthlyDiscount > 0
                                                "
                                                class="flex justify-between"
                                            >
                                                <span
                                                    class="text-gray-600 dark:text-gray-400"
                                                    >28 nights (with
                                                    {{
                                                        formData.pricing
                                                            .monthlyDiscount
                                                    }}% off)</span
                                                >
                                                <span
                                                    class="font-medium text-green-600 dark:text-green-400"
                                                    >{{ currency
                                                    }}{{ monthlyStayTotal }}
                                                    <span class="text-xs"
                                                        >({{ currency
                                                        }}{{
                                                            priceAfterMonthlyDiscount
                                                        }}/night)</span
                                                    ></span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                >
                                    <div class="mb-4">
                                        <h3
                                            class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                                        >
                                            <svg
                                                class="w-5 h-5 mr-2 text-blue-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                                />
                                            </svg>
                                            Booking Type
                                        </h3>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            Choose how you want to receive
                                            reservations
                                        </p>
                                    </div>

                                    <div class="space-y-3">
                                        <!-- Instant Booking -->
                                        <button
                                            @click="
                                                formData.pricing.bookingType =
                                                    'instant'
                                            "
                                            :class="[
                                                'w-full p-4 border-2 rounded-xl text-left transition-all duration-200',
                                                formData.pricing.bookingType ===
                                                'instant'
                                                    ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
                                                    : 'border-gray-300 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div class="flex-1">
                                                    <div
                                                        class="flex items-center space-x-2 mb-2"
                                                    >
                                                        <svg
                                                            class="w-5 h-5 text-blue-500"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M13 10V3L4 14h7v7l9-11h-7z"
                                                            />
                                                        </svg>
                                                        <h4
                                                            class="text-base font-semibold text-gray-900 dark:text-white"
                                                        >
                                                            Instant Booking
                                                        </h4>
                                                    </div>
                                                    <p
                                                        class="text-sm text-gray-600 dark:text-gray-400"
                                                    >
                                                        Guests can book
                                                        immediately without
                                                        waiting for your
                                                        approval. Get more
                                                        bookings faster!
                                                    </p>
                                                </div>
                                                <div
                                                    v-if="
                                                        formData.pricing
                                                            .bookingType ===
                                                        'instant'
                                                    "
                                                    class="ml-4"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-900 dark:text-white"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                        </button>

                                        <!-- Request to Book -->
                                        <button
                                            @click="
                                                formData.pricing.bookingType =
                                                    'request'
                                            "
                                            :class="[
                                                'w-full p-4 border-2 rounded-xl text-left transition-all duration-200',
                                                formData.pricing.bookingType ===
                                                'request'
                                                    ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
                                                    : 'border-gray-300 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div class="flex-1">
                                                    <div
                                                        class="flex items-center space-x-2 mb-2"
                                                    >
                                                        <svg
                                                            class="w-5 h-5 text-purple-500"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                                            />
                                                        </svg>
                                                        <h4
                                                            class="text-base font-semibold text-gray-900 dark:text-white"
                                                        >
                                                            Request to Book
                                                        </h4>
                                                    </div>
                                                    <p
                                                        class="text-sm text-gray-600 dark:text-gray-400"
                                                    >
                                                        Guests send a booking
                                                        request that you can
                                                        approve or decline. You
                                                        have full control over
                                                        who stays.
                                                    </p>
                                                </div>
                                                <div
                                                    v-if="
                                                        formData.pricing
                                                            .bookingType ===
                                                        'request'
                                                    "
                                                    class="ml-4"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-900 dark:text-white"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                        </button>
                                    </div>

                                    <!-- Info Note -->
                                    <div
                                        class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                    >
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            <strong>Tip:</strong> Instant
                                            booking typically results in 3x more
                                            reservations compared to
                                            request-only listings.
                                        </p>
                                    </div>
                                </div>

                                <!-- Minimum Stay Requirements -->
                                <div
                                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                >
                                    <div class="mb-4">
                                        <h3
                                            class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                                        >
                                            <svg
                                                class="w-5 h-5 mr-2 text-orange-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            Minimum Stay Requirements
                                        </h3>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            Set the minimum number of nights
                                            guests must book
                                        </p>
                                    </div>

                                    <div class="space-y-6">
                                        <!-- General Minimum Stay -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            >
                                                Minimum stay (all days)
                                            </label>
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <div
                                                    class="relative flex-1 max-w-xs"
                                                >
                                                    <input
                                                        v-model.number="
                                                            formData.pricing
                                                                .minStay
                                                        "
                                                        type="number"
                                                        min="1"
                                                        max="365"
                                                        class="w-full px-4 py-2 border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                        placeholder="1"
                                                    />
                                                </div>
                                                <span
                                                    class="text-sm text-gray-600 dark:text-gray-400"
                                                >
                                                    night{{
                                                        formData.pricing
                                                            .minStay !== 1
                                                            ? "s"
                                                            : ""
                                                    }}
                                                </span>
                                            </div>
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400 mt-2"
                                            >
                                                Example: If set to 3, guests
                                                cannot book for 1 or 2 nights
                                            </p>
                                        </div>

                                        <!-- Day-Specific Minimum Stay -->
                                        <div>
                                            <div
                                                class="flex items-start justify-between mb-3"
                                            >
                                                <div>
                                                    <h4
                                                        class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                                    >
                                                        Day-specific minimum
                                                        stay
                                                    </h4>
                                                    <p
                                                        class="text-xs text-gray-500 dark:text-gray-400"
                                                    >
                                                        Set different minimum
                                                        stays for bookings
                                                        starting on specific
                                                        days
                                                    </p>
                                                </div>
                                                <label
                                                    class="relative inline-flex items-center cursor-pointer ml-4"
                                                >
                                                    <input
                                                        v-model="
                                                            formData.pricing
                                                                .hasDaySpecificMinStay
                                                        "
                                                        type="checkbox"
                                                        class="sr-only peer"
                                                    />
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                                                    ></div>
                                                </label>
                                            </div>

                                            <!-- Day Selection -->
                                            <div
                                                v-if="
                                                    formData.pricing
                                                        .hasDaySpecificMinStay
                                                "
                                                class="space-y-3"
                                            >
                                                <div
                                                    v-for="day in daysOfWeek"
                                                    :key="day.id"
                                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
                                                >
                                                    <div
                                                        class="flex items-center space-x-3"
                                                    >
                                                        <input
                                                            v-model="
                                                                formData.pricing
                                                                    .daySpecificMinStay[
                                                                    day.id
                                                                ].enabled
                                                            "
                                                            type="checkbox"
                                                            :id="`day-${day.id}`"
                                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                        />
                                                        <label
                                                            :for="`day-${day.id}`"
                                                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                                        >
                                                            {{ day.name }}
                                                        </label>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            formData.pricing
                                                                .daySpecificMinStay[
                                                                day.id
                                                            ].enabled
                                                        "
                                                        class="flex items-center space-x-2"
                                                    >
                                                        <input
                                                            v-model.number="
                                                                formData.pricing
                                                                    .daySpecificMinStay[
                                                                    day.id
                                                                ].nights
                                                            "
                                                            type="number"
                                                            min="1"
                                                            max="30"
                                                            class="w-20 px-3 py-1 text-sm border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                                                        />
                                                        <span
                                                            class="text-sm text-gray-600 dark:text-gray-400"
                                                            >nights</span
                                                        >
                                                    </div>
                                                </div>

                                                <!-- Example -->
                                                <div
                                                    class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                                >
                                                    <p
                                                        class="text-xs text-gray-600 dark:text-gray-400"
                                                    >
                                                        <strong
                                                            >Example:</strong
                                                        >
                                                        If you set Friday to 3
                                                        nights, guests whose
                                                        booking starts on Friday
                                                        must stay at least until
                                                        Monday.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price Breakdown -->
                                <div
                                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl space-y-4"
                                >
                                    <h3
                                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Price breakdown (weekday)
                                    </h3>

                                    <!-- Base Price -->
                                    <div
                                        class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            Base price
                                        </span>
                                        <span
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ currency
                                            }}{{
                                                formData.pricing.basePrice || 0
                                            }}
                                        </span>
                                    </div>

                                    <!-- Guest Service Fee -->
                                    <div
                                        class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                Guest service fee
                                            </span>
                                            <button
                                                @click="
                                                    showServiceFeeInfo =
                                                        !showServiceFeeInfo
                                                "
                                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                        <span
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ currency }}{{ serviceFee }}
                                        </span>
                                    </div>

                                    <!-- Service Fee Info -->
                                    <div
                                        v-if="showServiceFeeInfo"
                                        class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                    >
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            This fee helps us run our platform
                                            and provide 24/7 customer support
                                            for your trip.
                                        </p>
                                    </div>

                                    <!-- Total -->
                                    <div
                                        class="flex items-center justify-between pt-4"
                                    >
                                        <span
                                            class="text-base font-semibold text-gray-900 dark:text-white"
                                        >
                                            Guest pays
                                        </span>
                                        <span
                                            class="text-base font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ currency }}{{ guestPaysTotal }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Your Earnings -->
                                <div
                                    class="p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl"
                                >
                                    <div
                                        class="flex items-center justify-between mb-4"
                                    >
                                        <h3
                                            class="text-base font-semibold text-gray-900 dark:text-white"
                                        >
                                            You earn (per night)
                                        </h3>
                                        <span
                                            class="text-2xl font-bold text-green-600 dark:text-green-400"
                                        >
                                            {{ currency }}{{ youEarn }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        After the
                                        {{ hostServiceFeePercentage }}% host
                                        service fee
                                    </p>
                                </div>

                                <!-- Pricing Tips -->
                                <div
                                    class="p-6 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
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
                                        Pricing tips
                                    </h4>
                                    <ul
                                        class="space-y-2 text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Weekend prices are typically
                                                20-30% higher than weekday
                                                rates</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Long stay discounts attract
                                                guests planning extended
                                                trips</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Check similar listings in your
                                                area for competitive
                                                pricing</span
                                            >
                                        </li>
                                    </ul>
                                </div>

                                <!-- Competitive Analysis -->
                                <div
                                    class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
                                    >
                                        Similar listings in
                                        {{ formData.address.city }}
                                    </h4>
                                    <div class="space-y-2">
                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <span
                                                class="text-gray-600 dark:text-gray-400"
                                                >Average price</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-white"
                                                >{{ currency
                                                }}{{ averagePrice }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <span
                                                class="text-gray-600 dark:text-gray-400"
                                                >Price range</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-white"
                                                >{{ currency
                                                }}{{ priceRange.min }} -
                                                {{ currency
                                                }}{{ priceRange.max }}</span
                                            >
                                        </div>
                                    </div>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400 mt-3"
                                    >
                                        Based on similar {{ propertyTypeName }}s
                                        in your area
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- Step 10: House Rules & Guest Requirements -->
                        <div v-else-if="currentStep === 10">
                            <h1
                                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                            >
                                Set house rules for your guests
                            </h1>
                            <p
                                class="text-lg text-gray-600 dark:text-gray-400 mb-8"
                            >
                                Guests must agree to your house rules before
                                they book.
                            </p>

                            <hr class="mb-8" />

                            <div class="max-w-2xl mx-auto space-y-8">
                                <!-- Standard Rules -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Standard rules
                                    </h3>
                                    <div class="space-y-4">
                                        <!-- Smoking -->
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center space-x-3"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-600 dark:text-gray-400"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                                        />
                                                    </svg>
                                                    <div>
                                                        <h4
                                                            class="text-base font-medium text-gray-900 dark:text-white"
                                                        >
                                                            Smoking
                                                        </h4>
                                                        <p
                                                            class="text-sm text-gray-600 dark:text-gray-400"
                                                        >
                                                            Allow smoking inside
                                                            the property
                                                        </p>
                                                    </div>
                                                </div>
                                                <label
                                                    class="relative inline-flex items-center cursor-pointer"
                                                >
                                                    <input
                                                        v-model="
                                                            formData.houseRules
                                                                .allowSmoking
                                                        "
                                                        type="checkbox"
                                                        class="sr-only peer"
                                                    />
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                                                    ></div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Pets -->
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center space-x-3"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-600 dark:text-gray-400"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                        />
                                                    </svg>
                                                    <div>
                                                        <h4
                                                            class="text-base font-medium text-gray-900 dark:text-white"
                                                        >
                                                            Pets
                                                        </h4>
                                                        <p
                                                            class="text-sm text-gray-600 dark:text-gray-400"
                                                        >
                                                            Allow guests to
                                                            bring pets
                                                        </p>
                                                    </div>
                                                </div>
                                                <label
                                                    class="relative inline-flex items-center cursor-pointer"
                                                >
                                                    <input
                                                        v-model="
                                                            formData.houseRules
                                                                .allowPets
                                                        "
                                                        type="checkbox"
                                                        class="sr-only peer"
                                                    />
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                                                    ></div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Events/Parties -->
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center space-x-3"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-600 dark:text-gray-400"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                                                        />
                                                    </svg>
                                                    <div>
                                                        <h4
                                                            class="text-base font-medium text-gray-900 dark:text-white"
                                                        >
                                                            Events and parties
                                                        </h4>
                                                        <p
                                                            class="text-sm text-gray-600 dark:text-gray-400"
                                                        >
                                                            Allow guests to host
                                                            events or parties
                                                        </p>
                                                    </div>
                                                </div>
                                                <label
                                                    class="relative inline-flex items-center cursor-pointer"
                                                >
                                                    <input
                                                        v-model="
                                                            formData.houseRules
                                                                .allowEvents
                                                        "
                                                        type="checkbox"
                                                        class="sr-only peer"
                                                    />
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                                                    ></div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Children -->
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center space-x-3"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-600 dark:text-gray-400"
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
                                                    <div>
                                                        <h4
                                                            class="text-base font-medium text-gray-900 dark:text-white"
                                                        >
                                                            Children
                                                        </h4>
                                                        <p
                                                            class="text-sm text-gray-600 dark:text-gray-400"
                                                        >
                                                            Suitable for
                                                            children (2-12
                                                            years)
                                                        </p>
                                                    </div>
                                                </div>
                                                <label
                                                    class="relative inline-flex items-center cursor-pointer"
                                                >
                                                    <input
                                                        v-model="
                                                            formData.houseRules
                                                                .allowChildren
                                                        "
                                                        type="checkbox"
                                                        class="sr-only peer"
                                                    />
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                                                    ></div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Infants -->
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center space-x-3"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-600 dark:text-gray-400"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                                        />
                                                    </svg>
                                                    <div>
                                                        <h4
                                                            class="text-base font-medium text-gray-900 dark:text-white"
                                                        >
                                                            Infants
                                                        </h4>
                                                        <p
                                                            class="text-sm text-gray-600 dark:text-gray-400"
                                                        >
                                                            Suitable for infants
                                                            (under 2 years)
                                                        </p>
                                                    </div>
                                                </div>
                                                <label
                                                    class="relative inline-flex items-center cursor-pointer"
                                                >
                                                    <input
                                                        v-model="
                                                            formData.houseRules
                                                                .allowInfants
                                                        "
                                                        type="checkbox"
                                                        class="sr-only peer"
                                                    />
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                                                    ></div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Check-in/Check-out Times -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Check-in and check-out
                                    </h3>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <!-- Check-in Time -->
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                        >
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
                                            >
                                                Check-in time
                                            </label>
                                            <div class="space-y-3">
                                                <div>
                                                    <label
                                                        class="text-xs text-gray-500 dark:text-gray-400 mb-1 block"
                                                        >From</label
                                                    >
                                                    <select
                                                        v-model="
                                                            formData.houseRules
                                                                .checkInFrom
                                                        "
                                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                    >
                                                        <option
                                                            v-for="time in timeSlots"
                                                            :key="time"
                                                            :value="time"
                                                        >
                                                            {{ time }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label
                                                        class="text-xs text-gray-500 dark:text-gray-400 mb-1 block"
                                                        >Until</label
                                                    >
                                                    <select
                                                        v-model="
                                                            formData.houseRules
                                                                .checkInUntil
                                                        "
                                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                    >
                                                        <option
                                                            v-for="time in timeSlots"
                                                            :key="time"
                                                            :value="time"
                                                        >
                                                            {{ time }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Check-out Time -->
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                        >
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
                                            >
                                                Check-out time
                                            </label>
                                            <select
                                                v-model="
                                                    formData.houseRules
                                                        .checkOutUntil
                                                "
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                            >
                                                <option
                                                    v-for="time in timeSlots"
                                                    :key="time"
                                                    :value="time"
                                                >
                                                    {{ time }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quiet Hours -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Quiet hours (optional)
                                    </h3>
                                    <div
                                        class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                                    >
                                        <div
                                            class="flex items-start justify-between mb-4"
                                        >
                                            <div>
                                                <h4
                                                    class="text-base font-medium text-gray-900 dark:text-white mb-1"
                                                >
                                                    Set quiet hours
                                                </h4>
                                                <p
                                                    class="text-sm text-gray-600 dark:text-gray-400"
                                                >
                                                    Specify when guests should
                                                    keep noise to a minimum
                                                </p>
                                            </div>
                                            <label
                                                class="relative inline-flex items-center cursor-pointer ml-4"
                                            >
                                                <input
                                                    v-model="
                                                        formData.houseRules
                                                            .hasQuietHours
                                                    "
                                                    type="checkbox"
                                                    class="sr-only peer"
                                                />
                                                <div
                                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                                                ></div>
                                            </label>
                                        </div>

                                        <div
                                            v-if="
                                                formData.houseRules
                                                    .hasQuietHours
                                            "
                                            class="grid grid-cols-2 gap-4"
                                        >
                                            <div>
                                                <label
                                                    class="text-xs text-gray-500 dark:text-gray-400 mb-1 block"
                                                    >From</label
                                                >
                                                <select
                                                    v-model="
                                                        formData.houseRules
                                                            .quietHoursFrom
                                                    "
                                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                >
                                                    <option
                                                        v-for="time in timeSlots"
                                                        :key="time"
                                                        :value="time"
                                                    >
                                                        {{ time }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="text-xs text-gray-500 dark:text-gray-400 mb-1 block"
                                                    >Until</label
                                                >
                                                <select
                                                    v-model="
                                                        formData.houseRules
                                                            .quietHoursUntil
                                                    "
                                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                                                >
                                                    <option
                                                        v-for="time in timeSlots"
                                                        :key="time"
                                                        :value="time"
                                                    >
                                                        {{ time }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Rules -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                    >
                                        Additional rules (optional)
                                    </h3>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400 mb-4"
                                    >
                                        Add any other rules or important
                                        information for guests
                                    </p>
                                    <fwb-textarea
                                        v-model="
                                            formData.houseRules.additionalRules
                                        "
                                        :rows="6"
                                        :maxlength="500"
                                        placeholder="Example: Please remove shoes at the entrance, No loud music after 10 PM, Keep the garden gate closed..."
                                        class="resize-none"
                                    />
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400 text-right mt-2"
                                    >
                                        {{
                                            formData.houseRules.additionalRules
                                                .length
                                        }}/500
                                    </p>
                                </div>

                                <!-- Cancellation Policy -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Cancellation policy
                                    </h3>
                                    <div class="space-y-3">
                                        <button
                                            v-for="policy in cancellationPolicies"
                                            :key="policy.id"
                                            @click="
                                                formData.houseRules.cancellationPolicy =
                                                    policy.id
                                            "
                                            :class="[
                                                'w-full p-4 border-2 rounded-xl text-left transition-all duration-200',
                                                formData.houseRules
                                                    .cancellationPolicy ===
                                                policy.id
                                                    ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
                                                    : 'border-gray-300 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white',
                                            ]"
                                        >
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div class="flex-1">
                                                    <h4
                                                        class="text-base font-semibold text-gray-900 dark:text-white mb-1"
                                                    >
                                                        {{ policy.name }}
                                                    </h4>
                                                    <p
                                                        class="text-sm text-gray-600 dark:text-gray-400"
                                                    >
                                                        {{ policy.description }}
                                                    </p>
                                                </div>
                                                <div
                                                    v-if="
                                                        formData.houseRules
                                                            .cancellationPolicy ===
                                                        policy.id
                                                    "
                                                    class="ml-4"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-gray-900 dark:text-white"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Info Box -->
                                <div
                                    class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
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
                                        Good to know
                                    </h4>
                                    <ul
                                        class="space-y-2 text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-blue-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Clear house rules help set
                                                expectations and reduce
                                                misunderstandings</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-blue-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >Flexible cancellation policies
                                                often attract more
                                                bookings</span
                                            >
                                        </li>
                                        <li class="flex items-start">
                                            <svg
                                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-blue-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                >You can update your rules
                                                anytime after publishing</span
                                            >
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Step 11: Review & Publish -->
<div v-else-if="currentStep === 11">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
        Review your listing
    </h1>
    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
        Here's what we'll show to guests. Make sure everything looks good!
    </p>

    <hr class="mb-8" />

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Listing Preview Card -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
            <!-- Photos Preview -->
            <div class="relative aspect-video bg-gray-100 dark:bg-gray-900">
                <img
                    v-if="formData.photos.length > 0"
                    :src="formData.photos[0].preview"
                    alt="Cover photo"
                    class="w-full h-full object-cover"
                />
                <div class="absolute bottom-4 right-4 px-3 py-1 bg-white dark:bg-gray-900 rounded-lg shadow-lg">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ formData.photos.length }} photos
                    </span>
                </div>
            </div>

            <!-- Listing Details -->
            <div class="p-6">
                <!-- Title & Location -->
                <div class="mb-4">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                        {{ formData.title }}
                    </h2>
                    <p class="text-base text-gray-600 dark:text-gray-400">
                        {{ formData.address.city }}, {{ formData.address.country }}
                    </p>
                </div>

                <!-- Quick Stats -->
                <div class="flex items-center space-x-6 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ formData.floorPlan.guests }} guests</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ formData.floorPlan.bedrooms }} bedrooms</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ formData.floorPlan.beds }} beds</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ formData.floorPlan.bathrooms }} bathrooms</span>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                        {{ formData.description }}
                    </p>
                </div>

                <!-- Price -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-baseline">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ currency }}{{ formData.pricing.basePrice }}
                        </span>
                        <span class="text-base text-gray-600 dark:text-gray-400 ml-1">
                            / night
                        </span>
                    </div>
                    <p v-if="formData.pricing.hasWeekendPrice" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Weekend: {{ currency }}{{ formData.pricing.weekendPrice }} / night
                    </p>
                </div>
            </div>
        </div>

        <!-- Editable Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Property Info -->
            <div class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Property info
                    </h3>
                    <button
                        @click="goToStep(1)"
                        class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    >
                        Edit
                    </button>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Type:</span>
                        <span class="text-gray-900 dark:text-white font-medium">{{ propertyTypeName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Occupation:</span>
                        <span class="text-gray-900 dark:text-white font-medium">{{ occupationTypeName }}</span>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Location
                    </h3>
                    <button
                        @click="goToStep(3)"
                        class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    >
                        Edit
                    </button>
                </div>
                <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                    <p>{{ formData.address.street }}</p>
                    <p>{{ formData.address.city }}, {{ formData.address.state }} {{ formData.address.zipCode }}</p>
                    <p>{{ formData.address.country }}</p>
                </div>
            </div>

            <!-- Amenities -->
            <div class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Amenities
                    </h3>
                    <button
                        @click="goToStep(5)"
                        class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    >
                        Edit
                    </button>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ formData.amenities.length }} amenities selected
                </p>
            </div>

            <!-- House Rules -->
            <div class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        House rules
                    </h3>
                    <button
                        @click="goToStep(10)"
                        class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    >
                        Edit
                    </button>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Check-in:</span>
                        <span class="text-gray-900 dark:text-white font-medium">{{ formData.houseRules.checkInFrom }} - {{ formData.houseRules.checkInUntil }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Check-out:</span>
                        <span class="text-gray-900 dark:text-white font-medium">{{ formData.houseRules.checkOutUntil }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Cancellation:</span>
                        <span class="text-gray-900 dark:text-white font-medium capitalize">{{ formData.houseRules.cancellationPolicy }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal Agreement -->
        <div class="p-6 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="flex items-start space-x-3">
                <input
                    v-model="agreedToTerms"
                    type="checkbox"
                    id="terms-checkbox"
                    class="mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                />
                <label for="terms-checkbox" class="text-sm text-gray-700 dark:text-gray-300">
                    I agree to the <a href="#" class="text-blue-600 hover:underline">Host Terms of Service</a>,
                    <a href="#" class="text-blue-600 hover:underline">Cancellation Policy</a>, and
                    <a href="#" class="text-blue-600 hover:underline">House Rules</a>.
                    I also acknowledge that I will comply with all applicable laws and regulations.
                </label>
            </div>
        </div>

        <!-- Publish Notice -->
        <div class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                What happens next?
            </h4>
            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-start">
                    <span class="mr-2">1.</span>
                    <span>Your listing will be reviewed by our team (usually within 24 hours)</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">2.</span>
                    <span>Once approved, it will be visible to guests</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">3.</span>
                    <span>You'll receive notifications when guests inquire or book</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">4.</span>
                    <span>You can edit your listing anytime from your dashboard</span>
                </li>
            </ul>
        </div>
    </div>
</div>
                    </div>
                </transition>
            </template>
        </div>

        <!-- Footer Navigation -->
        <div
            v-if="!loading"
            class="border-t border-gray-200 dark:border-gray-800"
        >
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
        <div
            v-if="previewPhotoIndex !== null"
            @click="previewPhotoIndex = null"
            class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4"
        >
            <div class="relative max-w-4xl w-full">
                <!-- Close Button -->
                <button
                    @click="previewPhotoIndex = null"
                    class="absolute -top-12 right-0 text-white hover:text-gray-300"
                >
                    <svg
                        class="w-8 h-8"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- Photo -->
                <img
                    :src="formData.photos[previewPhotoIndex].preview"
                    class="w-full h-auto rounded-lg"
                    @click.stop
                />

                <!-- Navigation -->
                <button
                    v-if="previewPhotoIndex > 0"
                    @click.stop="previewPhotoIndex--"
                    class="absolute left-4 top-1/2 -translate-y-1/2 p-2 bg-white dark:bg-gray-900 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg
                        class="w-6 h-6"
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
                </button>

                <button
                    v-if="previewPhotoIndex < formData.photos.length - 1"
                    @click.stop="previewPhotoIndex++"
                    class="absolute right-4 top-1/2 -translate-y-1/2 p-2 bg-white dark:bg-gray-900 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import BaseWrapper from "@/src/layouts/BaseWrapper.vue";
import AmenityCheckbox from "@/src/views/hosting/createAccommodation/AmenityCheckbox.vue";
import { mapState, mapActions } from "vuex";
import draggable from "vuedraggable";

export default {
    name: "CreateAccommodation",
    components: {
        BaseWrapper,
        AmenityCheckbox,
        draggable,
    },
    data() {
        return {
            loading: true,
            totalSteps: 11,
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
                floorPlan: {
                    guests: 1,
                    bedrooms: 1,
                    beds: 1,
                    bathrooms: 1,
                },
                amenities: [],
                photos: [],
                title: "",
                description: "",
                pricing: {
                    basePrice: 50,
                    hasWeekendPrice: false,
                    weekendPrice: 0,
                    weeklyDiscount: 0, // Percentage
                    monthlyDiscount: 0, // Percentage
                    bookingType: "instant", // 'instant' or 'request'
                    // NOVO - Minimum Stay
                    minStay: 1, // General minimum stay
                    hasDaySpecificMinStay: false,
                    daySpecificMinStay: {
                        monday: { enabled: false, nights: 1 },
                        tuesday: { enabled: false, nights: 1 },
                        wednesday: { enabled: false, nights: 1 },
                        thursday: { enabled: false, nights: 1 },
                        friday: { enabled: false, nights: 3 },
                        saturday: { enabled: false, nights: 2 },
                        sunday: { enabled: false, nights: 1 },
                    },
                },
                // House Rules
                houseRules: {
                    allowSmoking: false,
                    allowPets: false,
                    allowEvents: false,
                    allowChildren: true,
                    allowInfants: true,
                    checkInFrom: "15:00",
                    checkInUntil: "20:00",
                    checkOutUntil: "11:00",
                    hasQuietHours: false,
                    quietHoursFrom: "22:00",
                    quietHoursUntil: "08:00",
                    additionalRules: "",
                    cancellationPolicy: "moderate",
                },
            },
            createAccommodationErrors: {},
            generalAmenities: [
                { id: "wifi", name: "Wifi", icon: "📶" },
                { id: "tv", name: "TV", icon: "📺" },
                { id: "kitchen", name: "Kitchen", icon: "🍳" },
                { id: "washer", name: "Washer", icon: "🧺" },
                { id: "dryer", name: "Dryer", icon: "👕" },
                {
                    id: "air-conditioning",
                    name: "Air conditioning",
                    icon: "❄️",
                },
                { id: "heating", name: "Heating", icon: "🔥" },
                { id: "workspace", name: "Dedicated workspace", icon: "💼" },
            ],
            bathroomAmenities: [
                { id: "hair-dryer", name: "Hair dryer", icon: "💇" },
                { id: "shampoo", name: "Shampoo", icon: "🧴" },
                { id: "hot-water", name: "Hot water", icon: "🚿" },
                { id: "bathtub", name: "Bathtub", icon: "🛁" },
            ],
            kitchenAmenities: [
                { id: "refrigerator", name: "Refrigerator", icon: "🧊" },
                { id: "microwave", name: "Microwave", icon: "📟" },
                {
                    id: "dishes-silverware",
                    name: "Dishes and silverware",
                    icon: "🍽️",
                },
                { id: "stove", name: "Stove", icon: "🔥" },
                { id: "oven", name: "Oven", icon: "🍕" },
                { id: "coffee-maker", name: "Coffee maker", icon: "☕" },
                { id: "dishwasher", name: "Dishwasher", icon: "🧽" },
                { id: "dining-table", name: "Dining table", icon: "🪑" },
            ],
            heatingCoolingAmenities: [
                {
                    id: "central-air",
                    name: "Central air conditioning",
                    icon: "❄️",
                },
                { id: "central-heating", name: "Central heating", icon: "🔥" },
                { id: "portable-fans", name: "Portable fans", icon: "🌀" },
                {
                    id: "indoor-fireplace",
                    name: "Indoor fireplace",
                    icon: "🪵",
                },
            ],
            entertainmentAmenities: [
                {
                    id: "tv-streaming",
                    name: "TV with streaming services",
                    icon: "📺",
                },
                { id: "sound-system", name: "Sound system", icon: "🔊" },
                { id: "game-console", name: "Game console", icon: "🎮" },
                {
                    id: "books-reading",
                    name: "Books and reading material",
                    icon: "📚",
                },
            ],
            internetOfficeAmenities: [
                { id: "wifi-fast", name: "Wifi (Fast)", icon: "⚡" },
                { id: "ethernet", name: "Ethernet connection", icon: "🔌" },
                {
                    id: "dedicated-workspace",
                    name: "Dedicated workspace",
                    icon: "💻",
                },
            ],
            parkingFacilitiesAmenities: [
                {
                    id: "free-parking",
                    name: "Free parking on premises",
                    icon: "🅿️",
                },
                {
                    id: "paid-parking",
                    name: "Paid parking on premises",
                    icon: "💳",
                },
                {
                    id: "street-parking",
                    name: "Free street parking",
                    icon: "🚗",
                },
                { id: "elevator", name: "Elevator", icon: "🛗" },
                { id: "gym", name: "Gym", icon: "🏋️" },
                { id: "pool", name: "Pool", icon: "🏊" },
                { id: "hot-tub", name: "Hot tub", icon: "🛀" },
            ],
            outdoorAmenities: [
                { id: "patio-balcony", name: "Patio or balcony", icon: "🪴" },
                { id: "backyard", name: "Backyard", icon: "🌳" },
                { id: "bbq-grill", name: "BBQ grill", icon: "🍖" },
                {
                    id: "outdoor-furniture",
                    name: "Outdoor furniture",
                    icon: "🪑",
                },
                { id: "garden", name: "Garden or backyard", icon: "🌻" },
                { id: "beach-access", name: "Beach access", icon: "🏖️" },
            ],
            safetyAmenities: [
                { id: "smoke-alarm", name: "Smoke alarm", icon: "🚨" },
                {
                    id: "carbon-monoxide",
                    name: "Carbon monoxide alarm",
                    icon: "⚠️",
                },
                {
                    id: "fire-extinguisher",
                    name: "Fire extinguisher",
                    icon: "🧯",
                },
                { id: "first-aid", name: "First aid kit", icon: "🩹" },
                {
                    id: "security-cameras",
                    name: "Security cameras on property",
                    icon: "📹",
                },
            ],
            isDragging: false,
            uploadErrors: [],
            maxPhotos: 20,
            maxFileSize: 10 * 1024 * 1024, // 10MB
            previewPhotoIndex: null,
            // Title suggestions
            isGeneratingSuggestions: false,
            titleSuggestions: [],
            // Description templates
            showTemplates: false,
            // Pricing
            currency: "$",
            guestServiceFeePercentage: 14, // 14% guest service fee
            hostServiceFeePercentage: 3, // 3% host service fee
            showServiceFeeInfo: false,
            timeSlots: [
                "00:00",
                "01:00",
                "02:00",
                "03:00",
                "04:00",
                "05:00",
                "06:00",
                "07:00",
                "08:00",
                "09:00",
                "10:00",
                "11:00",
                "12:00",
                "13:00",
                "14:00",
                "15:00",
                "16:00",
                "17:00",
                "18:00",
                "19:00",
                "20:00",
                "21:00",
                "22:00",
                "23:00",
            ],
            cancellationPolicies: [
                {
                    id: "flexible",
                    name: "Flexible",
                    description:
                        "Full refund 1 day prior to arrival, except fees.",
                },
                {
                    id: "moderate",
                    name: "Moderate",
                    description:
                        "Full refund 5 days prior to arrival, except fees.",
                },
                {
                    id: "firm",
                    name: "Firm",
                    description:
                        "50% refund up until 30 days prior to arrival, except fees. No refund after that.",
                },
                {
                    id: "strict",
                    name: "Strict",
                    description:
                        "50% refund up until 7 days prior to arrival, except fees. No refund after that.",
                },
            ],
            daysOfWeek: [
                { id: "monday", name: "Monday" },
                { id: "tuesday", name: "Tuesday" },
                { id: "wednesday", name: "Wednesday" },
                { id: "thursday", name: "Thursday" },
                { id: "friday", name: "Friday" },
                { id: "saturday", name: "Saturday" },
                { id: "sunday", name: "Sunday" },
            ],
            // Review & Publish
        agreedToTerms: false,
        isPublishing: false,
        };
    },
    computed: {
        ...mapState("ui", ["countries"]),
        ...mapState("hosting", ["accommodationDraft"]),
        ...mapState("hosting/createAccommodation", [
            "accommodationTypes",
            "currentStep",
        ]),
        accommodationOccupations() {
            return (
                this.accommodationTypes.find(
                    (type) => type.id === this.formData.propertyType
                )?.available_occupations || []
            );
        },
        countryOptions() {
            if (!this.countries) return [];
            return this.countries.map((country) => ({
                value: country.iso_code_2,
                name: country.name,
            }));
        },
        propertyTypeName() {
            if (!this.formData.propertyType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.propertyType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        titleSuggestions() {
            // Generate suggestions based on available data
            const city = this.formData.address.city || "the city";
            const guests = this.formData.floorPlan.guests;
            const bedrooms = this.formData.floorPlan.bedrooms;
            const type = this.propertyTypeName;

            const suggestions = [];

            // Basic suggestions
            suggestions.push(`Charming ${type} in ${city}`);
            suggestions.push(
                `${bedrooms}BR ${type} perfect for ${guests} guests`
            );
            suggestions.push(`Cozy ${type} with modern amenities`);
            suggestions.push(`Beautiful ${type} near downtown ${city}`);

            // Add amenity-based suggestions
            if (this.formData.amenities.includes("wifi")) {
                suggestions.push(`${type} with high-speed WiFi in ${city}`);
            }
            if (this.formData.amenities.includes("pool")) {
                suggestions.push(`Stunning ${type} with pool access`);
            }
            if (this.formData.amenities.includes("parking")) {
                suggestions.push(`${type} with free parking in ${city}`);
            }
            if (this.formData.amenities.includes("air-conditioning")) {
                suggestions.push(`Cool and comfortable ${type} in ${city}`);
            }

            // Return top 5 suggestions
            return suggestions.slice(0, 5);
        },

        titleExamples() {
            const examples = {
                house: [
                    "Charming 3BR house with garden views",
                    "Modern family home near downtown",
                    "Cozy cottage with mountain backdrop",
                ],
                apartment: [
                    "Stylish loft in the heart of the city",
                    "Bright 2BR apartment with balcony",
                    "Downtown studio with city views",
                ],
                villa: [
                    "Luxury villa with private pool",
                    "Stunning beachfront villa retreat",
                    "Elegant villa in peaceful surroundings",
                ],
                cabin: [
                    "Rustic cabin nestled in the woods",
                    "Mountain cabin with fireplace",
                    "Secluded lakeside cabin getaway",
                ],
                default: [
                    "Beautiful place in prime location",
                    "Comfortable stay with great amenities",
                    "Perfect spot for your next trip",
                ],
            };

            return examples[this.formData.propertyType] || examples["default"];
        },

        wordCount() {
            if (!this.formData.description) return 0;
            return this.formData.description
                .trim()
                .split(/\s+/)
                .filter((word) => word.length > 0).length;
        },

        descriptionTemplates() {
            const city = this.formData.address.city || "the city";
            const type = this.propertyTypeName;
            const guests = this.formData.floorPlan.guests;
            const bedrooms = this.formData.floorPlan.bedrooms;
            const beds = this.formData.floorPlan.beds;

            // Get key amenities
            const hasWifi = this.formData.amenities.includes("wifi");
            const hasKitchen = this.formData.amenities.includes("kitchen");
            const hasParking =
                this.formData.amenities.includes("free-parking") ||
                this.formData.amenities.includes("paid-parking");
            const hasAC = this.formData.amenities.includes("air-conditioning");

            const amenitiesText = [];
            if (hasWifi) amenitiesText.push("high-speed WiFi");
            if (hasKitchen) amenitiesText.push("a fully equipped kitchen");
            if (hasParking) amenitiesText.push("free parking");
            if (hasAC) amenitiesText.push("air conditioning");

            const amenitiesList =
                amenitiesText.length > 0
                    ? amenitiesText.join(", ")
                    : "modern amenities";

            return [
                {
                    title: "Welcoming & Detailed",
                    content: `Welcome to our beautiful ${type} in ${city}! This charming space comfortably accommodates ${guests} guests with ${bedrooms} bedroom${
                        bedrooms !== 1 ? "s" : ""
                    } and ${beds} bed${
                        beds !== 1 ? "s" : ""
                    }. Enjoy ${amenitiesList} during your stay. The space is perfect for families, couples, or solo travelers looking for a comfortable and convenient base to explore the area. The neighborhood is vibrant yet peaceful, with restaurants, cafes, and shops just a short walk away. Public transportation is easily accessible, making it simple to get around the city.`,
                },
                {
                    title: "Short & Sweet",
                    content: `Cozy ${type} in the heart of ${city}. Perfect for ${guests} guests with all the essentials you need for a comfortable stay. Features ${amenitiesList}. Great location with easy access to local attractions and public transport. Looking forward to hosting you!`,
                },
                {
                    title: "Luxury Focused",
                    content: `Experience comfort and style in this thoughtfully designed ${type}. Located in ${city}, our space offers ${guests} guests a perfect blend of modern amenities and local charm. Featuring ${amenitiesList}, every detail has been carefully considered to ensure your stay is exceptional. The space boasts elegant interiors, quality furnishings, and a prime location that puts you close to the best the area has to offer.`,
                },
                {
                    title: "Family Friendly",
                    content: `Looking for a family-friendly ${type} in ${city}? You've found it! Our spacious accommodation sleeps ${guests} guests comfortably, with ${bedrooms} bedroom${
                        bedrooms !== 1 ? "s" : ""
                    } providing plenty of space for everyone. Kids will love the area, and parents will appreciate ${amenitiesList}. We're located in a safe, quiet neighborhood that's still close to all the action. Restaurants, parks, and family attractions are all nearby.`,
                },
                {
                    title: "Business Traveler",
                    content: `Ideal ${type} for business travelers visiting ${city}. This professional space accommodates ${guests} guest${
                        guests !== 1 ? "s" : ""
                    } and includes ${amenitiesList}, perfect for both work and relaxation. The location offers easy access to the business district and major transportation hubs. After a productive day, unwind in your comfortable, quiet retreat. Fast check-in and responsive hosting to support your busy schedule.`,
                },
            ];
        },

        serviceFee() {
            if (!this.formData.pricing.basePrice) return 0;
            return Math.round(
                this.formData.pricing.basePrice *
                    (this.guestServiceFeePercentage / 100)
            );
        },

        guestPriceBeforeTaxes() {
            if (!this.formData.pricing.basePrice) return 0;
            return this.formData.pricing.basePrice + this.serviceFee;
        },

        guestPaysTotal() {
            return this.guestPriceBeforeTaxes;
        },

        youEarn() {
            if (!this.formData.pricing.basePrice) return 0;
            const hostFee = Math.round(
                this.formData.pricing.basePrice *
                    (this.hostServiceFeePercentage / 100)
            );
            return this.formData.pricing.basePrice - hostFee;
        },

        // Weekend pricing
        weekendGuestPrice() {
            if (
                !this.formData.pricing.hasWeekendPrice ||
                !this.formData.pricing.weekendPrice
            ) {
                return this.guestPriceBeforeTaxes;
            }
            const weekendServiceFee = Math.round(
                this.formData.pricing.weekendPrice *
                    (this.guestServiceFeePercentage / 100)
            );
            return this.formData.pricing.weekendPrice + weekendServiceFee;
        },

        // Discount calculations
        weeklyDiscountAmount() {
            if (
                !this.formData.pricing.weeklyDiscount ||
                !this.formData.pricing.basePrice
            )
                return 0;
            return Math.round(
                this.formData.pricing.basePrice *
                    (this.formData.pricing.weeklyDiscount / 100)
            );
        },

        monthlyDiscountAmount() {
            if (
                !this.formData.pricing.monthlyDiscount ||
                !this.formData.pricing.basePrice
            )
                return 0;
            return Math.round(
                this.formData.pricing.basePrice *
                    (this.formData.pricing.monthlyDiscount / 100)
            );
        },

        priceAfterWeeklyDiscount() {
            return this.formData.pricing.basePrice - this.weeklyDiscountAmount;
        },

        priceAfterMonthlyDiscount() {
            return this.formData.pricing.basePrice - this.monthlyDiscountAmount;
        },

        weeklyStayTotal() {
            return this.priceAfterWeeklyDiscount * 7;
        },

        monthlyStayTotal() {
            return this.priceAfterMonthlyDiscount * 28;
        },

        averagePrice() {
            return 75;
        },

        priceRange() {
            return {
                min: 40,
                max: 120,
            };
        },

        occupationTypeName() {
        if (!this.formData.accommodationOccupation) return '';
        const occupation = this.accommodationOccupations.find(
            o => o.id === this.formData.accommodationOccupation
        );
        return occupation ? occupation.name : '';
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
                case 4:
                    return (
                        this.formData.floorPlan.guests >= 1 &&
                        this.formData.floorPlan.bedrooms >= 0 &&
                        this.formData.floorPlan.beds >= 1 &&
                        this.formData.floorPlan.bathrooms >= 0.5
                    );
                case 5:
                    // Opciono: možeš tražiti bar 1 amenity ili pustiti da nastavi bez ijednog
                    return true; // ili: return this.formData.amenities.length > 0;
                case 6:
                    // Minimum 5 photos required
                    return this.formData.photos.length >= 5;
                case 7:
                    // Title must be at least 10 characters
                    return this.formData.title.trim().length >= 10;
                case 8:
                    // Description must be at least 50 characters
                    return this.formData.description.trim().length >= 50;
                case 9:
                    const basePrice = this.formData.pricing.basePrice;
                    const validBasePrice = basePrice && basePrice >= 10;

                    if (this.formData.pricing.hasWeekendPrice) {
                        const weekendPrice = this.formData.pricing.weekendPrice;
                        if (!weekendPrice || weekendPrice < 10) {
                            return false;
                        }
                    }

                    const weeklyDiscount = this.formData.pricing.weeklyDiscount;
                    const monthlyDiscount =
                        this.formData.pricing.monthlyDiscount;

                    if (weeklyDiscount < 0 || weeklyDiscount > 99) return false;
                    if (monthlyDiscount < 0 || monthlyDiscount > 99)
                        return false;

                    if (
                        monthlyDiscount > 0 &&
                        weeklyDiscount > 0 &&
                        monthlyDiscount <= weeklyDiscount
                    ) {
                        return false;
                    }

                    // Validate minimum stay
                    const minStay = this.formData.pricing.minStay;
                    if (!minStay || minStay < 1) return false;

                    // Validate booking type
                    if (!this.formData.pricing.bookingType) return false;

                    return validBasePrice;
                case 10:
                    // Check-in and check-out times are required
                    const hasValidCheckIn =
                        this.formData.houseRules.checkInFrom &&
                        this.formData.houseRules.checkInUntil;
                    const hasValidCheckOut =
                        this.formData.houseRules.checkOutUntil;

                    // If quiet hours are enabled, both times must be set
                    if (this.formData.houseRules.hasQuietHours) {
                        const hasValidQuietHours =
                            this.formData.houseRules.quietHoursFrom &&
                            this.formData.houseRules.quietHoursUntil;
                        return (
                            hasValidCheckIn &&
                            hasValidCheckOut &&
                            hasValidQuietHours
                        );
                    }

                    // Cancellation policy must be selected
                    const hasCancellationPolicy =
                        this.formData.houseRules.cancellationPolicy !== null;

                    return (
                        hasValidCheckIn &&
                        hasValidCheckOut &&
                        hasCancellationPolicy
                    );
                    case 11:
                // Must agree to terms
                return this.agreedToTerms;
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
        "formData.pricing.hasWeekendPrice"(enabled) {
            if (enabled && !this.formData.pricing.weekendPrice) {
                // Default weekend price is 25% higher
                this.formData.pricing.weekendPrice = Math.round(
                    this.formData.pricing.basePrice * 1.25
                );
            }
        },

        "formData.pricing.basePrice"(newPrice) {
            // Auto-update weekend price if it was auto-generated
            if (
                this.formData.pricing.hasWeekendPrice &&
                this.formData.pricing.weekendPrice
            ) {
                const ratio =
                    this.formData.pricing.weekendPrice /
                    (this.formData.pricing.basePrice || 1);
                if (ratio >= 1.2 && ratio <= 1.3) {
                    // Looks like auto-generated, update it
                    this.formData.pricing.weekendPrice = Math.round(
                        newPrice * 1.25
                    );
                }
            }
        },

        "formData.houseRules.checkInFrom"(newTime) {
            // Ensure check-in 'until' is after 'from'
            if (this.formData.houseRules.checkInUntil) {
                const fromIndex = this.timeSlots.indexOf(newTime);
                const untilIndex = this.timeSlots.indexOf(
                    this.formData.houseRules.checkInUntil
                );

                if (untilIndex <= fromIndex) {
                    // Set 'until' to 2 hours after 'from'
                    const newUntilIndex = Math.min(
                        fromIndex + 2,
                        this.timeSlots.length - 1
                    );
                    this.formData.houseRules.checkInUntil =
                        this.timeSlots[newUntilIndex];
                }
            }
        },

        "formData.houseRules.hasQuietHours"(enabled) {
            if (enabled && !this.formData.houseRules.quietHoursFrom) {
                // Set default quiet hours: 22:00 - 08:00
                this.formData.houseRules.quietHoursFrom = "22:00";
                this.formData.houseRules.quietHoursUntil = "08:00";
            }
        },

        "formData.pricing.hasDaySpecificMinStay"(enabled) {
            if (enabled) {
                // Set Friday and Saturday as common weekend requirements
                this.formData.pricing.daySpecificMinStay.friday.enabled = true;
                this.formData.pricing.daySpecificMinStay.friday.nights = 3;
                this.formData.pricing.daySpecificMinStay.saturday.enabled = true;
                this.formData.pricing.daySpecificMinStay.saturday.nights = 2;
            }
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
                        accommodation_occupation:
                            this.formData.accommodationOccupation,
                        address: {
                            country: this.formData.address.country,
                            street: this.formData.address.street,
                            city: this.formData.address.city,
                            state: this.formData.address.state,
                            zip_code: this.formData.address.zipCode,
                        },
                    };

                    try {
                        await this.updateAccommodationDraft({
                            draftData,
                            currentStep: this.currentStep + 1,
                        });
                        this.incrementCurrentStep();
                    } catch (error) {
                        console.error("Error updating draft:", error);
                        if (error.response?.data?.errors) {
                            this.createAccommodationErrors =
                                error.response.data.errors;
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
                    accommodation_occupation:
                        this.formData.accommodationOccupation,
                    address: {
                        country: this.formData.address.country,
                        street: this.formData.address.street,
                        city: this.formData.address.city,
                        state: this.formData.address.state,
                        zip_code: this.formData.address.zipCode,
                    },
                };

                await this.updateAccommodationDraft({
                    draftData,
                    currentStep: this.currentStep,
                });
                this.$router.push({ name: "page-hosting-home" });
            } catch (error) {
                console.error("Error saving draft:", error);
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
                floorPlan: {
                    guests: draft.floor_plan?.guests || 1,
                    bedrooms: draft.floor_plan?.bedrooms || 1,
                    beds: draft.floor_plan?.beds || 1,
                    bathrooms: draft.floor_plan?.bathrooms || 1,
                },
                amenities: draft.amenities || [],
                photos: draft.photos || [],
                title: draft.title || "",
                description: draft.description || "",
                pricing: {
                    basePrice: draft.pricing?.base_price || 50,
                    smartPricing: draft.pricing?.smart_pricing || false,
                    minPrice: draft.pricing?.min_price || 30,
                    maxPrice: draft.pricing?.max_price || 100,
                },
                houseRules: {
                    allowSmoking:
                        draft.house_rules?.allow_smoking !== undefined
                            ? draft.house_rules.allow_smoking
                            : false,
                    allowPets:
                        draft.house_rules?.allow_pets !== undefined
                            ? draft.house_rules.allow_pets
                            : false,
                    allowEvents:
                        draft.house_rules?.allow_events !== undefined
                            ? draft.house_rules.allow_events
                            : false,
                    allowChildren:
                        draft.house_rules?.allow_children !== undefined
                            ? draft.house_rules.allow_children
                            : true,
                    allowInfants:
                        draft.house_rules?.allow_infants !== undefined
                            ? draft.house_rules.allow_infants
                            : true,
                    checkInFrom: draft.house_rules?.check_in_from || "15:00",
                    checkInUntil: draft.house_rules?.check_in_until || "20:00",
                    checkOutUntil:
                        draft.house_rules?.check_out_until || "11:00",
                    hasQuietHours: draft.house_rules?.has_quiet_hours || false,
                    quietHoursFrom:
                        draft.house_rules?.quiet_hours_from || "22:00",
                    quietHoursUntil:
                        draft.house_rules?.quiet_hours_until || "08:00",
                    additionalRules: draft.house_rules?.additional_rules || "",
                    cancellationPolicy:
                        draft.house_rules?.cancellation_policy || "moderate",
                },
            };
        },

        // Guests
        incrementGuests() {
            if (this.formData.floorPlan.guests < 16) {
                this.formData.floorPlan.guests++;
            }
        },
        decrementGuests() {
            if (this.formData.floorPlan.guests > 1) {
                this.formData.floorPlan.guests--;
            }
        },

        // Beds
        incrementBeds() {
            if (this.formData.floorPlan.beds < 50) {
                this.formData.floorPlan.beds++;
            }
        },
        decrementBeds() {
            if (this.formData.floorPlan.beds > 1) {
                this.formData.floorPlan.beds--;
            }
        },

        // Bathrooms (increment by 0.5)
        incrementBathrooms() {
            if (this.formData.floorPlan.bathrooms < 20) {
                this.formData.floorPlan.bathrooms += 0.5;
            }
        },
        decrementBathrooms() {
            if (this.formData.floorPlan.bathrooms > 0.5) {
                this.formData.floorPlan.bathrooms -= 0.5;
            }
        },
        toggleAmenity(amenityId) {
            const index = this.formData.amenities.indexOf(amenityId);
            if (index > -1) {
                // Remove amenity
                this.formData.amenities.splice(index, 1);
            } else {
                // Add amenity
                this.formData.amenities.push(amenityId);
            }
        },
        // Handle file selection from input
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.processFiles(files);
            // Reset input
            event.target.value = "";
        },

        // Handle drag & drop
        handleDrop(event) {
            this.isDragging = false;
            const files = Array.from(event.dataTransfer.files);
            this.processFiles(files);
        },

        // Process and validate files
        processFiles(files) {
            this.uploadErrors = [];

            // Check if adding these files would exceed max
            if (this.formData.photos.length + files.length > this.maxPhotos) {
                this.uploadErrors.push(
                    `You can only upload a maximum of ${this.maxPhotos} photos.`
                );
                return;
            }

            files.forEach((file) => {
                // Validate file type
                if (!file.type.match("image/(jpeg|jpg|png)")) {
                    this.uploadErrors.push(
                        `${file.name} is not a valid image format. Please use JPG or PNG.`
                    );
                    return;
                }

                // Validate file size
                if (file.size > this.maxFileSize) {
                    this.uploadErrors.push(
                        `${file.name} is too large. Maximum size is 10MB.`
                    );
                    return;
                }

                // Create preview and add to photos
                this.addPhoto(file);
            });
        },

        // Add photo with preview
        addPhoto(file) {
            const reader = new FileReader();

            reader.onload = (e) => {
                const photo = {
                    id: Date.now() + Math.random(), // Unique ID
                    file: file,
                    preview: e.target.result,
                    uploading: false,
                    uploaded: false,
                    url: null, // Server URL after upload
                };

                this.formData.photos.push(photo);

                // Optionally, upload immediately
                // this.uploadPhoto(photo);
            };

            reader.readAsDataURL(file);
        },

        // Remove photo
        removePhoto(index) {
            this.formData.photos.splice(index, 1);
        },

        // Upload photo to server (optional - can be done at submission)
        async uploadPhoto(photo) {
            photo.uploading = true;

            const formData = new FormData();
            formData.append("photo", photo.file);

            try {
                const response = await axios.post(
                    "/api/listings/photos/upload",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                );

                photo.uploaded = true;
                photo.url = response.data.url;
            } catch (error) {
                this.uploadErrors.push(`Failed to upload ${photo.file.name}`);
                this.removePhoto(this.formData.photos.indexOf(photo));
            } finally {
                photo.uploading = false;
            }
        },

        // Upload all photos at once (call this in nextStep or submitListing)
        async uploadAllPhotos() {
            const uploadPromises = this.formData.photos
                .filter((photo) => !photo.uploaded)
                .map((photo) => this.uploadPhoto(photo));

            await Promise.all(uploadPromises);
        },

        selectTitleSuggestion(suggestion) {
            this.formData.title = suggestion;
        },

        clearDescriptionError() {
            if (this.createListingErrors.description) {
                delete this.createListingErrors.description;
            }
        },

        clearPricingError() {
            if (this.createListingErrors.pricing) {
                delete this.createListingErrors.pricing;
            }
        },
        selectDescriptionTemplate(template) {
            this.formData.description = template.content;
            this.clearDescriptionError();
            this.showTemplates = false;

            // Scroll to textarea
            this.$nextTick(() => {
                const textarea = document.querySelector("textarea");
                if (textarea) {
                    textarea.focus();
                    textarea.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                }
            });
        },
        goToStep(step) {
        this.currentStep = step;
    },
    async uploadAllPhotos() {
        const uploadPromises = this.formData.photos
            .filter(photo => !photo.uploaded)
            .map(photo => this.uploadPhoto(photo));

        await Promise.all(uploadPromises);
    },
    async uploadPhoto(photo) {
        photo.uploading = true;

        const formData = new FormData();
        formData.append('photo', photo.file);

        try {
            const response = await axios.post('/api/listings/photos/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            photo.uploaded = true;
            photo.url = response.data.url;
        } catch (error) {
            throw new Error(`Failed to upload ${photo.file.name}`);
        } finally {
            photo.uploading = false;
        }
    },
    },
    async created() {
        await this.fetchAccommodationTypes();
        if (!this.accommodationDraft) {
            await this.fetchAccommodationDraft().finally(() => {
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
