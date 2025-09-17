<script>
import { h, defineComponent } from 'vue';
import LogInModal from './auth/LogInModal.vue';
import SignUpModal from './auth/SignUpModal.vue';
import ForgotPasswordModal from './auth/ForgotPasswordModal.vue';

export default defineComponent({
  render() {
    return [
        h(LogInModal),
        h(SignUpModal),
        h(ForgotPasswordModal)
    ];
  }
});
</script>
