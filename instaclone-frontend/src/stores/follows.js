import { defineStore } from 'pinia'
import * as followsService from '@/services/follows.service'

export const useFollowsStore = defineStore('follows', {
  state: () => ({
    followingIds: new Set(),
    pendingIds: new Set(),
    hydrated: false,
  }),
  getters: {
    isFollowing: (state) => (userId) => state.followingIds.has(userId),
    isPending: (state) => (userId) => state.pendingIds.has(userId),
  },
  actions: {
    async hydrateFor(viewerId) {
      if (!viewerId) {
        this.followingIds = new Set()
        this.hydrated = false
        return
      }

      try {
        const response = await followsService.following(viewerId, 50, 1)
        const ids = (response.data ?? []).map((user) => user.id)
        this.followingIds = new Set(ids)
        this.hydrated = true
      } catch {
        this.followingIds = new Set()
        this.hydrated = false
      }
    },

    markFollowing(userId, value) {
      const next = new Set(this.followingIds)
      if (value) {
        next.add(userId)
      } else {
        next.delete(userId)
      }
      this.followingIds = next
    },

    markPending(userId, value) {
      const next = new Set(this.pendingIds)
      if (value) {
        next.add(userId)
      } else {
        next.delete(userId)
      }
      this.pendingIds = next
    },

    async follow(userId) {
      if (this.pendingIds.has(userId)) {
        return null
      }
      this.markPending(userId, true)
      try {
        await followsService.follow(userId)
        this.markFollowing(userId, true)
        return true
      } finally {
        this.markPending(userId, false)
      }
    },

    async unfollow(userId) {
      if (this.pendingIds.has(userId)) {
        return null
      }
      this.markPending(userId, true)
      try {
        await followsService.unfollow(userId)
        this.markFollowing(userId, false)
        return true
      } finally {
        this.markPending(userId, false)
      }
    },

    async toggle(userId) {
      return this.followingIds.has(userId) ? this.unfollow(userId) : this.follow(userId)
    },
  },
})
