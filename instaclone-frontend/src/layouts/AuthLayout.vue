<template>
  <div class="auth-shell">
    <div class="auth-shell__panel card border-0">
      <div class="auth-shell__brand">
        <span class="auth-shell__wordmark">InstaClone</span>
        <p>Acesse sua conta ou crie um novo perfil.</p>
      </div>

      <RouterView />
    </div>
  </div>
</template>

<style scoped>
.auth-shell {
  width: 100%;
  min-height: 100dvh;
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  place-items: center;
  padding: clamp(0.85rem, 4vw, 1.5rem);
  background:
    radial-gradient(circle at top, rgba(0, 149, 246, 0.12), transparent 32%),
    var(--app-bg);
}

.auth-shell__panel {
  width: min(28rem, calc(100vw - 1.7rem));
  min-width: 0;
  padding: clamp(1.15rem, 5vw, 2rem);
  border-radius: clamp(0.9rem, 4vw, 1.35rem);
  background: var(--app-surface);
  overflow: hidden;
}

.auth-shell__panel > * {
  min-width: 0;
}

.auth-shell__brand {
  display: grid;
  gap: 0.35rem;
  min-width: 0;
  margin-bottom: 1.5rem;
  text-align: center;
}

.auth-shell__wordmark {
  display: block;
  width: 100%;
  font-family: 'Grand Hotel', cursive;
  font-size: clamp(2rem, 12vw, 3rem);
  line-height: 0.95;
  overflow-wrap: anywhere;
  text-align: center;
}

.auth-shell__brand p {
  margin: 0;
  color: var(--app-muted);
  line-height: 1.6;
}

@media (max-width: 575.98px) {
  .auth-shell {
    padding: 0.75rem;
  }

  .auth-shell__panel {
    width: calc(100vw - 1.5rem);
    padding: 1.1rem;
  }

  .auth-shell__brand {
    margin-bottom: 1.2rem;
  }

  .auth-shell__wordmark {
    font-size: 2rem;
  }
}
</style>
