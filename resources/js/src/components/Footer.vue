<template>
  <footer class="footer-container">
    <n-card class="footer-content">
      <n-space vertical size="large">
        <!-- Main Footer Content -->
        <n-grid :cols="footerCols" :x-gap="24" :y-gap="24" responsive="screen">
          <!-- Company Info -->
          <n-grid-item>
            <n-space vertical size="medium">
              <n-text strong style="font-size: 20px; color: #2080f0;">
                Premium Properties
              </n-text>
              <n-text depth="3">
                Experience luxury accommodations at the finest destinations.
                We offer unique properties that create unforgettable memories.
              </n-text>
              <n-space size="medium">
                <n-button
                  v-for="social in socialLinks"
                  :key="social.name"
                  circle
                  secondary
                  @click="openSocialLink(social.url)"
                  :title="social.name"
                >
                  <template #icon>
                    <n-icon size="18">
                      <component :is="'svg'" viewBox="0 0 24 24" fill="currentColor">
                        <path :d="social.iconPath"/>
                      </component>
                    </n-icon>
                  </template>
                </n-button>
              </n-space>
            </n-space>
          </n-grid-item>

          <!-- Quick Links -->
          <n-grid-item>
            <n-space vertical size="medium">
              <n-text strong style="font-size: 16px;">Quick Links</n-text>
              <n-space vertical size="small">
                <n-button
                  v-for="link in quickLinks"
                  :key="link.name"
                  text
                  @click="navigateTo(link.path)"
                  class="footer-link"
                >
                  {{ link.name }}
                </n-button>
              </n-space>
            </n-space>
          </n-grid-item>

          <!-- Services -->
          <n-grid-item>
            <n-space vertical size="medium">
              <n-text strong style="font-size: 16px;">Services</n-text>
              <n-space vertical size="small">
                <n-button
                  v-for="service in services"
                  :key="service.name"
                  text
                  @click="navigateTo(service.path)"
                  class="footer-link"
                >
                  {{ service.name }}
                </n-button>
              </n-space>
            </n-space>
          </n-grid-item>

          <!-- Contact Info -->
          <n-grid-item>
            <n-space vertical size="medium">
              <n-text strong style="font-size: 16px;">Contact Us</n-text>
              <n-space vertical size="small">
                <n-space align="center" size="small">
                  <n-icon color="#52c41a">
                    <svg viewBox="0 0 24 24">
                      <path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                  </n-icon>
                  <n-text depth="3">{{ contactInfo.address }}</n-text>
                </n-space>

                <n-space align="center" size="small">
                  <n-icon color="#1890ff">
                    <svg viewBox="0 0 24 24">
                      <path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                    </svg>
                  </n-icon>
                  <n-text depth="3">{{ contactInfo.phone }}</n-text>
                </n-space>

                <n-space align="center" size="small">
                  <n-icon color="#fa8c16">
                    <svg viewBox="0 0 24 24">
                      <path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                  </n-icon>
                  <n-text depth="3">{{ contactInfo.email }}</n-text>
                </n-space>
              </n-space>
            </n-space>
          </n-grid-item>
        </n-grid>

        <!-- Newsletter Subscription -->
        <n-divider />

        <n-space justify="center">
          <n-card style="max-width: 500px; width: 100%;">
            <n-space vertical align="center" size="medium">
              <n-text strong style="font-size: 18px;">Stay Updated</n-text>
              <n-text depth="3" style="text-align: center;">
                Subscribe to our newsletter for exclusive offers and updates
              </n-text>
              <n-space style="width: 100%;">
                <n-input
                  v-model:value="newsletterEmail"
                  placeholder="Enter your email"
                  :status="emailError ? 'error' : undefined"
                  style="flex: 1;"
                />
                <n-button
                  type="primary"
                  @click="subscribeNewsletter"
                  :loading="subscribing"
                >
                  Subscribe
                </n-button>
              </n-space>
              <n-text v-if="emailError" type="error" depth="3">
                {{ emailError }}
              </n-text>
              <n-text v-if="subscriptionMessage" type="success" depth="3">
                {{ subscriptionMessage }}
              </n-text>
            </n-space>
          </n-card>
        </n-space>

        <!-- Bottom Bar -->
        <n-divider />

        <n-space justify="space-between" align="center" class="footer-bottom">
          <n-text depth="3">
            © {{ currentYear }} Premium Properties. All rights reserved.
          </n-text>

          <n-space size="medium">
            <n-button
              v-for="legal in legalLinks"
              :key="legal.name"
              text
              size="small"
              @click="navigateTo(legal.path)"
              class="footer-legal-link"
            >
              {{ legal.name }}
            </n-button>
          </n-space>
        </n-space>
      </n-space>
    </n-card>
  </footer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Reactive data
const newsletterEmail = ref('')
const subscribing = ref(false)
const emailError = ref('')
const subscriptionMessage = ref('')

// Computed
const currentYear = computed(() => new Date().getFullYear())

const footerCols = computed(() => {
  return 'xs:1 s:2 m:2 l:4 xl:4'
})

// Static data
const socialLinks = [
  {
    name: 'Facebook',
    url: 'https://facebook.com',
    iconPath: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'
  },
  {
    name: 'Instagram',
    url: 'https://instagram.com',
    iconPath: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'
  },
  {
    name: 'Twitter',
    url: 'https://twitter.com',
    iconPath: 'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z'
  },
  {
    name: 'LinkedIn',
    url: 'https://linkedin.com',
    iconPath: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'
  }
]

const quickLinks = [
  { name: 'Home', path: '/' },
  { name: 'Accommodations', path: '/accommodations' },
  { name: 'About Us', path: '/about' },
  { name: 'Contact', path: '/contact' },
  { name: 'Blog', path: '/blog' }
]

const services = [
  { name: 'Property Management', path: '/services/management' },
  { name: 'Booking Support', path: '/services/support' },
  { name: 'Guest Services', path: '/services/guest' },
  { name: 'Maintenance', path: '/services/maintenance' },
  { name: 'Marketing', path: '/services/marketing' }
]

const legalLinks = [
  { name: 'Privacy Policy', path: '/privacy' },
  { name: 'Terms of Service', path: '/terms' },
  { name: 'Cookie Policy', path: '/cookies' },
  { name: 'Disclaimer', path: '/disclaimer' }
]

const contactInfo = {
  address: 'Belgrade, Serbia',
  phone: '+381 11 123 4567',
  email: 'info@premiumproperties.com'
}

// Methods
const navigateTo = (path) => {
  // For Laravel/Vue router
  // this.$router.push(path)
  console.log(`Navigate to: ${path}`)
}

const openSocialLink = (url) => {
  window.open(url, '_blank', 'noopener,noreferrer')
}

const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

const subscribeNewsletter = async () => {
  emailError.value = ''
  subscriptionMessage.value = ''

  if (!newsletterEmail.value) {
    emailError.value = 'Email is required'
    return
  }

  if (!validateEmail(newsletterEmail.value)) {
    emailError.value = 'Please enter a valid email address'
    return
  }

  subscribing.value = true

  try {
    // Laravel API call for newsletter subscription
    // await axios.post('/api/newsletter/subscribe', {
    //   email: newsletterEmail.value
    // })

    await new Promise(resolve => setTimeout(resolve, 1500))

    subscriptionMessage.value = 'Thank you for subscribing to our newsletter!'
    newsletterEmail.value = ''

    setTimeout(() => {
      subscriptionMessage.value = ''
    }, 5000)

  } catch (error) {
    emailError.value = 'Subscription failed. Please try again.'
  } finally {
    subscribing.value = false
  }
}

// Lifecycle
onMounted(() => {
  // Any initialization logic
})
</script>

<style scoped>
.footer-container {
  margin-top: auto;
}

.footer-content {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: none;
}

.footer-link {
  justify-content: flex-start !important;
  padding: 4px 0 !important;
  color: #666 !important;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: #2080f0 !important;
}

.footer-legal-link {
  color: #999 !important;
  font-size: 12px !important;
}

.footer-legal-link:hover {
  color: #2080f0 !important;
}

.footer-bottom {
  flex-wrap: wrap;
  gap: 16px;
}

@media (max-width: 768px) {
  .footer-bottom {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
}
</style>
