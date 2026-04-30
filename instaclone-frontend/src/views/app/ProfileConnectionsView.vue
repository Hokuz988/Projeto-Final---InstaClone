<script setup>
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterLink, useRoute } from 'vue-router'
import AccountCard from '@/components/profile/AccountCard.vue'
import { useAuthStore } from '@/stores/auth'
import { useFollowsStore } from '@/stores/follows'
import * as usersService from '@/services/users.service'
import * as followsService from '@/services/follows.service'
import { extractErrorMessage } from '@/services/api'
import { normalizeUser } from '@/stores/profileUtils'
import { CONNECTION_LIST_TYPES, ROUTE_NAMES, isConnectionListType } from '@/router/routeNames'

const route = useRoute()
const { currentUser } = storeToRefs(useAuthStore())
const followsStore = useFollowsStore()

const feedbackMessage = ref('')
const loadError = ref('')
const isLoading = ref(false)

const profile = ref(null)
const people = ref([])
const totalPeople = ref(0)
const currentPage = ref(1)
const hasMore = ref(false)

const selectedUsername = computed(() =>
  typeof route.params.username === 'string' ? route.params.username.trim().toLowerCase() : '',
)

const listType = computed(() => (isConnectionListType(route.params.type) ? route.params.type : ''))

const isOwnProfile = computed(
  () => Boolean(profile.value && currentUser.value && profile.value.id === currentUser.value.id),
)

const listTitle = computed(() =>
  listType.value === CONNECTION_LIST_TYPES.followers ? 'Seguidores' : 'Seguindo',
)
const listHeading = computed(() => {
  const isFollowers = listType.value === CONNECTION_LIST_TYPES.followers
  if (isOwnProfile.value) {
    return isFollowers ? 'Seus seguidores' : 'Você está seguindo'
  }
  if (!profile.value) {
    return listTitle.value
  }
  return isFollowers
    ? `Seguidores de @${profile.value.username}`
    : `@${profile.value.username} está seguindo`
})
const listDescription = computed(() => {
  if (!profile.value) {
    return ''
  }
  return listType.value === CONNECTION_LIST_TYPES.followers
    ? `Pessoas que acompanham @${profile.value.username}.`
    : `Perfis acompanhados por @${profile.value.username} no momento.`
})

const backRoute = computed(() => {
  if (!profile.value || isOwnProfile.value) {
    return { name: ROUTE_NAMES.profile }
  }
  return { name: ROUTE_NAMES.userProfile, params: { username: profile.value.username } }
})

async function loadProfile() {
  loadError.value = ''
  feedbackMessage.value = ''
  profile.value = null
  people.value = []
  totalPeople.value = 0
  currentPage.value = 1
  hasMore.value = false

  try {
    const targetUsername = selectedUsername.value || currentUser.value?.username
    if (!targetUsername) {
      loadError.value = 'Perfil não encontrado.'
      return
    }
    const raw = await usersService.getByUsername(targetUsername)
    profile.value = normalizeUser(raw)
  } catch (error) {
    loadError.value = extractErrorMessage(error, 'Não foi possível carregar o perfil.')
  }
}

async function loadPeople({ reset = true } = {}) {
  if (!profile.value || !listType.value) {
    return
  }

  isLoading.value = true

  try {
    const page = reset ? 1 : currentPage.value + 1
    const service =
      listType.value === CONNECTION_LIST_TYPES.followers
        ? followsService.followers
        : followsService.following
    const response = await service(profile.value.id, 20, page)
    const users = (response.data ?? []).map(normalizeUser).filter(Boolean)

    people.value = reset ? users : [...people.value, ...users]
    totalPeople.value = Number(response.total ?? people.value.length)
    currentPage.value = Number(response.current_page ?? page)
    hasMore.value = Boolean(response.next_page_url)
  } catch (error) {
    feedbackMessage.value = extractErrorMessage(error, 'Não foi possível carregar a lista.')
  } finally {
    isLoading.value = false
  }
}

function handleFollowChanged({ account, wasFollowing }) {
  feedbackMessage.value = wasFollowing
    ? `Você deixou de seguir @${account.username}.`
    : `Agora você segue @${account.username}.`
}

watch(
  [selectedUsername, () => currentUser.value?.id],
  async () => {
    await loadProfile()
    await Promise.all([
      followsStore.hydrateFor(currentUser.value?.id),
      loadPeople({ reset: true }),
    ])
  },
  { immediate: true },
)

watch(listType, () => {
  loadPeople({ reset: true })
})
</script>

<template>
  <section v-if="profile && listType" class="profile-list">
    <section class="profile-list__hero card border-0">
      <div>
        <span class="profile-list__eyebrow">Rede</span>
        <h2>{{ listHeading }}</h2>
        <p>{{ listDescription }}</p>
      </div>

      <RouterLink class="btn btn-outline-secondary" :to="backRoute">Voltar ao perfil</RouterLink>
    </section>

    <p v-if="feedbackMessage" class="profile-list__feedback" role="status">
      {{ feedbackMessage }}
    </p>

    <section v-if="people.length > 0" class="profile-list__grid">
      <AccountCard
        v-for="account in people"
        :key="account.id"
        :account="account"
        @follow-changed="handleFollowChanged"
      />

      <div v-if="hasMore" class="profile-list__more">
        <button
          class="btn btn-outline-secondary"
          type="button"
          :disabled="isLoading"
          @click="loadPeople({ reset: false })"
        >
          {{ isLoading ? 'Carregando...' : 'Carregar mais' }}
        </button>
      </div>
    </section>

    <section v-else-if="!isLoading" class="profile-list__empty card border-0">
      <h3>Nenhum perfil nesta lista</h3>
      <p>
        {{ listType === CONNECTION_LIST_TYPES.followers
          ? 'Assim que alguém acompanhar esse perfil, a lista aparece aqui.'
          : 'Quando esse perfil seguir alguém, a relação passa a aparecer aqui.' }}
      </p>
    </section>

    <section v-else class="profile-list__empty card border-0">
      <p class="mb-0">Carregando lista...</p>
    </section>
  </section>

  <section v-else-if="loadError" class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h2 class="h4 mb-3">Lista indisponível</h2>
      <p class="text-body-secondary mb-3">{{ loadError }}</p>
      <RouterLink class="btn btn-outline-secondary" :to="{ name: ROUTE_NAMES.profile }">
        Voltar ao perfil
      </RouterLink>
    </div>
  </section>

  <section v-else class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <p class="text-body-secondary mb-0">Carregando...</p>
    </div>
  </section>
</template>

<style scoped>
.profile-list {
  display: grid;
  gap: 1rem;
}

.profile-list__hero,
.profile-list__empty {
  padding: 1.4rem;
  border-radius: 1.75rem;
  background: var(--app-surface);
}

.profile-list__hero {
  display: grid;
  gap: 1rem;
}

.profile-list__eyebrow {
  display: inline-block;
  margin-bottom: 0.35rem;
  color: var(--app-accent-strong);
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.profile-list__hero h2,
.profile-list__empty h3 {
  margin: 0 0 0.35rem;
  font-size: clamp(1.6rem, 4vw, 2.3rem);
  font-weight: 800;
}

.profile-list__hero p,
.profile-list__empty p {
  margin: 0;
  color: var(--app-muted);
  line-height: 1.65;
}

.profile-list__feedback {
  margin: 0;
  padding: 0.95rem 1rem;
  border: 1px solid var(--app-border);
  border-radius: 1rem;
  color: var(--app-text);
  font-weight: 600;
  background: var(--app-surface-soft);
}

.profile-list__grid {
  display: grid;
  gap: 1rem;
}

.profile-list__more {
  display: flex;
  justify-content: center;
  padding: 0.5rem 0;
}

@media (min-width: 768px) {
  .profile-list__hero {
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
  }
}

@media (max-width: 575.98px) {
  .profile-list {
    gap: 0.85rem;
  }

  .profile-list__hero,
  .profile-list__empty,
  .profile-list__feedback {
    border-radius: 0.85rem;
  }

  .profile-list__hero,
  .profile-list__empty {
    padding: 1rem;
  }

  .profile-list__hero .btn,
  .profile-list__more .btn {
    width: 100%;
  }
}
</style>
