# RuangKerja

Laravel 13 full-stack project and Kanban board for **Seksi Aplikasi, Siber dan Statistik**.

## Application

- Department portfolio of multiple projects.
- Portfolio filters for status, priority, owner, search, and overdue projects; progress is calculated from completed tasks.
- Leaders and owners can update project details, ownership, status, priority, and due date.
- Project boards with To do, In progress, Blocked and Done columns.
- Create, edit and delete tasks, including their description, owner, priority, due date and status.
- Recent project activity for project changes and task creation, editing, status changes, and deletion.
- Account login and registration controlled by a private invitation code.
- PostgreSQL support for shared hosting; SQLite for local development.
- Department membership checks on project and task requests. Project creation is limited to leaders/owners; deleting another member's task is limited to leaders/owners.

The Laravel application is at the repository root. The root `index.html` is the earlier static prototype, kept for GitHub Pages preview compatibility; it saves data in each browser and does not use the Laravel backend.

## Run locally

Requirements: PHP 8.3+, Composer, and SQLite. Required PHP extensions include `pdo_sqlite`, `openssl`, `mbstring`, `fileinfo`, and `curl`.

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Set a strong private `REGISTRATION_CODE` in `.env`, then run:

```powershell
php artisan migrate
php artisan serve
```

Open `http://127.0.0.1:8000/register` and create the first account. The first account becomes department owner; subsequent accounts join as members. Keep `.env` private and never commit it. Remove the registration code to close registration after initial onboarding.

## Deploy

This repo includes a Dockerfile and a Render Blueprint. See [DEPLOYMENT.md](DEPLOYMENT.md) for the hosted database and environment setup. GitHub Pages serves only the static prototype; use a PHP host to run Laravel.

## Pilot limits

Registration uses one shared invitation code; there is not yet an invitation manager, email verification, password recovery, a complete audit trail, or backup workflow. The activity feed is for recent team updates, not a records-retention audit system. Review account access and restore procedures before using official project data.
