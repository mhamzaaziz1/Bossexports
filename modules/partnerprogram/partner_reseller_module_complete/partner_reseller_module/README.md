# Partner & Reseller Management Module for Perfex CRM

**Version:** 1.0.0  
**Author:** Your Company  
**License:** Commercial  
**Requires:** Perfex CRM 2.3.0+

## 📋 Overview

The Partner & Reseller Management Module is an enterprise-level solution that transforms Perfex CRM into a complete B2B partner ecosystem platform. It enables businesses to manage partner relationships, process deal registrations, track commissions, and provide partners with a dedicated self-service portal.

## ✨ Key Features

### 🤝 Partner Management
- Multiple partner types (Reseller, Referral, Contractor, Corporate)
- Comprehensive partner profiles with contacts and documents
- KYC/document management system
- Contract tracking with start/end dates
- Territory-based partner assignment
- Partner status management (Pending, Active, Suspended, Inactive)

### 📊 Deal Registration System
- Partner-submitted lead registration
- **Duplicate lead detection** (critical for UAE market)
- **Lead ownership protection**
- Multi-level approval workflow (Approve/Reject/Request Info)
- Automatic CRM lead creation upon approval
- Real-time deal pipeline tracking
- Status progression: New → Contacted → Quotation → Negotiation → Won/Lost

### 💰 Commission Management
- Flexible commission structures:
  - Fixed amount
  - Percentage-based
  - Tiered (volume incentives)
- Multiple payment conditions:
  - Project completion
  - Invoice clearance
  - Multi-stage payments
- Split commissions (multiple partners on one deal)
- Automated commission calculation
- Invoice generation
- Payment tracking and history

### 🎯 Partner Portal
- Secure login system
- Self-service deal submission
- Real-time deal tracking
- Commission visibility
- Invoice downloads
- Profile management
- Multi-contact management
- Activity history

### 📈 Analytics & Reporting
- Partner performance rankings
- Conversion rate analytics
- Revenue by partner/service/territory
- Commission summaries
- ROI tracking
- Export to Excel/PDF
- Custom date range filtering

### 🔔 Multi-Channel Notifications
- Email notifications
- WhatsApp integration (configurable API)
- Telegram bot support (optional)
- In-app notifications
- Notification events:
  - Lead approved/rejected
  - Deal status changed
  - Commission released
  - Payment processed

### 🤖 AI Integration (Advanced)
- Auto-classify leads
- Suggest deal values
- Predict conversion probability
- Detect duplicate/fake leads
- Smart partner ranking

### 🔐 Security Features
- Role-based access control
- Partner data isolation
- Activity logging
- CSRF protection
- SQL injection prevention
- Secure password hashing
- API token authentication

## 📦 Installation

### Prerequisites
- Perfex CRM 2.3.0 or higher
- PHP 7.4+ (PHP 8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.2+
- mod_rewrite enabled
- SSL certificate (recommended)

### Installation Steps

1. **Upload Module**
   ```bash
   # Upload the module folder to:
   /path/to/perfex/modules/partner_reseller/
   ```

2. **Install via Admin Panel**
   - Login to Perfex CRM admin panel
   - Navigate to **Setup → Modules**
   - Find "Partner & Reseller Management"
   - Click **Install**
   - Wait for installation to complete
   - Click **Activate**

3. **Configure Settings**
   - Go to **Partner & Reseller → Settings**
   - Configure notification settings
   - Set commission defaults
   - Enable/disable self-registration
   - Configure API integrations (WhatsApp, Telegram)

4. **Set Permissions**
   - Go to **Setup → Staff**
   - Assign partner module permissions to staff roles

## 🎛️ Configuration

### Essential Settings

**Notification Settings:**
```php
partner_notification_email: 1 (Enable/Disable)
partner_notification_whatsapp: 0 (Enable/Disable)
partner_notification_telegram: 0 (Enable/Disable)
partner_whatsapp_api_key: YOUR_API_KEY
partner_whatsapp_api_url: https://api.whatsapp.com/...
```

**Commission Defaults:**
```php
commission_payment_condition: invoice_clearance
commission_tier_config: [
    ['min' => 0, 'max' => 10000, 'rate' => 5],
    ['min' => 10001, 'max' => 50000, 'rate' => 7],
    ['min' => 50001, 'max' => 999999999, 'rate' => 10]
]
```

**Deal Management:**
```php
deal_auto_create_lead: 1 (Auto-create CRM leads)
duplicate_detection: 1 (Enable duplicate checking)
deal_lock_period: 30 (Days to lock deal ownership)
```

## 📚 Database Schema

The module creates 10 tables:

- `tblpartner_resellers` - Partner master data
- `tblpartner_contacts` - Partner contacts
- `tblpartner_documents` - KYC documents
- `tblpartner_deals` - Deal registrations
- `tblpartner_commissions` - Commission records
- `tblpartner_payments` - Payment tracking
- `tblpartner_notifications` - Notification system
- `tblpartner_activities` - Activity logs
- `tblpartner_settings` - Module configuration
- `tblpartner_territories` - Territory management

## 🔌 API Integration

### Authentication
```bash
curl -X GET "https://yourcrm.com/api/partners" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"
```

### Available Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/partners` | GET | List all partners |
| `/api/partners/{id}` | GET | Get partner details |
| `/api/partners` | POST | Create partner |
| `/api/deals` | GET | List all deals |
| `/api/deals` | POST | Submit deal |
| `/api/deals/{id}/approve` | POST | Approve deal |
| `/api/commissions` | GET | List commissions |

## 🎨 Customization

### Adding Custom Fields

1. **Database:**
   ```sql
   ALTER TABLE tblpartner_resellers 
   ADD COLUMN custom_field VARCHAR(255);
   ```

2. **Model:** Update `Partner_model.php`
   ```php
   public function get_custom_field($partner_id) {
       return $this->db->select('custom_field')
                       ->where('id', $partner_id)
                       ->get($this->table)
                       ->row();
   }
   ```

3. **View:** Add to `views/partners/form.php`
   ```php
   <input type="text" name="custom_field" 
          value="<?php echo $partner->custom_field ?? ''; ?>">
   ```

### Custom Commission Logic

Edit `partner_reseller_init.php`:
```php
function calculate_commission($deal_value, $commission_type, $commission_value, $tier_config = [])
{
    // Add your custom logic here
    if ($commission_type == 'custom') {
        return your_custom_calculation($deal_value);
    }
    // ... existing logic
}
```

## 📖 Usage Guide

### For Administrators

**Creating a Partner:**
1. Navigate to **Partners → Add New Partner**
2. Fill in company details
3. Set commission structure
4. Upload required documents
5. Activate portal access
6. Save

**Approving Deals:**
1. Go to **Deals → Pending Deals**
2. Review deal details
3. Check for duplicates
4. Click **Approve** or **Reject**
5. Deal converts to CRM lead (if auto-create enabled)

**Processing Commissions:**
1. Navigate to **Commissions**
2. Select pending commissions
3. Click **Approve**
4. Invoice generates automatically
5. Process payment and record details

### For Partners

**Submitting a Deal:**
1. Login to partner portal
2. Click **Submit New Deal**
3. Fill in client information
4. Enter deal details
5. Submit for approval
6. Track status in dashboard

**Viewing Commissions:**
1. Go to **Commissions** section
2. View all commission records
3. Download invoices as needed
4. Track payment status

## 🔧 Troubleshooting

### Common Issues

**Partners Cannot Login:**
- Check `portal_access` flag is enabled
- Verify password hash in database
- Check partner status is "active"

**Deals Not Creating CRM Leads:**
- Verify `deal_auto_create_lead` setting
- Check staff permissions
- Review error logs

**Notifications Not Sending:**
- Verify email settings in Perfex
- Check WhatsApp/Telegram API credentials
- Review notification queue

**Commission Calculations Incorrect:**
- Review partner's commission structure
- Check deal value
- Verify tier configuration (if tiered)

### Debug Mode

Enable debugging in `config.php`:
```php
define('ENVIRONMENT', 'development');
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Check logs: `/application/logs/log-YYYY-MM-DD.php`

## 📊 Performance Optimization

1. **Database Indexing:**
   ```sql
   CREATE INDEX idx_partner_status ON tblpartner_resellers(status);
   CREATE INDEX idx_deal_status ON tblpartner_deals(status, approval_status);
   CREATE INDEX idx_partner_deals ON tblpartner_deals(partner_id, status);
   ```

2. **Enable Caching:**
   - Use Redis/Memcached for session storage
   - Enable query caching
   - Implement result caching for reports

3. **Optimize Queries:**
   - Use pagination for large datasets
   - Implement lazy loading
   - Add database indexes on foreign keys

## 🔒 Security Recommendations

- Enable SSL/HTTPS
- Use strong passwords (min 12 characters)
- Enable two-factor authentication
- Regular security audits
- Keep module updated
- Backup database regularly
- Implement IP whitelisting for sensitive operations
- Review activity logs periodically

## 📞 Support

**Email:** support@yourcompany.com  
**Documentation:** https://docs.yourcompany.com/partner-module  
**Updates:** Quarterly releases with new features  
**License:** Commercial - One license per installation

## 📝 Changelog

### Version 1.0.0 (2025-01-XX)
- Initial release
- Complete partner management system
- Deal registration and approval workflow
- Commission calculation and tracking
- Partner portal with self-service
- Multi-channel notifications
- Comprehensive analytics and reporting
- API integration support
- AI-powered features (optional)

## 🎓 Training Resources

- **Video Tutorials:** Available in documentation portal
- **User Manual:** Comprehensive PDF guide included
- **Technical Reference:** Complete developer documentation
- **Sample Workflows:** Real-world usage examples
- **API Documentation:** Complete endpoint reference

## 🤝 Contributing

For feature requests or bug reports, please contact support.

## 📄 License

This module is licensed for use with Perfex CRM under commercial license terms. 
Redistribution or resale is prohibited without written authorization.

---

**Copyright © 2025 Your Company. All rights reserved.**
