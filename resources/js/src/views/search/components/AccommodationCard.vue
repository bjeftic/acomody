<template>
    <div
        @click="$emit('click', accommodation)"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
        class="group cursor-pointer"
    >
        <!-- Swiper Carousel -->
        <div class="relative aspect-square rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-800 mb-3">
            <swiper
                :pagination="true"
                :modules="modules"
                :slides-per-view="1"
                :space-between="0"
                :loop="false"
                :allow-touch-move="true"
                :navigation="{
                    prevEl: `.swiper-prev-${uniqueId}`,
                    nextEl: `.swiper-next-${uniqueId}`,
                }"
                :effect="'flip'"
                class="h-full accommodation-swiper"
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

            <!-- Custom Navigation Buttons -->
            <button
                v-if="accommodation.photos.length > 1"
                :class="`swiper-prev-${uniqueId}`"
                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                @click.stop
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button
                v-if="accommodation.photos.length > 1"
                :class="`swiper-next-${uniqueId}`"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                @click.stop
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Wishlist Button -->
            <button
                @click.stop="toggleWishlist"
                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center transition-transform hover:scale-110 z-10"
            >
                <svg
                    :class="[
                        'w-6 h-6 transition-all duration-300',
                        isInWishlist ? 'fill-red-500 text-red-500 scale-110' : 'fill-none text-white drop-shadow-lg'
                    ]"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>

            <!-- Badges -->
            <div class="absolute bottom-3 left-3 flex items-center space-x-2 z-10">
                <span
                    v-if="accommodation.isInstantBook"
                    class="px-2 py-1 bg-white text-xs font-semibold rounded shadow-lg flex items-center space-x-1"
                >
                    <span>⚡</span>
                    <span>Instant</span>
                </span>
                <span
                    v-if="accommodation.isSuperhost"
                    class="px-2 py-1 bg-white text-xs font-semibold rounded shadow-lg flex items-center space-x-1"
                >
                    <span>⭐</span>
                    <span>Superhost</span>
                </span>
            </div>

            <!-- Custom Pagination Dots -->
            <div
                v-if="accommodation.photos.length > 1"
                :class="`swiper-pagination-${uniqueId}`"
                class="!bottom-3"
            ></div>
        </div>

        <!-- Content -->
        <div>
            <!-- Location & Rating -->
            <div class="flex items-start justify-between mb-1">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                        {{ accommodation.location || accommodation.title }}
                    </h3>
                </div>
                <div v-if="accommodation.rating" class="flex items-center space-x-1 ml-2">
                    <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ accommodation.rating }}
                    </span>
                    <span v-if="accommodation.reviewCount" class="text-sm text-gray-500 dark:text-gray-400">
                        ({{ accommodation.reviewCount }})
                    </span>
                </div>
            </div>

            <!-- Dates Available -->
            <p v-if="accommodation.availableDates" class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                {{ accommodation.availableDates }}
            </p>

            <!-- Property Type or Description -->
            <p v-if="accommodation.accommodation_category" class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                {{ accommodation.accommodation_category }}
            </p>

            <!-- Price -->
            <div class="mt-2">
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ formatPrice(accommodation.runded_price || accommodation.regular_price) }}
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    / night
                </span>
            </div>

            <!-- Total Price (if dates selected) -->
            <p v-if="accommodation.totalPrice" class="text-sm text-gray-600 dark:text-gray-400">
                {{ formatPrice(accommodation.totalPrice) }} total
            </p>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

let cardCounter = 0;

export default {
    name: 'AccommodationCard',
    components: {
        Swiper,
        SwiperSlide,
    },
    props: {
        accommodation: {
            type: Object,
            required: true,
            validator: (value) => {
                return value.id && value.photos && Array.isArray(value.photos);
            }
        },
        hovered: {
            type: Boolean,
            default: false,
        },
    },
    setup() {
        return {
            modules: [Navigation, Pagination, EffectFade],
        };
    },
    computed: {
        ...mapState('ui', ['selectedCurrency']),
    },
    data() {
        return {
            uniqueId: `card-${++cardCounter}-${Date.now()}`,
            swiperInstance: null,
            isInWishlist: false,
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
        handleMouseEnter() {
            this.$emit('hover', this.accommodation.id);
        },
        handleMouseLeave() {
            this.$emit('hover', null);
        },
        toggleWishlist() {
            this.isInWishlist = !this.isInWishlist;

            // Emit event for parent to handle
            this.$emit('wishlist-toggle', {
                accommodationId: this.accommodation.id,
                isInWishlist: this.isInWishlist
            });

            // TODO: Call API to add/remove from wishlist
            // Example:
            // if (this.isInWishlist) {
            //     this.$store.dispatch('wishlist/add', this.accommodation.id);
            // } else {
            //     this.$store.dispatch('wishlist/remove', this.accommodation.id);
            // }
        },
        formatPrice(price) {
            if (!price) return this.selectedCurrency?.symbol + ' 0' || '$0';

            // Handle object format
            if (typeof price === 'object' && price.amount) {
                return price.currency + ' ' + Math.ceil(price.amount);
            }

            // Handle number format
            const symbol = this.selectedCurrency?.symbol || '$';
            const formattedPrice = Math.ceil(Number(price));

            return `${symbol}${formattedPrice}`;
        }
    },
    mounted() {
        // Optional: Check if accommodation is already in wishlist
        // this.isInWishlist = this.$store.getters['wishlist/isInWishlist'](this.accommodation.id);
    },
    beforeUnmount() {
        // Clean up swiper instance if needed
        if (this.swiperInstance) {
            this.swiperInstance.destroy();
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
    gap: 4px;
}

:deep(.swiper-pagination-bullet) {
    width: 6px;
    height: 6px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 1;
    transition: all 0.3s ease;
    border-radius: 50%;
    cursor: pointer;
}

:deep(.swiper-pagination-bullet-active) {
    background: white;
    width: 8px;
    height: 8px;
}

:deep(.swiper-pagination-bullet:hover) {
    background: rgba(255, 255, 255, 0.8);
}

/* Smooth fade transition */
:deep(.swiper-slide) {
    transition: opacity 0.3s ease;
}

/* Disable swiper button default styles */
:deep(.swiper-button-disabled) {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}

/* Image loading optimization */
:deep(.swiper-slide img) {
    user-select: none;
    -webkit-user-drag: none;
}

/* Ensure buttons are clickable */
.swiper-button-prev-custom,
.swiper-button-next-custom {
    pointer-events: auto;
}

/* Hide scrollbar for swiper if any */
:deep(.swiper-wrapper) {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

:deep(.swiper-wrapper::-webkit-scrollbar) {
    display: none;
}
</style>
