# Deployment setup

The Laravel app needs an always-available PHP runtime and a persistent database. GitHub Pages cannot execute Laravel/PHP; its current URL continues to show the static prototype.

## Services

- **Render** runs the Dockerized Laravel web service. The included `render.yaml` defines a free pilot service and creates an application key.
- **Supabase PostgreSQL** stores shared project and task data. Use the project's **Session Pooler** connection values for the Render service.

Create a Supabase project on the Free plan in a nearby region such as Singapore. Set a strong database password and store it in the Supabase account's password manager. In Supabase, open **Connect** and select the Session Pooler. Keep the connection details private.

In Render, connect the `fadhillarif/ruangkerja-app` GitHub repository and create the service from the Blueprint (`render.yaml`). In the service's environment settings, fill in:

| Variable | Value |
| --- | --- |
| `APP_URL` | The Render service's `https://…onrender.com` URL |
| `DB_HOST` | Supabase Session Pooler host |
| `DB_PORT` | Supabase Session Pooler port |
| `DB_DATABASE` | Supabase database name |
| `DB_USERNAME` | Supabase Session Pooler username |
| `DB_PASSWORD` | Supabase database password |
| `REGISTRATION_CODE` | A long, private code for initial team onboarding |

Set secrets directly in the Render dashboard. Do not add them to GitHub or send them in chat. The service uses PostgreSQL TLS (`DB_SSLMODE=require`) and runs Laravel migrations when the container starts.

After the first deploy, open the Render URL and use `/register` to create the department owner account. Give the registration code only to team members who should join the department. Removing the code closes registration; add a managed invitation workflow before needing broader onboarding.

## Free-tier operating limits

Render free web services sleep after 15 minutes without traffic, wake in about a minute, and use ephemeral local storage. Supabase Free projects may pause after a week of low database activity and have no downloadable automatic backups. Keep the database on Supabase rather than the Render filesystem, export backups regularly, and upgrade to paid hosting before relying on this app for official reporting. See [Render free services](https://render.com/docs/free), [Supabase free-project pausing](https://supabase.com/docs/guides/platform/free-project-pausing), and [Supabase billing](https://supabase.com/docs/guides/platform/billing-on-supabase).
