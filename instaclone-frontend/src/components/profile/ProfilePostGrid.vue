<script setup>
import { RouterLink } from 'vue-router'
import { ROUTE_NAMES } from '@/router/routeNames'

defineProps({
  posts: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <section class="profile-grid">
    <RouterLink
      v-for="post in posts"
      :key="post.id"
      :to="{ name: ROUTE_NAMES.postDetails, params: { postId: post.id } }"
      class="profile-grid__item"
    >
      <img :src="post.imageUrl" :alt="post.imageAlt" loading="lazy" />

      <div class="profile-grid__overlay">
        <span>{{ post.likesCount }} curtidas</span>
        <span>{{ post.commentsCount }} comentários</span>
      </div>
    </RouterLink>
  </section>
</template>

<style scoped>
.profile-grid {
  display: grid;
  gap: 0.2rem;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.profile-grid__item {
  position: relative;
  display: block;
  overflow: hidden;
  aspect-ratio: 1 / 1;
  color: inherit;
  text-decoration: none;
  background: var(--app-surface-soft);
}

.profile-grid__item img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-grid__overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.2rem;
  padding: 1rem;
  color: #fff;
  font-size: 0.92rem;
  font-weight: 700;
  text-align: center;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.08) 0%, rgba(0, 0, 0, 0.74) 100%);
  opacity: 0;
  transition: opacity 180ms ease;
}

.profile-grid__item:hover .profile-grid__overlay,
.profile-grid__item:focus-visible .profile-grid__overlay {
  opacity: 1;
}

@media (max-width: 767.98px) {
  .profile-grid__overlay {
    opacity: 1;
    align-items: flex-end;
    justify-content: space-between;
    gap: 0.75rem;
    font-size: 0.78rem;
  }
}

@media (max-width: 575.98px) {
  .profile-grid {
    gap: 0.12rem;
  }

  .profile-grid__overlay {
    align-items: flex-start;
    flex-direction: column;
    justify-content: flex-end;
    gap: 0.1rem;
    padding: 0.45rem;
    font-size: 0.67rem;
    line-height: 1.2;
  }
}
</style>
