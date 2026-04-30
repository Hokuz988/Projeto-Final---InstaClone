# InstaClone (frontend) - Especificacao de build

Lista de tarefas para reconstruir o frontend. Cada item descreve comportamento esperado, endpoints consumidos e restrições de UI. Os nomes de rota, endpoints, stores compartilhados e limites são contratos com o backend e devem ser respeitados.

## 1 - Setup do Projeto

- [ ] Inicializar projeto com vue@latest
- [ ] Estrutura de pastas por dominio: `components/`, `composables/`, `layouts/`, `router/`, `services/`, `stores/`, `utils/`, `views/`
- [ ] Vue Router com rota curinga `/:pathMatch(.*)*` servindo uma view `NotFound`
- [ ] Todas as views devem ser lazy-loaded com `component: () => import(...)`
- [ ] Constantes de nomes de rota e tipos de lista em `src/router/routeNames.js`
- [ ] Pinia para estado compartilhado (`auth`, `feed`, `follows`)
- [ ] Tema global em `src/assets/styles/theme.css` (importado no `main.js` apos Bootstrap)
- [ ] Google Fonts carregado por `<link rel="preconnect">` + stylesheet em `index.html`
- [ ] Cliente axios centralizado em `src/services/api.js`:
  - baseURL lida de `import.meta.env.VITE_API_URL`, com fallback para `http://localhost:8000/api`
  - interceptor de request injeta `Authorization: Bearer <token>` quando existe token
  - interceptor de response trata `401` chamando o handler de unauthorized (que limpa a sessão); o redirect para `/login` é responsabilidade do guard `requiresAuth` na próxima navegação
- [ ] `.env.example` com `VITE_API_URL=http://localhost:8000/api`
- [ ] `.dockerignore` excluindo `node_modules`, `dist`, `.env`, mantendo `.env.example`

## 2 - Autenticação

- [ ] Tela `/login` com campos `email` e `password`
- [ ] Tela `/register` com campos `name`, `username`, `email`, `password`, `password_confirmation`
- [ ] Store `auth` (Pinia) com estado `user`, `token`, `isAuthenticated`, actions `login`, `register`, `logout`, `fetchMe`/`hydrateAuthState`
- [ ] `POST /auth/login` devolve `access_token` e `user`; salvar token em `localStorage` com chave fixa (ex.: `instaclone.token`)
- [ ] `POST /auth/register` cria conta e já autentica
- [ ] `POST /auth/logout` limpa sessão local mesmo se o token já estiver inválido
- [ ] `GET /auth/me` hidrata o usuario atual a partir do token salvo ao entrar em rota protegida
- [ ] Servico de autenticacão deve expor apenas endpoints usados pela UI
- [ ] Guards de rota:
  - `requiresAuth`: sem token, redireciona para `/login`
  - `requiresGuest`: com token, redireciona para `/feed`
- [ ] Mensagens de erro exibidas inline abaixo do formulario
- [ ] Código browser-only pode acessar APIs do browser sem guards de SSR desnecessarios

## 3 - Layout, Navegacao e Icones

- [ ] `AuthLayout` para telas de visitante (`/login`, `/register`)
- [ ] `AppLayout` para area autenticada
- [ ] Layout autenticado concentrado em `src/layouts/AppLayout.vue`
- [ ] Shell autenticado responsivo:
  - mobile: barra de navegacao inferior fixa
  - desktop: sidebar lateral
- [ ] Entradas de navegacao: `Home` (`/feed`), `Buscar` (`/discover`), `Criar` (`/create`), `Perfil` (`/profile`)
- [ ] Todas as navegacoes por nome devem usar `ROUTE_NAMES`, nao strings literais em `name: '...'`
- [ ] `RouterView` usando `v-slot="{ Component }"` + `<component :is="Component" />` para troca de views
- [ ] `AppIcon` deve aceitar prop `filled` para desenhar corretamente icones preenchidos, incluindo coracao curtido

## 4 - Feed (`/feed`)

- [ ] `GET /feed` retorna `{ data: [...], next_cursor: string|null }`
- [ ] Store `feed` mantem uma lista ordenada de posts normalizados em `feedPosts`, com `feedCursor` e `feedHasNext` controlando a paginacao
- [ ] Actions do store: `fetchFeed({ reset })`, `loadMoreFeed()`, `toggleLike(post)`, `addComment(postId, body)`, `createPost({ image, caption })`, `deletePost(postId)`, `applyPostPatch(postId, patch)`
- [ ] `normalizeComment` deve viver em `stores/feed.js`
- [ ] `defaultAuthor()` deve viver em `stores/profileUtils.js` para evitar duplicar o autor fallback em post/comentario
- [ ] Botao "carregar mais" visivel enquanto `next_cursor !== null`
- [ ] Card de post exibe:
  - avatar + username do autor (link para `/profile?user=<username>`)
  - imagem do post
  - contador de curtidas e botao de like
  - legenda
  - data relativa (ex.: "ha 2h")
  - contador de comentarios
  - campo inline para adicionar comentario
- [ ] Curtir: `POST /posts/:id/like`. Descurtir: `DELETE /posts/:id/like`. Atualizar contador de forma otimista
- [ ] Comentar inline: `POST /posts/:id/comments` com `{ body }`

## 5 - Descobrir (`/discover`)

- [ ] `GET /users/suggestions` lista perfis sugeridos
- [ ] `GET /users/:viewerId/following` carrega quem o viewer ja segue para marcar o estado do botao
- [ ] Estado de follow deve vir do store `follows`, nao de Sets locais na view
- [ ] Store `follows` deve manter `followingIds` e `pendingIds` como `Set`, sempre trocando por um novo `Set` a cada mutacao para preservar reatividade
- [ ] Botao "Seguir" / "Seguindo" por card:
  - seguir: `POST /users/:id/follow`
  - deixar de seguir: `DELETE /users/:id/follow`
- [ ] Cards de usuarios devem usar `components/profile/AccountCard.vue`
- [ ] Clique no card abre `/users/:username` (rota nomeada `userProfile`) ou `/profile` se for o proprio
- [ ] Paginacao por pagina (`?page=<n>`)
- [ ] Campo de busca com debounce (~300 ms) que troca a listagem para `GET /users/search?q=<termo>`; lista limpa volta a mostrar sugestoes

## 6 - Criar Post (`/create`)

- [ ] Input `<input type="file">` aceita apenas `image/jpeg`, `image/jpg`, `image/png`, `image/webp`
- [ ] Limite de 5 MB (validar no cliente antes do upload)
- [ ] Preview local com `URL.createObjectURL`; revogar o blob ao trocar imagem, limpar ou desmontar
- [ ] Logica de file picker, preview blob e cleanup deve vir de `composables/useImageUpload.js`
- [ ] Campo de legenda com limite de `2200` caracteres e contador visivel
- [ ] Botao de publicar desabilitado enquanto imagem ou legenda estao ausentes
- [ ] `POST /posts` com `FormData` contendo `image` e `caption`
- [ ] Exibir feedback de sucesso (redirecionar para `/feed`) e mensagens de erro

## 7 - Perfil (`/profile` e `/users/:username`)

- [ ] `GET /users/{username}` resolve o perfil alvo
- [ ] Em paralelo, carregar:
  - `GET /users/{id}/posts` (grid de posts)
  - `GET /users/{id}/followers` (contador)
  - `GET /users/{id}/following` (contador)
- [ ] Para perfis de terceiros, consultar `GET /users/{id}/is-following` para decidir o estado inicial do botao
- [ ] Acoes de seguir/deixar de seguir devem usar o store `follows`
- [ ] Acoes:
  - seguir: `POST /users/:id/follow`
  - deixar de seguir: `DELETE /users/:id/follow`
- [ ] Botao "Editar perfil" aparece apenas no proprio perfil e leva a `/profile/edit`
- [ ] Contadores de seguidores e seguindo levam a `/profile/list/followers` e `/profile/list/following` para o proprio perfil, e a `/users/:username/list/(followers|following)` (rota nomeada `userConnections`) para perfis de terceiros
- [ ] Grid de posts: clicar em um post abre `/posts/:postId`
- [ ] `ProfileView` deve delegar UI para `ProfileHeader`, `ProfileSummaryCards` e `ProfilePostGrid`

## 7.1 - Editar Perfil (`/profile/edit`)

- [ ] `PUT /users/me` com `{ name, username, bio }`
- [ ] `POST /users/me/avatar` com `FormData` contendo `avatar`
- [ ] Limites validados no cliente:
  - `name`: 255 caracteres
  - `username`: 30 caracteres, regex `^[A-Za-z0-9._]+$`
  - `bio`: 500 caracteres
  - avatar: 2 MB
- [ ] Constantes `PROFILE_*_MAX_LENGTH` devem viver em `stores/profileUtils.js`
- [ ] Logica de upload/preview/cleanup do avatar deve usar `composables/useImageUpload.js`
- [ ] Mensagens de erro por campo vindas do backend

## 8 - Listas de Conexao (`/profile/list/:type` e `/users/:username/list/:type`)

- [ ] `:type` aceita `followers` ou `following`
- [ ] Tipos validos devem vir de `CONNECTION_LIST_TYPES`
- [ ] Rota `userConnections` (`/users/:username/list/:type`) lista conexoes de outro perfil
- [ ] `GET /users/{id}/followers` e `GET /users/{id}/following` com paginacao por pagina
- [ ] Cada linha mostra avatar, nome, username e botao de seguir / deixar de seguir
- [ ] Cada conta deve ser renderizada por `components/profile/AccountCard.vue`
- [ ] Estado de follow/pending deve vir do store `follows`
- [ ] Botao de voltar para o perfil de origem

## 9 - Detalhes do Post (`/posts/:postId`)

- [ ] `GET /posts/:id` carrega o post
- [ ] `GET /posts/:id/comments` carrega comentarios com paginacao por pagina
- [ ] Comentarios devem usar componentes dedicados:
  - `PostCommentList`
  - `PostCommentItem`
  - `PostCommentForm`
- [ ] Botao "carregar mais" para comentarios
- [ ] Campo para adicionar comentario: `POST /posts/:id/comments`
- [ ] `DELETE /comments/:id` disponivel apenas para o autor do comentario
- [ ] `DELETE /posts/:id` disponivel apenas para o autor do post; redireciona para `/feed` apos sucesso
- [ ] Contadores de curtidas e comentarios vem do payload do backend

## 10 - Datas e Performance

- [ ] Formatadores de data devem viver em `utils/dates.js`
- [ ] Instancias de `Intl.DateTimeFormat` devem ser hoistadas para escopo de modulo:
  - `formatDayMonthYear`
  - `formatRelative`
  - `formatFullDateTime`
  - `formatShortDateTime`
- [ ] Build de producao deve manter views em chunks separados por causa das rotas lazy-loaded
- [ ] Build de producao deve gerar chunks JavaScript separados em `dist/assets` por causa das rotas lazy-loaded

## 11 - 404

- [ ] View `NotFound` com link para `/feed` (autenticado) ou `/login` (visitante)

## 12 - Docker e Entrega

- [ ] `Dockerfile` multi-stage:
  - stage `build`: `node:22-alpine`, roda `npm ci` e `npm run build`
  - stage runtime: `nginx:1.27-alpine` servindo `/usr/share/nginx/html`
- [ ] `docker/nginx.conf` com `try_files $uri $uri/ /index.html` para o history mode do Vue Router
- [ ] `compose.yaml` expondo `3000:80` e passando `VITE_API_URL` como build-arg
- [ ] `.dockerignore` excluindo `node_modules`, `dist`, `.env`, preservando `.env.example`
- [ ] `npm run build` gera bundle de producao em `dist/`

## Fora de escopo deliberado

Estes itens nao fazem parte desta etapa porque mudam o formato do projeto ou exigem uma auditoria propria:

- TypeScript
- Otimizacao fina de importacao do Bootstrap
- Modal customizado para confirmacoes destrutivas

## Endpoints consumidos (resumo)

| Recurso | Metodo | Rota |
| --- | --- | --- |
| Auth | POST | `/auth/login` |
| Auth | POST | `/auth/register` |
| Auth | POST | `/auth/logout` |
| Auth | GET | `/auth/me` |
| Feed | GET | `/feed?cursor=<c>` |
| Posts | POST | `/posts` (multipart) |
| Posts | GET | `/posts/:id` |
| Posts | DELETE | `/posts/:id` |
| Likes | POST | `/posts/:id/like` |
| Likes | DELETE | `/posts/:id/like` |
| Comments | GET | `/posts/:id/comments` |
| Comments | POST | `/posts/:id/comments` |
| Comments | DELETE | `/comments/:id` |
| Users | GET | `/users/search?q=<termo>` |
| Users | GET | `/users/suggestions` |
| Users | GET | `/users/:username` |
| Users | GET | `/users/:id/posts` |
| Users | GET | `/users/:id/followers` |
| Users | GET | `/users/:id/following` |
| Users | GET | `/users/:id/is-following` |
| Users | POST | `/users/:id/follow` |
| Users | DELETE | `/users/:id/follow` |
| Users | PUT | `/users/me` |
| Users | POST | `/users/me/avatar` (multipart) |
