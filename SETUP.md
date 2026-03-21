# Konik Website - Setup nach Git Clone

Diese Anleitung beschreibt die Schritte, damit die Website lokal nach einem frischen Clone stabil laeuft.

## 1) Voraussetzungen

- PHP 8.1 oder neuer
- Git
- Ein lokaler Webserver
  - Option A: eingebauter PHP-Server
  - Option B: Apache (XAMPP/WAMP)

Optional, aber empfohlen:

- Ein Mail-Setup fuer PHP (fuer Passwort-Reset per `mail()`)
- HTTPS in produktionsnahen Umgebungen

## 2) Repository clonen

```bash
git clone <DEIN_REPO_URL>
cd Konik-Website-main
```

## 3) Wichtige Ordner anlegen/pruefen

Die folgenden Pfade muessen existieren und beschreibbar sein:

- `logs/`
- `public/database/data/`
- `public/database/json/`
- `public/database/images/uploads/`
- `public/database/images/uploads/pending/`
- `public/database/images/uploads/rejected/`
- `public/database/json/history/`

Falls etwas fehlt, anlegen:

```powershell
New-Item -ItemType Directory -Force logs | Out-Null
New-Item -ItemType Directory -Force public/database/data | Out-Null
New-Item -ItemType Directory -Force public/database/json | Out-Null
New-Item -ItemType Directory -Force public/database/images/uploads | Out-Null
New-Item -ItemType Directory -Force public/database/images/uploads/pending | Out-Null
New-Item -ItemType Directory -Force public/database/images/uploads/rejected | Out-Null
New-Item -ItemType Directory -Force public/database/json/history | Out-Null
```

## 4) Basis-Daten pruefen

Diese Dateien sollten vorhanden und gueltiges JSON sein:

- `public/database/data/user.json`
- `public/database/data/stats.json`
- `public/database/data/visitors.json`
- `public/database/data/uptime.json`
- `public/database/json/gallery.json`
- `public/database/json/history.json`

Wenn Dateien fehlen, leere Defaults anlegen:

```powershell
if (!(Test-Path public/database/data/stats.json)) { '{"approved":0,"rejected":0,"approved_items":[],"rejected_items":[]}' | Out-File public/database/data/stats.json -Encoding utf8 }
if (!(Test-Path public/database/data/visitors.json)) { '[]' | Out-File public/database/data/visitors.json -Encoding utf8 }
if (!(Test-Path public/database/data/uptime.json)) { '[]' | Out-File public/database/data/uptime.json -Encoding utf8 }
if (!(Test-Path public/database/json/gallery.json)) { '{}' | Out-File public/database/json/gallery.json -Encoding utf8 }
if (!(Test-Path public/database/json/history.json)) { '[]' | Out-File public/database/json/history.json -Encoding utf8 }
```

## 5) Pflicht-Umgebungsvariable fuer Passwort-Reset

Ohne diese Variable ist Web-Passwort-Reset deaktiviert:

- `KONIK_PASSWORD_RESET_SECRET` (mindestens 32 Zeichen)

PowerShell (nur aktuelle Session):

```powershell
$env:KONIK_PASSWORD_RESET_SECRET = "replace-with-a-long-random-secret-at-least-32-chars"
```

Optional:

- `APP_TIMEZONE` (z. B. `Europe/Berlin`)

```powershell
$env:APP_TIMEZONE = "Europe/Berlin"
```

## 6) EmailJS Konfiguration

Die Website liest EmailJS-Daten aus:

- `public/database/data/user.json`

Erforderliche Felder unter `emailjs.emailjs_data[0]`:

- `service_id`
- `public_key`
- `template_id` (Kontaktformular)
- `upload_template_id` (Upload-Status-Mail)

Hinweis:

- Upload-Bestaetigung geht clientseitig (Browser + EmailJS SDK).
- Approve/Reject nutzt serverseitigen Versuch und Browser-Fallback im Admin-Panel.

## 7) Lokalen Server starten

Wichtig: Document Root muss `public/` sein.

Option A (PHP Built-in Server):

```bash
php -S localhost:8000 -t public
```

Danach im Browser:

- `http://localhost:8000/home/index.php`
- Admin: `http://localhost:8000/admin-panel/dashboard/admin-panel.php`

Option B (Apache/XAMPP):

- Projekt so einbinden, dass `public/` als Webroot dient.

## 8) Funktionstest (Smoke Test)

- Seite laedt ohne PHP Fatal Errors.
- Login im Admin-Bereich funktioniert.
- Gallery Upload legt Bild in `public/database/images/uploads/pending/` ab.
- Approve verschiebt Bild nach `public/database/images/uploads/`.
- Reject verschiebt Bild nach `public/database/images/uploads/rejected/`.
- EmailJS-Mails kommen fuer Upload/Approve/Reject.
- Passwort-Reset erzeugt Link in `logs/password-reset-links.log`.

## 9) Typische Fehler und schnelle Loesung

- 403 bei Formularen: CSRF Token fehlt oder Session/Cookies nicht stabil.
- Upload failt: Ordnerrechte auf `public/database/images/uploads/*` pruefen.
- Passwort-Reset deaktiviert: `KONIK_PASSWORD_RESET_SECRET` nicht gesetzt/zu kurz.
- Keine Reset-Mail: PHP `mail()` lokal nicht konfiguriert.
- Discord Forum Webhook Fehler 220001: Workflow muss mit `thread_id` oder `thread_name` posten.

## 10) Optional fuer Produktion

- Webserver so konfigurieren, dass nur `public/` direkt erreichbar ist.
- Schreibrechte so restriktiv wie moeglich setzen.
- Logs rotieren.
- GitHub Secrets fuer Workflows setzen (`DISCORD_WEBHOOK`, optionale `DISCORD_THREAD_ID_*`).
