<template>
    <div class="mt-10">
        <h2 class="section-title">Popularne destinacije</h2>
        <p class="section-subtitle">Otkrijte najlepša mesta u Srbiji</p>

        <div class="destinations-grid">
            <div
                v-for="destination in destinations"
                :key="destination.id"
                class="destination-card"
                @click="selectDestination(destination)"
            >
                <div class="card-image-container">
                    <img
                        :src="destination.image"
                        :alt="destination.name"
                        class="card-image"
                        @error="handleImageError"
                    />
                    <div class="card-overlay">
                        <div class="card-content">
                            <h3 class="destination-name">
                                {{ destination.name }}
                            </h3>
                            <p class="destination-description">
                                {{ destination.description }}
                            </p>
                            <div class="destination-stats">
                                <span class="properties-count"
                                    >{{ destination.properties }}+
                                    smeštaja</span
                                >
                                <span class="rating" v-if="destination.rating">
                                    <n-icon class="star-icon">
                                        <StarIcon />
                                    </n-icon>
                                    {{ destination.rating }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected destination info -->
        <div v-if="selectedDestination" class="selected-info">
            <n-alert type="success" closable @close="clearSelection">
                <template #header>
                    Izabrali ste: {{ selectedDestination.name }}
                </template>
                {{ selectedDestination.fullDescription }}
            </n-alert>
        </div>
    </div>
</template>

<script setup>
import { ref, h } from "vue";

// Selected destination
const selectedDestination = ref(null);

// Star icon component
const StarIcon = {
    render() {
        return h(
            "svg",
            {
                width: "16",
                height: "16",
                viewBox: "0 0 24 24",
                fill: "currentColor",
            },
            [
                h("path", {
                    d: "M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z",
                }),
            ]
        );
    },
};

// Destinations data with placeholder images (you can replace with real images)
const destinations = ref([
    {
        id: 1,
        name: "Beograd",
        description: "Glavni grad Srbije",
        fullDescription:
            "Beograd je prestonica Srbije i najveći grad u zemlji. Poznat po svojoj bogatoj istoriji, živahnom noćnom životu i kulturnim znamenitostima.",
        image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop&crop=center",
        properties: 450,
        rating: 4.5,
    },
    {
        id: 2,
        name: "Novi Sad",
        description: "Grad muzike i kulture",
        fullDescription:
            "Novi Sad je drugi najveći grad u Srbiji, poznat po EXIT festivalu, Petrovaradinskoj tvrđavi i multietničkoj kulturi.",
        image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop&crop=center",
        properties: 180,
        rating: 4.6,
    },
    {
        id: 3,
        name: "Kopaonik",
        description: "Kralj srpskih planina",
        fullDescription:
            "Kopaonik je najpoznatiji ski centar u Srbiji sa odličnim stazama za skijanje i boravak u prirodi tokom cele godine.",
        image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop&crop=center",
        properties: 95,
        rating: 4.4,
    },
    {
        id: 4,
        name: "Zlatibor",
        description: "Planinski raj",
        fullDescription:
            "Zlatibor je jedna od najlepših planina u Srbiji, poznata po čistom vazduhu, tradicionalnoj hrani i brojnim aktivnostima.",
        image: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop&crop=center",
        properties: 120,
        rating: 4.7,
    },
    {
        id: 5,
        name: "Vrnjačka Banja",
        description: "Kraljica banja",
        fullDescription:
            "Vrnjačka Banja je najpoznatija spa destinacija u Srbiji sa termalnim vodama i wellness centrima.",
        image: "https://images.unsplash.com/photo-1540979388789-6cee28a1cdc9?w=400&h=300&fit=crop&crop=center",
        properties: 85,
        rating: 4.3,
    },
    {
        id: 6,
        name: "Tara",
        description: "Netaknuta priroda",
        fullDescription:
            "Nacionalni park Tara je poznat po svojoj netaknutoj prirodi, Drini i tradicionalnim selima.",
        image: "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=300&fit=crop&crop=center",
        properties: 65,
        rating: 4.8,
    },
]);

// Handle destination selection
const selectDestination = (destination) => {
    selectedDestination.value = destination;

    // Emit event or call parent function
    console.log("Selected destination:", destination.name);
};

// Clear selection
const clearSelection = () => {
    selectedDestination.value = null;
};

// Handle image loading errors
const handleImageError = (event) => {
    // Fallback to a placeholder image
    event.target.src =
        "https://via.placeholder.com/400x300/cccccc/666666?text=Slika+nije+dostupna";
};
</script>

<style scoped>
.destinations-section {
    padding: 40px 0;
    background: #f8f9fa;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-title {
    font-size: 32px;
    font-weight: 700;
    color: #262626;
    text-align: center;
    margin-bottom: 8px;
    margin-top: 0;
}

.section-subtitle {
    font-size: 18px;
    color: #717171;
    text-align: center;
    margin-bottom: 40px;
    margin-top: 0;
}

.destinations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.destination-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    height: 300px;
}

.destination-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card-image-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.destination-card:hover .card-image {
    transform: scale(1.05);
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.1) 0%,
        rgba(0, 0, 0, 0.4) 60%,
        rgba(0, 0, 0, 0.8) 100%
    );
    display: flex;
    align-items: flex-end;
    padding: 24px;
}

.card-content {
    color: white;
    width: 100%;
}

.destination-name {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.destination-description {
    font-size: 16px;
    margin: 0 0 12px 0;
    opacity: 0.9;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.destination-stats {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 14px;
}

.properties-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 8px;
    border-radius: 16px;
    backdrop-filter: blur(10px);
}

.rating {
    display: flex;
    align-items: center;
    gap: 4px;
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 8px;
    border-radius: 16px;
    backdrop-filter: blur(10px);
}

.star-icon {
    color: #ffd700;
}

.selected-info {
    margin-top: 20px;
}

/* Responsive design */
@media (max-width: 768px) {
    .destinations-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .destination-card {
        height: 250px;
    }

    .section-title {
        font-size: 24px;
    }

    .section-subtitle {
        font-size: 16px;
        margin-bottom: 24px;
    }

    .card-content {
        padding: 16px;
    }

    .destination-name {
        font-size: 20px;
    }

    .destination-description {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .destinations-grid {
        grid-template-columns: 1fr;
    }

    .destination-card {
        height: 200px;
    }

    .container {
        padding: 0 16px;
    }
}
</style>
