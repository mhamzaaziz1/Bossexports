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

## 🧩 Phase 1.5: Comprehensive Module Ecosystem

Bossexports ships with over *60 pre-integrated modules*, making it a truly all-in-one business management platform. These add-ons are organized into the following functional areas:

### 💼 Finance, Accounting & Payments
- **Accounting:** Full dual-entry accounting integration.
- **eInvoice & Zatca:** Electronic invoicing and regional compliance.
- **Proforma:** Creation of proforma invoices.
- **Payment Gateways:** Razorpay, GoCardless Gateway, Ideal, MercadoPago.
- **Other Financial:** Commission calculation, Decimal Settings, Payment Signature, Sales Tax Breakdown.

### 📦 E-Commerce, Logistics & Inventory
- **Warehouse:** Comprehensive inventory routing, auditing, and storage management.
- **Omni Sales:** Multi-channel sales integration point-of-sale systems.
- **WooCommerce:** Direct sync with WooCommerce storefronts.
- **Logistics & Shipments:** Delivery routing and shipping tracking.
- **Products:** Detailed product catalogs.
- **Delivery Notes:** Documentation for outbound inventory.

### 👥 HR, Team & Recruitment
- **HRM (Human Resource Management):** Employee records, leave, and payroll.
- **Recruitment:** Candidate tracking and job postings.
- **Timesheets:** Employee time logging and shift management.
- **OKR (Objectives & Key Results):** Goal tracking and team alignment.

### 🤖 AI, Data & Productivity
- **OpenAI & AI Writer:** AI-powered text generation and insights.
- **AI Query Builder:** Natural language database queries.
- **Smart Reports & Advanced Analytics:** Elevated BI reporting.
- **Spreadsheet Online:** Collaborative in-CRM spreadsheets.
- **Goals & Team Password:** Target setting and secure credential sharing.
- **Smart Documentation & Task Bookmarks:** Elevated wiki and bookmarking systems.

### 💬 Communication, Support & Marketing
- **Mailbox:** In-app IMAP/SMTP email client.
- **Chat Integrations:** WhatsApp Chat, Telegram Chat, PR Chat, Facebook Leads Integration.
- **Custom Notifications:** Advanced Email and SMS notification routing.
- **Support Contact & Feedback:** Extended ticketing and customer feedback gathering.
- **Surveys:** Customer satisfaction and data collection tools.

### 🛠️ Customization & Utilities
- **Theme Style & Perfex Dark Theme:** Advanced UI tweaking (alongside GenZ Theme).
- **Custom PDF & Report Builder:** Tailored document generation.
- **Menu Setup & Custom Links:** Navigation restructuring.
- **Webhooks & APIs:** Native REST API and outgoing webhook connections.
- **Account Planning & Supplier123:** Strategic vendor and CRM planning tools.
- And many more including Backup, Exports, Feedback, Inject Javascript, Line Discounts, Customer Phone Search, and SI Task Filters.

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
