# Adding AI Chat + CRUD + Roles/Permissions + Admin Reports to your Laravel app

This zip contains files to **drop into your existing Laravel 12 project** (the
skeleton you shared). Nothing here requires new Composer packages — roles/
permissions are implemented with plain DB tables, and the chatbot uses
`guzzlehttp/guzzle` (already in your `composer.json`) to call OpenAI's Chat
Completions API.

## 1. Copy files in

Copy everything from this zip into your project root, merging folders. This
will:

- **Add** new files: models (`Role`, `Permission`, `Task`, `ChatMessage`),
  middleware, controllers, views, migrations, a seeder.
- **Replace** these existing files (back them up first if you've customized
  them): `app/Models/User.php`, `routes/web.php`, `bootstrap/app.php`,
  `config/services.php`, `database/seeders/DatabaseSeeder.php`.

## 2. Install a UI dependency for pagination styling (optional)

`tasks.index` calls `{{ $tasks->links() }}`, which uses Tailwind pagination
views by default in Laravel 12 — no extra package needed.

## 3. Environment variables

Add to your `.env`:

```
OPENAI_API_KEY=sk-...your key...
OPENAI_MODEL=gpt-4o-mini
# OPENAI_BASE_URI=https://api.openai.com/v1/   # override for OpenRouter/Groq/Azure/local Ollama, etc.
```

If `OPENAI_API_KEY` is empty, the chatbot still works but replies with a
"not configured yet" message instead of erroring.

## 4. Run migrations & seed roles/admin user

```bash
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\RolePermissionSeeder
```

(or just `php artisan migrate --seed` if `DatabaseSeeder` is untouched —
it now calls `RolePermissionSeeder` automatically.)

This creates:
- Roles: `admin`, `user`
- Permissions: `tasks.view/create/edit/delete`, `reports.view`, `users.manage`
- A default admin login:
  - **email:** `admin@example.com`
  - **password:** `password`

  **Change this password immediately** (or delete/edit the seeder before
  running it in production).

## 5. What you get

- **Register / Login / Logout** — plain session auth (`/register`, `/login`,
  `POST /logout`). New signups are auto-assigned the `user` role.
- **Roles & permissions** — `users.role_id` -> `roles` -> `permissions`
  (many-to-many). Two middlewares:
  - `->middleware('role:admin')` — require a specific role
  - `->middleware('permission:tasks.delete')` — require a specific permission
  Helper methods on `User`: `$user->isAdmin()`, `$user->hasRole('admin')`,
  `$user->hasPermission('tasks.delete')`.
- **CRUD** — a `tasks` resource (`/tasks`) as the example CRUD module:
  create/edit/delete/list, with users seeing only their own tasks and admins
  seeing everyone's. Swap `Task` for whatever domain model you actually need
  — the controller/middleware pattern is reusable.
- **AI chatbot** — `/chat`, a simple chat UI backed by `ChatController`,
  which calls OpenAI's `/chat/completions` endpoint and stores history per
  user in `chat_messages`. Swap the base URI/model in `config/services.php`
  to point at any OpenAI-compatible provider.
- **Admin-only reports & charts** — `/admin/reports`, gated by
  `role:admin` middleware, rendering Chart.js (via CDN) charts fed by a JSON
  endpoint (`/admin/reports/data`): tasks by status, users by role, signups
  over the last 14 days. Regular users get a 403 if they try to visit it.

## 6. Extending it

- Add more permissions in `RolePermissionSeeder` and gate routes/controller
  actions with `->middleware('permission:your.permission')`.
- Add an admin UI to manage roles/permissions/users (a simple
  `Admin\UserController` + `Admin\RoleController` CRUD, same pattern as
  `TaskController`) if you want that manageable from the browser instead of
  the seeder/tinker.
- If you'd rather use a battle-tested package for roles/permissions later
  (e.g. `spatie/laravel-permission`), this hand-rolled version maps closely
  enough that migrating is straightforward.
