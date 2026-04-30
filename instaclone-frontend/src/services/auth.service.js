import { api } from '@/services/api'

function isPlainObject(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value)
}

function createApiContractError(message) {
  const error = new Error(message)
  error.name = 'ApiContractError'
  return error
}

function assertAuthSessionPayload(payload, endpoint) {
  if (!isPlainObject(payload)) {
    throw createApiContractError(
      `Resposta inválida da API em ${endpoint}. Retorne JSON com access_token e user.`,
    )
  }

  if (typeof payload.access_token !== 'string' || payload.access_token.trim().length === 0) {
    throw createApiContractError(
      `Resposta inválida da API em ${endpoint}. O campo access_token é obrigatório.`,
    )
  }

  if (!isPlainObject(payload.user)) {
    throw createApiContractError(
      `Resposta inválida da API em ${endpoint}. O campo user deve ser um objeto.`,
    )
  }

  if (payload.user.id === undefined || payload.user.id === null) {
    throw createApiContractError(
      `Resposta inválida da API em ${endpoint}. O campo user.id é obrigatório.`,
    )
  }

  if (typeof payload.user.username !== 'string' || payload.user.username.trim().length === 0) {
    throw createApiContractError(
      `Resposta inválida da API em ${endpoint}. O campo user.username é obrigatório.`,
    )
  }

  return {
    ...payload,
    access_token: payload.access_token.trim(),
    user: {
      ...payload.user,
      username: payload.user.username.trim(),
    },
  }
}

function handleAuthEndpointError(error, endpoint) {
  const status = error?.response?.status

  if (status === 404 || status === 405) {
    throw createApiContractError(
      `Endpoint de autenticação incorreto. O frontend chama ${endpoint}; conferir rota e método no backend.`,
    )
  }

  throw error
}

export async function login(credentials) {
  try {
    const { data } = await api.post('/auth/login', credentials)
    return assertAuthSessionPayload(data, 'POST /auth/login')
  } catch (error) {
    handleAuthEndpointError(error, 'POST /auth/login')
  }
}

export async function register(payload) {
  try {
    const { data } = await api.post('/auth/register', payload)
    return assertAuthSessionPayload(data, 'POST /auth/register')
  } catch (error) {
    handleAuthEndpointError(error, 'POST /auth/register')
  }
}

export async function logout() {
  const { data } = await api.post('/auth/logout')
  return data
}

export async function me() {
  const { data } = await api.get('/auth/me')
  return data
}
