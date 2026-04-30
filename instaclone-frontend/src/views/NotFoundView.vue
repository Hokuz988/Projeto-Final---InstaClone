<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { ROUTE_NAMES } from '@/router/routeNames'

const { isAuthenticated } = storeToRefs(useAuthStore())

const fallbackRoute = computed(() => ({
  name: isAuthenticated.value ? ROUTE_NAMES.feed : ROUTE_NAMES.login,
}))
const fallbackLabel = computed(() =>
  isAuthenticated.value ? 'Voltar para o feed' : 'Ir para o login',
)
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center bg-body-tertiary py-4">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <section class="card border-0 shadow-sm text-center">
            <div class="card-body p-4 p-md-5">
              <h2 class="h4 mb-3">Página não encontrada</h2>
              <p class="text-body-secondary mb-4">A rota acessada não existe no InstaClone.</p>
              <RouterLink class="btn btn-primary" :to="fallbackRoute">
                {{ fallbackLabel }}
              </RouterLink>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>
