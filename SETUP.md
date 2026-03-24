# Konik Website - Setup after Git Clone

This guide describes the steps to get the website running locally after a fresh clone.

## 1. Requirements

- PHP 8.1 or newer
- Git
- A local web server
  - Option A: PHP built-in server
  - Option B: Apache (XAMPP/WAMP)

Optional, but recommended:

- A mail setup for PHP (for password reset via `mail()`)
- HTTPS in production-like environments

## 2. Clone the repository

```bash
git clone https://github.com/NexoryOrg/Konik-Website.git
cd Konik-Website
```

## 3. Create/verify required directories

The following paths must exist and be writable:

- `logs/`
- `public/database/data/`
- `public/database/json/`
- `public/database/images/uploads/`
- `public/database/images/uploads/pending/`
- `public/database/images/uploads/rejected/`
- `public/database/json/history/`

If anything is missing, create the directories:

```powershell
New-Item -ItemType Directory -Force logs | Out-Null
New-Item -ItemType Directory -Force public/database/data | Out-Null
New-Item -ItemType Directory -Force public/database/json | Out-Null
New-Item -ItemType Directory -Force public/database/images/uploads | Out-Null
New-Item -ItemType Directory -Force public/database/images/uploads/pending | Out-Null
New-Item -ItemType Directory -Force public/database/images/uploads/rejected | Out-Null
New-Item -ItemType Directory -Force public/database/json/history | Out-Null
```

## 4. Verify base data files

The following files must exist and contain valid JSON:

- `public/database/data/user.json`
- `public/database/data/stats.json`
- `public/database/data/visitors.json`
- `public/database/data/uptime.json`
- `public/database/json/gallery.json`
- `public/database/json/history.json`

If any files are missing, create empty defaults:

```powershell
if (!(Test-Path public/database/data/stats.json)) { '{"approved":0,"rejected":0,"approved_items":[],"rejected_items":[]}' | Out-File public/database/data/stats.json -Encoding utf8 }
if (!(Test-Path public/database/data/visitors.json)) { '[]' | Out-File public/database/data/visitors.json -Encoding utf8 }
if (!(Test-Path public/database/data/uptime.json)) { '[]' | Out-File public/database/data/uptime.json -Encoding utf8 }
if (!(Test-Path public/database/json/gallery.json)) { '{}' | Out-File public/database/json/gallery.json -Encoding utf8 }
if (!(Test-Path public/database/json/history.json)) { '[]' | Out-File public/database/json/history.json -Encoding utf8 }
```

## 5. Required environment variable for password reset

Without this variable, web-based password reset is disabled:

- `KONIK_PASSWORD_RESET_SECRET` (at least 32 characters)

PowerShell (current session only):

```powershell
$env:KONIK_PASSWORD_RESET_SECRET = "replace-with-a-long-random-secret-at-least-32-chars"
```

Optional:

- `APP_TIMEZONE` (e.g. `Europe/Berlin`)

```powershell
$env:APP_TIMEZONE = "Europe/Berlin"
```

## 6. EmailJS configuration

The website reads EmailJS settings from:

- `public/database/data/user.json`

Required fields under `emailjs.emailjs_data[0]`:

- `service_id`
- `public_key`
- `template_id` (contact form)
- `upload_template_id` (upload status email)

Notes:

- Upload confirmation is handled client-side (browser + EmailJS SDK).
- Approve/Reject uses a server-side attempt with a browser fallback in the admin panel.

## 7. Start the local server

Important: the document root must be `public/`.

Option A (PHP built-in server):

```bash
php -S localhost:8000 -t public
```

Then open in your browser:

- `http://localhost:8000/home/index.php`
- Admin: `http://localhost:8000/admin-panel/dashboard/admin-panel.php`

Option B (Apache/XAMPP):

- Configure the project so that `public/` is used as the web root.

## 8. Smoke test

- Page loads without PHP fatal errors.
- Login in the admin area works.
- Gallery upload places the image in `public/database/images/uploads/pending/`.
- Approve moves the image to `public/database/images/uploads/`.
- Reject moves the image to `public/database/images/uploads/rejected/`.
- EmailJS emails are sent for upload/approve/reject.
- Password reset creates a link in `logs/password-reset-links.log`.

## 9. Common errors and quick fixes

- 403 on forms: CSRF token missing or session/cookies not stable.
- Upload fails: check folder permissions for `public/database/images/uploads/*`.
- Password reset disabled: `KONIK_PASSWORD_RESET_SECRET` not set or too short.
- No reset email: PHP `mail()` not configured locally.
- Discord forum webhook error 220001: workflow must post with `thread_id` or `thread_name`.

## 10. Optional for production

- Configure the web server so that only `public/` is directly accessible.
- Set write permissions as restrictively as possible.
- Rotate logs regularly.
- Set GitHub Secrets for workflows (`DISCORD_WEBHOOK`, optional `DISCORD_THREAD_ID_*`).
