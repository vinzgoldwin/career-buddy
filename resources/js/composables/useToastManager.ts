import { ref } from 'vue'
import type { NotificationItem, NotificationType, NotificationPosition } from '@/components/notifications/ToastContainer.vue'

let uid = 1

const notifications = ref<NotificationItem[]>([])
const position = ref<NotificationPosition>('bottom-right')

export function useToastManager() {
  function addNotification(type: NotificationType, title: string, message?: string, showIcon = true, duration?: number): number {
    const id = uid++
    notifications.value = [
      ...notifications.value,
      { id, type, title, message, showIcon, duration },
    ]
    return id
  }

  function addLoadingWithSuccess(loadingTitle: string, loadingMessage: string, successTitle: string, successMessage: string, loadingDuration = 3000) {
    const id = addNotification('loading', loadingTitle, loadingMessage, true)
    setTimeout(() => {
      notifications.value = notifications.value.map(n =>
        n.id === id ? { ...n, type: 'success', title: successTitle, message: successMessage, duration: 4000 } : n,
      )
    }, loadingDuration)
    return id
  }

  function close(id: number) {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  function setPosition(p: NotificationPosition) {
    position.value = p
  }

  // Shortcuts
  const success = (title: string, message?: string, duration = 3000) => addNotification('success', title, message, true, duration)
  const error = (title: string, message?: string, duration = 5000) => addNotification('error', title, message, true, duration)
  const warning = (title: string, message?: string, duration = 4000) => addNotification('warning', title, message, true, duration)
  const info = (title: string, message?: string, duration = 4000) => addNotification('info', title, message, true, duration)
  const loading = (title: string, message?: string) => addNotification('loading', title, message, true)

  return { notifications, position, addNotification, addLoadingWithSuccess, close, setPosition, success, error, warning, info, loading }
}

