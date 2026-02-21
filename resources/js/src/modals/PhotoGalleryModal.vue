<template>
    <fwb-modal v-if="show" @close="close" size="7xl" :persistent="false">
        <template #header>
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">
                    Photo {{ currentGalleryIndex + 1 }} of
                    {{ options.photos.length }}
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
                    <swiper-slide
                        v-for="(photo, index) in options.photos"
                        :key="index"
                    >
                        <div
                            class="flex items-center justify-center bg-gray-900"
                            style="min-height: 60vh"
                        >
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
</template>

<script>
import config from "@/config";
import { Swiper, SwiperSlide } from "swiper/vue";
import { Navigation, Pagination } from "swiper/modules";
import { toCamelCase } from "@/utils/helpers";
import { mapState, mapActions } from "vuex";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

const modalName = config.modals.photoGalleryModal;
const modalNameCamelCase = toCamelCase(modalName);

export default {
    name: "PhotoGalleryModal",
    computed: {
        ...mapState({
            show: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].shown
                    : false,
            promise: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].promise
                    : null,
            resolve: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].resolve
                    : null,
            reject: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].reject
                    : null,
            options: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].options
                    : false,
        }),
    },
    components: {
        Swiper,
        SwiperSlide,
    },
    data() {
        return {
            modalName,
            modules: [Navigation, Pagination],
            currentGalleryIndex: 0,
            currentSlide: 0,
        };
    },
    watch: {
        options: {
            immediate: true,
            handler(val) {
                if (val && val.index !== undefined) {
                    this.currentGalleryIndex = val.index;
                }
            },
        },
    },
    methods: {
        ...mapActions(["initModal", "closeModal"]),
        onSlideChange(swiper) {
            this.currentGalleryIndex = swiper.activeIndex;
            this.currentSlide = swiper.activeIndex;
        },

        close() {
            // Reset form data
            Object.assign(this.$data, this.$options.data.call(this));
            this.closeModal({ modalName: this.modalName });
        },
    },
    created() {
        this.initModal({ modalName: this.modalName });
    },
};
</script>

<style scoped>
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
