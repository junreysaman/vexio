# Vexio

Movie and TV show streaming web app built on PHP with TMDB integration.

---

## Requirements

- PHP 8.1+
- MySQL 8.0+
- Composer
- A [TMDB API key or access token](https://www.themoviedb.org/settings/api)

---

## Installation

**1. Clone and install dependencies**

```bash
git clone <repo-url> vexio
cd vexio
composer install
```

**2. Configure environment**

```bash
cp .env.sample .env
```

Edit `.env` and fill in your database credentials and TMDB API key:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=vexio
DB_USER=root
DB_PASSWORD=

TMDB_ACCESS_TOKEN=your_tmdb_access_token
# or use TMDB_API_KEY if you don't have an access token
TMDB_API_KEY=your_tmdb_api_key
```

**3. Create the database**

```bash
php cli.php install
```

This creates the database and imports `database.sql`. Default admin login:

- **Username:** `admin`
- **Email:** `admin@example.com`
- **Password:** `password`

> Change the admin password after first login.

---

## CLI Commands

| Command | Description |
|---|---|
| `php cli.php install` | Create database and import schema |
| `php cli.php publish-scheduled` | Reimport and publish scheduled episodes whose air date has passed |
| `php cli.php create-controller` | Scaffold a new controller |
| `php cli.php create-middleware` | Scaffold a new middleware |
| `php cli.php create-service` | Scaffold a new service |
| `php cli.php help` | Show all available commands |

---

## Scheduled Episode Publishing

When episodes are imported before their air date, they are stored with `status = scheduled` and hidden from the frontend. Once the air date arrives, the `publish-scheduled` command reimports each episode from TMDB (picking up the latest synopsis, backdrop, and metadata) and sets it to `published`.

Run it manually at any time:

```bash
php cli.php publish-scheduled
```

### Automating with Windows Task Scheduler

Run this once in an elevated (Administrator) command prompt to register a daily task at midnight:

```cmd
schtasks /create ^
  /tn "Vexio Publish Scheduled Episodes" ^
  /tr "php d:\laragon\www\vexio\cli.php publish-scheduled" ^
  /sc daily ^
  /st 00:00 ^
  /f
```

Or as a single line:

```cmd
schtasks /create /tn "Vexio Publish Scheduled Episodes" /tr "php d:\laragon\www\vexio\cli.php publish-scheduled" /sc daily /st 00:00 /f
```

**Verify the task was created:**

```cmd
schtasks /query /tn "Vexio Publish Scheduled Episodes"
```

**Run it immediately (for testing):**

```cmd
schtasks /run /tn "Vexio Publish Scheduled Episodes"
```

**Remove the task:**

```cmd
schtasks /delete /tn "Vexio Publish Scheduled Episodes" /f
```

> If `php` is not in your system PATH, use the full path to the PHP executable, e.g. `C:\laragon\bin\php\php8.x\php.exe`.

### Automating with Linux / macOS Cron

Open your crontab:

```bash
crontab -e
```

Add a line to run at midnight every day:

```cron
0 0 * * * php /var/www/vexio/cli.php publish-scheduled >> /var/log/vexio-scheduler.log 2>&1
```

To run every hour instead (useful for shows that air throughout the day):

```cron
0 * * * * php /var/www/vexio/cli.php publish-scheduled >> /var/log/vexio-scheduler.log 2>&1
```

---

## Admin Panel

Access the admin panel at `/admin/dashboard` after logging in.

| Section | URL |
|---|---|
| Dashboard | `/admin/dashboard` |
| Content | `/admin/content` |
| Importer | `/admin/importer` |
| Users | `/admin/users` |
| Comments | `/admin/comments` |

---

## TMDB Importer

The importer fetches metadata from TMDB and stores it locally.

- **Movies** — imports title, synopsis, poster, backdrop, genres, cast, rating, and more
- **TV Shows** — imports show metadata plus seasons and episodes
- **Scheduled episodes** — episodes with a future air date are imported as `scheduled` and automatically published when their air date arrives (via the `publish-scheduled` command)

---

## License

See [LICENSE](LICENSE).
