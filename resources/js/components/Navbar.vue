<template>
  <div>
    <n-config-provider :theme="theme">
      <n-layout>
        <n-layout-header style="height: 64px; padding: 0 24px;" bordered>
          <div style="display: flex; align-items: center; height: 100%; justify-content: space-between;">
            <!-- Logo/Brand -->
            <div style="display: flex; align-items: center;">
              <h3 style="margin: 0; color: #18a058;">TuristApp</h3>
            </div>

            <!-- Navigation Items -->
            <div style="display: flex; align-items: center; gap: 24px;">
              <!-- Oglasi smeštaj dugme -->
              <n-button type="primary" size="medium">
                <template #icon>
                  <n-icon>
                    <HomeIcon />
                  </n-icon>
                </template>
                Oglasi smeštaj
              </n-button>

              <!-- Destinacije dropdown -->
              <n-dropdown
                :options="destinationOptions"
                @select="handleDestinationSelect"
                trigger="hover"
              >
                <n-button text style="font-size: 16px;">
                  Destinacije
                  <template #icon>
                    <n-icon style="margin-left: 4px;">
                      <ChevronDownIcon />
                    </n-icon>
                  </template>
                </n-button>
              </n-dropdown>

              <!-- Prijava dugme -->
              <n-button secondary>
                <template #icon>
                  <n-icon>
                    <UserIcon />
                  </n-icon>
                </template>
                Prijava
              </n-button>
            </div>
          </div>
        </n-layout-header>
      </n-layout>
    </n-config-provider>
  </div>
</template>

<script setup>
import { ref, h } from 'vue'
import {
  NConfigProvider,
  NLayout,
  NLayoutHeader,
  NLayoutContent,
  NButton,
  NDropdown,
  NCard,
  NIcon,
  NSpace,
  NTag,
  darkTheme
} from 'naive-ui'
// SVG ikone komponente
const HomeIcon = {
  render() {
    return h('svg', {
      width: '16',
      height: '16',
      viewBox: '0 0 24 24',
      fill: 'currentColor'
    }, [
      h('path', {
        d: 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z'
      })
    ])
  }
}

const UserIcon = {
  render() {
    return h('svg', {
      width: '16',
      height: '16',
      viewBox: '0 0 24 24',
      fill: 'currentColor'
    }, [
      h('path', {
        d: 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'
      })
    ])
  }
}

const ChevronDownIcon = {
  render() {
    return h('svg', {
      width: '16',
      height: '16',
      viewBox: '0 0 24 24',
      fill: 'currentColor'
    }, [
      h('path', {
        d: 'M7.41 8.84L12 13.42l4.59-4.58L18 10.25l-6 6-6-6z'
      })
    ])
  }
}

// Theme (možete promeniti u darkTheme za tamnu temu)
const theme = ref(null)

// Selected destination
const selectedDestination = ref('')

// Destinacije options za dropdown
const destinationOptions = [
  {
    label: 'Popularne destinacije',
    key: 'popular',
    type: 'group',
    children: [
      {
        label: 'Beograd',
        key: 'beograd'
      },
      {
        label: 'Novi Sad',
        key: 'novi-sad'
      },
      {
        label: 'Niš',
        key: 'nis'
      }
    ]
  },
  {
    label: 'Planinske destinacije',
    key: 'mountains',
    type: 'group',
    children: [
      {
        label: 'Kopaonik',
        key: 'kopaonik'
      },
      {
        label: 'Zlatibor',
        key: 'zlatibor'
      },
      {
        label: 'Tara',
        key: 'tara'
      }
    ]
  },
  {
    label: 'Spa destinacije',
    key: 'spa',
    type: 'group',
    children: [
      {
        label: 'Vrnjačka Banja',
        key: 'vrnjacka-banja'
      },
      {
        label: 'Sokobanja',
        key: 'sokobanja'
      },
      {
        label: 'Bukovička Banja',
        key: 'bukovicka-banja'
      }
    ]
  }
]

// Handle destination selection
const handleDestinationSelect = (key) => {
  if (key !== 'popular' && key !== 'mountains' && key !== 'spa') {
    selectedDestination.value = destinationOptions
      .flatMap(group => group.children || [])
      .find(item => item.key === key)?.label || key
  }
}
</script>

<style scoped>
/* Dodatni stilovi ako su potrebni */
</style>
