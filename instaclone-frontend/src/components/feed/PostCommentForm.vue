<script setup>
import { computed, ref } from 'vue'
import { POST_CAPTION_MAX_LENGTH } from '@/stores/feed'

const props = defineProps({
  submitting: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit'])

const text = ref('')
const trimmed = computed(() => text.value.trim())
const canSubmit = computed(() => Boolean(trimmed.value) && !props.submitting)

function handleSubmit() {
  if (!canSubmit.value) {
    return
  }
  emit('submit', trimmed.value, () => {
    text.value = ''
  })
}
</script>

<template>
  <form class="comment-form" @submit.prevent="handleSubmit">
    <textarea
      v-model="text"
      class="comment-form__input"
      :maxlength="POST_CAPTION_MAX_LENGTH"
      rows="3"
      placeholder="Adicione um comentário"
    />
    <button
      class="comment-form__submit"
      type="submit"
      :disabled="!canSubmit"
    >
      {{ submitting ? 'Enviando...' : 'Enviar comentário' }}
    </button>
  </form>
</template>

<style scoped>
.comment-form {
  display: grid;
  gap: 0.75rem;
}

.comment-form__input {
  width: 100%;
  min-height: 7rem;
  padding: 0.95rem 1rem;
  border: 1px solid var(--app-border-strong);
  border-radius: 1.15rem;
  color: var(--app-text);
  resize: vertical;
  background: var(--app-surface-soft);
}

.comment-form__input:focus-visible {
  outline: 2px solid rgba(0, 149, 246, 0.2);
  border-color: rgba(0, 149, 246, 0.45);
}

.comment-form__submit {
  justify-self: start;
  min-width: 13rem;
  padding: 0.85rem 1rem;
  border: 0;
  border-radius: 999px;
  color: #fff;
  font-weight: 800;
  background: var(--app-link);
  transition: transform 180ms ease, background-color 180ms ease;
}

.comment-form__submit:hover:not(:disabled),
.comment-form__submit:focus-visible {
  transform: translateY(-1px);
}

.comment-form__submit:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}

@media (max-width: 575.98px) {
  .comment-form__input {
    min-height: 6rem;
    border-radius: 0.85rem;
  }

  .comment-form__submit {
    width: 100%;
    justify-self: stretch;
    border-radius: 0.8rem;
  }
}
</style>
