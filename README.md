# B-Triad Jiu-Jitsu

Sistema web para gestão da academia **B-Triad Jiu-Jitsu**: cadastro de alunos, turmas, listas de presença, graduações, usuários e área do aluno (dashboard, perfil e ranking de treinos).

O projeto é uma aplicação **monolítica Laravel** com **frontend Vue 3** integrado via **Vite**, não um repositório separado de API + SPA. O PHP serve as rotas, a API REST e a view que monta o Vue; o JavaScript é compilado para `public/build` e carregado com `@vite`.

---

## Stack tecnológica

| Camada | Tecnologia |
|--------|------------|
| Backend | PHP 8.2+, Laravel 12 |
| API / autenticação | Laravel Sanctum (Bearer token) |
| Frontend | Vue 3, Vue Router 4, Axios |
| Build / CSS | Vite 7, Tailwind CSS 4, Sass |
| Banco de dados | MySQL 8 (Docker) ou SQLite (desenvolvimento local) |
| Containerização | Docker Compose (PHP-FPM, Nginx, MySQL) |

---

## Arquitetura geral

```mermaid
flowchart TB
    subgraph browser [Navegador]
        Vue[Vue 3 SPA]
        Router[Vue Router]
        Axios[Axios + token Sanctum]
    end

    subgraph laravel [Laravel]
        Web[routes/web.php]
        API[routes/api.php]
        Controllers[Controllers API]
        Models[Eloquent Models]
        Blade[resources/views/app.blade.php]
    end

    subgraph assets [Assets]
        Vite[Vite dev / build]
        JS[resources/js]
    end

    Vue --> Router
    Vue --> Axios
    Axios -->|JSON /api/*| API
  API --> Controllers --> Models
    Web -->|GET /admin/*, /student/*, /login| Blade
    Blade -->|@vite app.js| Vite
    Vite --> JS
    JS --> Vue
```

### Como o Vue “vive” dentro do Laravel

1. **Entrada única da SPA** — `resources/views/app.blade.php` expõe apenas `<div id="app"></div>` e inclui `@vite('resources/js/app.js')`.
2. **Bootstrap do Vue** — `resources/js/app.js` cria a app, registra o **Vue Router** e configura **Axios** (token no `localStorage`, interceptors para 401).
3. **Rotas web (Laravel)** — Em `routes/web.php`, paths como `/admin/students`, `/student/dashboard` e `/login` devolvem a mesma view `app`. Isso permite **refresh direto na URL** e bookmarks sem 404.
4. **Rotas do cliente (Vue)** — Em `resources/js/router/index.js` ficam as rotas reais da interface, com guards por `meta.requiresAuth` e `meta.roles` (`student`, `admin`, `instructor`).
5. **API JSON** — Toda persistência passa por `routes/api.php` (`/api/students`, `/api/attendance-lists`, etc.), protegida com `auth:sanctum`.
6. **Site institucional** — A rota `/` usa `resources/views/index.blade.php` (landing em Blade/CSS), separada da área logada em Vue.

Ou seja: **Laravel = servidor + API + shell HTML**; **Vue = UI interativa** após o login.

---

## Perfis de acesso

| Perfil | Rotas Vue (exemplos) | Funções principais |
|--------|----------------------|--------------------|
| **Aluno** (`student`) | `/student/dashboard`, `/student/profile`, `/student/ranking` | Dashboard, histórico de treinos/graduação, ranking mensal |
| **Admin / Instrutor** | `/admin/students`, `/admin/attendance-lists`, … | CRUD de alunos, turmas, presenças, graduações, usuários, ranking |

O login (`POST /api/auth/login`) devolve token Sanctum e dados do usuário; o front guarda `token` e `user` no `localStorage`.

---

## Estrutura de pastas (resumo)

```
btriad/
├── app/
│   ├── Http/Controllers/Api/   # Controllers REST (JSON)
│   ├── Http/Requests/            # Validação de entrada
│   └── Models/                   # Eloquent (Student, User, AttendanceList, …)
├── database/
│   ├── migrations/
│   └── seeders/                  # Faixas, alunos, usuário admin
├── docker/                       # Nginx + PHP-FPM
├── public/                       # index.php, assets compilados (build)
├── resources/
│   ├── js/
│   │   ├── app.js                # Entrada Vue
│   │   ├── App.vue
│   │   ├── router/index.js       # Rotas SPA
│   │   ├── views/                # Páginas (Admin, Student, Auth)
│   │   ├── components/           # Formulários, layout, paginação
│   │   ├── layouts/              # BaseLayout, SideBar, Header
│   │   └── utils/                # Helpers (ex.: paginação, dashboard)
│   ├── css/
│   └── views/
│       ├── app.blade.php         # Shell da SPA
│       └── home/                 # Landing pública
├── routes/
│   ├── api.php                   # API REST
│   └── web.php                   # Blade + fallback SPA
├── vite.config.js                # Laravel Vite + Vue + Tailwind
└── docker-compose.yml
```

---

## Domínio principal (banco de dados)

- **users** — Contas de acesso (`admin`, `instructor`, `student`), vinculáveis a um aluno.
- **students** — Dados cadastrais, faixa, foto, endereço, contatos, turma (kids/adult).
- **belts** — Faixas de graduação.
- **classes** — Turmas/horários (`type`: kids ou adult).
- **attendance_lists** + **attendance_list_students** — Listas de presença por data/turma.
- **student_graduations** — Histórico de graduações do aluno.

O tipo de turma (**kids/adult**) está em `classes.type`; as listas de presença referenciam a turma via `class_id`.

---

## API (visão geral)

Prefixo: **`/api`**. Autenticação: header `Authorization: Bearer {token}` (exceto login).

| Recurso | Endpoints principais |
|---------|----------------------|
| Auth | `POST /auth/login`, `POST /auth/logout`, `GET /auth/user`, treinos/graduações/foto do aluno logado |
| Alunos | `apiResource students` + `GET students/{id}/trainings`, `…/graduations` |
| Turmas | `apiResource classes` |
| Presenças | `apiResource attendance-lists` |
| Graduações | `apiResource student-graduations` |
| Usuários | `apiResource users` (index, store, show, update) |
| Faixas | `GET/POST belts` |

Listagens admin usam **paginação** (`?page=1&per_page=15`). Para selects e telas que precisam da lista completa, use `?all=1` (usuários) ou `?all=1` / `?for_user_link=1` (alunos), conforme o controller.

---

## Frontend (Vue)

- **Views** em `resources/js/views/` agrupadas por contexto (`Student/`, `Students/`, `AttendanceLists/`, etc.).
- **Componentes** reutilizáveis: formulários (`FormInput`, `FormSelect`), `Tabs`, `PaginationBar`, `BeltBadge`, layout (`BaseLayout`, `SideBar`).
- **Perfil do aluno (admin)** reutiliza `Student/Profile.vue` com modo leitura + **Habilitar edição** na mesma página.
- **Estilos**: Tailwind + SCSS scoped nos `.vue`; CSS global em `resources/css/`.

Alias `@/` aponta para `resources/js/` (configurado no Vite).

---

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+ e npm
- MySQL 8 (recomendado com Docker) ou SQLite

---

## Instalação e execução

### 1. Dependências

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure no `.env` a conexão com o banco (`DB_*`). Com Docker MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=btriad
DB_USERNAME=root
DB_PASSWORD=root
```

### 2. Banco e seeders

```bash
php artisan migrate
php artisan db:seed
```

Os seeders criam faixas, alunos de exemplo e um usuário administrador (ver `database/seeders/AdminUserSeeder.php`).

### 3. Storage (fotos de alunos)

```bash
php artisan storage:link
```

### 4. Frontend

**Desenvolvimento** (Vite + hot reload):

```bash
npm install
npm run dev
```

**Produção**:

```bash
npm run build
```

### 5. Servidor PHP

```bash
php artisan serve
```

Ou use o script Composer que sobe servidor, fila, logs e Vite em paralelo:

```bash
composer dev
```

### 6. Docker (opcional)

```bash
docker compose up -d
```

Aplicação em **http://localhost:8000** (Nginx). Ajuste `DB_HOST=mysql` no `.env` quando rodar migrations **dentro** do container `app`.

---

## Fluxo de desenvolvimento típico

1. Alterar API → `app/Http/Controllers/Api`, `app/Http/Requests`, migrations se necessário.
2. Alterar UI → componentes/views em `resources/js/`.
3. Nova rota de tela → registrar em `resources/js/router/index.js` **e** adicionar rota GET correspondente em `routes/web.php` (view `app`).
4. Testar com `npm run dev` + `php artisan serve` (ou `composer dev`).

---

## Testes

```bash
php artisan test
# ou
composer test
```

---

## Licença

Projeto baseado no framework Laravel ([MIT](https://opensource.org/licenses/MIT)). Código da aplicação B-Triad conforme repositório do autor.
