<script setup>
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/components/layout/AppIcon.vue'
import ProfileHeader from '@/components/profile/ProfileHeader.vue'
import ProfileSummaryCards from '@/components/profile/ProfileSummaryCards.vue'
import ProfilePostGrid from '@/components/profile/ProfilePostGrid.vue'
import { useAuthStore } from '@/stores/auth'
import { useFollowsStore } from '@/stores/follows'
import { extractErrorMessage } from '@/services/api'
import * as followsService from '@/services/follows.service'
import * as usersService from '@/services/users.service'
import { normalizePost } from '@/stores/feed'
import { normalizeUser } from '@/stores/profileUtils'
import { CONNECTION_LIST_TYPES, ROUTE_NAMES } from '@/router/routeNames'

const route = useRoute()
const { currentUser } = storeToRefs(useAuthStore())
const followsStore = useFollowsStore()

const profile = ref(null)
const postsList = ref([])
const postsCount = ref(0)
const followers = ref([])
const followersCount = ref(0)
const following = ref([])
const followingCount = ref(0)
const isFollowedByViewer = ref(false)
const isLoading = ref(false)
const loadError = ref('')
const feedbackMessage = ref('')

const selectedUsername = computed(() =>
  typeof route.params.username === 'string' ? route.params.username.trim().toLowerCase() : '',
)

const isOwnProfile = computed(
  () => Boolean(profile.value && currentUser.value && profile.value.id === currentUser.value.id),
)

const followersRoute = computed(() => {
  if (!profile.value || isOwnProfile.value) {
    return {
      name: ROUTE_NAMES.profileConnections,
      params: { type: CONNECTION_LIST_TYPES.followers },
    }
  }
  return {
    name: ROUTE_NAMES.userConnections,
    params: { username: profile.value.username, type: CONNECTION_LIST_TYPES.followers },
  }
})

const followingRoute = computed(() => {
  if (!profile.value || isOwnProfile.value) {
    return {
      name: ROUTE_NAMES.profileConnections,
      params: { type: CONNECTION_LIST_TYPES.following },
    }
  }
  return {
    name: ROUTE_NAMES.userConnections,
    params: { username: profile.value.username, type: CONNECTION_LIST_TYPES.following },
  }
})

async function loadProfile() {
  isLoading.value = true
  loadError.value = ''
  feedbackMessage.value = ''

  profile.value = null
  postsList.value = []
  postsCount.value = 0
  followers.value = []
  followersCount.value = 0
  following.value = []
  followingCount.value = 0
  isFollowedByViewer.value = false

  try {
    const targetUsername = selectedUsername.value || currentUser.value?.username
    if (!targetUsername) {
      loadError.value = 'Perfil não encontrado.'
      return
    }

    const raw = await usersService.getByUsername(targetUsername)
    const targetUser = normalizeUser(raw)
    if (!targetUser) {
      loadError.value = 'Perfil não encontrado.'
      return
    }

    profile.value = targetUser

    const [postsResp, followersResp, followingResp] = await Promise.all([
      usersService.getPostsByUser(targetUser.id, 9, 1),
      followsService.followers(targetUser.id, 8, 1),
      followsService.following(targetUser.id, 8, 1),
    ])

    postsList.value = (postsResp.data ?? []).map(normalizePost).filter(Boolean)
    postsCount.value = Number(postsResp.total ?? postsList.value.length)

    followers.value = (followersResp.data ?? []).map(normalizeUser).filter(Boolean)
    followersCount.value = Number(followersResp.total ?? followers.value.length)

    following.value = (followingResp.data ?? []).map(normalizeUser).filter(Boolean)
    followingCount.value = Number(followingResp.total ?? following.value.length)

    if (currentUser.value?.id && currentUser.value.id !== targetUser.id) {
      try {
        const result = await followsService.isFollowing(targetUser.id)
        isFollowedByViewer.value = Boolean(result.is_following)
      } catch {
        isFollowedByViewer.value = false
      }
    }
  } catch (error) {
    loadError.value = extractErrorMessage(error, 'Não foi possível carregar o perfil.')
  } finally {
    isLoading.value = false
  }
}

async function handleToggleFollow() {
  if (!profile.value || isOwnProfile.value || followsStore.isPending(profile.value.id)) {
    return
  }

  try {
    if (isFollowedByViewer.value) {
      await followsStore.unfollow(profile.value.id)
      isFollowedByViewer.value = false
      followersCount.value = Math.max(0, followersCount.value - 1)
      feedbackMessage.value = `Você deixou de seguir @${profile.value.username}.`
    } else {
      await followsStore.follow(profile.value.id)
      isFollowedByViewer.value = true
      followersCount.value = followersCount.value + 1
      feedbackMessage.value = `Agora você segue @${profile.value.username}.`
    }
  } catch (error) {
    feedbackMessage.value = extractErrorMessage(
      error,
      'Não foi possível atualizar o relacionamento agora.',
    )
  }
}

watch(selectedUsername, loadProfile, { immediate: true })
watch(
  () => currentUser.value?.id,
  () => {
    if (!selectedUsername.value) {
      loadProfile()
    }
  },
)
</script>

<template>
  <section v-if="isLoading && !profile" class="profile-view__state card border-0">
    <p class="mb-0 text-body-secondary">Carregando perfil...</p>
  </section>

  <section v-else-if="loadError" class="profile-view__state card border-0">
    <h2 class="h4 mb-3">Perfil indisponível</h2>
    <p class="text-body-secondary mb-0">{{ loadError }}</p>
  </section>

  <section v-else-if="profile" class="profile-view">
    <p v-if="feedbackMessage" class="profile-view__feedback" role="status">
      {{ feedbackMessage }}
    </p>

    <ProfileHeader
      :profile="profile"
      :is-own-profile="isOwnProfile"
      :is-following="isFollowedByViewer"
      :follow-pending="followsStore.isPending(profile.id)"
      :posts-count="postsCount"
      :followers-count="followersCount"
      :following-count="followingCount"
      :followers-route="followersRoute"
      :following-route="followingRoute"
      @toggle-follow="handleToggleFollow"
    />

    <ProfileSummaryCards
      :followers="followers"
      :following="following"
      :followers-count="followersCount"
      :following-count="followingCount"
      :followers-route="followersRoute"
      :following-route="followingRoute"
    />

    <nav class="profile-tabs" aria-label="Seções do perfil">
      <span class="profile-tabs__item is-active">
        <AppIcon name="grid" />
        <span>Publicações</span>
      </span>

      <RouterLink class="profile-tabs__item" :to="followersRoute">
        <AppIcon name="profile" />
        <span>Seguidores</span>
      </RouterLink>

      <RouterLink class="profile-tabs__item" :to="followingRoute">
        <AppIcon name="discover" />
        <span>Seguindo</span>
      </RouterLink>
    </nav>

    <ProfilePostGrid v-if="postsList.length > 0" :posts="postsList" />

    <section v-else class="profile-empty card border-0">
      <h3>Nenhum post por aqui ainda</h3>
      <p>
        {{ isOwnProfile
          ? 'Publique algo para preencher sua grade e mostrar atividade no perfil.'
          : 'Quando este usuário publicar, a grade começa a aparecer aqui.' }}
      </p>
      <RouterLink
        v-if="isOwnProfile"
        class="btn btn-primary align-self-start"
        :to="{ name: ROUTE_NAMES.createPost }"
      >
        Criar primeiro post
      </RouterLink>
    </section>
  </section>
</template>

<style scoped>
.profile-view {
  display: grid;
  gap: 1.5rem;
}

.profile-view__state,
.profile-empty {
  padding: 1.5rem;
  border-radius: 1rem;
  background: var(--app-surface);
}

.profile-view__feedback {
  margin: 0;
  padding: 0.85rem 1rem;
  border: 1px solid var(--app-border);
  border-radius: 0.85rem;
  color: var(--app-text);
  font-weight: 600;
  background: var(--app-surface-soft);
}

.profile-tabs {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid var(--app-border);
}

.profile-tabs__item {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding-top: 1rem;
  margin-top: -1rem;
  border-top: 1px solid transparent;
  color: var(--app-muted);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-decoration: none;
  text-transform: uppercase;
}

.profile-tabs__item.is-active {
  border-top-color: var(--app-text);
  color: var(--app-text);
}

.profile-empty {
  display: grid;
  gap: 0.8rem;
}

.profile-empty h3 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
}

.profile-empty p {
  margin: 0;
  color: var(--app-muted);
  line-height: 1.7;
}

@media (max-width: 767.98px) {
  .profile-tabs {
    gap: 0.85rem;
    overflow-x: auto;
    justify-content: flex-start;
  }
}

@media (max-width: 575.98px) {
  .profile-view {
    gap: 1rem;
  }

  .profile-view__state,
  .profile-empty,
  .profile-view__feedback {
    border-radius: 0.85rem;
  }

  .profile-view__state,
  .profile-empty {
    padding: 1rem;
  }

  .profile-tabs {
    gap: 0.5rem;
    padding-top: 0.75rem;
  }

  .profile-tabs__item {
    flex: 0 0 auto;
    gap: 0.35rem;
    padding-top: 0.75rem;
    margin-top: -0.75rem;
    font-size: 0.66rem;
  }

  .profile-empty .btn {
    width: 100%;
  }
}
</style>
