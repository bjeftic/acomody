<template>
  <div
    class="px-4 py-2 border-2 rounded-2xl cursor-pointer transition-all duration-200 select-none"
    :class="[
      isSelected
        ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
        : 'border-gray-300 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white'
    ]"
    @click="handleToggle"
    role="checkbox"
    :aria-checked="isSelected"
    tabindex="0"
    @keyup.enter.space="handleToggle"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <!-- Icon -->
        <IconLoader :name="amenity.icon" :size="24" />
        <!-- Text -->
        <h4 class="text-base font-medium text-gray-900 dark:text-white">
          {{ amenity.name }}
        </h4>
      </div>

      <!-- Checkbox -->
      <fwb-checkbox :model-value="isSelected" @update:model-value="handleToggle" />
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
