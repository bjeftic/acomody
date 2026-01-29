<template>
    <div
        @click="$emit('click', accommodation)"
        @mouseenter="$emit('hover', accommodation.id)"
        @mouseleave="$emit('hover', null)"
        :class="[
            'group cursor-pointer transition-transform duration-200',
            hovered ? 'scale-105' : ''
        ]"
    >
        <!-- Image Carousel -->
        <div class="relative aspect-square rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-800 mb-3">
            <!-- Images -->
            <div class="relative w-full h-full">
                <img
                    v-for="(image, index) in accommodation.images"
                    :key="index"
                    v-show="currentImageIndex === index"
                    :src="image"
                    :alt="`${accommodation.title} - Image ${index + 1}`"
                    class="w-full h-full object-cover"
                />
            </div>

            <!-- Carousel Navigation -->
            <!-- <button
                v-if="accommodation.images.length > 1"
                @click.stop="previousImage"
                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button
                v-if="accommodation.images.length > 1"
                @click.stop="nextImage"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button> -->

            <!-- Wishlist Button -->
            <button
                @click.stop="toggleWishlist"
                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center transition-transform hover:scale-110"
            >
                <svg
                    :class="[
                        'w-6 h-6',
                        isInWishlist ? 'fill-red-500 text-red-500' : 'fill-none text-white'
                    ]"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>

            <!-- Badges -->
            <div class="absolute bottom-3 left-3 flex items-center space-x-2">
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

            <!-- Image Dots -->
            <!-- <div
                v-if="accommodation.images.length > 1"
                class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center space-x-1"
            >
                <div
                    v-for="(_, index) in accommodation.images"
                    :key="index"
                    :class="[
                        'w-1.5 h-1.5 rounded-full transition-colors',
                        currentImageIndex === index ? 'bg-white' : 'bg-white/50'
                    ]"
                ></div>
            </div> -->
        </div>

        <!-- Content -->
        <div>
            <!-- Location & Rating -->
            <div class="flex items-start justify-between mb-1">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                        {{ accommodation.location }}
                    </h3>
                </div>
                <div v-if="accommodation.rating" class="flex items-center space-x-1 ml-2">
                    <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ accommodation.rating }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        ({{ accommodation.reviewCount }})
                    </span>
                </div>
            </div>

            <!-- Dates Available -->
            <p v-if="accommodation.availableDates" class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                {{ accommodation.availableDates }}
            </p>

            <!-- Price -->
            <div class="mt-2">
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ formatPrice(accommodation.price) }}
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    / night
                </span>
            </div>

            <!-- Total Price (if dates selected) -->
            <p v-if="accommodation.totalPrice" class="text-sm text-gray-600 dark:text-gray-400">
                ${{ accommodation.totalPrice }} total
            </p>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
    name: 'AccommodationCard',
    props: {
        accommodation: {
            type: Object,
            required: true,
        },
        hovered: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        ...mapState('ui', ['selectedCurrency']),
    },
    data() {
        return {
            currentImageIndex: 0,
            isInWishlist: false,
        };
    },
    methods: {
        nextImage() {
            if (this.currentImageIndex < this.accommodation.images.length - 1) {
                this.currentImageIndex++;
            } else {
                this.currentImageIndex = 0;
            }
        },
        previousImage() {
            if (this.currentImageIndex > 0) {
                this.currentImageIndex--;
            } else {
                this.currentImageIndex = this.accommodation.images.length - 1;
            }
        },
        toggleWishlist() {
            this.isInWishlist = !this.isInWishlist;
        },
        formatPrice(price) {
            if (!price) return this.selectedCurrency.symbol + ' 0';

            if (typeof price === 'object' && price.amount) {
                return price.currency + ' ' + Math.ceil(price.amount);
            }

            return Math.ceil(price) + ' ' + this.selectedCurrency.symbol;
        }
    },
};
</script>
