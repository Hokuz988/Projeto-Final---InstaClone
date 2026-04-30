<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import AppIcon from '@/components/layout/AppIcon.vue'
import ProfileAvatar from '@/components/profile/ProfileAvatar.vue'
import { useAuthStore } from '@/stores/auth'
import { useFollowsStore } from '@/stores/follows'
import * as usersService from '@/services/users.service'
import { normalizeUser } from '@/stores/profileUtils'
import { ROUTE_NAMES } from '@/router/routeNames'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const followsStore = useFollowsStore()
const { currentUser } = storeToRefs(authStore)

const navItems = [
  { name: ROUTE_NAMES.feed, key: 'feed', label: 'Home', icon: 'home' },
  { name: ROUTE_NAMES.discover, key: 'discover', label: 'Buscar', icon: 'search' },
  { name: ROUTE_NAMES.createPost, key: 'create', label: 'Criar', icon: 'create' },
  { name: ROUTE_NAMES.profile, key: 'profile', label: 'Perfil', icon: 'profile' },
]

const activeNavKey = computed(() => route.meta.navItem ?? '')
const isFeedRoute = computed(() => activeNavKey.value === 'feed')
const contentMode = computed(() => {
  if (isFeedRoute.value) return 'feed'
  if (activeNavKey.value === 'profile') return 'profile'
  return 'default'
})
const accountHandle = computed(() =>
  currentUser.value?.username ? `@${currentUser.value.username}` : '@instaclone',
)
const accountName = computed(() => currentUser.value?.name || 'Sua conta')

const rawSuggestions = ref([])
const loadingSuggestions = ref(false)

const railSuggestions = computed(() =>
  rawSuggestions.value.filter((account) => !followsStore.isFollowing(account.id)).slice(0, 5),
)

function getProfileRoute(username) {
  if (currentUser.value?.username === username) {
    return { name: ROUTE_NAMES.profile }
  }
  return { name: ROUTE_NAMES.userProfile, params: { username } }
}

async function loadSuggestions() {
  if (!currentUser.value?.id || !isFeedRoute.value) {
    rawSuggestions.value = []
    return
  }

  loadingSuggestions.value = true

  try {
    const response = await usersService.suggestions(20, 1)
    rawSuggestions.value = (response.data ?? [])
      .map(normalizeUser)
      .filter((account) => account && account.id !== currentUser.value?.id)
  } catch {
    rawSuggestions.value = []
  } finally {
    loadingSuggestions.value = false
  }
}

async function handleFollowSuggestion(account) {
  if (!account || followsStore.isPending(account.id)) {
    return
  }

  try {
    await followsStore.follow(account.id)
  } catch {
    // ignore — pending flag is reset by the store
  }
}

async function handleLogout() {
  await authStore.logout()
  router.replace({ name: ROUTE_NAMES.login })
}

onMounted(() => {
  if (currentUser.value?.id && !followsStore.hydrated) {
    followsStore.hydrateFor(currentUser.value.id)
  }
})

watch(() => currentUser.value?.id, (id) => {
  if (id) {
    followsStore.hydrateFor(id)
  }
})

watch([() => currentUser.value?.id, isFeedRoute], loadSuggestions, { immediate: true })
</script>

<template>
  <RouterView v-slot="{ Component }">
    <div class="ig-layout" :class="`is-${contentMode}`">
      <aside class="ig-sidebar">
        <RouterLink class="ig-brand" :to="{ name: ROUTE_NAMES.feed }" aria-label="Ir para o feed">
          <span class="ig-brand__glyph">
            <AppIcon name="instagram" />
          </span>
          <span class="ig-brand__wordmark">InstaClone</span>
        </RouterLink>

        <nav class="ig-nav" aria-label="Navegação principal">
          <RouterLink
            v-for="item in navItems"
            :key="item.key"
            :to="{ name: item.name }"
            class="ig-nav__link"
            :class="{ 'is-active': activeNavKey === item.key }"
            :title="item.label"
          >
            <AppIcon :name="item.icon" />
            <span class="ig-nav__label">{{ item.label }}</span>
          </RouterLink>
        </nav>

        <div class="ig-sidebar__footer">
          <RouterLink
            :to="{ name: ROUTE_NAMES.profile }"
            class="ig-sidebar__account"
            title="Abrir seu perfil"
          >
            <ProfileAvatar
              :name="currentUser?.name"
              :username="currentUser?.username"
              :avatar-url="currentUser?.avatarUrl"
              :colors="currentUser?.colors"
              size="sm"
            />
            <span class="ig-nav__label">Perfil</span>
          </RouterLink>

          <button class="ig-sidebar__more" type="button" title="Encerrar sessão" @click="handleLogout">
            <AppIcon name="menu" />
            <span class="ig-nav__label">Sair</span>
          </button>
        </div>
      </aside>

      <div class="ig-content">
        <header v-if="isFeedRoute" class="ig-topbar">
          <RouterLink class="ig-search" :to="{ name: ROUTE_NAMES.discover }">
            <AppIcon name="search" />
            <span>Pesquisar perfis</span>
          </RouterLink>
        </header>

        <main class="ig-main" :class="`ig-main--${contentMode}`">
          <component :is="Component" />
        </main>
      </div>

      <aside v-if="isFeedRoute" class="ig-rail">
        <section class="ig-rail__account">
          <div class="ig-rail__identity">
            <ProfileAvatar
              :name="currentUser?.name"
              :username="currentUser?.username"
              :avatar-url="currentUser?.avatarUrl"
              :colors="currentUser?.colors"
              size="md"
            />

            <div class="ig-rail__copy">
              <strong>{{ accountHandle }}</strong>
              <span>{{ accountName }}</span>
            </div>
          </div>

          <RouterLink class="ig-rail__action" :to="{ name: ROUTE_NAMES.editProfile }">
            Editar
          </RouterLink>
        </section>

        <section class="ig-rail__suggestions">
          <div class="ig-rail__heading">
            <strong>Sugestões para você</strong>
            <RouterLink :to="{ name: ROUTE_NAMES.discover }">Ver tudo</RouterLink>
          </div>

          <ul class="ig-rail__list">
            <li v-if="loadingSuggestions" class="ig-rail__empty">Carregando sugestões...</li>
            <li v-else-if="railSuggestions.length === 0" class="ig-rail__empty">
              Sem novas contas para sugerir agora.
            </li>
            <li v-for="account in railSuggestions" :key="account.id" class="ig-rail__list-item">
              <RouterLink :to="getProfileRoute(account.username)" class="ig-rail__item">
                <ProfileAvatar
                  :name="account.name"
                  :username="account.username"
                  :avatar-url="account.avatarUrl"
                  :colors="account.colors"
                  size="sm"
                />

                <span>
                  <strong>{{ account.username }}</strong>
                  <small>{{ account.name }}</small>
                </span>
              </RouterLink>

              <button
                class="ig-rail__follow"
                type="button"
                :disabled="followsStore.isPending(account.id)"
                @click="handleFollowSuggestion(account)"
              >
                {{ followsStore.isPending(account.id) ? '...' : 'Seguir' }}
              </button>
            </li>
          </ul>
        </section>

        <p class="ig-rail__meta">
          Sobre · Ajuda · API · Privacidade · Termos · Localizações
        </p>
      </aside>
    </div>
  </RouterView>
</template>
