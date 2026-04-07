<template>
  <component
    :is="resolvedIcon"
    :size="size"
    :color="color"
    :stroke-width="strokeWidth"
    :default-class="defaultClass"
  />
</template>

<script>
import * as icons from "lucide-vue-next";
import { createLucideIcon } from "lucide-vue-next";

let labIcons = null;

async function getLabIcons() {
  if (labIcons !== null) {
    return labIcons;
  }
  try {
    labIcons = await import("@lucide/lab");
  } catch {
    labIcons = {};
  }
  return labIcons;
}

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
    },
    fallback: {
      type: String,
      default: 'HelpCircle'
    }
  },

  data() {
    return {
      labIcon: null,
      labLoaded: false,
    };
  },

  computed: {
    isLabIcon() {
      return !(this.name in icons);
    },

    resolvedIcon() {
      if (!this.isLabIcon) {
        return icons[this.name] ?? icons[this.fallback] ?? icons['HelpCircle'];
      }

      if (!this.labLoaded) {
        return null;
      }

      return this.labIcon ?? icons[this.fallback] ?? icons['HelpCircle'];
    }
  },

  watch: {
    name: {
      immediate: true,
      async handler(name) {
        if (!this.isLabIcon) {
          this.labLoaded = true;
          return;
        }

        this.labLoaded = false;
        this.labIcon = null;

        const lab = await getLabIcons();
        const camelName = name.charAt(0).toLowerCase() + name.slice(1);
        const iconData = lab[camelName] ?? lab[name];

        if (iconData) {
          this.labIcon = createLucideIcon(name, iconData);
        }

        this.labLoaded = true;
      }
    }
  }
}
</script>
