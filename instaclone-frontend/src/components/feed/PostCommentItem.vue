<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { formatShortDateTime } from '@/utils/dates'

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
  isPendingDelete: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['delete', 'cancel-delete', 'confirm-delete'])

const { currentUser } = storeToRefs(useAuthStore())

const isAuthor = computed(
  () => Boolean(currentUser.value?.id) && currentUser.value.id === props.comment.authorId,
)
const formattedDate = computed(() => formatShortDateTime(props.comment.createdAt))
</script>

<template>
  <li class="comment-item">
    <div class="comment-item__meta">
      <strong>{{ comment.author.name }}</strong>
      <span>@{{ comment.author.username }}</span>
    </div>

    <p class="comment-item__body">{{ comment.body }}</p>

    <div class="comment-item__footer">
      <time :datetime="comment.createdAt">{{ formattedDate }}</time>
      <button
        v-if="isAuthor && !isPendingDelete"
        type="button"
        class="comment-item__delete"
        @click="$emit('delete', comment)"
      >
        Apagar
      </button>
    </div>

    <div
      v-if="isAuthor && isPendingDelete"
      class="comment-item__confirm"
      role="alertdialog"
    >
      <span>Apagar este comentário?</span>
      <div class="comment-item__confirm-actions">
        <button
          type="button"
          class="btn btn-sm btn-outline-secondary"
          @click="$emit('cancel-delete')"
        >
          Cancelar
        </button>
        <button
          type="button"
          class="btn btn-sm btn-danger"
          @click="$emit('confirm-delete')"
        >
          Apagar
        </button>
      </div>
    </div>
  </li>
</template>

<style scoped>
.comment-item {
  display: grid;
  gap: 0.55rem;
  padding: 1rem;
  border-radius: 1.1rem;
  background: var(--app-surface-soft);
}

.comment-item__meta {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  flex-wrap: wrap;
}

.comment-item__meta strong {
  color: var(--app-text);
}

.comment-item__meta span {
  color: var(--app-muted);
}

.comment-item__body {
  margin: 0;
  line-height: 1.7;
}

.comment-item__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
  font-size: 0.9rem;
  color: var(--app-muted);
}

.comment-item__delete {
  border: 0;
  background: none;
  color: var(--app-link);
  font-weight: 700;
  cursor: pointer;
}

.comment-item__delete:hover {
  text-decoration: underline;
}

.comment-item__confirm {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.6rem;
  padding: 0.6rem 0.75rem;
  border: 1px solid rgba(255, 48, 64, 0.32);
  border-radius: 0.7rem;
  background: rgba(255, 48, 64, 0.08);
  color: var(--app-text);
  font-size: 0.9rem;
}

.comment-item__confirm-actions {
  margin-left: auto;
  display: flex;
  gap: 0.4rem;
}

@media (max-width: 575.98px) {
  .comment-item {
    padding: 0.85rem;
    border-radius: 0.85rem;
  }

  .comment-item__confirm {
    align-items: stretch;
  }

  .comment-item__confirm-actions {
    width: 100%;
    margin-left: 0;
  }

  .comment-item__confirm-actions > * {
    flex: 1;
  }
}
</style>
