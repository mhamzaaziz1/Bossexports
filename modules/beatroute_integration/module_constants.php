<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Module Name
define('BEATROUTE_INTEGRATION_MODULE_NAME', 'beatroute_integration');

// Module Version
define('BEATROUTE_INTEGRATION_VERSION', '1.0.0');

// Module Tables
define('BEATROUTE_API_CONFIG_TABLE', db_prefix() . 'beatroute_api_config');
define('BEATROUTE_SKUS_TABLE', db_prefix() . 'items');
define('BEATROUTE_CUSTOMERS_TABLE', db_prefix() . 'clients');
define('BEATROUTE_INVOICES_TABLE', db_prefix() . 'beatroute_invoices');
define('BEATROUTE_INVOICE_ITEMS_TABLE', db_prefix() . 'beatroute_invoice_items');
define('BEATROUTE_PAYMENTS_TABLE', db_prefix() . 'beatroute_payments');
define('BEATROUTE_SYNC_LOGS_TABLE', db_prefix() . 'beatroute_sync_logs');

// Module Paths
define('BEATROUTE_INTEGRATION_MODULE_PATH', module_dir_path(BEATROUTE_INTEGRATION_MODULE_NAME));
define('BEATROUTE_INTEGRATION_MODULE_URL', module_dir_url(BEATROUTE_INTEGRATION_MODULE_NAME));

// Module Options
define('BEATROUTE_INTEGRATION_ENABLED', 'beatroute_integration_enabled');
define('BEATROUTE_SYNC_INTERVAL', 'beatroute_sync_interval');
define('BEATROUTE_AUTO_SYNC_ENABLED', 'beatroute_auto_sync_enabled');
define('BEATROUTE_WEBHOOK_ENABLED', 'beatroute_webhook_enabled');

// Sync Statuses
define('BEATROUTE_SYNC_STATUS_PENDING', 'pending');
define('BEATROUTE_SYNC_STATUS_SYNCED', 'synced');
define('BEATROUTE_SYNC_STATUS_FAILED', 'failed');

// Entity Statuses
define('BEATROUTE_STATUS_ACTIVE', 'active');
define('BEATROUTE_STATUS_INACTIVE', 'inactive');
