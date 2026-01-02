import { ref } from 'vue'

const isOpen = ref(false)
const title = ref('')
const description = ref('')
let resolver = () => {}

export function useConfirmDialog() {
  const init = (t, d = '') => {
    title.value = t
    description.value = d
    isOpen.value = true

    return new Promise((resolve) => {
      resolver = resolve
    })
  }

  const onConfirm = () => {
    isOpen.value = false
    resolver(true)
  }

  const onCancel = () => {
    isOpen.value = false
    resolver(false)
  }

  return {
    isOpen,
    title,
    description,
    init,
    onConfirm,
    onCancel,
  }
}

const instance = useConfirmDialog()

export const confirmModal = instance.init
export const confirmDialogState = instance

const deleteIsOpen = ref(false)
const deleteTitle = ref('')
const deleteDescription = ref('')
const deleteItemName = ref('')
let deleteResolver = () => {}

export function useDeleteConfirmDialog() {
  const init = (t, d = '', itemName = '') => {
    deleteTitle.value = t
    deleteDescription.value = d
    deleteItemName.value = itemName
    deleteIsOpen.value = true

    return new Promise((resolve) => {
      deleteResolver = resolve
    })
  }

  const onConfirm = () => {
    deleteIsOpen.value = false
    deleteResolver(true)
  }

  const onCancel = () => {
    deleteIsOpen.value = false
    deleteResolver(false)
  }

  return {
    isOpen: deleteIsOpen,
    title: deleteTitle,
    description: deleteDescription,
    itemName: deleteItemName,
    init,
    onConfirm,
    onCancel,
  }
}

const deleteInstance = useDeleteConfirmDialog()

export const deleteConfirmModal = deleteInstance.init
export const deleteConfirmDialogState = deleteInstance
