import { ref, onMounted, onUnmounted } from 'vue'
import api from '../services/axios'

export function useNotifications() {
  const unreadCount = ref(0)
  const isNotificationPanelOpen = ref(false)
  let intervalId: number | null = null

  const fetchUnreadCount = async () => {
    try {
      const response = await api.get('/notifications/unread-count')
      unreadCount.value = response.data.count
    } catch (error) {
      // Silently fail
    }
  }

  const openNotificationPanel = () => {
    isNotificationPanelOpen.value = true
  }

  const closeNotificationPanel = () => {
    isNotificationPanelOpen.value = false
  }

  const updateCount = () => {
    fetchUnreadCount()
  }

  onMounted(() => {
    fetchUnreadCount()
    // Poll every 30 seconds
    intervalId = window.setInterval(fetchUnreadCount, 30000)
  })

  onUnmounted(() => {
    if (intervalId) {
      clearInterval(intervalId)
    }
  })

  return {
    unreadCount,
    isNotificationPanelOpen,
    openNotificationPanel,
    closeNotificationPanel,
    updateCount
  }
}
