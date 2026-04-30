import { ref } from 'vue'

const MIN_RATIO = 9 / 16
const MAX_RATIO = 4 / 3

export function usePostAspect(initial = 1) {
  const aspectRatio = ref(initial)

  function handleImageLoad(event) {
    const img = event?.target
    if (!img?.naturalWidth || !img?.naturalHeight) {
      return
    }
    const natural = img.naturalWidth / img.naturalHeight
    aspectRatio.value = Math.min(Math.max(natural, MIN_RATIO), MAX_RATIO)
  }

  return { aspectRatio, handleImageLoad }
}
