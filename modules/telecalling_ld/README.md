# Telecalling Lead Distribution Module
**Version:** 2.0.0 | **Platform:** Perfex CRM 3.0+ | **Author:** SoloSoft Solutions LLC

---

## Quick Install
1. Upload zip via **Setup → Modules → Upload Module**
2. Click **Activate**
3. Go to **Telecalling → Settings** to configure email / WhatsApp / AI

---

## File Map
```
telecalling_ld/
├── telecalling_ld.php      ← Module bootstrap + sidebar menu
├── init.php                ← Perfex hooks registration
├── install.php             ← CREATE TABLE (runs on activation)
├── uninstall.php           ← DROP TABLE (runs on removal)
│
├── controllers/
│   └── Telecalling_ld.php  ← ALL routes (one controller)
│
├── models/
│   └── Telecalling_ld_model.php  ← ALL database queries
│
├── helpers/
│   ├── tc_distribution.php ← Lead assignment + utility functions
│   ├── tc_email.php        ← Email sending, queue, tracking
│   ├── tc_whatsapp.php     ← WhatsApp Cloud API
│   └── tc_ai_score.php     ← AI scoring via Anthropic/OpenAI
│
├── views/
│   ├── admin/
│   │   ├── dashboard.php   ← Admin analytics dashboard
│   │   ├── assign.php      ← Lead assignment page
│   │   ├── groups.php      ← Group management
│   │   ├── targets.php     ← Performance targets
│   │   ├── campaigns.php   ← Email campaigns + templates
│   │   ├── reports.php     ← Reports & analytics
│   │   ├── requests.php    ← Agent lead requests
│   │   └── settings.php    ← Module settings
│   ├── agent/
│   │   ├── dashboard.php   ← Agent personal dashboard
│   │   └── my_leads.php    ← Agent lead list + call logging
│   ├── group_leader/
│   │   └── dashboard.php   ← Group leader dashboard
│   └── partials/
│       ├── lead_tab.php    ← Call log tab on lead profile
│       ├── email_tab.php   ← Email history tab on lead profile
│       └── whatsapp_tab.php← WhatsApp log tab on lead profile
│
├── assets/
│   ├── css/tc_style.css    ← All module styles
│   └── js/tc_main.js       ← Chart.js initialisation
│
└── languages/english/
    └── telecalling_ld_lang.php  ← Translatable strings
```

---

## HOW TO: Add a New Page

**Step 1** — Add controller method in `controllers/Telecalling_ld.php`:
```php
public function my_new_page()
{
    $d = ['title'=>'My Page', 'page_title'=>'My New Page'];
    $this->_view('admin/layout/header', $d);
    $this->_view('modules/telecalling_ld/views/admin/my_new_page', $d);
    $this->_view('admin/layout/footer');
}
```

**Step 2** — Create view `views/admin/my_new_page.php`:
```php
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="tc-wrap">
  <div class="tc-page-header">
    <h2 class="tc-page-title">My New Page</h2>
  </div>
  <!-- Your content here -->
</div>
```

**Step 3** — Add to sidebar in `telecalling_ld.php` (optional):
```php
['tc-newpage', 'My Page', 'telecalling_ld/my_new_page', 9],
```

---

## HOW TO: Add a Database Table

**In `install.php`:**
```sql
CREATE TABLE IF NOT EXISTS `{$pfx}tc_newtable` (
  `id`  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**In `uninstall.php`:**
```php
$CI->db->query("DROP TABLE IF EXISTS `{$pfx}tc_newtable`");
```

**In `models/Telecalling_ld_model.php`:**
```php
public function get_newtable() {
    return $this->db->get($this->pfx.'tc_newtable')->result();
}
```

---

## HOW TO: Add Email Campaign Trigger

In `helpers/tc_email.php`, function `tc_check_score_trigger()` — this is called automatically from `init.php` every time a call is logged. Campaigns with `trigger_score > 0` that match the lead's new score will be queued automatically.

To trigger a campaign manually:
```php
require_once FCPATH.'modules/telecalling_ld/helpers/tc_email.php';
tc_queue_campaign($lead_id, $campaign_id);
```

---

## HOW TO: Add a New Perfex Hook

In `init.php`:
```php
hooks()->add_action('your_hook_name', 'tc_your_handler');
function tc_your_handler($param) {
    // your code
}
```

---

## Database Tables Reference

| Table | Purpose |
|---|---|
| tbltc_groups | Agent groups |
| tbltc_group_members | Agent ↔ group membership |
| tbltc_assignments | Lead ↔ agent assignments |
| tbltc_call_log | Every call logged |
| tbltc_targets | Performance targets |
| tbltc_campaigns | Email campaigns |
| tbltc_templates | Email templates in campaigns |
| tbltc_email_log | Every email sent + tracking |
| tbltc_email_queue | Scheduled emails to send |
| tbltc_unsubscribes | Unsubscribed emails |
| tbltc_rotation_queue | Leads waiting for rotation |
| tbltc_lead_requests | Agent requests for leads |
| tbltc_whatsapp_log | WhatsApp messages |
| tbltc_settings | Module key-value settings |

---

## Webhook URLs (configure in external services)

| Service | URL |
|---|---|
| AWS SES Bounce | `https://yourcrm.com/telecalling_ld/bounce_webhook` |
| Email open tracking pixel | Auto-embedded in every email |
| Unsubscribe link | Auto-embedded in every email |

---

## Utility Functions Quick Reference

```php
tc_bulk_assign($agent_id, $count)          // Assign N leads to agent
tc_range_assign($agent_id, $from, $to)     // Assign by lead ID range
tc_group_distribute($group_id, $count)     // Spread across group
tc_auto_assign_next($agent_id)             // Pull next from rotation
tc_setting($key)                           // Read setting
tc_save_setting($key, $value)              // Write setting
tc_is_manager()                            // Is current user a group leader?
tc_period_range('week')                    // Returns ['2026-03-10','2026-03-15']
tc_target_status($agent_id, 'calls', 'daily')  // Returns target progress array
tc_send_email($lead_id, $template_id)      // Send one email now
tc_queue_campaign($lead_id, $campaign_id)  // Queue full drip sequence
tc_handle_bounce($email, $reason)          // Remove bounced email
tc_wa_send($lead_id, $message)             // Send WhatsApp message
tc_avatar_color($staff_id)                 // Returns hex color string
```

---
*SoloSoft Solutions LLC — telecalling_ld v2.0.0*
