<script setup>
import PostCommentItem from '@/components/feed/PostCommentItem.vue'

defineProps({
  comments: {
    type: Array,
    required: true,
  },
  hasMore: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  pendingDeleteId: {
    type: [Number, String, null],
    default: null,
  },
})

defineEmits(['delete-comment', 'cancel-delete', 'confirm-delete', 'load-more'])
</script>

<template>
  <ul v-if="comments.length > 0" class="comment-list">
    <PostCommentItem
      v-for="comment in comments"
      :key="comment.id"
      :comment="comment"
      :is-pending-delete="pendingDeleteId === comment.id"
      @delete="$emit('delete-comment', $event)"
      @cancel-delete="$emit('cancel-delete')"
      @confirm-delete="$emit('confirm-delete')"
    />
  </ul>

  <p v-else class="comment-list__empty">
    Ainda não há comentários. Comece a conversa neste post.
  </p>

  <button
    v-if="hasMore"
    class="comment-list__load-more"
    type="button"
    :disabled="loading"
    @click="$emit('load-more')"
  >
    {{ loading ? 'Carregando...' : 'Carregar mais comentários' }}
  </button>
</template>

<style scoped>
.comment-list {
  display: grid;
  gap: 0.85rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.comment-list__empty {
  margin: 0;
  color: var(--app-muted);
  line-height: 1.7;
}

.comment-list__load-more {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 0.85rem 1rem;
  border: 0;
  border-radius: 999px;
  color: var(--app-text);
  font-weight: 800;
  background: var(--app-surface-soft);
  transition: background-color 180ms ease, color 180ms ease;
}

.comment-list__load-more:hover:not(:disabled),
.comment-list__load-more:focus-visible {
  color: #fff;
  background: var(--app-link);
}

.comment-list__load-more:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}
</style>
