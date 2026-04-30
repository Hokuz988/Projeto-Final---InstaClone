import { computed, onBeforeUnmount, ref } from 'vue'

const DEFAULT_ACCEPTED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']

export function useImageUpload({
  acceptedTypes = DEFAULT_ACCEPTED_TYPES,
  maxBytes = 5 * 1024 * 1024,
  invalidTypeMessage = 'Use uma imagem nos formatos JPG, PNG ou WEBP.',
  maxSizeMessage = 'Escolha uma imagem com até 5 MB.',
} = {}) {
  const inputRef = ref(null)
  const file = ref(null)
  const previewUrl = ref('')
  const fileName = ref('')

  const hasFile = computed(() => Boolean(file.value))

  function revokePreview() {
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
      URL.revokeObjectURL(previewUrl.value)
    }
  }

  function reset() {
    revokePreview()
    file.value = null
    previewUrl.value = ''
    fileName.value = ''
    if (inputRef.value) {
      inputRef.value.value = ''
    }
  }

  function selectFile(candidate) {
    if (!candidate) {
      return null
    }

    if (!acceptedTypes.includes(candidate.type)) {
      reset()
      return invalidTypeMessage
    }

    if (candidate.size > maxBytes) {
      reset()
      return maxSizeMessage
    }

    revokePreview()
    file.value = candidate
    fileName.value = candidate.name
    previewUrl.value = URL.createObjectURL(candidate)
    return null
  }

  function handleChange(event) {
    return selectFile(event.target.files?.[0])
  }

  onBeforeUnmount(revokePreview)

  return {
    inputRef,
    file,
    previewUrl,
    fileName,
    hasFile,
    handleChange,
    reset,
  }
}
