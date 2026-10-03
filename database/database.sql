-- WePower Solar Solutions — MySQL schema + seed data
-- Import this file via hPanel → Databases → phpMyAdmin (Import tab)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------
-- contacts
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contacts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40),
  subject VARCHAR(200),
  message TEXT NOT NULL,
  status VARCHAR(20) DEFAULT 'new',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contacts_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- bookings
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  city VARCHAR(100),
  property_type VARCHAR(50),
  service_type VARCHAR(100),
  preferred_date VARCHAR(40),
  notes TEXT,
  status VARCHAR(20) DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bookings_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- load_calculations
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS load_calculations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150),
  email VARCHAR(190),
  phone VARCHAR(40),
  city VARCHAR(100),
  property_type VARCHAR(50),
  num_people INT,
  appliances_json TEXT,
  total_load_w DOUBLE,
  recommended_kva DOUBLE,
  estimated_bill DOUBLE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- careers
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS careers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  position VARCHAR(150) NOT NULL,
  experience_years INT,
  education VARCHAR(200),
  cover_letter TEXT,
  resume_filename VARCHAR(255),
  status VARCHAR(20) DEFAULT 'new',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- applications ("Get a Quote" popup submissions)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  city VARCHAR(100),
  property_type VARCHAR(50),
  service_type VARCHAR(100),
  notes TEXT,
  status VARCHAR(20) DEFAULT 'new',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- visitors (simple page-view analytics)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS visitors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(64),
  user_agent VARCHAR(255),
  page VARCHAR(255),
  referrer VARCHAR(255),
  visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_visitors_date (visited_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- admins
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(150),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- site_settings (key/value store, admin-editable)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
  `key` VARCHAR(100) PRIMARY KEY,
  value TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- jobs (careers postings)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  type VARCHAR(20) DEFAULT 'full',
  dept VARCHAR(100),
  location VARCHAR(150),
  experience VARCHAR(100),
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- login_attempts — DB-backed replacement for express-rate-limit's
-- in-memory admin-login limiter (10 attempts / 10 min / IP)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(64) NOT NULL,
  email VARCHAR(190),
  attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- form_submissions — DB-backed replacement for the public-form
-- rate limiter (20 submissions / 15 min / IP)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS form_submissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(64) NOT NULL,
  endpoint VARCHAR(50) NOT NULL,
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_form_submissions_ip_time (ip, submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin notifications (also auto-created on first use by includes/notifications.php)
CREATE TABLE IF NOT EXISTS admin_notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(30) NOT NULL,
  title VARCHAR(200) NOT NULL,
  body VARCHAR(500),
  url VARCHAR(255),
  ref_id INT UNSIGNED,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admin_notifications_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notification_prefs (
  admin_id INT UNSIGNED NOT NULL,
  type VARCHAR(30) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (admin_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS push_subscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  endpoint VARCHAR(500) NOT NULL,
  keys_json TEXT,
  user_agent VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_push_endpoint (endpoint(191)),
  INDEX idx_push_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =================================================================
-- SEED DATA
-- =================================================================

-- site_settings defaults (mirrors database.js settingDefaults).
-- NOTE: active_template / spotlight_use_hero_banner intentionally
-- dropped — the PHP port ships Aurora only, no template switcher.
INSERT IGNORE INTO site_settings (`key`, value) VALUES
('company_name', 'WePower Solar Solutions'),
('company_short', 'WePower'),
('company_tagline', 'Empower Yourself with Solar Energy'),
('company_address', 'Office 3, GF Plaza 179, Intellectual Village Spring North, Bahria Town Phase 7, Rawalpindi'),
('company_phone1', '0335-5777898'),
('company_phone2', '0304-7357138'),
('company_email', 'wepowersolarsolutions@gmail.com'),
('company_instagram', '@wepower__'),
('company_logo', '/assets/images/logo.png'),
('social_facebook', 'https://www.facebook.com/share/r/1CR6RbC6Eb/'),
('social_instagram', 'https://www.instagram.com/wepower__/'),
('social_linkedin', 'https://www.linkedin.com/company/wepower-solar-solutions/'),
('social_youtube', 'https://www.youtube.com/@wepowersolar'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from_name', 'WePower Solar'),
('smtp_secure', 'false'),
('admin_notification_emails', ''),
('company_whatsapp', '03355777898'),
('whatsapp_message', 'Hi WePower! I''m interested in solar solutions. Can you help me?'),
('promo_video_url', ''),
('promo_video_enabled', 'false');

-- Default job postings (mirrors database.js seed)
INSERT INTO jobs (title, type, dept, location, experience, description) VALUES
('Solar Design Engineer', 'full', 'Engineering', 'Rawalpindi', '2–4 years', 'Design and model solar PV systems using PVSyst and AutoCAD. Prepare technical proposals and single-line diagrams for residential, commercial, and industrial projects.'),
('Site Supervisor / Installer', 'full', 'Operations', 'Rawalpindi / Field', '1–3 years', 'Supervise and participate in solar panel installation, wiring, and commissioning activities. Ensure compliance with safety and quality standards on all project sites.'),
('Sales & Business Development', 'full', 'Sales', 'Rawalpindi', '1–3 years', 'Generate leads, build client relationships, and close solar projects for residential and commercial segments. Develop proposals and follow through on the full sales cycle.'),
('Electrical Technician', 'full', 'Operations', 'Field – Nationwide', '1–2 years', 'Install, test, and maintain solar PV systems including inverters, batteries, and wiring. Respond to service calls and troubleshoot system issues.'),
('Marketing & Social Media Executive', 'full', 'Marketing', 'Remote / Rawalpindi', '1–2 years', 'Create and manage content for LinkedIn, Instagram, and other platforms. Run digital marketing campaigns and grow WePower online brand presence.');

-- Default admin account: admin@wepower.pk / Admin@123
-- Hash below is a real bcrypt hash of 'Admin@123' (cost 10). PHP's password_verify()
-- accepts $2a$/$2b$/$2y$ bcrypt variants interchangeably, so this verifies correctly
-- with password_verify() even though it wasn't generated by PHP itself.
-- CHANGE THIS PASSWORD immediately after first login via Admin → Settings → Security.
INSERT IGNORE INTO admins (email, password_hash, name) VALUES
('admin@wepower.pk', '$2a$10$/Eb3Y5gRZT7uRZIVTJMRgO5zPB0ZKWe//85DH5xL0izgNl1uZdiTW', 'Administrator');

SET FOREIGN_KEY_CHECKS = 1;
