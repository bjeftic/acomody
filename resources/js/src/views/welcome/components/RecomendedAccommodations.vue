<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-4 gap-6"
        >
        <listing-card v-for="accommodation in paginatedAccommodations" :key="accommodation.id"
                @click="showDetails(accommodation)" :listing="accommodation"></listing-card>
        </div>

        <!-- Details Modal -->
        <fwb-modal v-if="showModal" @close="showModal = false" size="2xl">
            <template #header>
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Accommodation Details
                    </h3>
                </div>
            </template>

            <template #body>
                <div v-if="selectedAccommodation" class="space-y-6">
                    <img
                        :src="selectedAccommodation.image"
                        :alt="selectedAccommodation.name"
                        class="w-full h-72 object-cover rounded-lg"
                    />

                    <div
                        class="border border-gray-200 rounded-lg overflow-hidden"
                    >
                        <dl class="divide-y divide-gray-200">
                            <div
                                class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                            >
                                <dt class="text-sm font-medium text-gray-500">
                                    Name
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                                >
                                    {{ selectedAccommodation.name }}
                                </dd>
                            </div>
                            <div
                                class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                            >
                                <dt class="text-sm font-medium text-gray-500">
                                    Location
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                                >
                                    {{ selectedAccommodation.location }}
                                </dd>
                            </div>
                            <div
                                class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                            >
                                <dt class="text-sm font-medium text-gray-500">
                                    Price
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                                >
                                    €{{ selectedAccommodation.price }} /
                                    {{ selectedAccommodation.period }}
                                </dd>
                            </div>
                            <div
                                class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                            >
                                <dt class="text-sm font-medium text-gray-500">
                                    Rating
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                                >
                                    <div class="flex items-center gap-2">
                                        <fwb-rating
                                            :rating="
                                                selectedAccommodation.rating
                                            "
                                            :read-only="true"
                                        />
                                        <span
                                            >{{
                                                selectedAccommodation.rating
                                            }}
                                            ({{
                                                selectedAccommodation.reviewCount
                                            }})</span
                                        >
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="amenity in selectedAccommodation.amenities"
                            :key="amenity"
                            class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-800 bg-blue-100 rounded-lg"
                        >
                            {{ getAmenityIcon(amenity) }} {{ amenity }}
                        </span>
                    </div>

                    <p class="text-gray-700">
                        {{ selectedAccommodation.description }}
                    </p>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <fwb-button @click="showModal = false" color="alternative">
                        Close
                    </fwb-button>
                    <fwb-button
                        @click="book(selectedAccommodation)"
                        color="blue"
                    >
                        Book Now
                    </fwb-button>
                </div>
            </template>
        </fwb-modal>
    </div>
</template>

<script>
import ListingCard from "@/src/components/ListingCard.vue";

export default {
    name: "DestinationsGrid",
    components: {
        ListingCard,
    },
    data() {
        return {
            loading: false,
            error: null,
            accommodations: [],
            showModal: false,
            selectedAccommodation: null,
            currentPage: 1,
            itemsPerPage: 6,
            booking: {},
            successMessage: "",
            errorMessage: "",
            filter: {
                location: null,
                maxPrice: null,
                minRating: 0,
            },
        };
    },
    computed: {
        filtered() {
            let result = this.accommodations;

            if (this.filter.location) {
                result = result.filter((a) =>
                    a.location
                        .toLowerCase()
                        .includes(this.filter.location.toLowerCase())
                );
            }

            if (this.filter.maxPrice) {
                result = result.filter((a) => a.price <= this.filter.maxPrice);
            }

            if (this.filter.minRating > 0) {
                result = result.filter(
                    (a) => a.rating >= this.filter.minRating
                );
            }

            return result;
        },
        totalPages() {
            return Math.ceil(this.filtered.length / this.itemsPerPage);
        },
        paginatedAccommodations() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filtered.slice(start, end);
        },
        visiblePages() {
            const pages = [];
            const total = this.totalPages;
            const current = this.currentPage;

            if (total <= 7) {
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 3) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                } else if (current >= total - 2) {
                    for (let i = total - 4; i <= total; i++) pages.push(i);
                } else {
                    for (let i = current - 2; i <= current + 2; i++)
                        pages.push(i);
                }
            }

            return pages;
        },
    },
    mounted() {
        this.loadAccommodations();
    },
    methods: {
        async loadAccommodations() {
            this.loading = true;
            this.error = null;

            try {
                // Replace with actual Laravel API call
                // const response = await axios.get('/api/accommodations')
                // this.accommodations = response.data

                // Mock data
                await new Promise((resolve) => setTimeout(resolve, 1000));

                this.accommodations = [
                    {
                        id: 1,
                        name: "Luxury City Center Apartment",
                        location: "Belgrade, Old Town",
                        price: 85,
                        period: "night",
                        rating: 4.9,
                        reviewCount: 127,
                        image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop",
                        amenities: ["WiFi", "Parking", "Coffee Shop"],
                        description:
                            "Elegant apartment in the heart of Belgrade with a view of Kalemegdan",
                    },
                    {
                        id: 2,
                        name: "Villa with Pool",
                        location: "Novi Sad, Petrovaradin",
                        price: 120,
                        period: "night",
                        rating: 4.8,
                        reviewCount: 89,
                        image: "https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&h=300&fit=crop",
                        amenities: ["WiFi", "Parking", "Pool"],
                        description:
                            "Private villa with pool and beautiful view of the Danube",
                    },
                    {
                        id: 3,
                        name: "Mountain House",
                        location: "Zlatibor",
                        price: 65,
                        period: "night",
                        rating: 4.7,
                        reviewCount: 156,
                        image: "https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=400&h=300&fit=crop",
                        amenities: ["WiFi", "Fireplace", "Terrace"],
                        description:
                            "Rustic house in Zlatibor with authentic ambiance",
                    },
                    {
                        id: 4,
                        name: "Modern Studio Downtown",
                        location: "Belgrade, Center",
                        price: 55,
                        period: "night",
                        rating: 4.6,
                        reviewCount: 203,
                        image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop",
                        amenities: ["WiFi", "Kitchen", "Gym"],
                        description:
                            "Contemporary studio apartment perfect for business travelers",
                    },
                    {
                        id: 5,
                        name: "Riverside Cottage",
                        location: "Novi Sad, Danube",
                        price: 75,
                        period: "night",
                        rating: 4.8,
                        reviewCount: 94,
                        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=300&fit=crop",
                        amenities: ["WiFi", "Garden", "Boat Access"],
                        description:
                            "Charming cottage by the Danube river with private garden",
                    },
                    {
                        id: 6,
                        name: "Ski Lodge",
                        location: "Kopaonik",
                        price: 95,
                        period: "night",
                        rating: 4.5,
                        reviewCount: 167,
                        image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop",
                        amenities: ["WiFi", "Ski Storage", "Sauna"],
                        description:
                            "Cozy ski lodge with direct access to slopes",
                    },
                ];
            } catch (err) {
                this.error = "Error loading accommodations. Please try again.";
                console.error("Error loading accommodations:", err);
            } finally {
                this.loading = false;
            }
        },
        filterAccommodations() {
            this.currentPage = 1;
        },
        resetFilters() {
            this.filter.location = null;
            this.filter.maxPrice = null;
            this.filter.minRating = 0;
            this.currentPage = 1;
        },
        showDetails(accommodation) {
            this.selectedAccommodation = accommodation;
            this.showModal = true;
        },
        async book(accommodation) {
            this.$set(this.booking, accommodation.id, true);

            try {
                // Laravel API call for booking
                // await axios.post(`/api/accommodations/${accommodation.id}/book`)

                await new Promise((resolve) => setTimeout(resolve, 1500));

                this.successMessage = `Booking successful! You have booked ${accommodation.name}`;
                setTimeout(() => {
                    this.successMessage = "";
                }, 5000);

                this.showModal = false;
            } catch (err) {
                this.errorMessage = "Booking failed. Please try again.";
                setTimeout(() => {
                    this.errorMessage = "";
                }, 5000);
            } finally {
                this.$delete(this.booking, accommodation.id);
            }
        },
        changePage(page) {
            this.currentPage = page;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
        getAmenityIcon(amenity) {
            const icons = {
                WiFi: "📶",
                Parking: "🚗",
                Pool: "🏊",
                Fireplace: "🔥",
                "Coffee Shop": "☕",
                Terrace: "🏡",
                Kitchen: "🍳",
                Gym: "💪",
                Garden: "🌿",
                "Boat Access": "⛵",
                "Ski Storage": "🎿",
                Sauna: "🧖",
            };
            return icons[amenity] || "✨";
        },
        handleImageError(event) {
            event.target.src =
                "https://via.placeholder.com/400x300?text=No+Image";
        },
    },
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
