<script setup>
import { RouterLink } from 'vue-router'
import AppIcon from '@/components/layout/AppIcon.vue'
import ProfileAvatar from '@/components/profile/ProfileAvatar.vue'
import { ROUTE_NAMES } from '@/router/routeNames'

defineProps({
  profile: {
    type: Object,
    required: true,
  },
  isOwnProfile: {
    type: Boolean,
    default: false,
  },
  isFollowing: {
    type: Boolean,
    default: false,
  },
  followPending: {
    type: Boolean,
    default: false,
  },
  postsCount: {
    type: Number,
    default: 0,
  },
  followersCount: {
    type: Number,
    default: 0,
  },
  followingCount: {
    type: Number,
    default: 0,
  },
  followersRoute: {
    type: Object,
    required: true,
  },
  followingRoute: {
    type: Object,
    required: true,
  },
})

defineEmits(['toggle-follow'])
</script>

<template>
  <section class="profile-header">
    <div class="profile-header__avatar">
      <ProfileAvatar
        :name="profile.name"
        :username="profile.username"
        :avatar-url="profile.avatarUrl"
        :colors="profile.colors"
        size="xl"
      />
    </div>

    <div class="profile-header__content">
      <div class="profile-header__identity">
        <div class="profile-header__title-row">
          <h1>{{ profile.username }}</h1>

          <RouterLink
            v-if="isOwnProfile"
            class="profile-header__settings"
            :to="{ name: ROUTE_NAMES.editProfile }"
            aria-label="Editar configurações do perfil"
          >
            <AppIcon name="settings" />
          </RouterLink>
        </div>

        <div class="profile-header__actions">
          <RouterLink
            v-if="isOwnProfile"
            class="btn btn-outline-secondary"
            :to="{ name: ROUTE_NAMES.editProfile }"
          >
            Editar perfil
          </RouterLink>

          <button
            v-else
            class="btn btn-outline-secondary"
            type="button"
            :disabled="followPending"
            @click="$emit('toggle-follow')"
          >
            {{ isFollowing ? 'Deixar de seguir' : 'Seguir perfil' }}
          </button>

          <RouterLink class="btn btn-outline-secondary" :to="followersRoute">
            {{ isOwnProfile ? 'Ver conexões' : 'Ver seguidores' }}
          </RouterLink>
        </div>
      </div>

      <div class="profile-header__stats">
        <article>
          <strong>{{ postsCount }}</strong>
          <span>publicações</span>
        </article>

        <RouterLink class="profile-header__stat-link" :to="followersRoute">
          <strong>{{ followersCount }}</strong>
          <span>seguidores</span>
        </RouterLink>

        <RouterLink class="profile-header__stat-link" :to="followingRoute">
          <strong>{{ followingCount }}</strong>
          <span>seguindo</span>
        </RouterLink>
      </div>

      <div class="profile-header__bio">
        <strong>{{ profile.name }}</strong>
        <p v-if="profile.bio">{{ profile.bio }}</p>
        <p v-else class="profile-header__bio-muted">
          {{ isOwnProfile
            ? 'Adicione uma bio para completar seu perfil.'
            : 'Este perfil ainda não escreveu uma bio.' }}
        </p>
      </div>
    </div>
  </section>
</template>

<style scoped>
.profile-header {
  display: grid;
  gap: 1.5rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--app-border);
}

.profile-header__avatar {
  display: flex;
  justify-content: center;
}

.profile-header__content,
.profile-header__identity,
.profile-header__bio {
  display: grid;
  gap: 0.45rem;
  min-width: 0;
}

.profile-header__title-row,
.profile-header__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.profile-header__title-row h1 {
  margin: 0;
  color: var(--app-text);
  font-size: clamp(1.7rem, 4vw, 2.1rem);
  font-weight: 600;
}

.profile-header__settings {
  display: grid;
  place-items: center;
  width: 2.4rem;
  height: 2.4rem;
  border: 1px solid var(--app-border);
  border-radius: 999px;
  color: var(--app-text);
  background: var(--app-surface-soft);
  text-decoration: none;
}

.profile-header__stats {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.profile-header__stats article,
.profile-header__stat-link {
  display: grid;
  gap: 0.2rem;
  color: inherit;
  text-decoration: none;
}

.profile-header__stats strong {
  color: var(--app-text);
  font-size: 1.12rem;
}

.profile-header__stats span,
.profile-header__bio p,
.profile-header__bio-muted {
  color: var(--app-muted);
  line-height: 1.7;
  overflow-wrap: anywhere;
}

.profile-header__bio strong {
  color: var(--app-text);
  font-size: 0.98rem;
}

@media (min-width: 768px) {
  .profile-header {
    grid-template-columns: minmax(10rem, 0.8fr) minmax(0, 1.2fr);
    align-items: start;
  }
}

@media (max-width: 575.98px) {
  .profile-header {
    gap: 1rem;
    padding-bottom: 1.25rem;
  }

  .profile-header__title-row,
  .profile-header__actions {
    align-items: stretch;
  }

  .profile-header__actions > * {
    width: 100%;
  }

  .profile-header__stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
  }

  .profile-header__stats article,
  .profile-header__stat-link {
    padding: 0.75rem;
    border: 1px solid var(--app-border);
    border-radius: 0.75rem;
    background: var(--app-surface-soft);
  }
}
</style>
