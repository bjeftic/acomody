<template>
    <div class="photo-gallery">
        <!-- Desktop Gallery Layout (Grid) -->
        <div class="hidden md:block">
            <div
                v-if="photos && photos.length > 0"
                class="grid grid-cols-4 gap-2 rounded-xl overflow-hidden"
                :class="{
                    'grid-rows-2': photos.length >= 5,
                    'grid-rows-1': photos.length < 5,
                }"
            >
                <!-- Main Large Photo -->
                <div
                    class="col-span-2 row-span-2 relative cursor-pointer group overflow-hidden"
                    @click="openGallery(0)"
                >
                    <img
                        :src="photos[0].urls.original"
                        alt="Main photo"
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                        style="max-height: 500px"
                    />
                    <div
                        class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity"
                    ></div>
                </div>

                <!-- Smaller Photos (up to 4) -->
                <template v-if="photos.length > 1">
                    <div
                        v-for="(photo, index) in photos.slice(1, 5)"
                        :key="index"
                        class="relative cursor-pointer group overflow-hidden"
                        @click="openGallery(index + 1)"
                    >
                        <img
                            :src="photo.urls.medium"
                            :alt="`Photo ${index + 2}`"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                            style="max-height: 250px"
                        />
                        <div
                            class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity"
                        ></div>

                        <!-- Show More Photos Overlay (on last visible photo) -->
                        <div
                            v-if="index === 3 && photos.length > 5"
                            class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center"
                            @click.stop="openGallery(0)"
                        >
                            <div class="text-white text-center">
                                <svg
                                    class="w-8 h-8 mx-auto mb-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span class="text-sm font-medium">
                                    +{{ photos.length - 5 }} more
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- View All Photos Button -->
            <button
                v-if="photos && photos.length > 0"
                @click="openGallery(0)"
                class="mt-4 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
                <svg
                    class="w-4 h-4 inline-block mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
                View all {{ photos.length }} photos
            </button>
        </div>

        <!-- Mobile Gallery (Swiper) -->
        <div class="md:hidden relative rounded-xl overflow-hidden">
            <div v-if="photos && photos.length > 0" class="relative">
                <swiper
                    :modules="modules"
                    :slides-per-view="1"
                    :space-between="0"
                    :pagination="{ clickable: true }"
                    :navigation="true"
                    class="accommodation-swiper"
                >
                    <swiper-slide v-for="(photo, index) in photos" :key="index">
                        <div class="aspect-video bg-gray-100 dark:bg-gray-800">
                            <img
                                :src="photo.urls.medium"
                                :alt="`Photo ${index + 1}`"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </swiper-slide>
                </swiper>

                <!-- Photo Counter -->
                <div
                    class="absolute bottom-4 right-4 px-3 py-1.5 bg-black bg-opacity-70 text-white text-sm rounded-lg z-10"
                >
                    {{ currentSlide + 1 }} / {{ photos.length }}
                </div>
            </div>
        </div>

        <!-- No Photos Placeholder -->
        <div
            v-if="!photos || photos.length === 0"
            class="aspect-video bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center"
        >
            <div class="text-center text-gray-400">
                <svg
                    class="w-16 h-16 mx-auto mb-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
                <p class="text-sm">No photos available</p>
            </div>
        </div>

        <!-- Fullscreen Gallery Modal -->
        <fwb-modal
            v-if="showGalleryModal"
            @close="closeGallery"
            size="7xl"
            :persistent="false"
        >
            <template #header>
                <div class="flex items-center justify-between w-full">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Photo {{ currentGalleryIndex + 1 }} of {{ photos.length }}
                    </h3>
                </div>
            </template>

            <template #body>
                <div class="relative">
                    <swiper
                        :modules="modules"
                        :slides-per-view="1"
                        :space-between="0"
                        :navigation="true"
                        :initial-slide="currentGalleryIndex"
                        @slideChange="onSlideChange"
                        class="fullscreen-swiper"
                    >
                        <swiper-slide v-for="(photo, index) in photos" :key="index">
                            <div class="flex items-center justify-center bg-gray-900" style="min-height: 60vh">
                                <img
                                    :src="photo.urls.original"
                                    :alt="`Photo ${index + 1}`"
                                    class="max-w-full max-h-[70vh] object-contain"
                                />
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>
            </template>
        </fwb-modal>
    </div>
</template>

<script>
import { Swiper, SwiperSlide } from "swiper/vue";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

export default {
    name: "PhotoGallery",
    components: {
        Swiper,
        SwiperSlide,
    },
    props: {
        photos: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            modules: [Navigation, Pagination],
            showGalleryModal: false,
            currentGalleryIndex: 0,
            currentSlide: 0,
        };
    },
    methods: {
        openGallery(index) {
            this.currentGalleryIndex = index;
            this.showGalleryModal = true;
        },
        closeGallery() {
            this.showGalleryModal = false;
        },
        onSlideChange(swiper) {
            this.currentGalleryIndex = swiper.activeIndex;
            this.currentSlide = swiper.activeIndex;
        },
    },
};
</script>

<style scoped>
.accommodation-swiper {
    width: 100%;
    height: 100%;
}

.accommodation-swiper :deep(.swiper-button-next),
.accommodation-swiper :deep(.swiper-button-prev) {
    color: white;
    background: rgba(0, 0, 0, 0.5);
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.accommodation-swiper :deep(.swiper-button-next):after,
.accommodation-swiper :deep(.swiper-button-prev):after {
    font-size: 16px;
}

.accommodation-swiper :deep(.swiper-pagination-bullet) {
    background: white;
    opacity: 0.7;
}

.accommodation-swiper :deep(.swiper-pagination-bullet-active) {
    opacity: 1;
}

.fullscreen-swiper {
    width: 100%;
}

.fullscreen-swiper :deep(.swiper-button-next),
.fullscreen-swiper :deep(.swiper-button-prev) {
    color: white;
    background: rgba(0, 0, 0, 0.6);
    width: 44px;
    height: 44px;
    border-radius: 50%;
}

.fullscreen-swiper :deep(.swiper-button-next):after,
.fullscreen-swiper :deep(.swiper-button-prev):after {
    font-size: 20px;
}
</style>
