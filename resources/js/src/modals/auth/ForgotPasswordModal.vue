<template>
  <fwb-modal v-if="show" @close="close" size="md">
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900">
          Forgot password
        </h3>
      </div>
    </template>
    <template #body>
      <p class="text-gray-600 mb-6">
        Please enter your email address to reset your password, and we'll email you a link to reset your password.
      </p>

      <form @submit.prevent="handleForgotPassword">
        <!-- Validation Alert Box -->
        <validation-alert-box
          v-if="Object.keys(forgotPasswordErrors).length > 0"
          :errors="forgotPasswordErrors"
          class="mb-4"
        />

        <!-- Email Input -->
        <div class="mb-6">
          <label
            for="email"
            class="block mb-2 text-sm font-medium text-gray-900"
          >
            Email address
          </label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="john@example.com"
            autocomplete="email"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            :class="{ 'border-red-500': emailError }"
            @keypress.enter="handleForgotPassword"
            @blur="validateEmail"
          />
          <p v-if="emailError" class="mt-2 text-sm text-red-600">
            {{ emailError }}
          </p>
        </div>

        <!-- Submit Button -->
        <fwb-button
          color="blue"
          size="lg"
          class="w-full"
          :is-loading="isLoading"
          :disabled="isLoading"
          @click="handleForgotPassword"
        >
          {{ isLoading ? "Sending..." : "Send reset link" }}
        </fwb-button>
      </form>
    </template>
  </fwb-modal>
</template>

<script>
import { FwbModal, FwbButton } from 'flowbite-vue'
import config from '@/config'
import { toCamelCase } from '@/utils/helpers'
import { mapState, mapActions } from 'vuex'

const modalName = config.modals.forgotPasswordModal
const modalNameCamelCase = toCamelCase(modalName)

export default {
  name: 'ForgotPasswordModal',
  components: {
    FwbModal,
    FwbButton
  },
  computed: {
    ...mapState({
      show: (state) =>
        state.modals[modalNameCamelCase]
          ? state.modals[modalNameCamelCase].shown
          : false,
      promise: (state) =>
        state.modals[modalNameCamelCase]
          ? state.modals[modalNameCamelCase].promise
          : null,
      resolve: (state) =>
        state.modals[modalNameCamelCase]
          ? state.modals[modalNameCamelCase].resolve
          : null,
      reject: (state) =>
        state.modals[modalNameCamelCase]
          ? state.modals[modalNameCamelCase].reject
          : null,
      options: (state) =>
        state.modals[modalNameCamelCase]
          ? state.modals[modalNameCamelCase].options
          : false
    })
  },
  data() {
    return {
      modalName,
      formData: {
        email: ''
      },
      isLoading: false,
      forgotPasswordErrors: {},
      emailError: ''
    }
  },
  methods: {
    ...mapActions(['initModal', 'closeModal']),
    ...mapActions('auth', ['forgotPassword']),
    validateEmail() {
      this.emailError = ''

      if (!this.formData.email) {
        this.emailError = 'Email address is required'
        return false
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(this.formData.email)) {
        this.emailError = 'Please enter valid email address'
        return false
      }

      return true
    },

    async handleForgotPassword() {
      this.clearMessage()

      if (!this.validateEmail()) {
        return
      }

      this.isLoading = true

      try {
        await this.forgotPassword({ email: this.formData.email })
        this.close()
      } catch (e) {
        if (e.error && e.error.error && e.error.error.validation_errors) {
          this.forgotPasswordErrors = e.error.error.validation_errors
        }
      } finally {
        this.isLoading = false
      }
    },

    clearMessage() {
      this.forgotPasswordErrors = {}
      this.emailError = ''
    },

    ok() {
      if (this.resolve !== null) {
        this.resolve({ formData: this.formData })
      }
      this.close()
    },

    close() {
      // Reset form data
      Object.assign(this.$data, this.$options.data.call(this))
      this.closeModal({ modalName: this.modalName })
    }
  },
  created() {
    this.initModal({ modalName: this.modalName })
  }
}
</script>
