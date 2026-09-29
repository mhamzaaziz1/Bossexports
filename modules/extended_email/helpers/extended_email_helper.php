<?php

    defined('BASEPATH') || exit('No direct script access allowed');

    function get_all_staff_members()
    {
        $CI = &get_instance();
        $CI->db->where('admin', 0);

        return $CI->db->get(db_prefix().'staff')->result_array();
    }

    function is_staff_active($staffID)
    {
        $CI = &get_instance();
        if (empty($staffID)) {
            return false;
        }
        $CI->load->model('staff_model');
        $result = $CI->staff_model->get($staffID);
        if ($result->active==1) {
            return true;
        }
        return false;
    }

    function get_config_array($staffId)
    {
        $CI = &get_instance();
        $email_settings    = $CI->extended_email_model->get_staff_extended_email_settings($staffId);
        $config['has_setting']  = false;
        if (!empty($email_settings)) {
            /* set only smtp_pass & smtp_host proper set */
            if ('' != $email_settings->smtp_password && '' != $email_settings->smtp_host) {
                if (is_staff_active($staffId) || is_admin()) {
                    $config['has_setting']  = true;
                    $config['useragent']    = $email_settings->mail_engine;
                    $config['protocol']     = $email_settings->email_protocol;
                    $config['smtp_host']    = trim($email_settings->smtp_host);

                    if ('' == $email_settings->smtp_username) {
                        $config['smtp_user'] = trim($email_settings->email);
                    } else {
                        $config['smtp_user'] = trim($email_settings->smtp_username);
                    }

                    $config['smtp_pass']    = $CI->encryption->decrypt($email_settings->smtp_password);
                    $config['smtp_port']    = trim($email_settings->smtp_port);
                    $config['smtp_crypto']  = $email_settings->smtp_encryption;
                    $charset                = strtoupper($email_settings->email_charset);
                    $charset                = trim($charset);
                    if ('' == $charset || 'utf8' == strcasecmp($charset, 'utf8')) {
                        $charset = 'utf-8';
                    }
                    $config['mailtype'] = 'html';
                    $config['charset']  = $charset;
                }
            }
        }
        return $config;
    }

    /* End of file "extended_email_helper.".php */
