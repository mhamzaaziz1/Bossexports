<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check if quote hash is equal
 * @param  mixed $id   quote id
 * @param  string $hash quote hash
 * @return void
 */
function check_quote_restrictions($id, $hash)
{
    $CI = & get_instance();
    $CI->load->model('quotes_model');
    if (!$hash || !$id) {
        show_404();
    }
    $quote = $CI->quotes_model->get($id);
    if (!$quote || ($quote->hash != $hash)) {
        show_404();
    }
}

/**
 * Check if quote email template for expiry reminders is enabled
 * @return boolean
 */
function is_quotes_email_expiry_reminder_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'quote-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending quote expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_quotes_expiry_reminders_enabled()
{
    return is_quotes_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_QUOTE_EXP_REMINDER);
}

/**
 * Return quote status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function quote_status_color_class($id, $replace_default_by_muted = false)
{
    if ($id == 1) {
        $class = 'default';
    } elseif ($id == 2) {
        $class = 'danger';
    } elseif ($id == 3) {
        $class = 'success';
    } elseif ($id == 4 || $id == 5) {
        // status sent and revised
        $class = 'info';
    } elseif ($id == 6) {
        $class = 'default';
    }
    if ($class == 'default') {
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    }

    return $class;
}
/**
 * Format quote status with label or not
 * @param  mixed  $status  quote status id
 * @param  string  $classes additional label classes
 * @param  boolean $label   to include the label or return just translated text
 * @return string
 */
function format_quote_status($status, $classes = '', $label = true)
{
    $id = $status;
    if ($status == 1) {
        $status      = _l('quote_status_open');
        $label_class = 'default';
    } elseif ($status == 2) {
        $status      = _l('quote_status_declined');
        $label_class = 'danger';
    } elseif ($status == 3) {
        $status      = _l('quote_status_accepted');
        $label_class = 'success';
    } elseif ($status == 4) {
        $status      = _l('quote_status_sent');
        $label_class = 'info';
    } elseif ($status == 5) {
        $status      = _l('quote_status_revised');
        $label_class = 'info';
    } elseif ($status == 6) {
        $status      = _l('quote_status_draft');
        $label_class = 'default';
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status quote-status-' . $id . '">' . $status . '</span>';
    }

    return $status;
}

/**
 * Function that format quote number based on the prefix option and the quote id
 * @param  mixed $id quote id
 * @return string
 */
function format_quote_number($id)
{
    return get_option('quote_number_prefix') . str_pad($id, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
}


/**
 * Function that return quote item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_quote_item_taxes($itemid)
{
    $CI = & get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'quote');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}


/**
 * Calculate quote percent by status
 * @param  mixed $status          quote status
 * @param  mixed $total_estimates in case the total is calculated in other place
 * @return array
 */
function get_quotes_percent_by_status($status, $total_quotes = '')
{
    $has_permission_view                 = has_permission('quotes', '', 'view');
    $has_permission_view_own             = has_permission('quotes', '', 'view_own');
    $allow_staff_view_quotes_assigned = get_option('allow_staff_view_quotes_assigned');
    $staffId                             = get_staff_user_id();

    $whereUser = '';
    if (!$has_permission_view) {
        if ($has_permission_view_own) {
            $whereUser = '(addedfrom=' . $staffId;
            if ($allow_staff_view_quotes_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staffId;
            }
            $whereUser .= ')';
        } else {
            $whereUser .= 'assigned=' . $staffId;
        }
    }

    if (!is_numeric($total_quotes)) {
        $total_quotes = total_rows(db_prefix() . 'quotes', $whereUser);
    }

    $data            = [];
    $total_by_status = 0;
    $where           = 'status=' . get_instance()->db->escape_str($status);
    if (!$has_permission_view) {
        $where .= ' AND (' . $whereUser . ')';
    }

    $total_by_status = total_rows(db_prefix() . 'quotes', $where);
    $percent         = ($total_quotes > 0 ? number_format(($total_by_status * 100) / $total_quotes, 2) : 0);

    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_quotes;

    return $data;
}

/**
 * Function that will search possible quote templates in applicaion/views/admin/quote/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_quote_templates()
{
    $quote_templates = [];
    if (is_dir(module_views_path(BOOKING_MODULE_NAME, 'admin/quotes/templates'))) {
        foreach (list_files(module_views_path(BOOKING_MODULE_NAME, 'admin/quotes/templates')) as $template) {
            $quote_templates[] = $template;
        }
    }

    return $quote_templates;
}
/**
 * Check if staff member can view quote
 * @param  mixed $id quote id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_quote($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('quotes', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, assigned');
    $CI->db->from(db_prefix() . 'quotes');
    $CI->db->where('id', $id);
    $quote = $CI->db->get()->row();

    if ((has_permission('quotes', $staff_id, 'view_own') && $quote->addedfrom == $staff_id)
            || ($quote->assigned == $staff_id && get_option('allow_staff_view_quotes_assigned') == 1)) {
        return true;
    }

    return false;
}
function parse_quote_content_merge_fields($quote)
{
    $id = is_array($quote) ? $quote['id'] : $quote->id;
    $CI = &get_instance();

    $CI->load->library('booking/merge_fields/quotes_merge_fields');
    $CI->load->library('merge_fields/other_merge_fields');

    $merge_fields = [];
    $merge_fields = array_merge($merge_fields, $CI->quotes_merge_fields->format($id));
    $merge_fields = array_merge($merge_fields, $CI->other_merge_fields->format());
    foreach ($merge_fields as $key => $val) {
        $content = is_array($quote) ? $quote['content'] : $quote->content;

        if (stripos($content, $key) !== false) {
            if (is_array($quote)) {
                $quote['content'] = str_ireplace($key, $val, $content);
            } else {
                $quote->content = str_ireplace($key, $val, $content);
            }
        } else {
            if (is_array($quote)) {
                $quote['content'] = str_ireplace($key, '', $content);
            } else {
                $quote->content = str_ireplace($key, '', $content);
            }
        }
    }

    return $quote;
}

/**
 * Check if staff member have assigned quotes / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_quotes($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-quotes-' . $staff_id);
    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'quotes', ['assigned' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-quotes-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

function get_quotes_sql_where_staff($staff_id)
{
    $has_permission_view_own            = has_permission('quotes', '', 'view_own');
    $allow_staff_view_invoices_assigned = get_option('allow_staff_view_quotes_assigned');
    $CI                                 = &get_instance();

    $whereUser = '';
    if ($has_permission_view_own) {
        $whereUser = '((' . db_prefix() . 'quotes.addedfrom=' . $CI->db->escape_str($staff_id) . ' AND ' . db_prefix() . 'quotes.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "quotes" AND capability="view_own"))';
        if ($allow_staff_view_invoices_assigned == 1) {
            $whereUser .= ' OR assigned=' . $CI->db->escape_str($staff_id);
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'assigned=' . $CI->db->escape_str($staff_id);
    }

    return $whereUser;
}
