<template>
  <component
    :is="icon"
    :size="size"
    :color="color"
    :stroke-width="strokeWidth"
    :default-class="defaultClass"
  />
</template>

<script>
import * as icons from "lucide-vue-next";

export default {
  name: 'IconLoader',

  props: {
    name: {
      type: String,
      required: true
    },
    size: {
      type: Number,
      default: 24
    },
    color: {
      type: String,
      default: undefined
    },
    strokeWidth: {
      type: Number,
      default: 2
    },
    defaultClass: {
      type: String,
      default: undefined
    }
  },

  computed: {
    icon() {
      return icons[this.name];
    }
  }
}
</script>
