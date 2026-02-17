<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden" style="width: 280px;">
        <!-- Swiper Carousel -->
        <div class="relative aspect-[4/3] bg-gray-200 dark:bg-gray-700">
            <!-- Close Button -->
            <button
                @click.stop="closePopup"
                class="absolute top-2 right-2 w-6 h-6 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg z-20"
            >
                <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <swiper
                v-if="accommodation.photos && accommodation.photos.length > 0"
                :modules="modules"
                :slides-per-view="1"
                :space-between="0"
                :loop="false"
                :allow-touch-move="true"
                :navigation="{
                    prevEl: `.swiper-prev-${uniqueId}`,
                    nextEl: `.swiper-next-${uniqueId}`,
                }"
                :pagination="{
                    clickable: true,
                    el: `.swiper-pagination-${uniqueId}`,
                }"
                :effect="'fade'"
                :fadeEffect="{ crossFade: true }"
                class="h-full w-full swiper-no-swiping"
                @swiper="onSwiper"
                @slideChange="onSlideChange"
            >
                <swiper-slide
                    v-for="(photo, index) in accommodation.photos"
                    :key="index"
                >
                    <img
                        :src="photo"
                        :alt="`${accommodation.title} - Photo ${index + 1}`"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                </swiper-slide>
            </swiper>

            <!-- Fallback if no photos -->
            <div
                v-else
                class="w-full h-full flex items-center justify-center bg-gray-300 dark:bg-gray-600"
            >
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <!-- Navigation Buttons - OUTSIDE swiper but INSIDE parent container -->
            <template v-if="accommodation.photos && accommodation.photos.length > 1">
                <button
                    :class="`swiper-prev-${uniqueId}`"
                    class="absolute left-2 top-1/2 -translate-y-1/2 w-7 h-7 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg z-10"
                    @click.stop="prevSlide"
                >
                    <svg
                        class="w-3.5 h-3.5 text-gray-700"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>
                <button
                    :class="`swiper-next-${uniqueId}`"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg z-10"
                    @click.stop="nextSlide"
                >
                    <svg
                        class="w-3.5 h-3.5 text-gray-700"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>

                <!-- Custom Pagination Dots -->
                <div
                    :class="`swiper-pagination-${uniqueId}`"
                    class="absolute bottom-2 left-1/2 -translate-x-1/2 z-10"
                ></div>
            </template>
        </div>

        <!-- Content -->
        <div class="p-3">
            <!-- Title -->
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-1 line-clamp-2 leading-tight">
                {{ accommodation.title }}
            </h3>

            <!-- Location -->
            <p v-if="accommodation.location" class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                📍 {{ accommodation.location }}
            </p>

            <!-- Category -->
            <p v-if="accommodation.accommodation_category" class="text-xs text-gray-500 dark:text-gray-400 mb-2 capitalize">
                {{ accommodation.accommodation_category }}
            </p>

            <!-- Amenities Preview -->
            <div
                v-if="accommodation.bedrooms || accommodation.beds || accommodation.bathrooms || accommodation.max_guests"
                class="flex items-center flex-wrap gap-2 mb-2 text-xs text-gray-600 dark:text-gray-400"
            >
                <span v-if="accommodation.bedrooms" class="flex items-center">
                    🛏️ {{ accommodation.bedrooms }} bed{{ accommodation.bedrooms > 1 ? 's' : '' }}
                </span>
                <span v-if="accommodation.bathrooms">
                    🚿 {{ accommodation.bathrooms }} bath{{ accommodation.bathrooms > 1 ? 's' : '' }}
                </span>
                <span v-if="accommodation.max_guests">
                    👥 {{ accommodation.max_guests }} guest{{ accommodation.max_guests > 1 ? 's' : '' }}
                </span>
            </div>

            <!-- Price and Rating -->
            <div class="flex items-center justify-between mb-3">
                <!-- Price -->
                <div class="text-sm">
                    <span class="font-bold text-gray-900 dark:text-white">
                        {{ currency.symbol }}{{ formatPrice(accommodation.rounded_price || accommodation.regular_price || accommodation.base_price_eur) }}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 ml-1">
                        /night
                    </span>
                </div>

                <!-- Rating -->
                <div v-if="accommodation.rating" class="flex items-center text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                    <svg
                        class="w-3.5 h-3.5 text-yellow-400 mr-1"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ accommodation.rating }}
                    </span>
                    <span v-if="accommodation.reviews_count" class="text-gray-500 dark:text-gray-400 ml-0.5">
                        ({{ accommodation.reviews_count }})
                    </span>
                </div>
            </div>

            <!-- View Details Button -->
            <button
                @click.stop="viewDetails"
                class="w-full px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg"
            >
                View Details
            </button>
        </div>
    </div>
</template>

<script>
import { Swiper, SwiperSlide } from "swiper/vue";
import { Navigation, Pagination, EffectFade } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import "swiper/css/effect-fade";

let popupCounter = 0;

export default {
    name: "MapPopup",
    components: {
        Swiper,
        SwiperSlide,
    },
    props: {
        accommodation: {
            type: Object,
            required: true,
            validator: (value) => {
                return value.id && value.title;
            }
        },
        currency: {
            type: Object,
            required: true,
            validator: (value) => {
                return value && value.symbol;
            }
        },
    },
    setup() {
        return {
            modules: [Navigation, Pagination, EffectFade],
        };
    },
    data() {
        return {
            uniqueId: `popup-${++popupCounter}-${Date.now()}`,
            swiperInstance: null,
            currentSlideIndex: 0,
        };
    },
    methods: {
        onSwiper(swiper) {
            this.swiperInstance = swiper;
        },
        onSlideChange(swiper) {
            this.currentSlideIndex = swiper.activeIndex;
        },
        prevSlide() {
            if (this.swiperInstance) {
                this.swiperInstance.slidePrev();
            }
        },
        nextSlide() {
            if (this.swiperInstance) {
                this.swiperInstance.slideNext();
            }
        },
        closePopup() {
            // Find the popup element and trigger Leaflet's close
            const popup = this.$el.closest('.leaflet-popup');
            if (popup) {
                const closeButton = popup.querySelector('.leaflet-popup-close-button');
                if (closeButton) {
                    closeButton.click();
                }
            }
            this.$emit('close-popup');
        },
        formatPrice(price) {
            if (!price) return '0';

            // Handle object format
            if (typeof price === 'object' && price.amount) {
                return Math.ceil(price.amount);
            }

            // Handle number format
            return Math.ceil(Number(price));
        },
        viewDetails() {
            // Navigate to accommodation details page
            this.$router.push({
                name: 'accommodation-details',
                params: { id: this.accommodation.id }
            });
        }
    },
    beforeUnmount() {
        // Clean up swiper instance
        if (this.swiperInstance) {
            try {
                this.swiperInstance.destroy(true, true);
            } catch (e) {
                console.warn('Swiper cleanup error:', e);
            }
            this.swiperInstance = null;
        }
    }
};
</script>

<style scoped>
/* Custom Swiper Pagination Dots */
:deep(.swiper-pagination) {
    display: flex;
    justify-content: center;
    gap: 3px;
}

:deep(.swiper-pagination-bullet) {
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.6);
    opacity: 1;
    border-radius: 50%;
    cursor: pointer;
}

:deep(.swiper-pagination-bullet-active) {
    background: white;
    width: 6px;
    height: 6px;
}

:deep(.swiper-pagination-bullet:hover) {
    background: rgba(255, 255, 255, 0.9);
}

/* Smooth fade transition */
:deep(.swiper-slide) {
    transition: opacity 0.3s ease;
}

/* Disable swiper button on edges */
:deep(.swiper-button-disabled) {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}

/* Image optimization */
:deep(.swiper-slide img) {
    user-select: none;
    -webkit-user-drag: none;
}

/* Line clamp for title */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Make sure buttons are clickable */
button {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Disable touch on swiper so buttons work */
.swiper-no-swiping {
    touch-action: pan-y !important;
}
</style>
