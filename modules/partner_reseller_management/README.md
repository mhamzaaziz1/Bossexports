# Partner & Reseller Management — v2.0.0
### Enterprise B2B Partner Program for Perfex CRM

A ground-up rebuild of the original `partnerprogram` module, designed and built as a **level-9 senior engineering** deliverable. Goes far beyond simple deal registration: this module is a full **Partner Relationship Management (PRM)** platform inside Perfex.

---

## What's new vs the v1 module

| Capability | v1 (`partnerprogram`) | v2 (this module) |
|---|---|---|
| Partner types | 4 | 8 (incl. distributor, MSP, VAD, affiliate) |
| Multi-tier hierarchy | ❌ | ✅ closure-table, unlimited depth |
| Tier ladder (Bronze→Diamond) | ❌ | ✅ auto-promotion based on revenue/deals |
| Commission models | fixed/%/tiered | + hybrid + product-based |
| Hierarchy override commissions | ❌ | ✅ multi-level |
| Recurring commissions (AMC/SaaS) | ❌ | ✅ monthly/quarterly/yearly + cron |
| Clawbacks | ❌ | ✅ refund/cancel/chargeback handling |
| Partner wallet & ledger | ❌ | ✅ full double-entry ledger |
| Self-service payout requests | ❌ | ✅ |
| MDF / Co-marketing campaigns | ❌ | ✅ allocation, approval, ROI tracking |
| Training & certifications | ❌ | ✅ courses, quizzes, certificates with verification codes |
| Gamification (badges, leaderboard, points) | ❌ | ✅ |
| AI lead scoring & win probability | placeholder | ✅ rule-based + LLM (OpenAI/Anthropic) |
| Fuzzy duplicate detection | basic | ✅ fingerprint + Levenshtein-style matching |
| Multi-currency + FX | ❌ | ✅ |
| Tax handling (inclusive/exclusive) | ❌ | ✅ |
| 2FA / lockout / session mgmt | ❌ | ✅ |
| Audit log (immutable) | ❌ | ✅ all actions recorded |
| Outgoing webhooks (HMAC signed) | ❌ | ✅ |
| Notification hub: email/SMS/WA/TG/in-app/webhook | partial | ✅ unified |
| White-label portal w/ theming | ❌ | ✅ |

---

## Architecture

```
modules/partner_reseller_management/
├── partner_reseller_management.php  # bootstrap, hooks, menus, routes
├── install/
│   ├── install.php                  # 23-table schema + seeds
│   └── uninstall.php
├── controllers/
│   ├── Partner_reseller_management.php  # admin
│   └── Partner_portal.php               # /partner-portal/*
├── models/
│   ├── Partners_model.php   ├── Deals_model.php
│   ├── Commissions_model.php├── Wallet_model.php
│   ├── Mdf_model.php        ├── Training_model.php
│   └── Audit_model.php
├── libraries/
│   ├── Commission_engine.php   # rule engine
│   ├── Lead_intelligence.php   # scoring + dedupe + LLM
│   └── Notification_hub.php    # multi-channel dispatcher
├── helpers/
│   └── partner_reseller_management_helper.php
├── views/
│   ├── admin/{dashboard,partners,deals,commissions,mdf,training,reports,audit,settings}/
│   └── portal/{login,register,dashboard,deals_list,deal_new,deal_view,
│                commissions,wallet,training,profile}.php
├── language/english/partner_reseller_management_lang.php
└── assets/{css,js,images}/
```

### Key engineering decisions
- **Closure-table** for hierarchy (`prm_partner_hierarchy`) → O(1) ancestor lookup for override commissions.
- **Fingerprint hash** on deals (sha256 of normalised email+phone+company) → instant duplicate match.
- **Lock window** (`lock_expires_at`) prevents two partners registering the same lead during a configurable cooldown.
- **Wallet ledger** is append-only; balance is derived from running sum.
- **LLM is optional** — Lead_intelligence has a free deterministic fallback.
- **Hooks everywhere** (`prm_deal_*`, `prm_commission_*`, `prm_partner_*`) so extending the workflow doesn't require touching core files.
- **All settings in DB** (`prm_settings`) — no edits to module files needed.

---

## Database tables (23)

`prm_partners`, `prm_partner_hierarchy`, `prm_partner_contacts`, `prm_partner_documents`, `prm_partner_tier_levels`, `prm_partner_products`, `prm_deals`, `prm_deal_history`, `prm_commissions`, `prm_recurring_commissions`, `prm_clawbacks`, `prm_wallet_ledger`, `prm_payouts`, `prm_mdf_funds`, `prm_mdf_campaigns`, `prm_training_courses`, `prm_training_progress`, `prm_certifications`, `prm_badges`, `prm_partner_badges`, `prm_audit_log`, `prm_partner_sessions`, `prm_settings`.

---

## Installation

1. Place this folder at `application/modules/partner_reseller_management/` (already done — same path).
2. Login to Perfex CRM → **Setup → Modules**.
3. Find **Partner & Reseller Management** → click **Install** → **Activate**.
4. Configure **Partner & Reseller → Settings**.
5. (Optional) Set `prm_ai_api_key` to enable LLM-powered lead scoring.
6. (Optional) Set `prm_webhook_url` to push events to your data warehouse.

The installer creates all 23 tables, default settings, 5-tier ladder, and starter badges.

---

## URLs

| Area | URL |
|---|---|
| Admin home | `/admin/partner_reseller_management/dashboard` |
| Partner portal | `/partner-portal` |
| Partner login | `/partner-portal/login` |
| Partner register | `/partner-portal/register` |
| Deal registration | `/partner-portal/deals/new` |

---

## Roles & permissions

| Capability | Description |
|---|---|
| `view`, `view_own` | List partners (own only or all) |
| `create`, `edit`, `delete` | CRUD on partners |
| `approve_deals` | Approve / reject submitted deals |
| `manage_commission` | Approve / clawback commissions |
| `process_payouts` | Approve & mark payouts paid |
| `manage_mdf` | Allocate MDF funds, approve campaigns |
| `view_audit` | View immutable audit log |

---

## Hooks you can use

```php
hooks()->add_action('prm_deal_submitted',   $cb);   // ($deal_id)
hooks()->add_action('prm_deal_approved',    $cb);   // ($deal_id)
hooks()->add_action('prm_deal_rejected',    $cb);   // ($deal_id, $reason)
hooks()->add_action('prm_deal_won',         $cb);   // ($deal_id, $value)
hooks()->add_action('prm_commission_calculated', $cb);
hooks()->add_action('prm_commission_paid',  $cb);
hooks()->add_action('prm_partner_tier_changed', $cb); // ($partner_id, $new_tier_id)
hooks()->add_action('prm_certification_issued',  $cb);
```

---

## Engineering notes

- PHP 7.4+ minimum; uses `password_hash`, `random_bytes`, `hash_hmac`.
- All write paths log to `prm_audit_log` via `audit_log()` helper.
- Cron: `app_cron` triggers `Commission_engine::run_recurring_cron()` daily.
- Routes for the portal are added via the `app_routes` filter in `partner_reseller_management.php`.
- View injection (CSS/JS) is scoped to module URLs only to avoid asset bleed.

---

## Roadmap (post-launch)

- [ ] React-based mobile PWA portal
- [ ] DocuSign/HelloSign e-sign integration on partner contracts
- [ ] Stripe Connect / Wise auto-payouts
- [ ] Power BI / Looker connector
- [ ] WebSocket real-time notifications
- [ ] Public partner directory page

---

**Author:** Hamza (mhamzaaziz1@gmail.com) — Senior Developer (L09)
**License:** Commercial / Proprietary
