<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/components/layout/AppIcon.vue'
import ProfileAvatar from '@/components/profile/ProfileAvatar.vue'

const props = defineProps({
  followers: {
    type: Array,
    required: true,
  },
  following: {
    type: Array,
    required: true,
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

const followersPreview = computed(() => props.followers.slice(0, 4))
const followingPreview = computed(() => props.following.slice(0, 4))
</script>

<template>
  <section class="profile-summary">
    <RouterLink class="profile-summary__card" :to="followersRoute">
      <div class="profile-summary__avatars">
        <ProfileAvatar
          v-for="account in followersPreview"
          :key="`followers-${account.id}`"
          :name="account.name"
          :username="account.username"
          :avatar-url="account.avatarUrl"
          :colors="account.colors"
          size="sm"
          class="profile-summary__avatar"
        />
        <span v-if="followersPreview.length === 0" class="profile-summary__placeholder">
          <AppIcon name="profile" />
        </span>
      </div>

      <div class="profile-summary__copy">
        <span>Seguidores</span>
        <strong>{{ followersCount }} pessoas acompanham este perfil</strong>
      </div>
    </RouterLink>

    <RouterLink class="profile-summary__card" :to="followingRoute">
      <div class="profile-summary__avatars">
        <ProfileAvatar
          v-for="account in followingPreview"
          :key="`following-${account.id}`"
          :name="account.name"
          :username="account.username"
          :avatar-url="account.avatarUrl"
          :colors="account.colors"
          size="sm"
          class="profile-summary__avatar"
        />
        <span v-if="followingPreview.length === 0" class="profile-summary__placeholder">
          <AppIcon name="discover" />
        </span>
      </div>

      <div class="profile-summary__copy">
        <span>Seguindo</span>
        <strong>{{ followingCount }} contas no radar</strong>
      </div>
    </RouterLink>
  </section>
</template>

<style scoped>
.profile-summary {
  display: grid;
  gap: 0.85rem;
}

.profile-summary__card {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  min-width: 0;
  padding: 1rem;
  border: 1px solid var(--app-border);
  border-radius: 1rem;
  color: inherit;
  text-decoration: none;
  background: var(--app-surface);
}

.profile-summary__avatars {
  display: flex;
  align-items: center;
  min-width: 7rem;
}

.profile-summary__avatar + .profile-summary__avatar {
  margin-left: -0.55rem;
}

.profile-summary__placeholder {
  display: grid;
  place-items: center;
  width: 2.2rem;
  height: 2.2rem;
  border: 1px solid var(--app-border);
  border-radius: 50%;
  color: var(--app-muted);
  background: var(--app-surface-soft);
}

.profile-summary__copy {
  display: grid;
  gap: 0.45rem;
  min-width: 0;
}

.profile-summary__copy strong {
  color: var(--app-text);
  font-size: 0.95rem;
  overflow-wrap: anywhere;
}

.profile-summary__copy span {
  color: var(--app-muted);
  line-height: 1.7;
}

@media (min-width: 768px) {
  .profile-summary {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 575.98px) {
  .profile-summary__card {
    align-items: flex-start;
    flex-direction: column;
    border-radius: 0.85rem;
  }

  .profile-summary__avatars {
    min-width: 0;
  }
}
</style>
