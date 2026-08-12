# Deploying WePower Solar (PHP) to Hostinger

This project is plain PHP + MySQL — no Composer, no Node, no SSH required.
Everything below uses Hostinger's hPanel and File Manager only.

## 1. Create the MySQL database

1. hPanel → **Databases** → **MySQL Databases**
2. Create a new database (e.g. `u123456789_wepower`) and a database user with a strong password. Note down:
   - Database host (usually `localhost`)
   - Database name
   - Database username
   - Database password

## 2. Import the schema

1. hPanel → **Databases** → **phpMyAdmin** → open your new database
2. Go to the **Import** tab
3. Choose the file `database/database.sql` from this project
4. Click **Go**

This creates all 11 tables and seeds default settings, 5 sample job postings, and one admin account:
- **Email:** `admin@wepower.pk`
- **Password:** `Admin@123`

⚠️ **Change this password immediately after your first login** (Admin → Settings → Security tab).

## 3. Upload the files

1. hPanel → **Files** → **File Manager**, or connect via FTP (credentials in hPanel → **Files** → **FTP Accounts**)
2. Upload **everything in this project folder** into `public_html` (or your domain's document root) — keep the folder structure exactly as-is (`config/`, `includes/`, `admin/`, `api/`, `assets/`, `uploads/`, etc. all need to end up as siblings inside the web root)

## 4. Configure database + site settings

Edit `config/config.php` (via File Manager's built-in editor, or edit locally and re-upload) and fill in:

```php
define('DB_HOST', 'localhost');       // from step 1
define('DB_NAME', 'u123456789_wepower');
define('DB_USER', 'u123456789_dbuser');
define('DB_PASS', 'your-db-password');

define('SITE_URL', 'https://yourdomain.com'); // no trailing slash
define('APP_ENV', 'production'); // hides PHP error details from visitors
```

The `SMTP_*_FALLBACK` constants in the same file are only used if the SMTP fields are left blank in the admin Settings page — you'll set your real Gmail app-password credentials there instead (see step 6), so you can leave these as-is.

## 5. Set folder permissions

The `uploads/` folder (and its `resumes/` and `logos/` subfolders) need to be writable by PHP:

- In File Manager, right-click `uploads/` → **Permissions** → set to `755` (or `775` if 755 doesn't work on your hosting)

## 6. Configure email (SMTP)

1. Log into the admin panel at `https://yourdomain.com/admin/login.php`
2. Go to **Settings → SMTP / Email**
3. Enter your Gmail address as the username, and a **Gmail App Password** (not your regular password — generate one at Google Account → Security → 2-Step Verification → App Passwords) as the password
4. Host: `smtp.gmail.com`, Port: `587`, leave "Use SSL/TLS" unchecked (587 uses STARTTLS)
5. Click **Save SMTP Settings**, then **Test Connection** to confirm it works — you should receive a test email

## 7. Verify everything works

- [ ] Homepage loads at `https://yourdomain.com/`
- [ ] All 6 public pages load without errors (About, Services, Careers, Load Calculator, Contact)
- [ ] Submit the Contact form and the "Get a Quote" popup — confirm rows appear in the admin panel and emails arrive
- [ ] Submit a Careers application with a resume file — confirm it uploads and is downloadable from Admin → Careers
- [ ] Log into `/admin/login.php`, confirm the dashboard loads with stats
- [ ] **Change the default admin password** (Settings → Security)
- [ ] Check `/sitemap.xml` and `/robots.txt` both load correctly

## 8. Point your domain (if not already)

If this is a new domain, point its DNS A record to your Hostinger server IP, or if using Hostinger's own domain, it should already resolve once the site is uploaded. Hostinger provides free SSL — enable it under hPanel → **Security** → **SSL** if it isn't already active, then set `SITE_URL` in `config/config.php` to the `https://` version.

---

## Notes on how this project differs from typical PHP hosting setups

- **No `.env` file** — `config/config.php` holds all connection/fallback constants directly (the PHP equivalent), since Hostinger shared hosting has no environment-variable injection like a Node PaaS would.
- **No Composer** — PHPMailer is vendored as plain source files under `vendor/PHPMailer/`, `require`'d directly. Nothing to `composer install`.
- **`uploads/` blocks PHP execution** via its own `.htaccess` — this is intentional hardening so an uploaded file can never run as a script.
- **Rate limiting is DB-backed** (`form_submissions`, `login_attempts` tables) rather than in-memory, since PHP processes don't persist state between requests the way a long-running Node server does.
