<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterLink } from 'vue-router'
import ProfileAvatar from '@/components/profile/ProfileAvatar.vue'
import { useAuthStore } from '@/stores/auth'
import { useFollowsStore } from '@/stores/follows'
import { ROUTE_NAMES } from '@/router/routeNames'

const props = defineProps({
  account: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['follow-changed'])

const { currentUser } = storeToRefs(useAuthStore())
const followsStore = useFollowsStore()

const isViewer = computed(
  () => Boolean(currentUser.value?.id) && currentUser.value.id === props.account.id,
)
const isFollowing = computed(() => followsStore.isFollowing(props.account.id))
const isPending = computed(() => followsStore.isPending(props.account.id))
const profileLink = computed(() => {
  if (currentUser.value?.username === props.account.username) {
    return { name: ROUTE_NAMES.profile }
  }
  return { name: ROUTE_NAMES.userProfile, params: { username: props.account.username } }
})

async function handleToggle() {
  const wasFollowing = isFollowing.value
  await followsStore.toggle(props.account.id)
  emit('follow-changed', { account: props.account, wasFollowing })
}
</script>

<template>
  <article class="account-card card border-0">
    <RouterLink :to="profileLink" class="account-card__identity">
      <ProfileAvatar
        :name="account.name"
        :username="account.username"
        :avatar-url="account.avatarUrl"
        :colors="account.colors"
        size="md"
      />

      <div class="account-card__copy">
        <strong>{{ account.name }}</strong>
        <span>@{{ account.username }}</span>
        <p v-if="account.bio">{{ account.bio }}</p>
      </div>
    </RouterLink>

    <span v-if="isViewer" class="account-card__owner-badge">Você</span>
    <button
      v-else
      class="btn"
      :class="isFollowing ? 'btn-outline-secondary' : 'btn-primary'"
      type="button"
      :disabled="isPending"
      @click="handleToggle"
    >
      {{ isFollowing ? 'Seguindo' : 'Seguir' }}
    </button>
  </article>
</template>

<style scoped>
.account-card {
  display: grid;
  gap: 1rem;
  padding: 1.4rem;
  border-radius: 1.75rem;
  background: var(--app-surface);
}

.account-card__identity {
  display: flex;
  align-items: flex-start;
  gap: 0.9rem;
  min-width: 0;
  color: inherit;
  text-decoration: none;
}

.account-card__copy {
  display: grid;
  gap: 0.2rem;
  min-width: 0;
}

.account-card__copy strong {
  font-size: 1.05rem;
  overflow-wrap: anywhere;
}

.account-card__copy span,
.account-card__copy p {
  margin: 0;
  color: var(--app-muted);
  line-height: 1.65;
  overflow-wrap: anywhere;
}

.account-card__owner-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: fit-content;
  padding: 0.7rem 1rem;
  border-radius: 999px;
  font-weight: 800;
  background: var(--app-surface-soft);
}

@media (min-width: 768px) {
  .account-card {
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
  }
}

@media (max-width: 575.98px) {
  .account-card {
    gap: 0.85rem;
    padding: 1rem;
    border-radius: 0.85rem;
  }

  .account-card__identity {
    gap: 0.75rem;
  }

  .account-card .btn,
  .account-card__owner-badge {
    width: 100%;
  }
}
</style>
