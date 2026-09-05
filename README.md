# 🌱 GreenGuard — Community Environmental Threat Platform

> **GreenGuard is a community-powered environmental threat detection, reporting, verification and resolution platform that combines photo evidence, location-based reporting, AI-assisted classification, community monitoring and administrative action.**

---

## 📌 Problem Statement: Environment & Natural Resource Protection
Pollution, illegal waste dumping, tree loss, and open burning often go unnoticed by municipal authorities until severe ecological damage occurs. GreenGuard bridges this gap by empowering citizens to become active environmental guardians—providing geotagged photo evidence, instant AI classification via Google Gemini, community-driven validation, and a centralized administrative dashboard for fast resolution.

---

## ✨ Key Features

1. **📍 Geotagged Environmental Reporting**
   - Single-click geolocation capture (Latitude & Longitude) using the browser Geolocation API.
   - Secure evidence photo upload (JPG, PNG, WEBP) with mime-type validation.
2. **🤖 Google Gemini AI Threat Analysis**
   - Real-time computer vision analysis categorizing threats (`ILLEGAL_DUMPING`, `POLLUTION`, `TREE_LOSS`, `WASTE_BURNING`).
   - Confidence rating, suggested severity (`CRITICAL`, `HIGH`, `MEDIUM`, `LOW`), and actionable remediation advice.
   - User override safeguard if manual correction is needed.
3. **👥 Community Monitoring & Crowdsourced Verification**
   - Nearby citizens can confirm or dispute reports.
   - Rule-based dynamic **Priority Score (0–100)** to surface critical threats immediately.
4. **🗺️ Interactive Environmental Map**
   - Interactive Leaflet.js & OpenStreetMap integration with color-coded severity markers.
   - Resolved reports dynamically turn **Green** on the map.
5. **👨‍💼 Administrative Workflow & Resolution Portal**
   - Full lifecycle triage: `PENDING` ➔ `VERIFIED` ➔ `IN_PROGRESS` ➔ `RESOLVED`.
   - Admin notes and resolution tracking.
6. **🔔 In-App Notifications**
   - Real-time updates for citizens whenever their reports change status.
7. **🔒 Lightweight & Secure JSON Architecture**
   - Zero SQL database configuration needed.
   - Protected data store with `.htaccess` deny rules.
   - `password_hash()` and `password_verify()` for secure credential management.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3 (Custom Design System), Vanilla JavaScript |
| **Backend** | PHP 8+ (No heavy frameworks or Composer required) |
| **Data Storage** | Protected JSON Files (`data/users.json`, `data/reports.json`, `data/notifications.json`) |
| **AI Vision** | Google Gemini API (Server-side cURL via `api/analyze_image.php`) |
| **Maps** | Leaflet.js & OpenStreetMap |
| **Local Development** | XAMPP / Apache / PHP |
| **Production Hosting** | InfinityFree / Any Standard PHP Shared Hosting |

---

## 🚀 Local Setup Instructions (XAMPP)

Follow these easy steps to run GreenGuard on your local machine:

### 1. Install & Start XAMPP
- Download and install [XAMPP](https://www.apachefriends.org/) (PHP 8+).
- Open the **XAMPP Control Panel** and click **Start** next to **Apache**.

### 2. Place Project in `htdocs`
- Copy or clone this project folder into your XAMPP `htdocs` directory:
  ```text
  C:\xampp\htdocs\hackforge
  ```

### 3. Create Configuration File
- In the `config/` directory, create a copy of `config.example.php` named `config.php`:
  ```text
  config/config.php
  ```
- Set `BASE_URL` if your folder name differs:
  ```php
  define('BASE_URL', 'http://localhost/hackforge');
  ```

### 4. Open in Browser
- Open your web browser and navigate to:
  ```text
  http://localhost/hackforge/
  ```
- You should immediately see the GreenGuard landing page with live sample metrics!

---

## 🔑 Gemini API Setup

To enable automated AI threat classification:

1. Obtain a free Google Gemini API Key from [Google AI Studio](https://aistudio.google.com/).
2. Open `config/config.php` and set your API key:
   ```php
   define('GEMINI_API_KEY', 'AIzaSy...');
   ```
3. **Security Guarantee**:
   - The API key is stored exclusively on the backend in `config/config.php`.
   - All AI calls route strictly through `api/analyze_image.php`.
   - Your API key is **NEVER** exposed to frontend JavaScript, HTML, localStorage, or GitHub (`.gitignore` protects `config/config.php`).

---

## 👥 Demo Credentials (Local Testing)

The sample data in `data/users.json` includes pre-configured demo accounts:

| Role | Email | Password | Access Area |
|---|---|---|---|
| **Admin** | `admin@greenguard.local` | `admin123` | `/admin/login.php` |
| **Citizen (Guardian)** | `priya@greenguard.local` | `citizen123` | `/login.php` |
| **Citizen (Guardian)** | `rahul@greenguard.local` | `citizen123` | `/login.php` |

*(Note: Passwords in `data/users.json` are securely stored as Bcrypt hashes).*

---

## 🌐 InfinityFree Deployment Guide

GreenGuard was engineered to deploy onto shared hosting like **InfinityFree** without complex build steps:

1. **Sign up / Create Account**: Create an account on [InfinityFree](https://www.infinityfree.com/) and register a free subdomain.
2. **Upload Files**:
   - Open the **File Manager** (MonstaFTP) or use an FTP client (FileZilla).
   - Upload all project files into the `htdocs/` directory of your InfinityFree hosting account.
3. **Configure `config.php`**:
   - Create/edit `config/config.php` on the server:
     ```php
     define('BASE_URL', 'https://yoursubdomain.infinityfreeapp.com');
     define('GEMINI_API_KEY', 'YOUR_ACTUAL_GEMINI_KEY');
     define('DEBUG_MODE', false);
     ```
4. **Verify Folder Permissions**:
   - Ensure `uploads/` and `data/` have write permissions (`755` or `777` if required by the host).
5. **Verify Security**:
   - Test by attempting to open `https://yoursubdomain.infinityfreeapp.com/data/users.json` in your browser. It will be blocked with `403 Forbidden` via `.htaccess`.

---

## 📂 Project Directory Structure

```text
hackforge/
│
├── index.php                 # Landing page & public overview
├── login.php                 # Citizen login interface (Phase 2)
├── register.php              # Citizen registration interface (Phase 2)
├── logout.php                # Session destruction (Phase 2)
├── dashboard.php             # Citizen dashboard & quick statistics (Phase 3)
├── report.php                # Environmental incident reporting form (Phase 4)
├── map.php                   # Interactive threat map with Leaflet.js (Phase 5)
├── my_reports.php            # User-specific incident tracker (Phase 3)
├── report_details.php        # Detailed view with community actions (Phase 8)
│
├── admin/                    # Administrative Control System (Phase 6)
│   ├── login.php             # Admin authentication
│   ├── dashboard.php         # Admin analytics & triage center
│   ├── reports.php           # Master report table & status actions
│   ├── report_details.php    # Inspection, notes & resolution modal
│   └── users.php             # User management
│
├── api/                      # Backend API Endpoints
│   ├── register.php          # User registration handler
│   ├── login.php             # User authentication handler
│   ├── submit_report.php     # Report creation & file upload
│   ├── analyze_image.php     # Google Gemini AI computer vision endpoint
│   ├── community_action.php  # Confirm / dispute / add evidence
│   ├── update_report_status.php # Admin status transition handler
│   └── notifications.php     # In-app notification polling
│
├── config/
│   ├── config.example.php    # Version-controlled configuration template
│   ├── config.php            # Local / Production config (Contains API key, ignored by Git)
│   └── .htaccess             # Direct access protection for configuration
│
├── data/                     # Secure JSON Data Store
│   ├── users.json            # User records with Bcrypt hashed passwords
│   ├── reports.json          # Reports with GPS, AI, & community metrics
│   ├── notifications.json    # User in-app notifications
│   └── .htaccess             # Direct access protection for JSON files
│
├── uploads/                  # User submitted photo evidence
│   └── .gitkeep              # Folder preservation in Git
│
├── css/                      # CSS Design System (Vanilla CSS3)
│   ├── style.css             # Main styling, variables & layout
│   ├── auth.css              # Authentication screens styling
│   ├── dashboard.css         # Citizen dashboard styling
│   ├── report.css            # Report submission styling
│   ├── map.css               # Leaflet map container styling
│   └── admin.css             # Administrative portal styling
│
├── js/                       # Vanilla JavaScript Modules
│   ├── main.js               # Common navigation, toasts & animations
│   ├── report.js             # Geolocation capture & AI preview
│   ├── map.js                # Leaflet map rendering & marker filtering
│   └── admin.js              # Admin dashboard & triage actions
│
├── .htaccess                 # Apache server security & rewrite rules
├── .gitignore                # Git ignore rules (protects API keys & uploads)
└── README.md                 # Project documentation & setup guide
```

---

## 🛡️ Security Best Practices Implemented
- 🔒 **Zero Plaintext Passwords**: All passwords are encrypted using PHP `password_hash(PASSWORD_BCRYPT)`.
- 🛑 **Data Directory Protection**: Root and folder-level `.htaccess` prevents unauthorized downloads of `.json` data files.
- 🔑 **Air-Gapped API Key**: Gemini API credentials remain strictly on the backend server.
- 🛡️ **XSS & Injection Protection**: Output escaping on rendered data.
