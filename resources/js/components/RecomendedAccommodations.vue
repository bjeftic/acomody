<template>
  <div class="accommodations-container">
    <!-- Header with main message -->
    <n-space vertical size="large">
      <n-card>
        <n-space vertical align="center">
          <n-h1 style="margin: 0; text-align: center; color: #2080f0;">
            Stay at Our Premium Unique Properties
          </n-h1>
          <n-text depth="3" style="font-size: 16px; text-align: center;">
            Discover the most beautiful destinations and enjoy the comfort of our exclusive accommodations
          </n-text>
        </n-space>
      </n-card>

      <!-- Success/Error Messages -->
      <n-card v-if="successMessage" type="success">
        <n-text type="success">{{ successMessage }}</n-text>
        <n-button text @click="successMessage = ''" style="float: right;">×</n-button>
      </n-card>

      <n-card v-if="errorMessage" type="error">
        <n-text type="error">{{ errorMessage }}</n-text>
        <n-button text @click="errorMessage = ''" style="float: right;">×</n-button>
      </n-card>

      <!-- Filters -->
      <n-card title="Filters">
        <n-space>
          <n-select
            v-model:value="filter.location"
            :options="locationOptions"
            placeholder="Select location"
            clearable
            style="width: 200px"
            @update:value="filterAccommodations"
          />
          <n-input-number
            v-model:value="filter.maxPrice"
            placeholder="Max price"
            :min="0"
            style="width: 150px"
            @update:value="filterAccommodations"
          >
            <template #suffix>€</template>
          </n-input-number>
          <n-rate
            v-model:value="filter.minRating"
            :count="5"
            allow-half
            @update:value="filterAccommodations"
          />
          <n-button @click="resetFilters" secondary>
            Reset Filters
          </n-button>
        </n-space>
      </n-card>

      <!-- Loading -->
      <n-spin v-if="loading" size="large">
        <n-card>
          <n-empty description="Loading accommodations..." />
        </n-card>
      </n-spin>

      <!-- Error -->
      <n-result
        v-else-if="error"
        status="error"
        title="Error loading data"
        :description="error"
      >
        <template #footer>
          <n-button @click="loadAccommodations" type="primary">
            Try Again
          </n-button>
        </template>
      </n-result>

      <!-- Accommodations Grid -->
      <n-grid v-else :cols="gridCols" :x-gap="16" :y-gap="16" responsive="screen">
        <n-grid-item v-for="accommodation in filtered" :key="accommodation.id">
          <n-card
            hoverable
            class="accommodation-card"
            @click="showDetails(accommodation)"
          >
            <template #cover>
              <img
                :src="accommodation.image"
                :alt="accommodation.name"
                style="height: 200px; object-fit: cover;"
                @error="handleImageError"
              />
            </template>

            <template #header>
              <n-space justify="space-between" align="center">
                <n-ellipsis style="max-width: 200px">
                  {{ accommodation.name }}
                </n-ellipsis>
                <n-space align="center" size="small">
                  <n-icon color="#faad14">
                    <svg viewBox="0 0 24 24">
                      <path fill="currentColor" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                  </n-icon>
                  <n-text>{{ accommodation.rating }}</n-text>
                </n-space>
              </n-space>
            </template>

            <n-space vertical size="medium">
              <n-space align="center" size="small">
                <n-icon color="#52c41a">
                  <svg viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                  </svg>
                </n-icon>
                <n-text depth="3">{{ accommodation.location }}</n-text>
              </n-space>

              <n-space size="small">
                <n-tag
                  v-for="amenity in accommodation.amenities"
                  :key="amenity"
                  size="small"
                  type="info"
                >
                  {{ getAmenityIcon(amenity) }} {{ amenity }}
                </n-tag>
              </n-space>

              <n-ellipsis line-clamp="2">
                <n-text depth="3">{{ accommodation.description }}</n-text>
              </n-ellipsis>

              <n-space justify="space-between" align="center">
                <n-space align="baseline" size="small">
                  <n-text strong type="primary" style="font-size: 20px">
                    €{{ accommodation.price }}
                  </n-text>
                  <n-text depth="3">/ {{ accommodation.period }}</n-text>
                </n-space>
                <n-text depth="3" size="small">
                  ({{ accommodation.reviewCount }} reviews)
                </n-text>
              </n-space>
            </n-space>

            <template #action>
              <n-button
                type="primary"
                block
                @click.stop="book(accommodation)"
                :loading="booking[accommodation.id]"
              >
                Book Now
              </n-button>
            </template>
          </n-card>
        </n-grid-item>
      </n-grid>

      <!-- Pagination -->
      <n-pagination
        v-if="totalPages > 1"
        v-model:page="currentPage"
        :page-count="totalPages"
        :page-size="itemsPerPage"
        show-size-picker
        :page-sizes="[6, 12, 24]"
        @update:page="changePage"
        @update:page-size="changePageSize"
      />
    </n-space>

    <!-- Details Modal -->
    <n-modal
      v-model:show="showModal"
      preset="card"
      title="Accommodation Details"
      style="width: 600px"
    >
      <div v-if="selectedAccommodation">
        <n-space vertical size="large">
          <img
            :src="selectedAccommodation.image"
            :alt="selectedAccommodation.name"
            style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px;"
          />

          <n-descriptions :column="2" bordered>
            <n-descriptions-item label="Name">
              {{ selectedAccommodation.name }}
            </n-descriptions-item>
            <n-descriptions-item label="Location">
              {{ selectedAccommodation.location }}
            </n-descriptions-item>
            <n-descriptions-item label="Price">
              €{{ selectedAccommodation.price }} / {{ selectedAccommodation.period }}
            </n-descriptions-item>
            <n-descriptions-item label="Rating">
              <n-space align="center" size="small">
                <n-rate :value="selectedAccommodation.rating" readonly allow-half size="small" />
                <n-text>{{ selectedAccommodation.rating }} ({{ selectedAccommodation.reviewCount }})</n-text>
              </n-space>
            </n-descriptions-item>
          </n-descriptions>

          <n-space>
            <n-tag
              v-for="amenity in selectedAccommodation.amenities"
              :key="amenity"
              type="info"
            >
              {{ getAmenityIcon(amenity) }} {{ amenity }}
            </n-tag>
          </n-space>

          <n-text>{{ selectedAccommodation.description }}</n-text>
        </n-space>
      </div>

      <template #action>
        <n-space justify="end">
          <n-button @click="showModal = false">Close</n-button>
          <n-button type="primary" @click="book(selectedAccommodation)">
            Book Now
          </n-button>
        </n-space>
      </template>
    </n-modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import {
  NH1, NText, NCard, NSpace, NGrid, NGridItem, NButton, NIcon,
  NTag, NEllipsis, NSelect, NInputNumber, NRate, NSpin, NEmpty,
  NResult, NPagination, NModal, NDescriptions, NDescriptionsItem
} from 'naive-ui'

// Reactive data
const loading = ref(false)
const error = ref(null)
const accommodations = ref([])
const showModal = ref(false)
const selectedAccommodation = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(6)
const booking = reactive({})
const successMessage = ref('')
const errorMessage = ref('')

// Filters
const filter = reactive({
  location: null,
  maxPrice: null,
  minRating: 0
})

// Computed
const gridCols = computed(() => {
  return 'xs:1 s:1 m:2 l:3 xl:3 2xl:4'
})

const locationOptions = computed(() => [
  { label: 'Belgrade', value: 'belgrade' },
  { label: 'Novi Sad', value: 'novi-sad' },
  { label: 'Niš', value: 'nis' },
  { label: 'Zlatibor', value: 'zlatibor' },
  { label: 'Kopaonik', value: 'kopaonik' }
])

const filtered = computed(() => {
  let result = accommodations.value

  if (filter.location) {
    result = result.filter(a =>
      a.location.toLowerCase().includes(filter.location.toLowerCase())
    )
  }

  if (filter.maxPrice) {
    result = result.filter(a => a.price <= filter.maxPrice)
  }

  if (filter.minRating > 0) {
    result = result.filter(a => a.rating >= filter.minRating)
  }

  return result
})

const totalPages = computed(() =>
  Math.ceil(filtered.value.length / itemsPerPage.value)
)

// Methods
const loadAccommodations = async () => {
  loading.value = true
  error.value = null

  try {
    // Replace with actual Laravel API call
    // const response = await axios.get('/api/accommodations')

    // Mock data
    await new Promise(resolve => setTimeout(resolve, 1000))

    accommodations.value = [
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
        description: "Elegant apartment in the heart of Belgrade with a view of Kalemegdan"
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
        description: "Private villa with pool and beautiful view of the Danube"
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
        description: "Rustic house in Zlatibor with authentic ambiance"
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
        description: "Contemporary studio apartment perfect for business travelers"
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
        description: "Charming cottage by the Danube river with private garden"
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
        description: "Cozy ski lodge with direct access to slopes"
      }
    ]
  } catch (err) {
    error.value = 'Error loading accommodations. Please try again.'
    console.error('Error loading accommodations:', err)
  } finally {
    loading.value = false
  }
}

const filterAccommodations = () => {
  currentPage.value = 1
}

const resetFilters = () => {
  filter.location = null
  filter.maxPrice = null
  filter.minRating = 0
  currentPage.value = 1
}

const showDetails = (accommodation) => {
  selectedAccommodation.value = accommodation
  showModal.value = true
}

const book = async (accommodation) => {
  booking[accommodation.id] = true

  try {
    // Laravel API call for booking
    // await axios.post(`/api/accommodations/${accommodation.id}/book`)

    await new Promise(resolve => setTimeout(resolve, 1500))

    successMessage.value = `Booking successful! You have booked ${accommodation.name}`
    setTimeout(() => {
      successMessage.value = ''
    }, 5000)

    showModal.value = false
  } catch (err) {
    errorMessage.value = 'Booking failed. Please try again.'
    setTimeout(() => {
      errorMessage.value = ''
    }, 5000)
  } finally {
    booking[accommodation.id] = false
  }
}

const changePage = (page) => {
  currentPage.value = page
}

const changePageSize = (size) => {
  itemsPerPage.value = size
  currentPage.value = 1
}

const getAmenityIcon = (amenity) => {
  const icons = {
    'WiFi': '📶',
    'Parking': '🚗',
    'Pool': '🏊',
    'Fireplace': '🔥',
    'Coffee Shop': '☕',
    'Terrace': '🏡',
    'Kitchen': '🍳',
    'Gym': '💪',
    'Garden': '🌿',
    'Boat Access': '⛵',
    'Ski Storage': '🎿',
    'Sauna': '🧖'
  }
  return icons[amenity] || '✨'
}

const handleImageError = (event) => {
  event.target.src = 'https://via.placeholder.com/400x300?text=No+Image'
}

// Lifecycle
onMounted(() => {
  loadAccommodations()
})
</script>

<style scoped>
.accommodations-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.accommodation-card {
  cursor: pointer;
  transition: all 0.3s ease;
}

.accommodation-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
</style>
