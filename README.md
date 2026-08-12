# WePower Solar Solutions — PHP Edition

Plain PHP + MySQL port of the WePower Solar Solutions website, built to deploy on
ordinary shared hosting (Hostinger, etc.) via simple FTP/File Manager upload —
no Node.js runtime, no Composer, no SSH required.

This is a from-scratch rebuild of the original Node/Express + React + SQLite
site with the same UI/UX, same public pages, the same "Get a Quote"/Careers/
Load Calculator/Contact forms with email notifications, and the same admin
panel (dashboard, leads management, job postings, settings) — just running on
a stack that works on basic shared hosting.

## Stack

- **Backend:** Plain PHP (no framework), PDO + MySQL
- **Frontend:** Server-rendered PHP pages + vanilla JavaScript (no build step, no client framework)
- **Email:** PHPMailer (vendored, no Composer)
- **Admin auth:** PHP native sessions + `password_hash()`/`password_verify()` (bcrypt)

## Local development

1. Install PHP + MySQL locally (XAMPP/Laragon/WAMP on Windows, or use your OS's packages).
2. Create a database and import `database/database.sql`.
3. Copy your DB credentials into `config/config.php`.
4. Run PHP's built-in dev server from this folder:
   ```
   php -S localhost:8000
   ```
5. Visit `http://localhost:8000/index.php`. Admin panel: `http://localhost:8000/admin/login.php` (default: `admin@wepower.pk` / `Admin@123`).

## Deploying to production

See [`README-DEPLOY.md`](README-DEPLOY.md) for the full Hostinger deployment walkthrough.

## Project structure

```
├── index.php, about.php, services.php, contact.php, careers.php, load-calculator.php
├── config/            → DB connection + static site content (services, FAQ, company info)
├── includes/          → shared PHP: db, auth, settings, mailer, helpers, header/footer
├── api/                → public form endpoints (contact, apply, careers, calculator, jobs)
├── admin/              → admin panel pages + admin/api/* endpoints
├── assets/css, assets/js, assets/images
├── uploads/            → resumes + logos (execution disabled via .htaccess)
├── vendor/PHPMailer/   → vendored email library (no Composer)
└── database/database.sql → full schema + seed data
```
