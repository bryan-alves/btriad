export type ToastType = 'success' | 'danger' | 'info'

export type ToastPayload = {
  message: string
  type?: ToastType
  duration?: number
}

const DEFAULT_DURATION = 3500

export function showToast(payload: ToastPayload) {
  window.dispatchEvent(
    new CustomEvent('app-toast', {
      detail: {
        ...payload,
        type: normalizeToastType(payload.type),
      },
    }),
  )
}

export function toastSuccess(message: string, duration = DEFAULT_DURATION) {
  showToast({ message, type: 'success', duration })
}

export function toastDanger(message: string, duration = DEFAULT_DURATION) {
  showToast({ message, type: 'danger', duration })
}

export function toastInfo(message: string, duration = DEFAULT_DURATION) {
  showToast({ message, type: 'info', duration })
}

/** @deprecated Use toastDanger */
export function toastError(message: string, duration = DEFAULT_DURATION) {
  toastDanger(message, duration)
}

function normalizeToastType(type?: string): ToastType {
  if (type === 'success' || type === 'danger' || type === 'info') {
    return type
  }

  if (type === 'error') {
    return 'danger'
  }

  return 'info'
}
