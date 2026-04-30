<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterLink } from 'vue-router'
import AppIcon from '@/components/layout/AppIcon.vue'
import ProfileAvatar from '@/components/profile/ProfileAvatar.vue'
import { useAuthStore } from '@/stores/auth'
import { ROUTE_NAMES } from '@/router/routeNames'
import { formatDayMonthYear, formatRelative } from '@/utils/dates'
import { POST_CAPTION_MAX_LENGTH } from '@/stores/feed'
import { usePostAspect } from '@/composables/usePostAspect'

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['toggle-like', 'submit-comment', 'delete-post'])

const { currentUser } = storeToRefs(useAuthStore())

const isOwnPost = computed(
  () => Boolean(currentUser.value?.id && props.post.author?.id === currentUser.value.id),
)

const commentText = ref('')

const { aspectRatio: imageAspectRatio, handleImageLoad } = usePostAspect()

const postLink = computed(() => ({
  name: ROUTE_NAMES.postDetails,
  params: { postId: props.post.id },
}))

const authorLink = computed(() => {
  if (currentUser.value?.username === props.post.author.username) {
    return { name: ROUTE_NAMES.profile }
  }
  return {
    name: ROUTE_NAMES.userProfile,
    params: { username: props.post.author.username },
  }
})

const likeLabel = computed(() => {
  const total = props.post.likesCount ?? 0
  return `${total} ${total === 1 ? 'curtida' : 'curtidas'}`
})

const commentLabel = computed(() => {
  const total = props.post.commentsCount ?? 0
  if (total === 0) return 'Seja o primeiro a comentar'
  if (total === 1) return 'Ver 1 comentário'
  return `Ver todos os ${total} comentários`
})

const publishedLabel = computed(() => formatDayMonthYear(props.post.createdAt))
const shortPublishedLabel = computed(() => formatRelative(props.post.createdAt))

const trimmedComment = computed(() => commentText.value.trim())
const canLikePost = computed(() => !isOwnPost.value)

function handleCommentSubmit() {
  if (!trimmedComment.value) {
    return
  }

  emit('submit-comment', {
    postId: props.post.id,
    text: trimmedComment.value,
  })

  commentText.value = ''
}

const menuOpen = ref(false)
const confirmingDelete = ref(false)

function handleDocumentClick(event) {
  if (!event.target.closest?.('.feed-post__menu-wrapper')) {
    menuOpen.value = false
    confirmingDelete.value = false
  }
}

function toggleMenu() {
  menuOpen.value = !menuOpen.value
  if (!menuOpen.value) confirmingDelete.value = false
  if (menuOpen.value) document.addEventListener('click', handleDocumentClick)
  else document.removeEventListener('click', handleDocumentClick)
}

function requestDelete() {
  confirmingDelete.value = true
}

function cancelDelete() {
  confirmingDelete.value = false
  menuOpen.value = false
  document.removeEventListener('click', handleDocumentClick)
}

function confirmDelete() {
  emit('delete-post', props.post.id)
  confirmingDelete.value = false
  menuOpen.value = false
  document.removeEventListener('click', handleDocumentClick)
}

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<template>
  <article class="feed-post card border-0">
    <header class="feed-post__header">
      <RouterLink :to="authorLink" class="feed-post__author">
        <ProfileAvatar
          :name="post.author.name"
          :username="post.author.username"
          :avatar-url="post.author.avatarUrl"
          :colors="post.author.colors"
          size="sm"
        />

        <span class="feed-post__author-meta">
          <strong>{{ post.author.username }}</strong>
          <span>{{ post.author.name }}</span>
        </span>
      </RouterLink>

      <div class="feed-post__header-side">
        <time class="feed-post__date" :datetime="post.createdAt">{{ shortPublishedLabel }}</time>
        <div v-if="isOwnPost" class="feed-post__menu-wrapper">
          <button
            class="feed-post__menu"
            type="button"
            aria-label="Mais opções"
            :aria-expanded="menuOpen"
            @click.stop="toggleMenu"
          >
            <AppIcon name="more" />
          </button>
          <div v-if="menuOpen" class="feed-post__menu-pop" role="menu">
            <button
              v-if="!confirmingDelete"
              type="button"
              class="feed-post__menu-item feed-post__menu-item--danger"
              @click="requestDelete"
            >
              Apagar post
            </button>
            <div v-else class="feed-post__menu-confirm" role="alertdialog">
              <p>Apagar este post?</p>
              <div class="feed-post__menu-confirm-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="cancelDelete">
                  Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-danger" @click="confirmDelete">
                  Apagar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <RouterLink :to="postLink" class="feed-post__media-link">
      <img
        class="feed-post__media"
        :src="post.imageUrl"
        :alt="post.imageAlt"
        :style="{ aspectRatio: imageAspectRatio }"
        loading="lazy"
        @load="handleImageLoad"
      />
    </RouterLink>

    <div class="feed-post__body">
      <div class="feed-post__toolbar">
        <div class="feed-post__toolbar-group">
          <button
            class="feed-post__icon-button"
            :class="{ 'is-active': post.likedByMe }"
            type="button"
            :disabled="!canLikePost"
            :aria-label="post.likedByMe ? 'Remover curtida' : 'Curtir post'"
            @click="emit('toggle-like', post.id)"
          >
            <AppIcon name="heart" :filled="post.likedByMe" />
          </button>

          <RouterLink :to="postLink" class="feed-post__icon-button" aria-label="Abrir comentários">
            <AppIcon name="comment" />
          </RouterLink>
        </div>
      </div>

      <p class="feed-post__likes">{{ likeLabel }}</p>

      <p v-if="post.caption" class="feed-post__caption">
        <RouterLink :to="authorLink" class="feed-post__caption-link">
          {{ post.author.username }}
        </RouterLink>
        {{ post.caption }}
      </p>

      <RouterLink v-if="post.commentsCount > 0" :to="postLink" class="feed-post__meta-link">
        {{ commentLabel }}
      </RouterLink>
      <p v-else class="feed-post__meta-link">{{ commentLabel }}</p>

      <time class="feed-post__timestamp" :datetime="post.createdAt">{{ publishedLabel }}</time>

      <form class="feed-post__comment-form" @submit.prevent="handleCommentSubmit">
        <input
          v-model="commentText"
          class="feed-post__comment-input"
          type="text"
          :maxlength="POST_CAPTION_MAX_LENGTH"
          placeholder="Adicione um comentário..."
        />
        <button class="feed-post__submit" type="submit" :disabled="!trimmedComment">
          Publicar
        </button>
      </form>
    </div>
  </article>
</template>

<style scoped>
.feed-post {
  overflow: hidden;
  border-radius: 0.9rem;
  background: var(--app-surface);
}

.feed-post__header,
.feed-post__body {
  padding: 0.9rem 1rem 0;
}

.feed-post__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.85rem;
}

.feed-post__author {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
  color: inherit;
  text-decoration: none;
}

.feed-post__author-meta {
  display: grid;
  min-width: 0;
}

.feed-post__author-meta strong {
  color: var(--app-text);
  font-size: 0.92rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.feed-post__author-meta span,
.feed-post__date,
.feed-post__timestamp {
  color: var(--app-muted);
  font-size: 0.78rem;
}

.feed-post__header-side {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.feed-post__menu-wrapper {
  position: relative;
}

.feed-post__menu {
  display: grid;
  place-items: center;
  width: 2rem;
  height: 2rem;
  padding: 0;
  border: 0;
  color: var(--app-text);
  background: none;
}

.feed-post__menu-pop {
  position: absolute;
  right: 0;
  top: calc(100% + 0.35rem);
  min-width: 12rem;
  padding: 0.5rem;
  background: var(--app-surface);
  border: 1px solid var(--app-border);
  border-radius: 0.6rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  z-index: 20;
}

.feed-post__menu-item {
  display: block;
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: 0;
  border-radius: 0.45rem;
  text-align: left;
  background: none;
  color: var(--app-text);
}

.feed-post__menu-item:hover,
.feed-post__menu-item:focus-visible {
  background: var(--app-surface-soft);
}

.feed-post__menu-item--danger {
  color: var(--app-danger);
  font-weight: 600;
}

.feed-post__menu-confirm p {
  margin: 0 0 0.55rem;
  padding: 0.25rem 0.25rem 0;
  font-size: 0.92rem;
}

.feed-post__menu-confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 0 0.25rem 0.25rem;
}

.feed-post__media-link {
  display: block;
}

.feed-post__media {
  display: block;
  width: 100%;
  object-fit: cover;
  background: var(--app-surface-soft);
}

.feed-post__body {
  padding-bottom: 0;
}

.feed-post__toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.feed-post__toolbar-group {
  display: flex;
  align-items: center;
  gap: 0.15rem;
}

.feed-post__icon-button {
  display: grid;
  place-items: center;
  width: 2.35rem;
  height: 2.35rem;
  padding: 0;
  border: 0;
  color: var(--app-text);
  background: none;
  text-decoration: none;
  transition: color 180ms ease;
}

.feed-post__icon-button:hover,
.feed-post__icon-button:focus-visible,
.feed-post__menu:hover,
.feed-post__menu:focus-visible {
  color: var(--app-muted);
}

.feed-post__icon-button.is-active {
  color: var(--app-danger);
}

.feed-post__icon-button:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.feed-post__likes {
  margin: 0 0 0.55rem;
  color: var(--app-text);
  font-size: 0.92rem;
  font-weight: 700;
}

.feed-post__caption {
  margin: 0 0 0.45rem;
  color: var(--app-text);
  line-height: 1.65;
  white-space: pre-line;
}

.feed-post__caption-link {
  margin-right: 0.35rem;
  color: var(--app-text);
  font-weight: 700;
  text-decoration: none;
}

.feed-post__meta-link {
  margin: 0 0 0.45rem;
  color: var(--app-muted);
  font-size: 0.9rem;
  text-decoration: none;
}

.feed-post__timestamp {
  display: block;
  margin-bottom: 0.75rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.feed-post__comment-form {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin: 0 -1rem;
  padding: 0.85rem 1rem;
  border-top: 1px solid var(--app-border);
}

.feed-post__comment-input {
  flex: 1;
  min-width: 9rem;
  padding: 0;
  border: 0;
  color: var(--app-text);
  background: transparent;
}

.feed-post__comment-input:focus-visible {
  outline: none;
}

.feed-post__submit {
  padding: 0;
  border: 0;
  color: var(--app-link);
  background: none;
  font-weight: 700;
}

.feed-post__submit:disabled {
  cursor: not-allowed;
  opacity: 0.45;
}

@media (max-width: 575.98px) {
  .feed-post {
    border-radius: 0.75rem;
  }

  .feed-post__header,
  .feed-post__body {
    padding-right: 0.8rem;
    padding-left: 0.8rem;
  }

  .feed-post__header {
    gap: 0.6rem;
  }

  .feed-post__author {
    gap: 0.6rem;
  }

  .feed-post__date {
    display: none;
  }

  .feed-post__comment-form {
    margin-right: -0.8rem;
    margin-left: -0.8rem;
    padding: 0.75rem 0.8rem;
  }
}

@media (max-width: 359.98px) {
  .feed-post__comment-input,
  .feed-post__submit {
    width: 100%;
  }
}
</style>
