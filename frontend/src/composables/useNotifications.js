import { reactive, readonly } from "vue";

const notifications = reactive([]);
let nextNotificationId = 1;

function removeNotification(id) {
  const index = notifications.findIndex((notification) => notification.id === id);

  if (index >= 0) {
    notifications.splice(index, 1);
  }
}

function pushNotification(message, variant = "info", options = {}) {
  const duration = options.duration ?? (variant === "error" ? 5200 : 3200);
  const id = nextNotificationId++;

  notifications.push({
    id,
    message,
    variant,
  });

  if (typeof window !== "undefined" && duration > 0) {
    window.setTimeout(() => {
      removeNotification(id);
    }, duration);
  }

  return id;
}

export function useNotifications() {
  return {
    notifications: readonly(notifications),
    notifyInfo(message, options) {
      return pushNotification(message, "info", options);
    },
    notifySuccess(message, options) {
      return pushNotification(message, "success", options);
    },
    notifyError(message, options) {
      return pushNotification(message, "error", options);
    },
    removeNotification,
  };
}
