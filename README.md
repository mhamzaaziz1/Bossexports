# Bossexports CRM

**Welcome to Bossexports!** A heavily customized and enhanced fork of Perfex CRM, designed to streamline your business operations with powerful new features, specifically tailored reporting, and a modernized user interface.

Bossexports is built to handle complex business workflows such as vendor management, cashbook operations, advanced reporting, and sleek theme customization out of the box.

---

## 🚀 Phase 1: Core Enhancements & Key Features

We've introduced several critical features to improve upon the standard Perfex CRM experience:

### 1. Modern "GenZ" Theme 🎨
Bossexports includes the exclusive **GenZ Theme**, designed to bring a fresh, modern aesthetic to the workspace. 
- Improved navigation and clearer layouts.
- Sleek typography and high-contrast elements for better readability.
- Faster rendering and optimized CSS.

### 2. Comprehensive Cashbook Module 💰
A fully integrated cashbook module to meticulously track daily financial transactions.
- Easily record and categorize all cash-in and cash-out activities.
- Real-time balance calculations.
- Seamless integration with existing accounting ledgers.

### 3. Advanced Custom Reports 📊
We understand that standard reporting often isn't enough. Bossexports comes with highly customized reporting capabilities.
- New predefined report templates tailored for comprehensive business analysis.
- Detailed insights into vendor performance and financial health.
- Export functionalities customized for seamless book balancing.

### 4. Vendor Date Optimization Fixes 📅
Critical bugs affecting vendor transaction dates have been completely resolved.
- Historical data is now accurately represented without timezone or formatting conflicts.
- Removed unnecessary debugging elements for cleaner, error-free production logs in `Reports.php`.

---

## 🛠️ Phase 2: System Requirements

Before installing Bossexports, ensure your server meets the following requirements:
- **PHP:** Version 7.4 or higher (8.1+ recommended for optimal performance)
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Web Server:** Apache (with `mod_rewrite` enabled) or Nginx
- **PHP Extensions:** `mysqli`, `pdo_mysql`, `gd`, `mbstring`, `curl`, `zip`, `xml`, `imap`

---

## 📦 Phase 3: Installation & Configuration Guide

### Step 1: File Deployment
1. Upload the entire Bossexports directory to your web server's document root (e.g., `public_html` or `www`).
2. Ensure the directory permissions are correctly set (directories to `755`, files to `644`). The `/uploads`, `/application/config`, and `/temp` folders must be writable (`777` or appropriate ownership).

### Step 2: Database Setup
1. Create a new MySQL database and a dedicated user with full privileges.
2. Import the provided SQL structure file (usually located in the root or a dedicated `/database` folder) into your newly created database.

### Step 3: Application Configuration
1. Navigate to `/application/config/`.
2. Locate the `app-config-sample.php` file and rename it to `app-config.php`.
3. Open `app-config.php` and update the following settings:
   - **Base URL:** Define your absolute application URL (e.g., `define('APP_BASE_URL','https://yourdomain.com/crm/');`).
   - **Database Credentials:** Provide your database Host, Username, Password, and Database Name.

### Step 4: Finalizing Setup
1. Navigate to your application's base URL in your web browser.
2. Log in using the default administrator credentials (provided separately or during initial setup).
3. Immediately navigate to **Setup -> Settings** to configure your core company details, timezones, and localization settings.

---

## ✨ Phase 4: Activating The GenZ Theme

To fully utilize the Bossexports visual experience:
1. Log in as an Administrator.
2. Navigate to **Setup -> Theme Style** (or equivalent settings page depending on configuration).
3. Select the **GenZ Theme** from the available custom themes.
4. Apply the changes and clear your browser cache to see the modernized UI.

---

## 🤝 Contribution & Development

If you are developing custom modules or modifying core files:
- **Never modify core files directly** if it can be avoided. Utilize Perfex CRM's powerful Action Hooks and Filters whenever possible to ensure future update compatibility.
- Ensure all custom reports are placed within the designated `/modules` structure to prevent conflicts.

### Developer Notes
- We have cleaned up unused code globally to improve performance.
- When creating new features, follow the existing MVC structure (CodeIgniter 3).
- Always test date-related queries against the recent vendor date fixes to ensure consistency.

---

## 📄 Licensing & Support

Bossexports is based on Perfex CRM. Ensure you hold a valid, legitimate license for the base Perfex CRM application. 
For support regarding the core CRM features, refer to the official [Perfex CRM Documentation](https://help.perfexcrm.com).

*This extensive README is maintained phase-by-phase as new extensive features are rolled out.*
