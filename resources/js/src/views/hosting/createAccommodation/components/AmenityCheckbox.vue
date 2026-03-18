<template>
  <div
    class="px-4 py-2 border-2 rounded-xl cursor-pointer transition-all duration-200 select-none"
    :class="[
      isSelected
        ? 'border-primary-600 dark:border-primary-400 bg-primary-50 dark:bg-primary-900/20'
        : 'border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-500'
    ]"
    @click="handleToggle"
    role="checkbox"
    :aria-checked="isSelected"
    tabindex="0"
    @keyup.enter.space="handleToggle"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <!-- Icon -->
        <IconLoader
          :name="amenity.icon"
          :size="20"
          :class="isSelected ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
        />
        <!-- Text -->
        <span class="text-sm font-medium" :class="isSelected ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200'">
          {{ amenity.name }}
        </span>
      </div>

      <!-- Checkbox -->
      <div class="w-5 h-5 rounded flex items-center justify-center border-2 transition-all"
        :class="isSelected ? 'border-primary-600 bg-primary-600 dark:border-primary-400 dark:bg-primary-400' : 'border-gray-300 dark:border-gray-600'"
      >
        <svg v-if="isSelected" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AmenityCheckbox",
  props: {
    amenity: {
      type: Object,
      required: true,
    },
    selected: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    isSelected() {
      return this.selected;
    },
  },
  methods: {
    handleToggle() {
      this.$emit("toggle", this.amenity.id);
    },
  },
};
</script>
