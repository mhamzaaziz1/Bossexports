<?php


// ----------------------
// ADMIN SETTINGS & DASHBOARD
// ----------------------
// Module Information
$lang['appointly_module_name'] = 'Appointly';
$lang['appointly_current_version'] = 'Huidige moduleversie: ';
$lang['appointly_settings_label_pointer'] = '<strong>Instellingen -> Afspraken</strong>';
$lang['appointly_integrations'] = 'Integraties';
$lang['general_settings'] = 'Algemene instellingen';
$lang['new_appointment_notification'] = 'Nieuwe afspraak vereist goedkeuring';
$lang['appointment_notification'] = 'U heeft een nieuwe afspraak toegewezen gekregen';
$lang['external_appointment_provider_notification'] = 'U bent toegewezen als provider voor een nieuwe externe afspraak';
$lang['appointment_datetime_changed'] = 'Afspraak datum/tijd is gewijzigd';
$lang['appointment_attendees_changed'] = 'Afspraak deelnemers zijn bijgewerkt';
$lang['appointment_details_changed'] = 'Afspraak details zijn bijgewerkt';
$lang['appointment_status_changed'] = 'Afspraak status is gewijzigd';
$lang['appointment_attendee_removed'] = 'Een deelnemer is verwijderd van de afspraak';

// Admin Settings
$lang['appointments_approve_automatically_label'] = 'Automatisch ingediende afspraken van externe webformulieren goedkeuren';
$lang['appointly_default_table_filter_label'] = 'Standaard afspraken tabel filter';
$lang['appointment_your_settings'] = 'Uw afspraakinstellingen';
$lang['appointments_buffer_hours_label'] = 'Datumkiezer voorbijgaande datums uitschakelen';

// Dashboard & Analytics
$lang['appointly_show_summary_in_appointments_dashboard'] = 'Afsprakenoverzicht weergeven in afspraken dashboard';
$lang['appointments_summary'] = 'Afsprakenoverzicht';
$lang['appointment_appointments_dashboard'] = 'Dashboard';
$lang['appointment_want_to_go_back'] = 'Afspraken';
$lang['appointly_analytics_dashboard'] = 'Appointly Analyse Dashboard';
$lang['appointment_history_label'] = 'Afgelopen afspraken';
$lang['appointment_history_label_menu_label'] = 'Afspraakgeschiedenis';
$lang['appointment_services_menu_label'] = 'Diensten';
$lang['appointment_analytics_and_reports_menu_label'] = 'Analyse & Rapporten';

// Invoice Conversion
$lang['appointment_convert_to_invoice'] = 'Converteren naar factuur';
$lang['appointment_converted_to_invoice'] = 'Afspraak succesvol geconverteerd naar factuur';
$lang['appointment_convert_to_invoice_success'] = 'Afspraak succesvol geconverteerd naar factuur #%s';
$lang['appointment_convert_to_invoice_failed'] = 'Mislukt om afspraak naar factuur te converteren';
$lang['appointment_are_you_sure_convert_to_invoice'] = 'Weet u zeker dat u deze afspraak naar een factuur wilt converteren?';
$lang['appointment_view_invoice'] = 'Bekijk factuur #%s';
$lang['appointment_convert_to_invoice_tooltip'] = 'Factuurcreatie is alleen beschikbaar voor afspraken met daadwerkelijke klanten. Voor leads of externe contacten moet u ze eerst converteren naar klanten.';
$lang['appointment_external_contact_converted_to_client'] = 'Extern contact succesvol geconverteerd naar klant';
$lang['appointment_external_contact_convert_to_client_error'] = 'Fout bij het converteren van extern contact naar klant';
$lang['appointment_convert_to_customer_first'] = 'Eerst converteren naar klant';
$lang['appointment_convert_lead_to_customer'] = 'Lead converteren naar klant';
$lang['appointment_convert_external_to_customer'] = 'Converteren naar klant';
$lang['appointment_convert_only'] = 'Alleen converteren';
$lang['appointment_convert_and_view'] = 'Converteren en bekijken';
$lang['appointment_convert_to_invoice_only_contacts_allowed'] = 'Alleen interne afspraken met contacten kunnen naar facturen worden geconverteerd';

// Lead Conversion
$lang['appointments_convert_to_lead'] = 'Afspraak converteren naar lead';
$lang['appointments_convert_to_lead_label'] = 'Converteren naar lead';
$lang['appointments_create_task_tooltip'] = 'Converteren naar taak';
$lang['appointments_convert_to_lead_tooltip'] = 'Converteren naar lead';
$lang['appointments_select_option'] = 'Optie selecteren';
$lang['appointments_contact_name_task'] = 'Afspraak: Contactnaam';

// Reports Dashboard
$lang['appointly_reports_dashboard'] = 'Appointly Rapporten Dashboard';
$lang['appointly_date_range'] = 'Datumbereik';
$lang['appointly_today'] = 'Vandaag';
$lang['appointly_this_week'] = 'Deze week';
$lang['appointly_this_month'] = 'Deze maand';
$lang['appointly_this_year'] = 'Dit jaar';
$lang['appointly_last_month'] = 'Vorige maand';
$lang['appointly_last_year'] = 'Vorig jaar';
$lang['appointly_last_30_days'] = 'Laatste 30 dagen';
$lang['appointly_custom_range'] = 'Aangepast bereik';
$lang['appointly_period_from'] = 'Periode van';
$lang['appointly_period_to'] = 'Periode tot';
$lang['appointly_date_range_required'] = 'Selecteer een geldig datumbereik';
$lang['appointly_apply'] = 'Toepassen';
$lang['appointly_total_appointments'] = 'Totaal afspraken';
$lang['appointly_completed_appointments'] = 'Voltooide afspraken';
$lang['appointly_cancelled_appointments'] = 'Geannuleerde afspraken';
$lang['appointly_monthly_stats'] = 'Maandelijkse statistieken';
$lang['appointly_popular_services'] = 'Populaire diensten';
$lang['appointly_staff_performance'] = 'Personeelsprestaties';
$lang['appointly_staff_member'] = 'Personeelslid';
$lang['appointly_appointment_total_appointments'] = 'Totaal afspraken';
$lang['appointly_appointment_completed_appointments'] = 'Voltooide afspraken';
$lang['appointly_appointment_cancelled_appointments'] = 'Geannuleerde afspraken';
$lang['appointly_appointment_completion_rate'] = 'Voltooiingspercentage';
$lang['appointly_filter'] = 'Filter';
$lang['appointly_no_staff_performance_data'] = 'Geen personeelsprestatie data beschikbaar voor de geselecteerde periode';
$lang['appointly_report'] = 'Rapport';
$lang['appointly_reports_updated_for_period'] = 'Rapporten bijgewerkt voor periode: %s tot %s';
$lang['appointly_no_data_for_period'] = 'Geen data beschikbaar voor de geselecteerde periode';
$lang['appointly_invalid_date_format'] = 'Ongeldig datumformaat. Gebruik de datumkiezer om datums te selecteren';
$lang['appointly_loading_data'] = 'Data laden...';
$lang['appointly_error_refreshing_stats'] = 'Fout bij het verversen van statistieken. Probeer opnieuw.';
$lang['appointly_no_data_found'] = 'Geen data gevonden';
$lang['appointly_staff_respect_availability'] = 'Personeel respecteert beschikbaarheid boekingsconflicten';
$lang['appointly_staff_respect_availability_tooltip'] = 'Wanneer ingeschakeld, kan personeel geen afspraken boeken tijdens hun bezette uren of bestaande conflicten';

// Analytics Dashboard (consolidated - removed duplicates)
$lang['total_appointments'] = 'Totaal afspraken';
$lang['completed_appointments'] = 'Voltooide afspraken';
$lang['cancelled_appointments'] = 'Geannuleerde afspraken';
$lang['monthly_statistics'] = 'Maandelijkse statistieken';
$lang['popular_services'] = 'Populaire diensten';
$lang['staff_performance'] = 'Personeelsprestaties';
$lang['staff_member'] = 'Personeelslid';
$lang['completion_rate'] = 'Voltooiingspercentage';
$lang['from_date'] = 'Van datum';
$lang['to_date'] = 'Tot datum';

$lang['appointment_services_to_show_in_booking_form'] = 'Selecteer diensten om weer te geven in het openbare boekingsformulier';
$lang['appointment_services_select_all_to_show_all'] = 'Leeg laten om alle actieve diensten weer te geven';
$lang['appointment_select_attendees'] = 'Deelnemers';
$lang['appointment_select_attendees_help'] = 'Selecteer extra personeelsleden die deze afspraak zullen bijwonen';
$lang['appointment_related'] = 'Gerelateerd';

// Service Availability Settings
$lang['services_availability_updated_successfully'] = 'Dienst beschikbaarheid succesvol bijgewerkt';
$lang['services_availability_update_failed'] = 'Mislukt om dienst beschikbaarheid bij te werken';
$lang['appointment_error_loading_providers'] = 'Fout bij het laden van providers';
$lang['appointment_select_service_warning'] = 'Selecteer een dienst';
$lang['appointment_select_provider_warning'] = 'Selecteer een provider';
$lang['appointment_select_date_time_warning'] = 'Selecteer een datum en tijd';
$lang['appointment_loading_providers'] = 'Providers laden...';
$lang['appointment_select_date'] = 'Selecteer datum';
$lang['appointment_your_details'] = 'Uw gegevens';
$lang['appointment_continue'] = 'Volgende';
$lang['appointment_back'] = 'Terug';
$lang['appointment_summary'] = 'Afspraak overzicht';
$lang['appointment_view_details'] = 'Details bekijken';
$lang['appointment_select'] = 'Selecteren';
$lang['error_loading_data'] = 'Fout bij het laden van data';
$lang['appointment_booking_failed'] = 'Afspraak boeken mislukt. Probeer opnieuw.';
$lang['appointment_successfully_scheduled'] = 'Afspraak succesvol ingepland';
$lang['appointment_successfully_scheduled_message'] = 'Bedankt! Uw afspraak is succesvol ingepland.';
$lang['appointment_successfully_scheduled_button'] = 'Geweldig!';
$lang['appointment_schedule_another'] = 'Nog een inplannen';
$lang['appointment_not_specified'] = 'Niet gespecificeerd';
$lang['appointment_booking_confirmed'] = 'Bedankt! Uw afspraak is succesvol ingepland.';
$lang['appointment_pending_approval_message'] = 'Uw afspraak wacht op goedkeuring door personeel. U wordt geïnformeerd zodra deze is bevestigd.';
$lang['appointment_whats_next'] = 'Wat gebeurt er nu?';
$lang['appointment_staff_review'] = 'Ons personeel bekijkt uw aanvraag. Wacht op bevestiging.';
$lang['appointment_email_confirmation'] = 'U ontvangt binnenkort een e-mailbevestiging met alle afspraakdetails.';
$lang['appointment_prepare'] = 'Bereid alle vereiste documenten of details voor uw afspraak voor.';
$lang['appointment_files'] = 'Bestanden';
$lang['appointment_externally_booked_info'] = 'Deze afspraak is geboekt via het externe boekingsformulier';
$lang['appointment_booked_from_external_booking_form'] = 'Geboekt vanaf';
$lang['appointment_subject_required'] = 'Onderwerp is vereist voor afspraak';
$lang['appointment_attendees_required'] = 'Minstens één personeelslid moet de afspraak bijwonen';
$lang['appointment_name_required'] = 'Naam is vereist voor externe afspraken';
$lang['appointment_email_required'] = 'E-mail is vereist voor externe afspraken';
$lang['appointment_email_invalid'] = 'Geef een geldig e-mailadres op';
$lang['appointment_contact_required'] = 'Selecteer een contact voor interne afspraak';
$lang['appointment_invalid_type'] = 'Ongeldig geselecteerd afspraaktype';
$lang['appointment_type_required'] = 'Selecteer een afspraaktype';
$lang['appointment_invalid_data'] = 'Ongeldige afspraakdata opgegeven';
$lang['appointment_could_not_be_created'] = 'Afspraak kon niet worden aangemaakt. Probeer opnieuw.';
$lang['appointment_unavailable_slots'] = 'Rode tijdslots zijn niet beschikbaar vanwege bestaande afspraken';
$lang['appointment_book_now_description'] = 'Boek nu uw afspraak en krijg de beste service van ons team';
$lang['appointment_book_now_button_description'] = 'Boek nu uw afspraak en krijg de beste service van ons team';
$lang['appointment_feature_coming_soon'] = 'Deze functie wordt beschikbaar in komende updates. Blijf op de hoogte!';
$lang['appointment_description_updated'] = 'Afspraak beschrijving succesvol bijgewerkt';
$lang['appointment_notes_updated'] = 'Afspraak notities succesvol bijgewerkt';
$lang['appointment_notes_update_failed'] = 'Mislukt om afspraak notities bij te werken';
$lang['appointment_update_failed'] = 'Mislukt om afspraak bij te werken';
$lang['appointment_description_edit_info'] = 'Klik om beschrijving te bewerken. Wijzigingen worden automatisch opgeslagen wanneer u klaar bent met bewerken.';
$lang['appointment_viewing_notes'] = 'Notities bekijken voor afspraak';
$lang['appointment_edit_history_notes'] = 'Notities bewerken';
$lang['appointment_email_missing'] = 'E-mailadres ontbreekt of is ongeldig voor deze afspraak';
$lang['appointment_no_name_provided'] = 'Geen naam opgegeven';
$lang['appointment_no_phone_provided'] = 'Geen telefoon opgegeven';
$lang['appointment_open_link'] = 'Link openen';
$lang['appointment_menu_form_link'] = 'Boekingsformulier';
$lang['external_form_heading'] = 'Boekingsformulier titel';
$lang['external_form_description'] = 'Boekingsformulier beschrijving';
$lang['appointment_date_location'] = 'Sessie details';
$lang['appointment_schedule_description'] = 'Vul het onderstaande formulier in om uw sessie met ons team te regelen';
$lang['appointment_preferred_date_time'] = 'Gewenste datum en tijd';
$lang['appointment_select_date_time'] = 'Selecteren...';
$lang['appointment_terms_description'] = 'Door verder te gaan, bevestigt u dat u akkoord gaat met onze';
$lang['appointment_terms_link'] = 'Algemene voorwaarden';
$lang['appointment_accept_terms'] = 'Ik erken en ga akkoord met de gebruiksvoorwaarden*';
$lang['appointly_recaptcha_enabled'] = 'Recaptcha inschakelen';
$lang['appointment_no_location_provided'] = 'Geen locatie opgegeven om Google Maps weer te geven';
$lang['appointment_imported_from_calendar'] = 'Geïmporteerd van %s';
$lang['appointment_imported_cant_edit_notice'] = 'Deze afspraak is geïmporteerd van een externe kalender en kan niet worden bewerkt.';
$lang['appointment_are_you_sure'] = 'Weet u zeker dat u deze afspraak wilt verwijderen?';
$lang['would_you_like_to_create_new_appointment_for_lead'] = 'Wilt u een nieuwe afspraak maken voor deze lead?';
$lang['would_you_like_to_create_new_appointment_for_client'] = 'Wilt u een nieuwe afspraak maken voor deze klant?';
$lang['would_you_like_to_create_new_appointment'] = 'Wilt u een nieuwe afspraak maken?';
$lang['no_appointments_found'] = 'Geen afspraken gevonden';

// ----------------------
// EXTERNAL BOOKING FORM
// ----------------------
$lang['appointment_available_days'] = 'Beschikbaar';
$lang['appointment_busy_days'] = 'Bezet (heeft afspraken)';
$lang['appointment_provider_unavailable'] = 'Provider niet beschikbaar';
$lang['appointment_blocked_days'] = 'Feestdag/Geblokkeerde bedrijfsdatum';
$lang['appointment_date_required'] = 'Selecteer eerst een datum';
$lang['appointment_select_time'] = 'Selecteer een tijd';
$lang['appointment_book_now'] = 'Nu boeken';
$lang['appointment_submitting'] = 'Afspraak boeken...';

// New appointment filter options
$lang['appointment_today'] = 'Afspraken van vandaag';
$lang['appointment_tomorrow'] = 'Afspraken van morgen';
$lang['appointment_this_week'] = 'Afspraken van deze week';
$lang['appointment_next_week'] = 'Afspraken van volgende week';
$lang['appointment_this_month'] = 'Afspraken van deze maand';
$lang['appointment_my_appointments'] = 'Mijn afspraken';
$lang['appointment_assigned_to_me'] = 'Toegewezen aan mij';


// ----------------------
// GENERAL/COMMON TERMS
// ----------------------
$lang['appointment_yes'] = 'Ja';
$lang['appointment_no'] = 'Nee';
$lang['appointment_appointments'] = 'Afspraken';
$lang['appointment_label'] = 'Afspraak';
$lang['wait_text'] = 'Even wachten...';
$lang['loading_text'] = 'Laden, even wachten...';
$lang['appointment_loading'] = 'Laden...';
$lang['unknown_error'] = 'Onbekende fout';
$lang['request_failed'] = 'Verzoek mislukt';
$lang['error_processing_response'] = 'Fout bij het verwerken van reactie';
$lang['invalid_appointment_id'] = 'Ongeldig afspraak ID';
$lang['appointment_select_option'] = 'Selecteer een optie';
$lang['appointly_note'] = 'Notitie';
$lang['appointment_optional'] = '<small> (optioneel) </small>';
$lang['required_field_missing'] = 'Veld is vereist';
$lang['appointly_required_field'] = 'Vereist veld';
$lang['settings_updated'] = 'Instellingen succesvol bijgewerkt';
$lang['appointment_settings_update_failed'] = 'Mislukt om instellingen bij te werken';

// Time and date terms
$lang['timezone'] = 'Tijdzone';
$lang['minutes'] = 'Minuten';
$lang['hours'] = 'Uren';
$lang['monday'] = 'Maandag';
$lang['tuesday'] = 'Dinsdag';
$lang['wednesday'] = 'Woensdag';
$lang['thursday'] = 'Donderdag';
$lang['friday'] = 'Vrijdag';
$lang['saturday'] = 'Zaterdag';
$lang['sunday'] = 'Zondag';
$lang['today'] = 'Vandaag';
$lang['this_week'] = 'Deze week';
$lang['this_month'] = 'Deze maand';
$lang['this_year'] = 'Dit jaar';
$lang['date_range'] = 'Datumbereik';
$lang['custom_range'] = 'Aangepast bereik';
$lang['filter'] = 'Filter';

// ----------------------
// APPOINTMENT BASICS
// ----------------------
$lang['appointment_back_to_appointments'] = 'Afspraken';
$lang['appointment_create_new_appointment'] = 'Consultatie inplannen';
$lang['appointment_select_contact'] = 'Selecteer contact';
$lang['appointment_new_appointment'] = 'Nieuwe afspraak maken';
$lang['appointment_edit_appointment'] = 'Afspraak bijwerken';
$lang['appointment_save_changes_btn_label'] = 'Wijzigingen opslaan';
$lang['appointment_subject'] = 'Vergadering onderwerp';
$lang['appointment_description'] = 'Sessie overzicht';
$lang['appointment_overview'] = 'Afspraak overzicht';
$lang['appointment_additional_info'] = 'Aanvullende informatie';
$lang['appointment_date'] = 'Datum';
$lang['appointment_time'] = 'Gewenste tijd';
$lang['appointment_date_and_time'] = 'Datum / Tijd';
$lang['appointment_date_time'] = 'Datum en tijd';
$lang['appointment_meeting_date'] = 'Afspraak datum';
$lang['appointment_meeting_time'] = 'Afspraak datum';
$lang['appointments_reminders_label'] = 'Herinneringen';
$lang['appointment_meeting_location'] = 'Locatie';
$lang['appointment_location'] = 'Locatie';
$lang['appointment_location_address'] = 'Locatie / Adres';
$lang['appointment_location_placeholder'] = 'Locatie details invoeren';
$lang['appointment_duration'] = 'Afspraak duur';
$lang['appointment_duration_label'] = 'Duur';
$lang['appointment_duration_help'] = 'Afspraak duur instellen in minuten';
$lang['appointment_notes'] = 'Notities';
$lang['appointment_private_notes'] = 'Privé notities';
$lang['appointment_client_notes'] = 'Afspraak notities';
$lang['appointment_created_by'] = 'Gemaakt door';
$lang['appointly_created_at'] = 'Gemaakt op';
$lang['appointment_schedule_details'] = 'Afspraak details';
$lang['appointment_additional_settings'] = 'Aanvullende instellingen';
$lang['appointment_updating'] = 'Bijwerken...';
$lang['appointment_requested_at'] = 'Aangevraagd op';

// ----------------------
// MISSING LANGUAGE VARIABLES - Added in v1.3.5
// ----------------------

// Button text
$lang['appointment_save'] = 'Opslaan';
$lang['appointment_cancel_btn'] = 'Annuleren';
$lang['appointment_delete_btn'] = 'Verwijderen';

// Status and general terms
$lang['appointment_unknown'] = 'Onbekend';
$lang['appointment_all_day'] = 'Hele dag';
$lang['appointment_to_be_confirmed'] = 'Te bevestigen';
$lang['appointment_timezone'] = 'Tijdzone';

// Error messages
$lang['appointment_error_loading_details'] = 'Fout bij het laden van afspraak details';
$lang['appointment_error_loading_contacts'] = 'Fout bij het laden van contacten';
$lang['appointment_unknown_error'] = 'Onbekende fout';
$lang['appointment_please_select_time_slot'] = 'Selecteer een beschikbaar tijdslot';
$lang['appointment_error_generating_html'] = 'Fout bij het genereren van HTML';
$lang['appointment_error_loading_blocked_days'] = 'Fout bij het laden van geblokkeerde dagen';
$lang['appointment_error_loading_invoice'] = 'Fout bij het laden van factuur';
$lang['appointment_error_loading_time_slots'] = 'Fout bij het laden van tijdslots';
$lang['appointment_no_html_generated'] = 'Geen HTML gegenereerd';
$lang['appointment_no_working_hours_data'] = 'Geen werkuren data beschikbaar';

// Form placeholders and labels
$lang['appointment_reschedule_reason_placeholder'] = 'Geef een reden op voor het verzetten van deze afspraak...';
$lang['appointment_new_date'] = 'Nieuwe datum';
$lang['appointment_new_time'] = 'Nieuwe tijd';

// Notification messages
$lang['appointment_marked_as_completed'] = 'Een afspraak is gemarkeerd als voltooid';
$lang['appointment_marked_as_cancelled'] = 'Een afspraak is gemarkeerd als geannuleerd';

// Removed console debug variables - not used in production code

// Appointment Status
$lang['appointment_status'] = 'Status';
$lang['appointment_status_text'] = 'Afspraak status';
$lang['appointment_status_pending'] = 'In behandeling';
$lang['appointment_status_in-progress'] = 'Bezig';
$lang['appointment_status_completed'] = 'Voltooid';
$lang['appointment_status_cancelled'] = 'Geannuleerd';
$lang['appointment_status_no-show'] = 'Niet verschenen';
$lang['appointment_upcoming'] = 'Aankomend';
$lang['appointment_finished'] = 'Afgerond';
$lang['appointment_ongoing'] = 'Bezig';
$lang['appointment_cancelled'] = 'Geannuleerd';
$lang['appointment_rescheduled'] = 'Verzet';
$lang['appointment_no_show'] = 'Niet verschenen';
$lang['appointment_missed_label'] = 'Gemist';
$lang['appointment_pending_approval'] = 'Wachten op goedkeuring';
$lang['appointment_not_approved'] = 'Wachten op goedkeuring';
$lang['appointment_pending_cancellation'] = 'Annulering in behandeling';
$lang['appointment_cancelled_text'] = 'Afspraak geannuleerd';
$lang['appointment_missed'] = 'Afspraak gemist (afspraak datum/tijd is voorbij)';
$lang['appointment_are_you_sure_mark_as_no_show'] = 'Weet u zeker dat u deze afspraak wilt markeren als niet verschenen?';
$lang['appointment_completed'] = 'Voltooid';
$lang['appointment_internal'] = 'Intern';
$lang['appointment_external'] = 'Extern';
$lang['appointment_lead_related'] = 'Lead';
$lang['appointment_internal_staff'] = 'Personeel';

// Status and Action Buttons
$lang['appointment_approve'] = 'Goedkeuren';
$lang['appointment_approved'] = 'Goedgekeurd';
$lang['appointment_mark_as_finished'] = 'Markeren als afgerond';
$lang['appointment_mark_as_ongoing'] = 'Markeren als bezig';
$lang['appointment_mark_as_cancelled'] = 'Geannuleerd';
$lang['appointment_mark_as_rescheduled'] = 'Markeren als verzet';
$lang['appointment_mark_as_no_show'] = 'Markeren als niet verschenen';
$lang['appointment_cancel'] = 'Afspraak annuleren';
$lang['appointment_description_to_cancel'] = 'Beschrijf waarom u deze afspraak wilt annuleren';
$lang['appointment_describe_reason_for_cancel'] = 'Beschrijving is vereist. Beschrijf wat de reden is voor het annuleren van de afspraak?';
$lang['appointment_request_to_cancel'] = 'Annulering aanvragen';
$lang['appointment_request_cancellation'] = 'Annulering aanvragen';
$lang['appointment_approve_cancellation'] = 'Annulering goedkeuren';
$lang['appointment_marked_as_no_show'] = 'Gemarkeerd als niet verschenen';
$lang['appointly_are_you_sure_mark_as_no_show'] = 'Weet u zeker dat u deze afspraak wilt markeren als niet verschenen?';

$lang['appointment_you_have_new_appointment'] = 'U heeft een nieuwe afspraak';
$lang['appointment_initiated_by'] = 'Organisator';
$lang['appointment_select_single_contact'] = 'Contact';
$lang['appointment_deleted'] = 'Afspraak succesvol verwijderd';
$lang['appointment_created'] = 'Nieuwe afspraak succesvol aangemaakt';
$lang['appointment_updated'] = 'Afspraak succesvol bijgewerkt';
$lang['appointment_appointment_approved'] = 'Afspraak succesvol goedgekeurd!';
$lang['appointment_no_appointments'] = 'U heeft geen afspraken voor vandaag';
$lang['appointment_please_wait'] = 'Even wachten...';
$lang['appointment_no_assigned_staff_found'] = 'Geen toegewezen deelnemers gevonden voor deze afspraak';
$lang['appointment_cancel_notification'] = 'Klant heeft annulering van afspraak aangevraagd';
$lang['appointment_marked_as_finished'] = 'Afspraak gemarkeerd als afgerond';
$lang['appointment_todays_appointments'] = 'Afspraken van vandaag';
$lang['appointment_scheduled_at'] = 'Ingepland op:';
$lang['appointment_view_meeting'] = 'Afspraak bekijken';
$lang['appointment_edit_meeting'] = 'Afspraak bewerken';
$lang['appointment_dismiss_meeting'] = 'Afspraak verwijderen';
$lang['appointment_not_exists'] = 'Afspraak niet gevonden, doorverwijzen naar afsprakenlijst';
$lang['appointment_marked_as_ongoing'] = 'Afspraak gemarkeerd als bezig';
$lang['appointment_general_info'] = 'Klant details';
$lang['appointment_general_details'] = 'Afspraak details';
$lang['appointment_source'] = 'Type';
$lang['appointment_source_external_text'] = 'Extern (Contact)';
$lang['appointment_source_external'] = 'Bron (Extern contact)';
$lang['appointment_source_external_contact'] = 'Extern contact';
$lang['appointment_source_internal'] = 'Intern (Contact)';
$lang['appointment_lead_required'] = 'Selecteer een lead voor de afspraak';
$lang['appointment_source_internal_client'] = 'Intern (Contact)';
$lang['appointment_source_internal_staff'] = 'Intern (Personeel)';
$lang['appointment_source_lead'] = 'Lead';
$lang['appointment_staff_only'] = 'Alleen personeel';
$lang['appointment_phone'] = 'Telefoon';
$lang['appointment_name'] = 'Naam';
$lang['appointment_email'] = 'E-mail';
$lang['appointment_contact'] = 'Klant details';
$lang['appointment_sent_successfully'] = 'Uw nieuwe afspraak aanvraag is succesvol verzonden, u wordt per e-mail geïnformeerd wanneer uw afspraak is goedgekeurd';
$lang['appointment_squeduled_at_text'] = 'Afspraak ingepland om te beginnen op';
$lang['appointment_staff_attendees'] = 'Deelnemers';
$lang['appointment_is_approved'] = 'Afspraak is goedgekeurd!';
$lang['appointment_public_url'] = 'Openbare URL';
$lang['appointment_is_cancelled'] = 'Afspraak is geannuleerd!';
$lang['appointment_cancel_notes'] = 'Annulering notities';
$lang['appointment_full_name'] = 'Klant naam';
$lang['appointment_your_email'] = 'Uw e-mail';
$lang['appointment_your_phone'] = 'Telefoon (met landcode)';
$lang['appointment_your_phone_example'] = '+1 69 1234 5678';
$lang['appointment_submit'] = 'Verzenden';
$lang['appointment_no_staff_members'] = 'Geen personeelsleden gevonden, u moet een personeelslid toevoegen en deze weergave herladen om een persoon te selecteren voor nieuwe afspraken die via het iFrame formulier worden ingediend.';
$lang['appointment_cancellation_description_label'] = 'Reden voor annulering';
$lang['appointments_thank_you_cancel_request'] = 'Bedankt voor uw annulering aanvraag. We zullen dit binnenkort beoordelen.';
$lang['appointments_already_applied_for_cancelling'] = 'U heeft al annulering voor deze afspraak aangevraagd.';
$lang['appointment_pending_cancellations'] = 'Aanvragen voor annulering in behandeling';
$lang['appointment_requested_by'] = 'Aangevraagd door';
$lang['appointment_cancellation_approved'] = 'Annulering aanvraag succesvol goedgekeurd';
$lang['appointly_schedule_new_appointment'] = 'Afspraak inplannen';
$lang['appointments_total_found'] = 'Totaal afspraken';
$lang['appointments_admin_label'] = 'Beheerder';
$lang['appointments_staff_label'] = 'Personeel';
$lang['appointments_no_delete_permissions'] = 'Deze afspraak is niet door u aangemaakt, kan niet worden verwijderd';
$lang['appointment_source_external_clients_area'] = 'Bron (Vanuit klanten gebied bestaand contact)';
$lang['appointments_source_external_label'] = 'Extern';
$lang['appointments_source_internal_label'] = 'Intern';
$lang['appointments_individual_contact'] = ' (Individueel contact)';
$lang['appointments_company_for_select'] = ' - Klant ';
$lang['appointment_preview_url_label'] = 'Voorbeeld';
$lang['appointment_booking_form_services'] = 'Boekingsformulier diensten';
$lang['appointment_source_leads_label'] = 'Leads';
$lang['appointment_connect'] = 'Verbinden';
$lang['appointment_connected'] = 'Verbonden';
$lang['appointments_outlook_revoke_confirm'] = 'Uitloggen van Outlook';
$lang['appointment_selected_service'] = 'Geselecteerde dienst';
$lang['appointment_please_enter_your_details'] = 'Voer uw gegevens in';
$lang['appointments_request_feedback_from_client'] = 'Feedback van klant vragen';
$lang['appointments_request_feedback'] = 'Feedback vragen';
$lang['appointment_feedback_label'] = 'Feedback';
$lang['appointment_view_feedback'] = 'Feedback bekijken';
$lang['appointment_feedback_label_added'] = 'Bedankt voor uw feedback!';
$lang['appointment_feedback_label_current'] = 'Uw huidige feedback!';
$lang['appointments_feedback_info'] = 'Uw standaard feedback statussen beheren';
$lang['ap_feedback_extremely_good'] = 'Uitstekend';
$lang['ap_feedback_very_good'] = 'Zeer goed';
$lang['ap_feedback_good'] = 'Goed';
$lang['ap_feedback_not_bad'] = 'Niet slecht';
$lang['ap_feedback_bad'] = 'Slecht';
$lang['ap_feedback_the_worst'] = 'Het slechtste';
$lang['ap_feedback_not_sure'] = 'Niet zeker';
$lang['appointment_feedback_title'] = 'Laat uw feedback achter voor deze afspraak';
$lang['appointmenet_feedback_comment'] = 'Uw opmerkingen en gedachten over deze afspraak: ';
$lang['appointment_feedback_comment_textarea'] = 'Minstens een paar woorden zijn vereist om uw ervaring met deze afspraak te beschrijven';
$lang['appointment_feedback_comment_textarea_info'] = 'Beschrijf uw ervaring met deze afspraak';
$lang['appointment_new_feedback_added'] = 'U heeft nieuwe feedback voor een afspraak';
$lang['appointly_feedback_updated'] = 'Feedback beoordeling is bijgewerkt';
$lang['appointment_email_tracking'] = 'E-mail tracking (is afspraak e-mail gelezen)';
$lang['appointment_feedback_requested_alert'] = 'Feedback succesvol aangevraagd, u wordt per e-mail geïnformeerd als feedback wordt gegeven!';
$lang['appointment_click_to_change_rating'] = 'Klik op sterren om uw beoordeling bij te werken';
$lang['appointment_staff_cant_provide_feedback'] = 'Personeelsleden kunnen geen feedback geven voor afspraken';
$lang['appointment_leave_feedback'] = 'Beoordeel uw ervaring met deze afspraak';
$lang['appointment_your_feedback'] = 'Uw feedback';
$lang['appointment_no_feedback_provided'] = 'Klant heeft nog geen feedback gegeven voor deze afspraak';
$lang['appointments_are_you_sure_request_feedback'] = 'Weet u zeker dat u feedback wilt vragen voor deze afspraak? Er wordt een e-mail naar de klant gestuurd.';

// Client Area
$lang['appointly_allow_non_logged_clients_appointment'] = 'Niet-ingelogde klanten toestaan om nieuwe afspraken aan te vragen via het externe boekingsformulier';
$lang['appointly_show_appointments_menu_item_in_clients_area'] = 'Afspraak aanvraag menu-item weergeven in klanten gebied (wanneer ingelogd)';
$lang['appointments_applies_for_clients'] = '(geldt alleen voor klanten)';

// ----------------------
// TIME SLOTS & AVAILABILITY
// ----------------------
$lang['appointly_no_providers_for_service'] = 'Geen providers beschikbaar voor deze dienst';
$lang['appointly_no_providers_with_hours'] = 'Geen providers met werkuren beschikbaar voor deze dienst';
$lang['appointly_select_staff'] = 'Personeelslid selecteren';
$lang['appointment_busy_hours'] = 'Bezette uren';
$lang['appointment_available_hours'] = 'Beschikbare uren';
$lang['appointment_meeting_hour_is_reserved'] = 'Afspraak uur is al gereserveerd';
$lang['appointment_requested_hour'] = 'Aangevraagd vergader uur';
$lang['appointment_time_unavailable'] = 'Deze tijdslot is niet beschikbaar';
$lang['appointment_date_blocked'] = 'Deze datum is geblokkeerd door de beheerder';
$lang['appointly_available_time_slots'] = 'Beschikbare tijdslots';
$lang['appointment_available_time_slots'] = 'Beschikbare tijdslots';
$lang['appointment_no_slots_available'] = 'Geen tijdslots beschikbaar voor deze dag';
$lang['appointment_slot_already_booked'] = 'Deze tijdslot is al geboekt';
$lang['appointment_unavailable_slots_shown'] = 'Niet-beschikbare tijdslots worden in rood weergegeven en kunnen niet worden geselecteerd';
$lang['appointment_all_slots_booked'] = 'Alle tijdslots voor deze dag zijn geboekt. Probeer een andere datum.';
$lang['appointment_slot_unavailable'] = 'Deze tijdslot is niet beschikbaar';
$lang['appointment_checking_availability'] = 'Beschikbaarheid controleren...';
$lang['appointment_checking_time_slots'] = 'Tijdslots laden...';
$lang['appointment_error_loading_slots'] = 'Fout bij het laden van tijdslots. Probeer opnieuw.';
$lang['appointment_not_available'] = 'Niet beschikbaar';
$lang['appointment_available'] = 'Beschikbaar';
$lang['appointment_available_date_tooltip'] = 'Deze datum is beschikbaar voor boekingen';
$lang['appointment_blocked_date_tooltip'] = 'Deze datum is geblokkeerd en niet beschikbaar voor boekingen';
$lang['appointment_past_date_tooltip'] = 'Voorbije datums kunnen niet worden geselecteerd';

// Schedule and calendar
$lang['appointment_recurring'] = 'Terugkerend';
$lang['appointment_recurring_re_created'] = 'Terugkerende afspraak opnieuw aangemaakt';
$lang['appointments_all_day_event'] = 'Hele dag evenement';
$lang['select_blocked_days'] = 'Dagen selecteren';
$lang['appointments_blocked_days_on_calendar_title'] = 'Geblokkeerde dagen<br><small class="text-muted">Selecteer datums waarop afspraken niet kunnen worden ingepland (feestdagen, bedrijfssluitingen, enz.).<br> Deze datums zijn niet beschikbaar voor interne en externe boekingen.</small>';
$lang['appointments_dates_blocked_info_text'] = 'Geselecteerde datums zijn niet beschikbaar in de boekingskalender. Vergaderingen kunnen niet intern of extern op deze datums worden ingepland.';
$lang['appointments_blocked_days_tab_title'] = 'Niet-werkdagen';
$lang['appointments_blocked_days_on_calendar_info'] = 'Geblokkeerde dagen kalender informatie';

// Working Hours and Schedules
$lang['appointments_default_hours_label'] = 'Uw standaard afspraak uren beheren';
$lang['appointly_company_schedule'] = 'Bedrijfsrooster';
$lang['appointly_company_schedule_info'] = 'Configureer de standaard werkuren van uw bedrijf. Deze uren worden gebruikt voor alle personeelsleden, tenzij zij hun eigen specifieke werkuren hebben.';
$lang['appointly_manage_company_schedule'] = 'Bedrijfsrooster beheren';
$lang['appointly_staff_working_hours'] = 'Personeel werkuren';
$lang['appointly_staff_working_hours_info'] = 'Configureer de werkuren voor dit personeelslid. Deze uren overschrijven het bedrijfsrooster wanneer dit personeelslid is geselecteerd als provider.';
$lang['appointly_view_staff_schedule'] = 'Personeelsrooster bekijken';
$lang['appointly_day'] = 'Dag';
$lang['appointly_enabled'] = 'Ingeschakeld';
$lang['appointly_available'] = 'Beschikbaar';
$lang['appointly_start_time'] = 'Starttijd';
$lang['appointly_end_time'] = 'Eindtijd';
$lang['appointly_use_company_schedule'] = 'Bedrijfsrooster gebruiken';
$lang['appointly_use_company_schedule_tooltip'] = 'Aanvinken om bedrijfsrooster instellingen voor deze dag te gebruiken in plaats van aangepaste instellingen.';
$lang['appointly_day_monday'] = 'Maandag';
$lang['appointly_day_tuesday'] = 'Dinsdag';
$lang['appointly_day_wednesday'] = 'Woensdag';
$lang['appointly_day_thursday'] = 'Donderdag';
$lang['appointly_day_friday'] = 'Vrijdag';
$lang['appointly_day_saturday'] = 'Zaterdag';
$lang['appointly_day_sunday'] = 'Zondag';
$lang['appointly_at_least_one_day_required'] = 'Minstens één dag moet ingeschakeld zijn';
$lang['appointly_no_working_hours_found'] = 'Geen werkuren gedefinieerd voor deze provider';
$lang['appointly_closed'] = 'Gesloten';
$lang['appointly_working_hours'] = 'Werkuren';
$lang['company_schedule_time_intervals_note'] = 'Tijden kunnen alleen ingesteld worden in intervallen van 15 minuten (bijv. 09:00, 09:15, 09:30, 09:45)';
$lang['working_hours_time_intervals_note'] = 'Tijdslots zijn beschikbaar in intervallen van 15 minuten';
$lang['appointly_time_error'] = 'Starttijd moet eerder zijn dan eindtijd voor';
$lang['appointly_company_schedule_sync_help'] = 'Personeel kan de optie "Bedrijfsrooster gebruiken" gebruiken om hun uren te synchroniseren met bedrijfsstandaarden.';

// Buffer settings
$lang['appointly_settings_buffer_times'] = 'Buffer tijden tussen afspraken inschakelen';
$lang['appointly_settings_buffer_times_info'] = 'Buffer tijden zorgen voor overgangsperiodes tussen afspraken';
$lang['appointly_buffer_before'] = 'Buffer voor (minuten)';
$lang['appointly_buffer_after'] = 'Buffer na (minuten)';
$lang['appointly_buffer_before_help'] = 'Extra tijd voor afspraak om voor te bereiden';
$lang['appointly_buffer_after_help'] = 'Extra tijd na afspraak om op te ruimen';

// ----------------------
// SERVICES & PROVIDERS
// ----------------------
$lang['appointly_services'] = 'Diensten';
$lang['service'] = 'Dienst';
$lang['appointment_service'] = 'Dienst';
$lang['appointment_services'] = 'Diensten';
$lang['appointment_select_service'] = 'Dienst selecteren';
$lang['appointment_service_duration'] = 'Duur';
$lang['appointment_service_price'] = 'Prijs';
$lang['appointment_service_description'] = 'Beschrijving';
$lang['appointments_service_heading'] = 'Dienst';
$lang['appointments_staff_heading'] = 'Personeel';
$lang['service_selection_required'] = 'Selecteer een dienst';
$lang['appointment_service_required'] = 'Selecteer een dienst voor de afspraak';
$lang['appointly_service_selection_label'] = 'Dienst';
$lang['appointments_selected_service'] = 'Geselecteerde dienst';
$lang['no_services_available'] = 'Geen diensten beschikbaar';

// Service Creation and Management
$lang['appointly_new_service'] = 'Nieuwe dienst';
$lang['appointly_edit_service'] = 'Dienst bewerken';
$lang['appointly_service_add_success'] = 'Dienst succesvol toegevoegd';
$lang['appointly_service_edit_success'] = 'Dienst succesvol bijgewerkt';
$lang['appointly_service_delete_success'] = 'Dienst succesvol verwijderd';
$lang['appointly_service_delete_error'] = 'Kan dienst niet verwijderen';
$lang['error_adding_service'] = 'Kan dienst niet toevoegen';
$lang['error_updating_service'] = 'Kan dienst niet bijwerken';
$lang['service_delete_error_active'] = 'Dienst is actief en kan niet worden verwijderd of uitgeschakeld.';
$lang['appointly_service_in_use_warning'] = 'Deze dienst wordt momenteel gebruikt in een of meer afspraken en kan niet worden verwijderd of uitgeschakeld.';

// Service Properties
$lang['service_availability_days'] = 'Beschikbare dagen';
$lang['service_hours_start'] = 'Werkuren start';
$lang['service_hours_end'] = 'Werkuren eind';
$lang['appointly_service_name'] = 'Naam';
$lang['appointly_service_name_label'] = 'Dienst naam';
$lang['appointly_service_duration'] = 'Duur';
$lang['appointly_service_price'] = 'Prijs';
$lang['appointly_service_color'] = 'Kleur';
$lang['appointly_service_description'] = 'Beschrijving';
$lang['appointly_service_active'] = 'Actief';
$lang['appointly_service_details'] = 'Dienst details';
$lang['appointly_service_back_to_list'] = 'Terug naar diensten lijst';
$lang['appointly_duration_validation'] = 'Duur moet in intervallen van 15 minuten zijn (15, 30, 45, enz.) en maximaal 480 minuten';
$lang['appointly_price_validation'] = 'Prijs kan niet negatief zijn';
$lang['appointly_duration_minutes'] = 'minuten';
$lang['appointly_service_staff'] = 'Toegewezen aan provider/personeel';
// Service Validation
$lang['service_name_required'] = 'Dienst naam is vereist';
$lang['service_duration_required'] = 'Dienst duur is vereist';
$lang['service_duration_numeric'] = 'Duur moet een getal zijn';
$lang['service_duration_greater'] = 'Duur moet groter zijn dan 0';
$lang['service_price_required'] = 'Dienst prijs is vereist';
$lang['service_price_greater_equal'] = 'Prijs moet groter of gelijk zijn aan 0';
$lang['service_days_required'] = 'Selecteer minstens één beschikbare dag';
$lang['service_hours_required'] = 'Dienst uren zijn vereist';
$lang['service_hours_invalid'] = 'Ongeldig tijd formaat';
$lang['service_hours_start_end'] = 'Eindtijd moet later zijn dan starttijd';
$lang['appointly_staff_required'] = 'Selecteer een personeelslid';
$lang['appointly_working_hours_required'] = '%s werkuren zijn vereist.';
$lang['appointly_working_hours_invalid'] = '%s eindtijd moet later zijn dan starttijd.';
$lang['appointly_working_hours_at_least_one'] = 'Minstens één dag moet ingeschakeld zijn.';

// Service Table Headers
$lang['service_th_name'] = 'Naam';
$lang['service_th_duration'] = 'Duur (minuten)';
$lang['service_th_price'] = 'Prijs';
$lang['service_th_availability'] = 'Beschikbaarheid';
$lang['service_th_status'] = 'Status';
$lang['service_th_options'] = 'Opties';

// Service Status
$lang['service_status_active'] = 'Actief';
$lang['service_status_inactive'] = 'Inactief';
$lang['service_status_changed_success'] = 'Dienst status succesvol bijgewerkt';
$lang['service_status_changed_error'] = 'Kan dienst status niet bijwerken';
$lang['service_toggle_active'] = 'Actieve status aan/uit';
$lang['error_updating_status'] = 'Fout bij het bijwerken van status. Probeer opnieuw.';
$lang['service_status_updated'] = 'Dienst status succesvol bijgewerkt';
$lang['service_status_update_failed'] = 'Kan dienst status niet bijwerken';

// Providers
$lang['appointment_provider'] = 'Provider';
$lang['appointly_provider'] = 'Provider';
$lang['service_provider_loading'] = 'Beschikbare providers laden...';
$lang['service_no_providers'] = 'Geen providers beschikbaar voor deze dienst';
$lang['service_provider_select'] = 'Provider selecteren';
$lang['appointment_select_provider'] = 'Provider selecteren';
$lang['appointly_select_provider'] = 'Provider selecteren';
$lang['appointly_no_staff'] = 'Geen personeel beschikbaar';
$lang['appointly_error_loading_schedule'] = 'Fout bij het laden van provider rooster';
$lang['appointment_no_provider_assigned'] = 'Geen provider toegewezen';
$lang['appointly_meeting_location'] = 'Vergader locatie';

// Multiple Providers
$lang['appointly_settings_multi_providers'] = 'Meerdere providers per dienst inschakelen';
$lang['appointly_settings_multi_providers_info'] = 'Toestaan dat diensten door meerdere personeelsleden worden geleverd';
$lang['appointly_primary_provider'] = 'Primaire provider';
$lang['appointly_add_provider'] = 'Provider toevoegen';
$lang['appointly_remove_provider'] = 'Provider verwijderen';
$lang['appointly_service_providers'] = 'Dienst providers';
$lang['appointly_confirm_provider_removal'] = 'Weet u zeker dat u deze provider wilt verwijderen?';
$lang['appointly_assigned_providers'] = 'Toegewezen providers';
$lang['appointly_service_primary_provider'] = 'Primaire provider';
$lang['appointment_external_provider'] = 'Externe provider';
$lang['appointment_provider_assigned'] = 'Provider toegewezen aan afspraak';
$lang['appointment_provider_removed'] = 'Provider verwijderd van afspraak';

// ----------------------
// NOTIFICATIONS & REMINDERS
// ----------------------
$lang['appointment_modal_notification_info'] = 'Vink de vakjes aan als u wilt dat geselecteerde deelnemers en contact herinneringen ontvangen, bijvoorbeeld als ingesteld 30 minuten voor het begin van de afspraak. Let op dat deze functie cron taak configuratie vereist.';
$lang['appointment_sms_notification_text'] = 'SMS meldingen verzenden';
$lang['appointment_email_notification_text'] = 'E-mail meldingen verzenden';
$lang['appointment_send_notification'] = 'Melding nu verzenden';
$lang['appointment_notified'] = 'Afspraak herinneringen';
$lang['appointment_notified_by_sms'] = 'SMS herinnering melding geactiveerd';
$lang['appointment_notified_by_email'] = 'E-mail herinnering melding geactiveerd';
$lang['appointment_send_early_reminders_label'] = 'Vroege herinneringen verzenden';
$lang['appointly_are_you_early_reminders'] = 'Weet u zeker dat u vroege afspraak herinneringen wilt verzenden?';
$lang['appointly_reminders_sent'] = 'Afspraak herinneringen verzonden naar alle deelnemers en contact';
$lang['appointment_manually_send_reminders_info'] = 'Handmatig melding herinneringen naar alle deelnemers verzenden';
$lang['appointment_early_reminders_notice_label'] = 'Afspraak is niet geannuleerd of voltooid om vroege herinneringen te verzenden';
$lang['appointment_email_read_at'] = 'Gelezen op: ';
$lang['appointment_email_not_read'] = 'Niet gelezen';
$lang['appoontment_sms_notification'] = 'SMS melding';
$lang['appoontment_email_notification'] = 'E-mail melding';

// ----------------------
// CALENDAR INTEGRATIONS
// ----------------------
// Google Calendar
$lang['appointly_calendar_integrations'] = 'Kalender integraties';
$lang['appointment_add_to_google_calendar'] = 'Toevoegen aan Google Agenda';
$lang['appointments_google_already_signed'] = 'U bent al ingelogd op uw Google account.';
$lang['appointments_added_to_google_calendar'] = 'Toegevoegd aan Google Agenda';
$lang['appointments_sign_in_google'] = 'Inloggen met Google';
$lang['appointments_google_revoke_confirm'] = 'Uitloggen van Google';
$lang['appointments_google_revoke'] = 'Huidige Google Agenda sessie intrekken en alle verleende toestemmingen aan uw Google account verwijderen.';
$lang['appointments_google_calendar_client_id'] = 'Google Agenda API Client ID <strong>(opgehaald van Instellingen->Google->API CLIENT ID)</strong>';
$lang['appointments_google_calendar_settings'] = 'Google Agenda API instellingen';
$lang['appointments_google_calendar_client_secret'] = 'Google Agenda API Client Secret';
$lang['appointments_redirect_url'] = 'Google autorisatie redirect URI';
$lang['appointly_show_google_appointments_from'] = 'Afspraken filteren op datumbereik:';
$lang['appointments_delete_from_google_label'] = 'Voor het verwijderen van afspraak, ook de afspraak verwijderen die is aangemaakt in uw Google Agenda <small>(Geldt alleen als Google Agenda is ingeschakeld en gesynchroniseerd)</small>';
$lang['appointment_add_to_google_calendar_external'] = 'Deze externe afspraak opnemen in uw Google Agenda (Aanvinken en klikken op Opslaan)';
$lang['appointment_open_google_calendar'] = 'Openen in Google Agenda';
$lang['appointment_google_not_added_yet'] = 'Het lijkt erop dat deze afspraak niet is toegevoegd aan een personeelslid Google Agenda. Wilt u deze afspraak toevoegen aan uw Google Agenda?';
$lang['appointment_add_to_calendar'] = 'Toevoegen aan kalender';
$lang['appointment_view_in_calendar'] = 'Bekijken in Google Agenda';
$lang['appointment_calendar_adding_to_google'] = 'Toevoegen aan Google Agenda...';
$lang['appointment_error_adding_to_calendar'] = 'Kan niet toevoegen aan kalender. Probeer opnieuw.';
$lang['event_not_found_in_google'] = 'Evenement bestaat mogelijk niet in Google Agenda';
$lang['appointments_delete_from_google_calendar'] = 'Verwijderen van Google Agenda';
$lang['appointments_synced_from_google'] = 'Gesynchroniseerd van Google';
$lang['appointments_googlesync_show_in_table_label'] = 'Als Google Agenda integratie actief is, alle Google Agenda afspraken weergeven in standaard tabel weergave.';
$lang['appointly_google_synced_title'] = ' Gesynchroniseerd met Google';
$lang['appointment_hide_google_calendar'] = 'Standaard weergave tonen';
$lang['appointment_google_calendar_synced'] = 'Google Agenda gesynchroniseerd';
$lang['appointment_googlesync_only_today'] = 'Vandaag';
$lang['appointment_googlesync_only_last_month'] = 'Vorige maand';
$lang['appointment_googlesync_only_last_three_months'] = 'Laatste 3 maanden';
$lang['appointment_googlesync_only_last_six_months'] = 'Laatste 6 maanden';
$lang['appointment_googlesync_only_last_year'] = 'Vorig jaar';
$lang['appointment_googlesync_show_all'] = 'Alles';
$lang['appointly_not_including_two_way_synced_appointments'] = 'Exclusief tweerichtings gesynchroniseerde afspraken';
$lang['appointment_external_calendar_event'] = 'Externe kalender evenement';

// Google Meet
$lang['appointment_google_meet_info'] = 'Deze afspraak is toegevoegd aan Google Agenda, u kunt Google Meet gebruiken om online uw klanten te ontmoeten';
$lang['appointment_google_meet_info_2'] = 'Deze afspraak is toegevoegd aan Google Agenda';
$lang['appointment_google_client_meet_info'] = 'Verbinden via Google Meet';
$lang['appointment_connect_via_google_meet'] = 'Ik wil verbinden via Google Meet';
$lang['appointment_meet_message'] = 'Hallo<br><br>Volg deze URL om met mij te verbinden via Google Meet: ';
$lang['appointment_meeting_request_sent'] = 'Uw vergader aanvraag bericht is succesvol verzonden';
$lang['appointment_leave_a_comment'] = 'Wilt u een opmerking achterlaten';
$lang['appointment_google_meet_connect_message'] = 'E-mail naar deelnemers verzenden en vragen om te verbinden via Google Meet';
$lang['appointment_google_meet_modal_custom_label'] = 'Personeel en klanten uitnodigen voor Google Meet via e-mail';
$lang['appointment_google_meet'] = 'Google Meet';
$lang['appointment_google_calendar'] = 'Google Agenda';

// Enhanced Google Meet Settings
$lang['appointment_google_meet_enhanced_settings'] = 'Uitgebreide Google Meet instellingen';
$lang['appointly_auto_enable_google_meet'] = 'Google Meet automatisch inschakelen voor alle afspraken';
$lang['appointly_auto_enable_google_meet_help'] = 'Wanneer ingeschakeld, zullen alle nieuwe afspraken automatisch Google Meet links bevatten wanneer gesynchroniseerd met Google Agenda';
$lang['appointly_auto_add_to_google_on_approval'] = 'Automatisch toevoegen aan Google Agenda bij goedkeuring van externe afspraken';
$lang['appointly_auto_add_to_google_on_approval_help'] = 'Wanneer ingeschakeld, zullen externe afspraken automatisch worden toegevoegd aan de Google Agenda van het toegewezen personeelslid bij goedkeuring';
$lang['appointly_google_meet_default_settings'] = 'Google Meet standaard instellingen';
$lang['appointly_google_meet_enable_recording'] = 'Opname standaard inschakelen';
$lang['appointly_google_meet_enable_waiting_room'] = 'Wachtkamer standaard inschakelen';
$lang['appointly_google_meet_reminder_minutes'] = 'Standaard herinnering tijd voor vergadering';
$lang['appointly_google_meet_reminder_help'] = 'Standaard herinnering tijd instellen voor Google Meet afspraken';
$lang['appointly_disable_google_meeting_emails'] = 'Google Agenda e-mail meldingen uitschakelen';
$lang['appointly_disable_google_meeting_emails_help'] = 'Wanneer ingeschakeld, zal Google geen automatische e-mail meldingen verzenden voor kalender evenementen';
$lang['appointly_minutes'] = 'minuten';
$lang['appointly_hour'] = 'uur';
$lang['appointly_hours'] = 'uren';

// Enhanced Google Meet Features
$lang['appointment_google_meet_join_before_start'] = 'Google Meet deelnemen';
$lang['appointment_google_meet_copy_link'] = 'Google Meet link kopiëren';
$lang['appointment_google_meet_link_copied'] = 'Google Meet link gekopieerd naar klembord';
$lang['appointment_google_meet_test_connection'] = 'Google Meet verbinding testen';
$lang['appointment_google_meet_connection_success'] = 'Google Meet verbinding test succesvol';
$lang['appointment_google_meet_connection_failed'] = 'Google Meet verbinding test mislukt';
$lang['appointment_google_meet_quick_join'] = 'Snel deelnemen aan vergadering';
$lang['appointment_google_meet_meeting_details'] = 'Vergadering details';
$lang['appointment_google_meet_dial_in'] = 'Inbel informatie';
$lang['appointment_google_meet_share_screen'] = 'Scherm delen beschikbaar';
$lang['appointment_google_meet_record_meeting'] = 'Opname beschikbaar';
$lang['appointment_google_meet_waiting_room_enabled'] = 'Wachtkamer ingeschakeld';
$lang['appointment_google_meet_testing_connection'] = 'Google Meet verbinding testen...';
$lang['appointment_google_meet_copy_failed'] = 'Kopiëren van link mislukt. Kopieer handmatig.';
$lang['appointment_google_meet_email_message'] = 'E-mail bericht';
$lang['appointment_google_meet_send_invitation'] = 'Google Meet uitnodiging verzenden';
$lang['appointment_google_meet_message_required'] = 'Voer een bericht in om te verzenden';
$lang['sending'] = 'Verzenden...';
$lang['appointment_email_send_failed'] = 'E-mail verzenden mislukt. Controleer uw e-mail instellingen.';
$lang['appointment_google_meet_send_to'] = 'Verzenden naar';
$lang['appointment_google_meet_primary_recipient'] = 'Primaire ontvanger';
$lang['appointment_google_meet_also_notify_attendees'] = 'Ook personeel deelnemers melden';
$lang['appointment_no_email_provided'] = 'Geen e-mailadres opgegeven';
$lang['appointment_google_meet_hd_video_audio'] = 'HD video en audio';
$lang['appointment_google_meet_always_enabled'] = 'Altijd ingeschakeld';
$lang['appointment_google_meet_recording_enabled'] = 'Opname ingeschakeld';
$lang['appointment_google_meet_recording_disabled'] = 'Opname uitgeschakeld';
$lang['appointment_google_meet_waiting_room_enabled_status'] = 'Wachtkamer ingeschakeld';
$lang['appointment_google_meet_waiting_room_disabled'] = 'Wachtkamer uitgeschakeld';
$lang['appointment_google_meet_quick_actions'] = 'Snelle acties';
$lang['appointment_google_meet_join_meeting'] = 'Google Meet vergadering deelnemen';
$lang['appointment_google_meet_send_invitation_btn'] = 'Uitnodiging verzenden';

// Google Maps
$lang['appointment_google_maps'] = 'Openen in Google Maps';
$lang['appointly_google_maps_not_shown'] = 'Google Maps wordt niet weergegeven.';
$lang['appointly_google_api_key_notset'] = 'Google API key is niet ingesteld, stel Google API key in bij afspraak instellingen';
$lang['appointly_message_will_hide'] = 'Dit bericht wordt verborgen over 5 seconden';

// Outlook Calendar
$lang['appointment_login_to_outlook'] = 'Inloggen met Outlook';
$lang['appointment_logout_from_outlook'] = 'Uitloggen van Outlook';
$lang['appointment_open_outlook_calendar'] = 'Openen in Outlook Kalender';
$lang['appointments_outlook_revoke'] = 'Huidige Outlook Kalender sessie intrekken en alle verleende toestemmingen aan uw Outlook account verwijderen.';
$lang['appointment_redirect_url_logout'] = 'Outlook autorisatie en redirect URI';
$lang['appointment_outlook_api_label'] = 'Outlook Kalender API';
$lang['appointment_outlook_client_id'] = 'Applicatie (client) ID';
$lang['appointment_outlook_calendar'] = 'Outlook Kalender';
$lang['appointment_outlook_calendar_info'] = 'Deze afspraak is toegevoegd aan Outlook Kalender';
$lang['appointment_add_to_outlook'] = 'Toevoegen aan Outlook Kalender';
$lang['appointment_outlook_not_added_yet'] = 'Niet toegevoegd aan Outlook Kalender';
$lang['appointment_is_added_to_outlook'] = 'Toegevoegd aan Outlook Kalender';
$lang['appointment_calendar_adding_to_outlook'] = 'Toevoegen aan Outlook Kalender...';
$lang['appointment_added_to_outlook'] = 'Evenement succesvol toegevoegd aan Outlook Kalender';
$lang['appointment_added_to_outlook_but_not_saved'] = 'Evenement toegevoegd aan Outlook maar opslaan in database mislukt';
$lang['appointment_outlook_event_saved'] = 'Outlook evenement details opgeslagen';
$lang['appointment_outlook_event_save_failed'] = 'Outlook evenement details opslaan mislukt';
$lang['appointment_outlook_error'] = 'Er is een fout opgetreden bij toevoegen aan Outlook';
$lang['appointment_outlook_auth_error'] = 'Outlook authenticatie fout';
$lang['appointment_invalid_date'] = 'Ongeldige afspraak datum';
$lang['appointment_sign_in_to_outlook'] = 'Inloggen op Outlook';
$lang['appointment_sign_out_from_outlook'] = 'Uitloggen van Outlook';
$lang['appointments_outlook_view_in_calendar'] = 'Bekijken in Outlook Kalender';
$lang['appointment_outlook_calendar_title'] = 'Outlook Kalender';
$lang['appointment_outlook_sync_status'] = 'Outlook synchronisatie status';
$lang['appointment_outlook_last_synced'] = 'Laatst gesynchroniseerd: %s';
$lang['appointment_outlook_sync_error'] = 'Laatste synchronisatie mislukt: %s';
$lang['appointment_outlook_event_deleted'] = 'Outlook evenement succesvol verwijderd';
$lang['appointment_outlook_event_delete_failed'] = 'Outlook evenement verwijderen mislukt';
$lang['appointments_table_calendar'] = 'Toegevoegd aan kalenders';
$lang['appointment_not_added_to_calendars_yet'] = 'Nog niet toegevoegd aan een kalender.';
$lang['permission_approve'] = 'Goedkeuren';
$lang['permission_view_reports'] = 'Rapporten bekijken';
$lang['appointly_missing_required_fields'] = 'Ontbrekende vereiste velden';
$lang['appointly_service_not_found'] = 'Dienst niet gevonden';
$lang['appointly_error_getting_time_slots'] = 'Fout bij het ophalen van beschikbare tijdslots. Probeer opnieuw.';
$lang['appointly_invalid_working_hours'] = 'Ongeldige werkuren configuratie';
$lang['appointly_service_price_invalid'] = 'Dienst prijs moet een geldig getal zijn (0 of hoger)';
$lang['appointly_service_duration_invalid'] = 'Dienst duur moet een positief getal zijn';
$lang['appointly_service_providers_required'] = 'Minstens één dienst provider moet toegewezen zijn';
$lang['appointly_error_loading_providers'] = 'Fout bij het laden van providers';
$lang['appointly_no_data_available'] = 'Geen data beschikbaar';
$lang['appointly_please_try_again'] = 'Probeer opnieuw';
$lang['appointly_installation_complete'] = 'Installatie succesvol voltooid';
$lang['appointly_database_updated'] = 'Database succesvol bijgewerkt';
$lang['appointly_menu_reset'] = 'Menu succesvol gereset';
$lang['appointly_default_service_created'] = 'Standaard dienst succesvol aangemaakt';
$lang['appointly_working_hours_configured'] = 'Werkuren succesvol geconfigureerd';
$lang['appointment_select_service_provider_first'] = 'Selecteer eerst dienst en provider';
$lang['appointment_are_you_sure_to_cancel'] = 'Weet u zeker dat u deze afspraak wilt annuleren?';
$lang['appointment_are_you_sure_to_mark_as_ongoing'] = 'Weet u zeker dat u deze afspraak wilt markeren als bezig?';
$lang['appointment_error_occurred'] = 'Er is een fout opgetreden. Probeer opnieuw.';
$lang['appointment_closed'] = 'Afspraak boeken is momenteel gesloten';
$lang['appointment_time_required'] = 'Selecteer een tijdslot';
$lang['appointment_no_providers'] = 'Geen providers beschikbaar voor deze dienst';
$lang['appointment_provider_not_available'] = 'Provider niet beschikbaar op die dag';
$lang['appointment_minutes'] = 'minuten';
$lang['appointment_unavailable'] = 'Niet beschikbaar';
$lang['is_required'] = 'is vereist';
$lang['appointment_schedule_info'] = 'Afspraak rooster';
$lang['appointment_form_info'] = 'Afspraak informatie';
$lang['appointment_marked_as_approved'] = 'Afspraak gemarkeerd als goedgekeurd';
$lang['appointment_cancellation_approval_failed'] = 'Afspraak annulering goedkeuring mislukt';
$lang['appointment_send_an_sms'] = 'SMS verzenden';
$lang['appointment_call_number'] = 'Bellen';
$lang['appointment_actions'] = 'Acties';
$lang['appointment_staff_cannot_provide_feedback'] = 'Personeelsleden kunnen geen feedback geven voor afspraken';
$lang['appointment_thank_you_for_feedback'] = 'Bedankt voor uw feedback!';
$lang['appointment_feedback_comment_required'] = 'Feedback opmerking is vereist';

// Reschedule functionality client side
$lang['appointment_reschedule'] = 'Verzetten';
$lang['appointment_reschedule_reason'] = 'Reden voor verzetten';
$lang['appointment_reschedule_reason_required'] = 'Reden voor verzetten is vereist';
$lang['appointment_request_reschedule'] = 'Verzetten aanvragen';
$lang['appointment_reschedule_request_submitted'] = 'Uw verzet aanvraag is ingediend en wordt beoordeeld door ons personeel.';
$lang['appointment_cannot_be_rescheduled'] = 'Deze afspraak kan niet worden verzet vanwege de huidige status.';

$lang['appointment_processing'] = 'Verwerken...';
$lang['appointment_select_date_first'] = 'Selecteer eerst een datum';
$lang['appointment_please_select_date_time'] = 'Selecteer een datum en tijd';
$lang['appointment_current_details'] = 'Huidige details';
$lang['appointment_loading_available_times'] = 'Beschikbare tijden laden...';
$lang['appointment_no_available_slots'] = 'Geen beschikbare slots';
$lang['appointment_no_available_times'] = 'Geen beschikbare tijden';

$lang['appointment_reschedule_requested'] = 'Verzetten aangevraagd';
$lang['appointment_reschedule_pending_review'] = 'Uw verzet aanvraag wordt beoordeeld door ons personeel.';
$lang['appointment_reschedule_pending_notice'] = 'Verzet aanvragen in behandeling';
$lang['appointment_requested_date'] = 'Aangevraagde datum';
$lang['appointment_requested_time'] = 'Aangevraagde tijd';
$lang['appointment_current_date'] = 'Huidige datum';
$lang['appointment_approve_reschedule'] = 'Verzetten goedkeuren';
$lang['appointment_reject_reschedule'] = 'Verzetten afwijzen';
$lang['appointment_pending_reschedules'] = 'Verzetten in behandeling';
$lang['appointment_reschedule_approved'] = 'Verzetten goedgekeurd';
$lang['appointment_reschedule_rejected'] = 'Verzetten afgewezen';
$lang['appointment_cancellation_requested'] = 'Annulering aangevraagd';
$lang['appointment_cancellation_pending_review'] = 'Uw annulering aanvraag wordt beoordeeld door ons personeel.';
$lang['appointment_cancellation_reason'] = 'Reden voor annulering';
$lang['appointment_cancellation_notes'] = 'Annulering notities';
$lang['appointment_cancellation_notes_placeholder'] = 'Leg uit waarom u deze afspraak wilt annuleren...';
$lang['appointment_cancellation_notes_required'] = 'Annulering notities zijn vereist';
$lang['appointment_cancellation_request_submitted'] = 'Uw annulering aanvraag is ingediend en wordt beoordeeld door ons personeel.';
$lang['appointment_confirm_approve_reschedule'] = 'Weet u zeker dat u deze verzet aanvraag wilt goedkeuren? Dit zal de afspraak bijwerken naar de nieuwe datum en tijd.';
$lang['appointment_reschedule_denial_reason'] = 'Geef een reden op voor het afwijzen van deze verzet aanvraag (dit wordt naar de klant gestuurd):';
$lang['appointment_reschedule_approved_successfully'] = 'Verzet aanvraag goedgekeurd en afspraak bijgewerkt.';
$lang['appointment_reschedule_denied_successfully'] = 'Verzet aanvraag afgewezen en klant geïnformeerd.';
$lang['appointment_reschedule_approval_failed'] = 'Verzet aanvraag goedkeuring mislukt. Probeer opnieuw.';
$lang['appointment_reschedule_denial_failed'] = 'Verzet aanvraag afwijzing mislukt. Probeer opnieuw.';
$lang['appointment_client_information'] = 'Klant informatie';
$lang['appointment_provider_information'] = 'Provider informatie';
$lang['appointment_reschedule_action_required'] = 'Actie vereist';
$lang['appointment_reschedule_instructions'] = 'Wanneer klanten verzetten aanvragen, verschijnen ze hier voor uw goedkeuring. Goedkeuring zal automatisch de afspraak bijwerken en de klant per e-mail informeren.';
$lang['appointment_no_pending_reschedules'] = 'Geen verzet aanvragen in behandeling gevonden.';
$lang['appointment_reschedule_request_details'] = 'Verzet aanvraag details';
$lang['appointment_reschedule_date_required'] = 'Nieuwe datum is vereist';
$lang['appointment_reschedule_time_required'] = 'Nieuwe tijd is vereist';
$lang['appointment_reschedule_future_datetime'] = 'Selecteer een toekomstige datum en tijd';
$lang['appointment_reschedule_request_subject'] = 'Afspraak verzet aanvraag';
$lang['appointment_reschedule_approved_subject'] = 'Afspraak verzetten goedgekeurd';
$lang['appointment_reschedule_denied_subject'] = 'Afspraak verzetten afgewezen';
$lang['appointment_book_again'] = 'Opnieuw boeken';
$lang['appointment_cancelled_title'] = 'Deze afspraak is geannuleerd';
$lang['appointment_book_new_appointment'] = 'Wilt u een nieuwe afspraak maken?';
$lang['appointment_book_new'] = 'Nieuwe afspraak maken';
$lang['appointment_deny_reschedule'] = 'Verzetten afwijzen';
$lang['appointment_reschedule_denial_reason_prompt'] = 'Geef een reden op voor het afwijzen van deze verzet aanvraag:';
$lang['appointment_reschedule_denial_reason_required'] = 'Afwijzing reden is vereist';
$lang['appointment_confirm_approve_cancellation'] = 'Weet u zeker dat u deze annulering aanvraag wilt goedkeuren? Dit zal de afspraak permanent annuleren.';
$lang['appointment_cancelled_book_again_message'] = 'Deze afspraak is geannuleerd. Wilt u een nieuwe afspraak maken?';
$lang['appointment_no_show_book_again_message'] = 'Deze afspraak was een niet-verschijning. Wilt u een nieuwe afspraak maken?';

// Calendar Integration Removal
$lang['appointment_remove_google_integration'] = 'Google Agenda integratie verwijderen';
$lang['appointment_remove_outlook_integration'] = 'Outlook Kalender integratie verwijderen';
$lang['appointment_confirm_remove_google_integration'] = 'Weet u zeker dat u Google Agenda integratie van deze afspraak wilt verwijderen? Dit zal ook het evenement van Google Agenda verwijderen indien mogelijk.';
$lang['appointment_confirm_remove_outlook_integration'] = 'Weet u zeker dat u Outlook Kalender integratie van deze afspraak wilt verwijderen? Dit zal ook het evenement van Outlook Kalender verwijderen indien mogelijk.';
$lang['appointment_google_integration_removed'] = 'Google Agenda integratie verwijderd van afspraak';
$lang['appointment_google_integration_removed_and_deleted'] = 'Google Agenda integratie verwijderd en evenement verwijderd van Google Agenda';
$lang['appointment_google_removal_failed'] = 'Google Agenda integratie verwijdering mislukt';
$lang['appointment_outlook_integration_removed'] = 'Outlook Kalender integratie verwijderd van afspraak';
$lang['appointment_outlook_integration_removed_and_deleted'] = 'Outlook Kalender integratie verwijderd en evenement verwijderd van Outlook Kalender';
$lang['appointment_outlook_removal_failed'] = 'Outlook Kalender integratie verwijdering mislukt';
$lang['appointment_missing_required_fields'] = 'Ontbrekende vereiste velden';
$lang['appointment_not_found'] = 'Afspraak niet gevonden';
$lang['appointment_outlook_not_authenticated_warning'] = 'Waarschuwing: U bent momenteel niet geauthenticeerd met Outlook. Integratie wordt alleen lokaal verwijderd, maar het evenement blijft in uw Outlook kalender.';
$lang['appointment_outlook_not_available_warning'] = 'Waarschuwing: Outlook integratie is niet beschikbaar. Integratie wordt alleen lokaal verwijderd.';
$lang['appointment_outlook_integration_removed_local_only'] = 'Outlook Kalender integratie lokaal verwijderd. Opmerking: Evenement kan nog steeds bestaan in uw Outlook kalender.';

// Dashboard Widgets
$lang['appointly_upcoming_appointments'] = 'Aankomende afspraken';
$lang['appointly_no_upcoming_appointments'] = 'Geen aankomende afspraken';
$lang['appointly_no_appointments_in_range'] = 'Geen afspraken ingepland in de %s';
$lang['appointly_next_7_days'] = 'volgende 7 dagen';
$lang['appointly_next_14_days'] = 'volgende 14 dagen';
$lang['appointly_next_30_days'] = 'volgende 30 dagen';
$lang['appointly_next_4_weeks'] = 'volgende 4 weken';
$lang['appointly_view_all_appointments'] = 'Alle afspraken bekijken';
$lang['appointly_dashboard_widgets_settings'] = 'Dashboard widgets instellingen';
$lang['appointly_today_widget_enabled'] = 'Vandaag afspraken widget tonen op dashboard';
$lang['appointly_upcoming_widget_enabled'] = 'Aankomende afspraken widget tonen op dashboard';
$lang['appointly_upcoming_widget_range'] = 'Aankomende afspraken widget datumbereik';
$lang['appointly_tomorrow'] = 'Morgen';
$lang['days'] = 'dagen';

$lang['appointly_invoice_default_vat'] = 'Standaard BTW/Belasting percentage';
$lang['appointly_invoice_vat_help'] = 'Standaard belasting percentage om toe te passen op automatisch gegenereerde facturen (0 instellen voor geen belasting)';
$lang['appointly_invoice_tax_type_help'] = 'Kies hoe u belastingen wilt toepassen op afspraak facturen';
$lang['appointly_invoice_tax_type_label'] = 'Belasting toepassing methode';
$lang['appointly_tax_type_none'] = 'Geen belasting';
$lang['appointly_tax_type_custom'] = 'Aangepast percentage';
$lang['appointly_tax_type_system'] = 'CRM belasting tarieven gebruiken';
$lang['appointly_default_vat_label'] = 'Aangepast belasting percentage';
$lang['appointly_default_vat_help'] = 'Aangepast belasting percentage om toe te passen op facturen (0 instellen voor geen belasting)';
$lang['appointly_system_tax_label'] = 'Belasting tarief selecteren';
$lang['appointly_system_tax_help'] = 'Kies uit uw geconfigureerde belasting tarieven in CRM';

// Enhanced Invoice Settings - Tab Names
$lang['appointly_invoice_settings'] = 'Factuur en belastingen';
$lang['appointly_tax_settings'] = 'Belasting configuratie';
$lang['appointly_tax_settings_help'] = 'Configureer hoe belastingen worden toegepast op facturen aangemaakt van afspraken. U kunt het CRM belasting systeem gebruiken of een aangepast percentage instellen.';
$lang['appointly_tax_type_label'] = 'Belasting toepassing methode';
$lang['appointly_tax_type_help'] = 'Kies hoe u belastingen wilt toepassen op afspraak facturen';

// Form Field Labels
$lang['appointly_enable'] = 'Inschakelen';
$lang['appointly_disable'] = 'Uitschakelen';
$lang['appointly_yes'] = 'Ja';
$lang['appointly_no'] = 'Nee';

// Client Dashboard Language Strings
$lang['appointment_client_dashboard_description'] = 'Beheer uw afspraken, bekijk geschiedenis en maak nieuwe afspraken.';
$lang['appointment_all'] = 'Alles';
$lang['appointment_total_appointments'] = 'Totaal afspraken';
$lang['appointment_completed_appointments'] = 'Voltooid';
$lang['appointment_upcoming_appointments'] = 'Aankomend';
$lang['appointment_cancelled_appointments'] = 'Geannuleerd';
$lang['appointment_no_appointments_found'] = 'Geen afspraken gevonden';
$lang['appointment_no_appointments_match_filter'] = 'Geen afspraken komen overeen met het huidige filter';
$lang['appointment_details'] = 'Afspraak details';
$lang['view_invoice'] = 'Factuur bekijken';
$lang['appointment_calendar_file'] = 'Kalender bestand';
$lang['appointment_download_ics'] = 'ICS downloaden';
$lang['appointment_ics_description'] = 'Kalender bestand voor deze afspraak';
$lang['appointment_ics_download_error'] = 'Fout bij downloaden van ICS bestand';
$lang['appointment_ics_file_downloaded'] = 'ICS bestand succesvol gedownload';
$lang['appointment_ics_tooltip'] = 'Kalender bestand (.ics) downloaden om toe te voegen aan uw kalender';

// Cancel and Reschedule
$lang['appointment_cancel_reason'] = 'Reden voor annulering';
$lang['appointment_cancel_reason_placeholder'] = 'Geef een reden op voor het annuleren van deze afspraak...';
$lang['appointment_cancel_request_sent'] = 'Annulering aanvraag verzonden naar personeel voor goedkeuring.';
$lang['appointment_cancel_request_failed'] = 'Annulering aanvraag verzenden mislukt. Probeer opnieuw.';
$lang['appointment_reschedule_request_sent'] = 'Verzet aanvraag verzonden naar personeel voor goedkeuring.';
$lang['appointment_reschedule_request_failed'] = 'Verzet aanvraag verzenden mislukt. Probeer opnieuw.';
$lang['appointment_client_dashboard'] = 'Klant dashboard';
$lang['appointment_available_times'] = 'Beschikbare tijden';
$lang['appointment_error_loading_times'] = 'Fout bij laden van tijden';
$lang['appointment_cancel_request_submitted'] = 'Annulering aanvraag succesvol ingediend';
$lang['appointment_date_time_required'] = 'Datum en tijd zijn vereist';
$lang['appointment_reschedule_pending'] = 'Verzet aanvraag in behandeling voor goedkeuring';
$lang['appointly_invoice_payment_mode_changed'] = 'Factuur betalingsmethode gewijzigd, sla instellingen op om wijzigingen toe te passen';
$lang['payment_received_for_appointment'] = 'Betaling ontvangen voor:';
$lang['appointly_create_invoice_when_completed'] = 'Factuur aanmaken wanneer afspraak is geboekt?';
$lang['invoice_created_for_appointment'] = 'Factuur #%s is aangemaakt voor deze afspraak.';
$lang['appointment_email_sent_success'] = 'E-mail succesvol verzonden';
$lang['appointment_email_sent_failed'] = 'E-mail verzenden mislukt';
$lang['customer_permission_appointments'] = 'Afspraken';
$lang['appointment_contact_relationship'] = 'Contact en relatie';
$lang['appointment_download_ics_tooltip'] = 'Kalender bestand (.ics) downloaden om deze afspraak toe te voegen aan uw kalender applicatie';
$lang['appointment_security_verification'] = 'Beveiligingsverificatie';
$lang['appointment_view_on_map'] = 'Bekijken op kaart';
$lang['appointment_no_notes_available'] = 'Geen notities beschikbaar voor deze afspraak';
$lang['appointment_session_overview'] = 'Sessie overzicht';
$lang['appointly_show_staff_email_booking_form'] = 'Personeel e-mailadressen tonen in boekingsformulier';
$lang['appointly_show_staff_phone_booking_form'] = 'Personeel telefoonnummers tonen in boekingsformulier';
$lang['appointly_no_available_time_slots'] = 'Geen beschikbare tijdslots';
$lang['appointment_status_changed_successfully'] = 'Afspraak status succesvol gewijzigd';
$lang['appointment_status_change_failed'] = 'Afspraak status wijzigen mislukt';
// Languages dropdown in external form
$lang['appointments_external_form_show_language_dropdown'] = 'Taalkeuze tonen';
$lang['appointments_external_form_show_language_dropdown_help'] = 'Schakel dit in om een taalkeuzemenu op het externe boekingsformulier te tonen, waardoor klanten tussen talen kunnen wisselen';

// Recurring appointments UI
$lang['recurring_appointment'] = 'Terugkerende Afspraak';
$lang['recurring'] = 'Terugkerend';
$lang['recurring_every'] = 'Elke';
$lang['recurring_day'] = 'Dag(en)';
$lang['recurring_week'] = 'Week/Weken';
$lang['recurring_month'] = 'Maand(en)';
$lang['recurring_year'] = 'Jaar/Jaren';
$lang['stop_recurring'] = 'Stop Herhaling';
$lang['stop_recurring_confirm'] = 'Weet je zeker dat je deze terugkerende afspraak wilt stoppen? Er worden geen nieuwe instanties meer aangemaakt.';
$lang['recurring_stopped_successfully'] = 'Terugkerende afspraken succesvol gestopt';
$lang['edit_recurring_settings'] = 'Herhalingsinstellingen Bewerken';
$lang['error_occurred'] = 'Er is een fout opgetreden. Probeer het opnieuw.';
