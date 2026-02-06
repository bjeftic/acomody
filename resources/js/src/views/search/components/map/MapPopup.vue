<template>
    <div class="p-2">
        <div class="font-semibold text-sm mb-1">{{ accommodation.title }}</div>
        <div class="text-xs text-gray-600 mb-1">
            {{ accommodation.location || "" }}
        </div>
        <div class="flex items-center justify-between">
            <div class="text-sm font-bold">
                {{ accommodation.runded_price }} {{ currency.symbol }}/night
            </div>
            <div v-if="accommodation.rating" class="flex items-center text-xs">
                <svg
                    class="w-3 h-3 text-yellow-400 mr-1"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                    />
                </svg>
                {{ accommodation.rating }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "MapPopup",
    props: {
        accommodation: {
            type: Object,
            required: true,
        },
        currency: {
            type: Object,
            required: true,
        },
    },
};
</script>
