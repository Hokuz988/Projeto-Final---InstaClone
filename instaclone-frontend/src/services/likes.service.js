import { api } from '@/services/api'

export async function like(postId) {
  const { data } = await api.post(`/posts/${postId}/like`)
  return data
}

export async function unlike(postId) {
  const { data } = await api.delete(`/posts/${postId}/unlike`)
  return data
}
