import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

export function useAuthGuard() {
    const showAuthModal = ref(false)
    const page = usePage()

    const isAuthenticated = () => {
        return !!page.props.auth?.user
    }

    const requireAuth = (callback) => {
        if (isAuthenticated()) {
            callback()
        } else {
            showAuthModal.value = true
        }
    }

    const closeAuthModal = () => {
        showAuthModal.value = false
    }

    return {
        isAuthenticated,
        requireAuth,
        showAuthModal,
        closeAuthModal
    }
}
