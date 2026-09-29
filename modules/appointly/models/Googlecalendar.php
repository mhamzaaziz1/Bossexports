<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Googlecalendar extends App_Model
{

    public $table;

    protected $googleReminders;

    public $unauthorized_url_settings;
    public $calendar;

    public function __construct()
    {
        parent::__construct();

        $this->table = db_prefix() . 'appointly_google';

        // Ensure database structure is correct for all tenants (auto-migration for multi-tenant systems)
        $this->ensureGoogleTokenColumnsAreLongtext();

        $this->load->library('appointly/googleplus');

        $this->unauthorized_url_settings = '<h3>Seems that something is wrong, please check that your Client Secret is correctly inserted in <a href="' . admin_url('settings?group=appointly-settings') . '">Setup->Settings->Appointments</a></h3>';

        $this->googleReminders = [
            'useDefault' => false,
            'overrides'  => [
                ['method' => 'email', 'minutes' => 24 * 60],
                ['method' => 'popup', 'minutes' => 60],
            ],
        ];
        /**
         * Init Google Calendar Service instance
         */
        $this->calendar = new Google\Service\Calendar($this->googleplus->client());
    }

    /**
     * Auto-migrate token columns to LONGTEXT for multi-tenant systems
     * 
     * Modern OAuth tokens can exceed VARCHAR(191) limit causing "Data too long" errors.
     * This method automatically upgrades existing installations without requiring manual intervention.
     * 
     * @return void
     */
    private function ensureGoogleTokenColumnsAreLongtext()
    {
        try {
            if ($this->db->table_exists($this->table)) {
                $columnInfo = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'access_token'")->row_array();

                // Check if we need to upgrade from VARCHAR to LONGTEXT
                if ($columnInfo && stripos($columnInfo['Type'], 'varchar') !== false) {
                    $this->db->query("ALTER TABLE {$this->table}
                        MODIFY COLUMN access_token LONGTEXT,
                        MODIFY COLUMN refresh_token LONGTEXT");

                    log_message('info', 'GoogleCalendar: Successfully upgraded token columns to LONGTEXT for ' . $this->table);
                }
            }
        } catch (Exception $e) {
            log_message('error', 'GoogleCalendar: Failed to ensure LONGTEXT columns for ' . $this->table . ' — ' . $e->getMessage());
        }
    }


    /**
     * Google OAuth Login Url
     *
     * @return void
     */
    public function loginUrl()
    {
        return $this->googleplus
            ->loginUrl();
    }


    /**
     * Google OAuth Login validation
     *
     * @return bool
     */
    public function login($code)
    {
        try {
            $login = $this->googleplus
                ->client
                ->authenticate($code);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            if ($e->getCode() === 401) {
                die($this->unauthorized_url_settings);
            }
        }
        if (isset($login['error_description']) && $login['error_description'] == 'Unauthorized') {
            die($this->unauthorized_url_settings);
        }

        if ($login) {
            // Safely trim long tokens to prevent data truncation errors
            // LONGTEXT supports up to 4GB, but we limit to 65,000 chars for safety
            $access_token  = isset($login['access_token']) ? substr($login['access_token'], 0, 65000) : null;
            $refresh_token = isset($login['refresh_token']) ? substr($login['refresh_token'], 0, 65000) : null;
            $expires_in    = isset($login['expires_in']) ? time() + (int)$login['expires_in'] : time();

            $this->db->where('staff_id', get_staff_user_id());
            $result = $this->db->get($this->table)->row_array();

            if ($result) {
                // Update existing record
                $this->db->where('staff_id', get_staff_user_id());
                $this->db->update($this->table, [
                    'access_token'  => $access_token,
                    'expires_in'    => $expires_in,
                    'refresh_token' => $refresh_token,
                ]);
            } else {
                // Insert new record
                $insertData = [
                    'staff_id'     => get_staff_user_id(),
                    'access_token' => $access_token,
                    'expires_in'   => $expires_in,
                ];

                // Only add refresh_token if it exists
                if ($refresh_token) {
                    $insertData['refresh_token'] = $refresh_token;
                }

                $this->db->insert($this->table, $insertData);
            }

            $token = $this->googleplus
                ->client
                ->getAccessToken();

            $this->googleplus
                ->client
                ->setAccessToken($token);

            $this->saveNewTokenValues($token);

            return true;
        }
        return false;
    }


    /**
     * Google get email user info
     *
     * @return array
     */
    public function getUserInfo()
    {
        return $this->googleplus->getUser();
    }


    /**
     * Google Calendar delete event
     *
     * @param $eventid
     * @return bool
     */
    public function deleteEvent($eventid)
    {
        try {
            // Check if we need to refresh the token
            if ($this->googleplus->isAccessTokenExpired()) {
                $refreshToken = $this->googleplus->getTokenType('refresh_token');
                if ($refreshToken) {
                    $newToken = $this->googleplus->refreshToken($refreshToken);
                    $this->googleplus->setAccessToken($newToken);
                } else {
                    log_message('error', 'Google Calendar: No refresh token available for token refresh');
                    return false;
                }
            }

            // First check if the event exists
            try {
                $event = $this->calendar->events->get('primary', $eventid);

                // Log successful retrieval
                log_message('debug', 'Google Calendar: Successfully found event ' . $eventid . ' before deletion');

                // Set sendUpdates parameter based on settings
                // Default to 'none' to prevent duplicate emails (Perfex already sends notifications)
                $params = [];
                if (get_option('appointly_disable_google_meeting_emails') == '1' || get_option('appointly_disable_google_meeting_emails') === false || get_option('appointly_disable_google_meeting_emails') === '') {
                    $params['sendUpdates'] = 'none';
                } else {
                    $params['sendUpdates'] = 'all';
                }

                // If we get here, the event exists, so try to delete it
                $deleteResult = $this->calendar->events->delete('primary', $eventid, $params);

                // Log success
                log_message('debug', 'Google Calendar: Successfully deleted event ' . $eventid);

                return true;
            } catch (Exception $innerException) {
                // If event not found
                if ($innerException->getCode() === 404) {
                    log_message('debug', 'Google Calendar: Event not found when trying to delete: ' . $eventid);
                    // Consider returning true here instead of false, since the end result is the same - event doesn't exist
                    return true;
                }

                // Log other exceptions
                log_message('error', 'Google Calendar inner exception: ' . $innerException->getMessage());
                throw $innerException;
            }
        } catch (Exception $e) {
            log_message('error', 'Google Calendar: Error deleting event: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Google Calendar get events
     *
     * @param string $calendarId
     * @param bool   $timeMin
     * @param bool   $timeMax
     * @param int    $maxResults
     * @return array
     */
    public function getEvents($calendarId = 'primary', $timeMin = false, $timeMax = false, $maxResults = 200)
    {
        // Respect the date range filter setting
        $dateRangeSetting = get_option('appointments_googlesync_show_from', 'last_3_months');

        if (! $timeMin) {
            switch ($dateRangeSetting) {
                case 'today':
                    $timeMin = date("c", strtotime(date('Y-m-d') . ' 00:00:00'));
                    break;
                case 'last_month':
                    $timeMin = date("c", strtotime("-1 month", strtotime(date('Y-m-d') . ' 00:00:00')));
                    break;
                case 'last_3_months':
                    $timeMin = date("c", strtotime("-3 months", strtotime(date('Y-m-d') . ' 00:00:00')));
                    break;
                case 'all':
                    // Fetch all events (up to API limits) - go back 5 years
                    $timeMin = date("c", strtotime("-5 years", strtotime(date('Y-m-d') . ' 00:00:00')));
                    break;
                default:
                    // Default to 3 months
                    $timeMin = date("c", strtotime("-3 months", strtotime(date('Y-m-d') . ' 00:00:00')));
                    break;
            }
        } else {
            $timeMin = date("c", strtotime($timeMin));
        }

        if (! $timeMax) {
            // Always fetch future events up to 2 years ahead
            $timeMax = date("c", strtotime("+2 years", strtotime(date('Y-m-d') . ' 23:59:59')));
        } else {
            $timeMax = date("c", strtotime($timeMax));
        }

        // Increase maxResults for 'all' setting to fetch more events
        if ($dateRangeSetting === 'all' && $maxResults === 200) {
            $maxResults = 2500; // Google Calendar API max is 2500
        }

        $optParams = [
            'maxResults'   => $maxResults,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin,
            'timeMax'      => $timeMax,
            'timeZone'     => get_option('default_timezone'),
        ];

        try {
            $results = $this->calendar->events->listEvents($calendarId, $optParams);

            $data = [];

            foreach ($results->getItems() as $item) {
                // Get the correct date/time format
                $start = $item->getStart();
                $end = $item->getEnd();

                // Make sure we can safely access dateTime
                $startDateTime = isset($start->dateTime) ? $start->dateTime : (isset($start->date) ? $start->date : null);
                $endDateTime = isset($end->dateTime) ? $end->dateTime : (isset($end->date) ? $end->date : null);

                if ($startDateTime) {
                    array_push($data, [
                        'id' => $item->getId(),
                        'summary' => $item->getSummary() ?? 'Untitled Event',
                        'description' => $item->getDescription() ?? '',
                        'creator' => $item->getCreator(),
                        'start' => $startDateTime,
                        'end' => $endDateTime,
                        'htmlLink' => $item->getHtmlLink(),
                        'recurringEventId' => $item->getRecurringEventId(),
                        'status' => $item->getStatus(),
                        'eventType' => $item->getEventType(),
                        'location' => $item->getLocation()
                    ]);
                }
            }

            return $data;
        } catch (Exception $e) {
            log_message('error', 'Google Calendar: Error fetching events: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Google Calendar add new event
     *
     * @param string $calendarId
     * @param        $data
     * @return Google_Service_Calendar_Event
     */
    public function addEvent($data, $calendarId = 'primary')
    {
        $event = $this->fillGoogleCalendarEvent($data);

        // Set sendUpdates parameter in the API call based on settings
        // Default to 'none' to prevent duplicate emails (Perfex already sends notifications)
        $params = ['conferenceDataVersion' => 1];
        if (get_option('appointly_disable_google_meeting_emails') == '1' || get_option('appointly_disable_google_meeting_emails') === false || get_option('appointly_disable_google_meeting_emails') === '') {
            $params['sendUpdates'] = 'none';
        } else {
            $params['sendUpdates'] = 'all';
        }

        return $this->calendar->events->insert($calendarId, $event, $params);
    }


    /**
     * Google Calendar update existing event
     *
     * @param $eventid
     * @param $data
     * @return Google\Service\Calendar\Event
     */
    public function updateEvent($eventid, $data)
    {
        $event = $this->fillGoogleCalendarEvent($data);

        // Set sendUpdates parameter in the API call based on settings
        // Default to 'none' to prevent duplicate emails (Perfex already sends notifications)
        $params = [];
        if (get_option('appointly_disable_google_meeting_emails') == '1' || get_option('appointly_disable_google_meeting_emails') === false || get_option('appointly_disable_google_meeting_emails') === '') {
            $params['sendUpdates'] = 'none';
        } else {
            $params['sendUpdates'] = 'all';
        }

        return $this->calendar->events->update('primary', $eventid, $event, $params);
    }

    /**
     * Manage data and fill google calendar event array
     *
     * @param $data
     * @return Google\Service\Calendar\Event
     */
    public function fillGoogleCalendarEvent($data)
    {
        $event = new Google\Service\Calendar\Event(
            [
                'summary'        => $data['summary'],
                'description'    => $data['description'],
                'location'       => ($data['location']) ? $data['location'] : '',
                'start'          => $data['start'],
                'end'            => $data['end'],
                'attendees'      => (array) $data['attendees'],
                'reminders'      => $this->googleReminders,
            ]
        );

        if (get_option('appointly_auto_enable_google_meet') == '1') {
            $event->setConferenceData(new Google\Service\Calendar\ConferenceData([
                'createRequest' => new Google\Service\Calendar\CreateConferenceRequest([
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => new Google\Service\Calendar\ConferenceSolutionKey([
                        'type' => 'hangoutsMeet'
                    ])
                ])
            ]));
        }

        return $event;
    }

    /**
     * Get logged in Google account details
     *
     * @return mixed
     */
    public function getAccountDetails()
    {
        $this->db->select();
        $this->db->where('staff_id', get_staff_user_id());
        $result = $this->db->get($this->table)->result();

        if ($result) {
            return $result;
        }
        return false;
    }


    /**
     * Google save / update new token values in database
     *
     * @param $data
     * @return void
     */
    public function saveNewTokenValues($data)
    {
        // Safely trim long tokens to prevent data truncation errors
        $access_token  = isset($data['access_token']) ? substr($data['access_token'], 0, 65000) : null;
        $refresh_token = isset($data['refresh_token']) ? substr($data['refresh_token'], 0, 65000) : null;
        $expires_in    = isset($data['expires_in']) ? time() + (int)$data['expires_in'] : time();

        $this->db->where('staff_id', get_staff_user_id());
        $this->db->update(
            $this->table,
            [
                'refresh_token' => $refresh_token,
                'access_token'  => $access_token,
                'expires_in'    => $expires_in
            ]
        );
    }
}
