<template>
    <div
        class="marker-pin"
        :class="{ 'marker-pin-hovered': isHovered }"
    >
        <div class="marker-price">
            {{ accommodation.rounded_price }} {{ currency.symbol }}
        </div>
    </div>
</template>

<script>
export default {
    name: "MapMarker",
    props: {
        accommodation: {
            type: Object,
            required: true,
        },
        currency: {
            type: Object,
            required: true,
        },
        isHovered: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style scoped>
.marker-pin {
    position: relative;
    transition: all 0.2s ease;
}

.marker-price {
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    white-space: nowrap;
    transition: all 0.2s ease;
}

.marker-pin-hovered .marker-price {
    background: #E11D48;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);
    z-index: 1000;
}
</style>
