# Instaclone — Backend

API REST para uma aplicação similar ao Instagram. Construída com **Laravel 12**, **Laravel Sanctum** e **FrankenPHP**. Roda via Docker.

---

## Tecnologias

| Camada | Tecnologia |
|--------|-----------|
| Runtime | PHP 8.4 + FrankenPHP |
| Framework | Laravel 12 |
| Autenticação | Laravel Sanctum (baseada em token) |
| Banco de dados | MySQL 8 |
| Documentação | L5-Swagger (OpenAPI 3) |
| Testes | PHPUnit 11 |

---

## Requisitos

- [Docker](https://www.docker.com/) + Docker Compose
- (Opcional, apenas local) PHP 8.2+, Composer 2, Node.js 18+

---

## Como Rodar (Docker — recomendado)

```bash
# 1. Clone o repositório
git clone <repo-url>
cd instaclone-backend

# 2. Copie e configure o ambiente
cp src/.env.example src/.env

# 3. Suba todos os serviços (app + mysql + swagger)
docker compose up -d

# 4. Execute as migrations
docker compose exec app php artisan migrate
```

Aplicação disponível em **http://localhost:8000**  
Swagger UI disponível em **http://localhost:8080**

---

## Como Rodar (Local)

```bash
cd src

# Instalar dependências PHP
composer install

# Instalar dependências JS
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Edite o .env — configure DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# Em seguida execute as migrations
php artisan migrate

# Iniciar servidor de desenvolvimento
composer run dev
```

---

## Variáveis de Ambiente

Principais variáveis em `src/.env`:

| Variável | Descrição |
|----------|-----------|
| `APP_KEY` | Chave da aplicação — gerada automaticamente pelo `key:generate` |
| `DB_HOST` | Host do MySQL (`mysql` no Docker, `127.0.0.1` local) |
| `DB_DATABASE` | Nome do banco de dados (padrão: `laravel`) |
| `DB_USERNAME` | Usuário do banco (padrão: `laravel`) |
| `DB_PASSWORD` | Senha do banco (padrão: `laravel`) |
| `VITE_API_URL` | URL da API consumida pelo frontend |

---

## Visão Geral da API

URL base: `/api`

Todas as rotas protegidas exigem o header: `Authorization: Bearer <token>`

### Autenticação (pública)
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/auth/register` | Cadastrar novo usuário |
| POST | `/auth/login` | Login, retorna token |

### Autenticação (protegida)
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/auth/logout` | Invalidar token |
| GET | `/auth/me` | Dados do usuário atual |

### Posts
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/posts` | Criar post |
| GET | `/posts/{id}` | Buscar post |
| PUT | `/posts/{id}` | Editar post |
| DELETE | `/posts/{id}` | Deletar post |

### Feed
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/feed` | Posts de usuários seguidos |

### Curtidas
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/posts/{id}/like` | Curtir post |
| DELETE | `/posts/{id}/unlike` | Descurtir post |
| GET | `/posts/{id}/likes` | Listar quem curtiu |

### Comentários
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/posts/{id}/comments` | Listar comentários |
| POST | `/posts/{id}/comments` | Adicionar comentário |
| PUT | `/comments/{id}` | Editar comentário |
| DELETE | `/comments/{id}` | Deletar comentário |

### Usuários
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/users/search` | Buscar usuários |
| GET | `/users/suggestions` | Sugestões de usuários para seguir |
| PUT | `/users/me` | Atualizar perfil |
| DELETE | `/users/me` | Deletar conta |
| POST | `/users/me/avatar` | Enviar avatar |
| GET | `/users/{username}` | Buscar perfil de usuário |
| GET | `/users/{id}/posts` | Listar posts de um usuário |

### Seguidores
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/users/{id}/follow` | Seguir usuário |
| DELETE | `/users/{id}/unfollow` | Deixar de seguir |
| GET | `/users/{id}/is-following` | Verificar se segue |
| GET | `/users/{id}/followers` | Listar seguidores |
| GET | `/users/{id}/following` | Listar quem segue |

Documentação interativa completa em **http://localhost:8080** (Swagger UI).

---

## Rodando os Testes

```bash
# Docker
docker compose exec app php artisan test

# Local
cd src && composer run test
```

---

## Estrutura do Projeto

```
instaclone-backend/
├── docker/             # Arquivos auxiliares do Docker (entrypoint, nginx, php.ini)
├── compose.yaml        # Serviços do Docker Compose
├── Dockerfile          # Build multi-stage (composer + FrankenPHP)
└── src/                # Raiz da aplicação Laravel
    ├── app/
    │   └── Http/Controllers/
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    ├── routes/
    │   └── api.php
    └── tests/
```
