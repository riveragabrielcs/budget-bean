<script setup>
import {ref, onMounted, watch, onUnmounted} from 'vue'

const props = defineProps({
    success: {
        type: [String, Object],
        default: null
    },
    error: {
        type: [String, Object],
        default: null
    },
    info: {
        type: [String, Object],
        default: null
    },
    warning: {
        type: [String, Object],
        default: null
    }
})

const isVisible = ref(false)
const shouldShow = ref(false)

// Determine which message to show and what type
const message = ref('')
const type = ref('')

// Timer references for cleanup
let showTimer = null
let dismissTimer = null

const updateMessage = () => {
    const extractMessage = (prop) => {
        if (!prop) return null
        return typeof prop === 'string' ? prop : prop.message
    }

    if (props.success) {
        message.value = extractMessage(props.success)
        type.value = 'success'
        shouldShow.value = true
    } else if (props.error) {
        message.value = extractMessage(props.error)
        type.value = 'error'
        shouldShow.value = true
    } else if (props.info) {
        message.value = extractMessage(props.info)
        type.value = 'info'
        shouldShow.value = true
    } else if (props.warning) {
        message.value = extractMessage(props.warning)
        type.value = 'warning'
        shouldShow.value = true
    } else {
        shouldShow.value = false
    }
}

// Watch for prop changes
watch(() => [props.success, props.error, props.info, props.warning], updateMessage, {immediate: true})

// Show animation when shouldShow becomes true
watch(shouldShow, (newVal) => {
    // Clear any existing timers first
    clearTimeout(showTimer)
    clearTimeout(dismissTimer)

    if (newVal) {
        // Reset visibility for new message
        isVisible.value = false

        // Show animation
        showTimer = setTimeout(() => {
            isVisible.value = true
        }, 50)

        // Auto-dismiss after 5 seconds
        dismissTimer = setTimeout(() => {
            dismiss()
        }, 5000)
    }
})

const dismiss = () => {
    // Clear any pending timers
    clearTimeout(showTimer)
    clearTimeout(dismissTimer)

    isVisible.value = false
    setTimeout(() => {
        shouldShow.value = false
        message.value = ''
        type.value = ''
    }, 300)
}

// Cleanup timers when component unmounts
onUnmounted(() => {
    clearTimeout(showTimer)
    clearTimeout(dismissTimer)
})

// Initialize on mount
onMounted(() => {
    updateMessage()
})

// Get styles based on message type
const getStyles = () => {
    switch (type.value) {
        case 'success':
            return {
                container: 'bg-emerald-50 border-emerald-200 text-emerald-800',
                icon: '✅',
                iconBg: 'bg-emerald-100',
                iconColor: 'text-emerald-600',
                closeBtn: 'text-emerald-400 hover:text-emerald-600'
            }
        case 'error':
            return {
                container: 'bg-red-50 border-red-200 text-red-800',
                icon: '❌',
                iconBg: 'bg-red-100',
                iconColor: 'text-red-600',
                closeBtn: 'text-red-400 hover:text-red-600'
            }
        case 'info':
            return {
                container: 'bg-blue-50 border-blue-200 text-blue-800',
                icon: 'ℹ️',
                iconBg: 'bg-blue-100',
                iconColor: 'text-blue-600',
                closeBtn: 'text-blue-400 hover:text-blue-600'
            }
        case 'warning':
            return {
                container: 'bg-amber-50 border-amber-200 text-amber-800',
                icon: '⚠️',
                iconBg: 'bg-amber-100',
                iconColor: 'text-amber-600',
                closeBtn: 'text-amber-400 hover:text-amber-600'
            }
        default:
            return {
                container: 'bg-stone-50 border-stone-200 text-stone-800',
                icon: '📋',
                iconBg: 'bg-stone-100',
                iconColor: 'text-stone-600',
                closeBtn: 'text-stone-400 hover:text-stone-600'
            }
    }
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out transform"
        enter-from-class="-translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-300 ease-in transform"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-2 opacity-0"
    >
        <div
            v-if="shouldShow && isVisible"
            :class="[
                'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4',
            ]"
        >
            <div
                :class="[
                    'border rounded-lg p-4 shadow-sm',
                    getStyles().container
                ]"
            >
                <div class="flex items-start">
                    <!-- Icon -->
                    <div
                        :class="[
                            'flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center mr-3',
                            getStyles().iconBg
                        ]"
                    >
                        <span class="text-sm">{{ getStyles().icon }}</span>
                    </div>

                    <!-- Message Content -->
                    <div class="flex-1 min-w-0 h-8 flex items-center">
                        <p class="text-sm font-medium leading-relaxed">
                            {{ message }}
                        </p>
                    </div>

                    <!-- Close Button -->
                    <div class="flex-shrink-0 ml-3">
                        <button
                            @click="dismiss"
                            :class="[
                                'inline-flex w-8 h-8 rounded-lg p-1.5 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
                                getStyles().closeBtn
                            ]"
                            :style="{ focusRingColor: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6b7280' }"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
