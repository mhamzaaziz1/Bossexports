<?php defined('BASEPATH') or exit('No direct script access allowed');

// ── Module & Menu ────────────────────────────────────────────────────────────
$lang['nedarimpay_menu_title']          = 'נדרים פיי';
$lang['nedarimpay_dashboard']           = 'לוח בקרה נדרים פיי';
$lang['nedarimpay_transactions']        = 'עסקאות';
$lang['nedarimpay_manual_charge']       = 'חיוב ידני';
$lang['nedarimpay_settings']            = 'הגדרות נדרים פיי';

// ── Invoice Pay Button (client area) ─────────────────────────────────────────
$lang['nedarimpay_pay_with_nedarim']    = 'שלם באמצעות נדרים פלוס';
$lang['nedarimpay_not_configured']      = 'נדרים פלוס לא הוגדר. אנא בקש מהמנהל להזין את מספר המוסד ואת ה-ApiValid בהגדרות שער התשלום.';

// ── Admin invoice "Copy Pay Link" button ─────────────────────────────────────
$lang['nedarimpay_copy_payment_link']   = 'העתק קישור תשלום נדרים';
$lang['nedarimpay_link_copied']         = 'קישור התשלום הועתק ללוח';
$lang['nedarimpay_link_copy_failed']    = 'לא ניתן להעתיק אוטומטית — העתק את הקישור ידנית';

// ── Dashboard KPIs ────────────────────────────────────────────────────────────
$lang['nedarimpay_total_transactions']  = 'סה"כ עסקאות';
$lang['nedarimpay_student_volume']      = 'תשלומי תלמידים';
$lang['nedarimpay_donation_volume']     = 'נפח תרומות';
$lang['nedarimpay_standing_orders']     = 'הוראות קבע';
$lang['nedarimpay_pending_alert']       = '%d עסקאות ממתינות לעיבוד.';
$lang['nedarimpay_failed_alert']        = '%d עסקאות נכשלו.';
$lang['nedarimpay_view_failed']         = 'הצג כישלונות';
$lang['nedarimpay_quick_actions']       = 'פעולות מהירות';
$lang['nedarimpay_recent_transactions'] = 'עסקאות אחרונות';
$lang['nedarimpay_view_all_transactions'] = 'הצג את כל העסקאות';
$lang['nedarimpay_no_transactions_yet'] = 'לא התקבלו עסקאות עדיין.';

// ── Receipt Types ─────────────────────────────────────────────────────────────
$lang['nedarimpay_type_student']        = 'תלמיד';
$lang['nedarimpay_type_donation']       = 'תרומה';
$lang['nedarimpay_receipt_series']      = 'סדרת מספרי קבלות';
$lang['nedarimpay_series_student_title']= 'סדרת תלמידים';
$lang['nedarimpay_series_student_desc'] = 'משמשת לשכר לימוד חודשי, דמי נסיעות וכל שאר תשלומי התלמידים.';
$lang['nedarimpay_series_donation_title']= 'סדרת תרומות';
$lang['nedarimpay_series_donation_desc']= 'סדרת מספרים נפרדת לחלוטין לתורמים. תבנית דוא"ל שונה.';
$lang['nedarimpay_prefix']              = 'קידומת';
$lang['nedarimpay_series_warning']      = 'אזהרה: שינוי קידומות לאחר שימוש בייצור יפגע ברצף המספרים.';
$lang['nedarimpay_student_payment']     = 'תשלום תלמיד';
$lang['nedarimpay_donation_payment']    = 'תרומה';

// ── Transaction Table Columns ─────────────────────────────────────────────────
$lang['nedarimpay_receipt_number']      = 'קבלה #';
$lang['nedarimpay_client_name']         = 'שם לקוח';
$lang['nedarimpay_type']                = 'סוג';
$lang['nedarimpay_amount']              = 'סכום';
$lang['nedarimpay_email']               = 'דוא"ל';
$lang['nedarimpay_phone']               = 'טלפון';
$lang['nedarimpay_email_sent']          = 'דוא"ל נשלח';
$lang['nedarimpay_date']                = 'תאריך';
$lang['nedarimpay_status_col']          = 'סטטוס';
$lang['nedarimpay_transaction_type']    = 'סוג עסקה';
$lang['nedarimpay_tashloumim']          = 'תשלומים';
$lang['nedarimpay_groupe']              = 'קבוצה';
$lang['nedarimpay_comments']            = 'הערות';
$lang['nedarimpay_card_last_4']         = '4 ספרות אחרונות של כרטיס';
$lang['nedarimpay_confirmation']        = 'אישור #';
$lang['nedarimpay_shovar']              = 'שובר';
$lang['nedarimpay_transaction_time']    = 'שעת עסקה';
$lang['nedarimpay_raw_payload']         = 'נתוני גולמיים (Debug)';
$lang['nedarimpay_transaction_info']    = 'פרטי עסקה';
$lang['nedarimpay_transaction_id_label']= 'מזהה עסקה בנדרים';
$lang['nedarimpay_transaction_detail']  = 'פרטי עסקה';
$lang['nedarimpay_transaction_not_found']= 'עסקה לא נמצאה.';

// ── Status Labels ─────────────────────────────────────────────────────────────
$lang['nedarimpay_status_processed']    = 'עובד';
$lang['nedarimpay_status_pending']      = 'ממתין';
$lang['nedarimpay_status_failed']       = 'נכשל';
$lang['nedarimpay_status_duplicate']    = 'כפול';

// ── Filters / Search ─────────────────────────────────────────────────────────
$lang['nedarimpay_filter_type']         = 'סוג';
$lang['nedarimpay_filter_status']       = 'סטטוס';
$lang['nedarimpay_date_from']           = 'מתאריך';
$lang['nedarimpay_date_to']             = 'עד תאריך';
$lang['nedarimpay_all_types']           = 'כל הסוגים';
$lang['nedarimpay_all_statuses']        = 'כל הסטטוסים';
$lang['nedarimpay_search_placeholder']  = 'שם / דוא"ל / קבלה #';
$lang['nedarimpay_no_transactions_found'] = 'לא נמצאו עסקאות התואמות את הסינון.';

// ── Settings ──────────────────────────────────────────────────────────────────
$lang['nedarimpay_credentials']         = 'פרטי חיבור לנדרים פלוס';
$lang['nedarimpay_mosad_number']        = 'מספר מוסד';
$lang['nedarimpay_mosad_help']          = 'מספר מוסד בן 7 ספרות מנדרים פלוס.';
$lang['nedarimpay_api_valid']           = 'טוקן API';
$lang['nedarimpay_api_key']             = 'מפתח / URL מלא של API';
$lang['nedarimpay_api_key_help']        = 'כתובת ה-API המלאה מנדרים פלוס (מהקישור שנשלח).';
$lang['nedarimpay_charge_api_url_label']= 'כתובת API לחיובים';
$lang['nedarimpay_charge_api_url_help'] = 'כתובת POST לחיובים מזדמנים. השאר ריק לשימוש בברירת מחדל: https://www.matara.pro/nedarimplus/online/';
$lang['nedarimpay_payment_mode']        = 'אמצעי תשלום ב-Perfex';
$lang['nedarimpay_select_payment_mode'] = '— בחר —';
$lang['nedarimpay_client_match_field']  = 'התאמה אוטומטית של לקוח לפי';
$lang['nedarimpay_match_field_help']    = 'שדה לאיתור לקוח Perfex מנתוני ה-Webhook.';
$lang['nedarimpay_groupe_filter']       = 'ערך קבוצה';
$lang['nedarimpay_groupe_filter_help']  = 'ערך הקבוצה בנדרים המזהה סוג זה. השאר ריק לזיהוי אוטומטי.';

// ── Webhook ───────────────────────────────────────────────────────────────────
$lang['nedarimpay_webhook_url_title']   = 'כתובת Webhook';
$lang['nedarimpay_webhook_note']        = 'שלח כתובת זו לתמיכה של נדרים פלוס:';
$lang['nedarimpay_webhook_instruction'] = 'בקש מהם להגדיר אותה עבור עסקאות רגילות וגם עבור הוראות קבע.';
$lang['nedarimpay_webhook_both_types']  = 'נקודת קצה זו מטפלת בתשלומים רגילים וגם בהגדרת הוראות קבע.';
$lang['nedarimpay_copy']                = 'העתק';
$lang['nedarimpay_copied']              = 'הועתק ללוח!';

// ── Email Templates ───────────────────────────────────────────────────────────
$lang['nedarimpay_email_templates']     = 'תבניות דוא"ל';
$lang['nedarimpay_email_subject']       = 'נושא';
$lang['nedarimpay_email_body']          = 'גוף (HTML מורשה)';
$lang['nedarimpay_email_placeholders']  = 'תגיות זמינות';
$lang['nedarimpay_view_receipt']        = 'הצג את הקבלה שלך';

// ── Email Actions ─────────────────────────────────────────────────────────────
$lang['nedarimpay_email_status']        = 'סטטוס דוא"ל';
$lang['nedarimpay_email_sent_ok']       = 'הדוא"ל נשלח בהצלחה';
$lang['nedarimpay_email_not_sent']      = 'הדוא"ל לא נשלח';
$lang['nedarimpay_resend_email']        = 'שלח שוב את הדוא"ל';
$lang['nedarimpay_email_resent']        = 'קבלת הדוא"ל נשלחה מחדש בהצלחה.';
$lang['nedarimpay_email_resend_failed'] = 'שליחת הדוא"ל מחדש נכשלה.';

// ── Invoice / Actions ─────────────────────────────────────────────────────────
$lang['nedarimpay_view_invoice']        = 'הצג חשבונית';
$lang['nedarimpay_view_detail']         = 'הצג פרטים';
$lang['nedarimpay_actions']             = 'פעולות';
$lang['nedarimpay_error']               = 'שגיאה';

// ── Manual Charge ─────────────────────────────────────────────────────────────
$lang['nedarimpay_charge_form_title']   = 'שלח חיוב לנדרים פלוס';
$lang['nedarimpay_charge_info']         = 'חיוב זה יועבר לכרטיס הלקוח בנדרים פלוס ויצורף לשורת החיוב החודשי הבאה שלו.';
$lang['nedarimpay_perfex_client']       = 'לקוח Perfex (אופציונלי)';
$lang['nedarimpay_select_client']       = '— בחר לקוח Perfex —';
$lang['nedarimpay_nedarim_client_id']   = 'מזהה לקוח בנדרים';
$lang['nedarimpay_nedarim_client_id_placeholder'] = 'ClientId בנדרים פלוס';
$lang['nedarimpay_nedarim_client_id_help'] = 'ה-ClientId מכרטיס הלקוח בנדרים פלוס.';
$lang['nedarimpay_currency']            = 'מטבע';
$lang['nedarimpay_tashlumim']           = 'תשלומים';
$lang['nedarimpay_tashlumim_help']      = 'מספר תשלומים (1 לחיוב חד-פעמי).';
$lang['nedarimpay_charge_type']         = 'סוג חיוב';
$lang['nedarimpay_charge_tuition']      = 'שכר לימוד חודשי';
$lang['nedarimpay_charge_travel']       = 'דמי נסיעה / טיול';
$lang['nedarimpay_charge_other']        = 'תשלום תלמיד אחר';
$lang['nedarimpay_charge_donation']     = 'תרומה';
$lang['nedarimpay_receipt_type']        = 'סדרת קבלות';
$lang['nedarimpay_groupe_placeholder']  = 'לדוגמה: tuition';
$lang['nedarimpay_description']         = 'תיאור';
$lang['nedarimpay_description_placeholder'] = 'לדוגמה: טיול לירושלים - מאי 2026';
$lang['nedarimpay_send_charge']         = 'שלח חיוב';
$lang['nedarimpay_confirm_charge']      = 'האם אתה בטוח שברצונך לשלוח חיוב זה לנדרים פלוס?';
$lang['nedarimpay_charge_sent_success'] = 'החיוב נשלח לנדרים פלוס בהצלחה.';
$lang['nedarimpay_charge_sent_error']   = 'שגיאה בשליחת החיוב לנדרים פלוס';
$lang['nedarimpay_charge_validation_error'] = 'נא למלא את כל השדות הנדרשים בערכים תקינים.';

// ── Payment Gateway (client invoice pay) ──────────────────────────────────────
$lang['nedarimpay_gw_mosad_number']              = 'מספר מוסד';
$lang['nedarimpay_gw_api_valid']                 = 'טוקן API';
$lang['nedarimpay_gw_iframe_url']                = 'כתובת בסיס iFrame';
$lang['nedarimpay_gw_default_receipt_type']      = 'סדרת קבלות ברירת מחדל לתשלומי חשבוניות';

// Pay page (client-facing iFrame)
$lang['nedarimpay_pay_title']                    = 'תשלום חשבונית %s';
$lang['nedarimpay_pay_invoice']                  = 'תשלום חשבונית %s';
$lang['nedarimpay_order_summary']                = 'סיכום הזמנה';
$lang['nedarimpay_amount_due']                   = 'סכום לתשלום';
$lang['nedarimpay_enter_payment_details']        = 'הזן פרטי תשלום';
$lang['nedarimpay_loading_payment_form']         = 'טוען טופס תשלום מאובטח…';
$lang['nedarimpay_payment_form']                 = 'טופס תשלום נדרים פיי';
$lang['nedarimpay_secure_payment_note']          = 'מאובטח על ידי נדרים פלוס. פרטי הכרטיס שלך לעולם אינם נשמרים בשרתים שלנו.';
$lang['nedarimpay_back_to_invoice']              = 'חזרה לחשבונית';
$lang['nedarimpay_powered_by']                   = 'מופעל על ידי';

// Verify / result messages
$lang['nedarimpay_payment_processing']           = 'התשלום שלך מעובד. תקבל קבלה בדוא"ל בקרוב.';
$lang['nedarimpay_payment_failed_or_cancelled']  = 'התשלום לא הושלם. נסה שוב או פנה אלינו.';
$lang['invoice_already_paid']                    = 'חשבונית זו כבר שולמה.';

// Manual charge how-to sidebar
$lang['nedarimpay_charge_howto_title']           = 'איך זה עובד';
$lang['nedarimpay_charge_step1']                 = 'הזן את ה-ClientId של הלקוח בנדרים פלוס.';
$lang['nedarimpay_charge_step2']                 = 'הגדר סכום, מטבע וסוג חיוב.';
$lang['nedarimpay_charge_step3']                 = 'לחץ שלח — החיוב מועבר לנדרים פלוס.';
$lang['nedarimpay_charge_step4']                 = 'נדרים פלוס מוסיף אותו למחזור החיוב החודשי הבא של הלקוח.';
$lang['nedarimpay_receipt_series_reminder']      = 'סדרת קבלות';
$lang['nedarimpay_receipt_type_auto_help']       = 'נקבע אוטומטית לפי סוג החיוב. ניתן לשנות ידנית.';

// Settings extra
$lang['nedarimpay_config_status']                = 'סטטוס תצורה';
$lang['nedarimpay_api_docs']                     = 'תיעוד API';
$lang['nedarimpay_preview']                      = 'תצוגה מקדימה';
$lang['nedarimpay_toggle']                       = 'הצג/הסתר';
$lang['nedarimpay_ids_panel']                    = 'מזהי נדרים / מערכת';
$lang['nedarimpay_confirm_resend']               = 'שלח שוב את דוא"ל הקבלה ללקוח זה?';

// ── מדריך התקנה ───────────────────────────────────────────────────────────────
$lang['nedarimpay_setup_guide_title']            = 'מדריך הגדרה';
$lang['nedarimpay_setup_guide_intro']            = 'עקוב אחר השלבים הבאים כדי לחבר את המערכת לחשבון נדרים פלוס שלך. כל התהליך אורך כחמש דקות.';

$lang['nedarimpay_setup_step1_title']            = 'קבל את פרטי ההתחברות מנדרים פלוס';
$lang['nedarimpay_setup_step1_body']             = 'פנה לתמיכה של נדרים פלוס ובקש: (1) את <strong>מספר המוסד</strong> — מספר בן 7 ספרות, (2) את <strong>טוקן ה-ApiValid</strong>, ו-(3) את <strong>כתובת מפתח ה-API המלאה</strong> שהם שולחים כקישור משותף. הכן פרטים אלה לפני שתמשיך.';

$lang['nedarimpay_setup_step2_title']            = 'הזן את פרטי ההתחברות';
$lang['nedarimpay_setup_step2_body']             = 'הדבק את מספר המוסד, טוקן ה-ApiValid וכתובת מפתח ה-API בלוח <em>פרטי חיבור לנדרים פלוס</em> משמאל. יש למלא את שלושת השדות כדי שהתשלומים יפעלו.';

$lang['nedarimpay_setup_step3_title']            = 'שלח את כתובת ה-Webhook לנדרים פלוס';
$lang['nedarimpay_setup_step3_body']             = 'העתק את הכתובת המוצגת בתיבת <em>כתובת Webhook</em> בפינה הימנית העליונה ושלח אותה לתמיכה של נדרים פלוס. בקש מהם לרשום אותה עבור <strong>עסקאות רגילות וגם עבור הוראות קבע</strong>. ללא שלב זה לא יגיעו תשלומים למערכת.';

$lang['nedarimpay_setup_step4_title']            = 'בחר אמצעי תשלום';
$lang['nedarimpay_setup_step4_body']             = 'בחר תחת איזה אמצעי תשלום ב-Perfex (לדוגמה "כרטיס אשראי" או "נדרים פלוס") יירשמו התשלומים הנכנסים. אם עדיין לא יצרת אחד, עבור אל <strong>הגדרות → אמצעי תשלום</strong> ולאחר מכן חזור לכאן.';

$lang['nedarimpay_setup_step5_title']            = 'הגדר קידומות למספרי קבלות';
$lang['nedarimpay_setup_step5_body']             = 'הגדר קידומת קצרה לכל סדרת קבלות — <strong>T</strong> לתשלומי תלמידים ו-<strong>D</strong> לתרומות הוא בחירה נפוצה. הקבלות ימוספרו <code>T-00001</code>, <code>T-00002</code>, וכן הלאה. <em>אין לשנות קידומות אלה לאחר שהתחלת לקבל תשלומים אמיתיים.</em>';

$lang['nedarimpay_setup_step6_title']            = 'שמור — וסיימת';
$lang['nedarimpay_setup_step6_body']             = 'לחץ על כפתור <strong>שמור</strong>. לוח סטטוס התצורה יציג סימן ירוק לכל שדה שמולא כראוי. כאשר מספר מוסד, טוקן ApiValid ואמצעי תשלום מציגים כולם סימן ירוק — המודול מוכן לקבל תשלומים.';

$lang['nedarimpay_setup_tip_title']              = 'טיפים שימושיים';
$lang['nedarimpay_setup_tip_email']              = '<strong>תבניות דוא"ל</strong> הן אופציונליות — המודול פועל גם בלעדיהן, אך הגדרתן מאפשרת ללקוחות לקבל דוא"ל קבלה ממותג לאחר כל תשלום.';
$lang['nedarimpay_setup_tip_groupe']             = '<strong>ערך קבוצה</strong> הוא אופציונלי. השאר ריק לזיהוי אוטומטי. מלא אותו רק אם נדרים פלוס שולחים קוד קבוצה ספציפי המזהה תשלומי תלמידים לעומת תרומות.';
$lang['nedarimpay_setup_tip_match']              = '<strong>התאמה אוטומטית של לקוח לפי</strong> קובע כיצד Webhook נכנס מקושר לרשומת לקוח ב-Perfex. <em>דוא"ל</em> הוא האפשרות האמינה ביותר אם ללקוחות שלך יש כתובות דוא"ל במערכת.';
$lang['nedarimpay_setup_need_help']              = 'זקוק לעזרה? פנה למנהל המערכת שלך או לצוות התמיכה של נדרים פלוס.';

// ── כפתור ומודל תשלום חשבונית ─────────────────────────────────────────────────
$lang['nedarimpay_record_payment_btn']           = 'נדרים פיי';
$lang['nedarimpay_record_payment_title']         = 'רישום תשלום נדרים';
$lang['nedarimpay_record_payment_invoice']       = 'חשבונית';
$lang['nedarimpay_record_payment_amount']        = 'סכום';
$lang['nedarimpay_record_payment_date']          = 'תאריך תשלום';
$lang['nedarimpay_record_payment_receipt_type']  = 'סדרת קבלות';
$lang['nedarimpay_record_payment_transaction_id']= 'מזהה עסקה בנדרים';
$lang['nedarimpay_record_payment_transaction_id_help'] = 'אופציונלי — הדבק את מזהה העסקה מנדרים פלוס אם זמין.';
$lang['nedarimpay_record_payment_note']          = 'הערה פנימית';
$lang['nedarimpay_record_payment_submit']        = 'רשום תשלום';
$lang['nedarimpay_record_payment_success']       = 'התשלום נרשם בהצלחה דרך נדרים פיי.';
$lang['nedarimpay_record_payment_fail']          = 'רישום התשלום נכשל. נסה שנית.';
$lang['nedarimpay_not_configured_title']         = 'נדרים פיי אינו מוגדר';
$lang['nedarimpay_not_configured_body']          = 'לפני שניתן לרשום תשלום נדרים, יש להשלים את הגדרת המודול: מלא את מספר המוסד, טוקן ה-API ובחר אמצעי תשלום.';
$lang['nedarimpay_not_configured_link']          = 'פתח הגדרות נדרים פיי';
