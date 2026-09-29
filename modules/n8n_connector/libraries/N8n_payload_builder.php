<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Responsible for building n8n-optimized JSON payloads with:
 * - Clean structure
 * - Relationship data
 * - Change tracking
 * - Metadata
 */
class N8n_payload_builder
{
    private $CI;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model(N8N_CONNECTOR_MODULE . '/N8n_webhooks_model', 'n8n_webhooks_model');
        $this->CI->load->model(N8N_CONNECTOR_MODULE . '/N8n_logs_model', 'n8n_logs_model');

        // Load events configuration
        require_once(module_dir_path(N8N_CONNECTOR_MODULE) . 'config/events.php');
    }

    /**
     * Build payload for an event
     *
     * @param string $event_key Event identifier (e.g., 'lead_created')
     * @param mixed $data Primary data object/array
     * @param array $options Additional options (old_data, metadata, etc.)
     * @return array Complete n8n payload
     */
    public function build($event_key, $data, $options = [])
    {
        // Get event configuration
        $event_config = n8n_get_event($event_key);

        if (!$event_config) {
            log_activity('n8n Connector: Unknown event key - ' . $event_key);
            return null;
        }

        // Convert data to array if object
        if (is_object($data)) {
            $data = (array) $data;
        }

        $now = Carbon::now();

        // Build base payload structure
        $payload = [
            'event' => $event_key,
            'event_label' => $event_config['label'],
            'event_description' => $event_config['description'],
            'triggered_at' => $now->toDateTimeString(),
            'triggered_at_unix' => $now->timestamp,
            'triggered_at_iso8601' => $now->toIso8601String(),
            'data' => $this->clean_data($data),
            'metadata' => $this->build_metadata($event_key, $data),
        ];

        // Add change tracking if old data provided
        if (isset($options['old_data']) && n8n_event_tracks_changes($event_key)) {
            $payload['changes'] = $this->track_changes($data, $options['old_data']);
        }

        // Add relationships if configured
        if (n8n_event_includes_relationships($event_key)) {
            $payload['relationships'] = $this->fetch_relationships($event_key, $data);
        }

        // Add custom metadata if provided
        if (isset($options['metadata'])) {
            $payload['metadata'] = array_merge($payload['metadata'], $options['metadata']);
        }

        return $payload;
    }

    /**
     * Clean data - remove sensitive info, format properly
     *
     * @param array $data Raw data
     * @return array Cleaned data
     */
    private function clean_data($data)
    {
        $exclude_fields = collect([
            'password',
            'password_hash',
            'new_pass_key',
            'new_pass_key_requested',
            'email_verification_key',
            'email_verification_sent_at',
            'two_factor_auth_code',
            'two_factor_auth_code_requested',
        ]);

        $dataCollection = collect($data);

        // Remove excluded fields
        $cleaned = $dataCollection->reject(function ($value, $key) use ($exclude_fields) {
            return $exclude_fields->contains($key);
        });

        // Format values
        $cleaned = $cleaned->map(function ($value) {
            if ($value === null) {
                return null;
            } elseif (is_numeric($value)) {
                return is_float($value) ? (float) $value : (int) $value;
            } elseif (is_string($value)) {
                return trim($value);
            } elseif (is_array($value) || is_object($value)) {
                return $this->clean_data((array) $value);
            }
            return $value;
        });

        return $cleaned->toArray();
    }

    /**
     * Build metadata for the event
     *
     * @param string $event_key Event key
     * @param array $data Primary data
     * @return array Metadata
     */
    private function build_metadata($event_key, $data)
    {
        $metadata = [
            'perfex_version' => get_app_version(),
            'module_version' => N8N_CONNECTOR_VERSION ?? '1.0.0',
            'environment' => ENVIRONMENT,
        ];

        // Add table information
        $table = n8n_get_event_table($event_key);
        if ($table) {
            $metadata['table'] = $table;
            $metadata['table_full'] = db_prefix() . $table;
        }

        // Add user context
        if (is_staff_logged_in()) {
            $staff = get_staff_user_id();
            $metadata['triggered_by'] = [
                'staff_id' => $staff,
                'name' => get_staff_full_name($staff),
                'email' => get_staff($staff)->email ?? null,
            ];
        } elseif (is_client_logged_in()) {
            $contact = get_contact_user_id();
            $metadata['triggered_by'] = [
                'contact_id' => $contact,
                'type' => 'client',
            ];
        } else {
            $metadata['triggered_by'] = [
                'type' => 'system',
            ];
        }

        return $metadata;
    }

    /**
     * Fetch related data for the record
     *
     * @param string $event_key Event key
     * @param array $data Primary data
     * @return array Relationships
     */
    private function fetch_relationships($event_key, $data)
    {
        $relationships = [];
        $table = n8n_get_event_table($event_key);

        if (!$table || !isset($data['id'])) {
            return $relationships;
        }

        // Map table to relationship methods
        $relationship_map = [
            'leads' => 'fetch_lead_relationships',
            'clients' => 'fetch_client_relationships',
            'invoices' => 'fetch_invoice_relationships',
            'estimates' => 'fetch_estimate_relationships',
            'projects' => 'fetch_project_relationships',
            'tasks' => 'fetch_task_relationships',
            'tickets' => 'fetch_ticket_relationships',
            'proposals' => 'fetch_proposal_relationships',
            'contracts' => 'fetch_contract_relationships',
            'expenses' => 'fetch_expense_relationships',
        ];

        if (isset($relationship_map[$table])) {
            $method = $relationship_map[$table];
            if (method_exists($this, $method)) {
                $relationships = $this->$method($data['id'], $data);
            }
        }

        return $relationships;
    }

    /**
     * Fetch lead relationships
     */
    private function fetch_lead_relationships($id, $data)
    {
        $relationships = [];

        // Lead status
        if (isset($data['status'])) {
            $this->CI->load->model('leads_model');
            $status = $this->CI->leads_model->get_status($data['status']);
            if ($status) {
                $relationships['status'] = [
                    'id' => $status->id,
                    'name' => $status->name,
                    'color' => $status->color ?? null,
                ];
            }
        }

        // Lead source
        if (isset($data['source'])) {
            $this->CI->load->model('leads_model');
            $source = $this->CI->leads_model->get_source($data['source']);
            if ($source) {
                $relationships['source'] = [
                    'id' => $source->id,
                    'name' => $source->name,
                ];
            }
        }

        // Assigned staff member
        if (isset($data['assigned']) && $data['assigned'] > 0) {
            $staff = get_staff($data['assigned']);
            if ($staff) {
                $relationships['assigned_to'] = [
                    'id' => $staff->staffid,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'email' => $staff->email,
                ];
            }
        }

        // Client if converted
        if (isset($data['client_id']) && $data['client_id'] > 0) {
            $this->CI->load->model('clients_model');
            $client = $this->CI->clients_model->get($data['client_id']);
            if ($client) {
                $relationships['client'] = [
                    'id' => $client->userid,
                    'company' => $client->company,
                ];
            }
        }

        return $relationships;
    }

    /**
     * Fetch client relationships
     */
    private function fetch_client_relationships($id, $data)
    {
        $relationships = [];

        // Primary contact
        $this->CI->load->model('clients_model');
        $contacts = $this->CI->clients_model->get_contacts($id, ['is_primary' => 1]);
        if (!empty($contacts)) {
            $contact = $contacts[0];
            $relationships['primary_contact'] = [
                'id' => $contact['id'],
                'firstname' => $contact['firstname'],
                'lastname' => $contact['lastname'],
                'email' => $contact['email'],
            ];
        }

        // All contacts count
        $all_contacts = $this->CI->clients_model->get_contacts($id);
        $relationships['contacts_count'] = count($all_contacts);

        // Active projects count
        $this->CI->db->where('clientid', $id);
        $this->CI->db->where('status', 2); // Active status
        $relationships['active_projects_count'] = $this->CI->db->count_all_results(db_prefix() . 'projects');

        // Outstanding invoices
        $this->CI->db->where('clientid', $id);
        $this->CI->db->where('status !=', 2); // Not paid
        $relationships['outstanding_invoices_count'] = $this->CI->db->count_all_results(db_prefix() . 'invoices');

        return $relationships;
    }

    /**
     * Fetch invoice relationships
     */
    private function fetch_invoice_relationships($id, $data)
    {
        $relationships = [];

        // Client
        if (isset($data['clientid']) && $data['clientid'] > 0) {
            $this->CI->load->model('clients_model');
            $client = $this->CI->clients_model->get($data['clientid']);
            if ($client) {
                $relationships['client'] = [
                    'id' => $client->userid,
                    'company' => $client->company,
                ];
            }
        }

        // Project
        if (isset($data['project_id']) && $data['project_id'] > 0) {
            $this->CI->load->model('projects_model');
            $project = $this->CI->projects_model->get($data['project_id']);
            if ($project) {
                $relationships['project'] = [
                    'id' => $project->id,
                    'name' => $project->name,
                ];
            }
        }

        // Sale agent
        if (isset($data['sale_agent']) && $data['sale_agent'] > 0) {
            $staff = get_staff($data['sale_agent']);
            if ($staff) {
                $relationships['sale_agent'] = [
                    'id' => $staff->staffid,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'email' => $staff->email,
                ];
            }
        }

        // Invoice items
        $this->CI->load->model('invoices_model');
        $invoice = $this->CI->invoices_model->get($id);
        if ($invoice) {
            $relationships['items'] = [];
            foreach ($invoice->items as $item) {
                $relationships['items'][] = [
                    'description' => $item['description'],
                    'long_description' => $item['long_description'] ?? null,
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'unit' => $item['unit'] ?? null,
                ];
            }
            $relationships['items_count'] = count($invoice->items);
        }

        return $relationships;
    }

    /**
     * Fetch estimate relationships
     */
    private function fetch_estimate_relationships($id, $data)
    {
        $relationships = [];

        // Client
        if (isset($data['clientid']) && $data['clientid'] > 0) {
            $this->CI->load->model('clients_model');
            $client = $this->CI->clients_model->get($data['clientid']);
            if ($client) {
                $relationships['client'] = [
                    'id' => $client->userid,
                    'company' => $client->company,
                ];
            }
        }

        // Sale agent
        if (isset($data['sale_agent']) && $data['sale_agent'] > 0) {
            $staff = get_staff($data['sale_agent']);
            if ($staff) {
                $relationships['sale_agent'] = [
                    'id' => $staff->staffid,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'email' => $staff->email,
                ];
            }
        }

        return $relationships;
    }

    /**
     * Fetch project relationships
     */
    private function fetch_project_relationships($id, $data)
    {
        $relationships = [];

        // Client
        if (isset($data['clientid']) && $data['clientid'] > 0) {
            $this->CI->load->model('clients_model');
            $client = $this->CI->clients_model->get($data['clientid']);
            if ($client) {
                $relationships['client'] = [
                    'id' => $client->userid,
                    'company' => $client->company,
                ];
            }
        }

        // Tasks count
        $this->CI->db->where('rel_type', 'project');
        $this->CI->db->where('rel_id', $id);
        $relationships['tasks_count'] = $this->CI->db->count_all_results(db_prefix() . 'tasks');

        // Members count
        $this->CI->db->where('project_id', $id);
        $relationships['members_count'] = $this->CI->db->count_all_results(db_prefix() . 'project_members');

        return $relationships;
    }

    /**
     * Fetch task relationships
     */
    private function fetch_task_relationships($id, $data)
    {
        $relationships = [];

        // Related to (project, invoice, etc.)
        if (isset($data['rel_type']) && isset($data['rel_id']) && $data['rel_id'] > 0) {
            $relationships['related_to'] = [
                'type' => $data['rel_type'],
                'id' => $data['rel_id'],
            ];

            // Get related record name
            if ($data['rel_type'] == 'project') {
                $this->CI->load->model('projects_model');
                $project = $this->CI->projects_model->get($data['rel_id']);
                if ($project) {
                    $relationships['related_to']['name'] = $project->name;
                }
            }
        }

        // Assigned members
        $this->CI->db->where('taskid', $id);
        $assigned = $this->CI->db->get(db_prefix() . 'task_assigned')->result_array();
        $relationships['assigned_count'] = count($assigned);

        return $relationships;
    }

    /**
     * Fetch ticket relationships
     */
    private function fetch_ticket_relationships($id, $data)
    {
        $relationships = [];

        // Client
        if (isset($data['userid']) && $data['userid'] > 0) {
            $this->CI->load->model('clients_model');
            $client = $this->CI->clients_model->get($data['userid']);
            if ($client) {
                $relationships['client'] = [
                    'id' => $client->userid,
                    'company' => $client->company,
                ];
            }
        }

        // Contact
        if (isset($data['contactid']) && $data['contactid'] > 0) {
            $contact = $this->CI->clients_model->get_contact($data['contactid']);
            if ($contact) {
                $relationships['contact'] = [
                    'id' => $contact->id,
                    'firstname' => $contact->firstname,
                    'lastname' => $contact->lastname,
                    'email' => $contact->email,
                ];
            }
        }

        // Assigned staff
        if (isset($data['assigned']) && $data['assigned'] > 0) {
            $staff = get_staff($data['assigned']);
            if ($staff) {
                $relationships['assigned_to'] = [
                    'id' => $staff->staffid,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'email' => $staff->email,
                ];
            }
        }

        return $relationships;
    }

    /**
     * Fetch proposal relationships
     */
    private function fetch_proposal_relationships($id, $data)
    {
        $relationships = [];

        // Related to (lead, customer, etc.)
        if (isset($data['rel_type']) && isset($data['rel_id']) && $data['rel_id'] > 0) {
            $relationships['related_to'] = [
                'type' => $data['rel_type'],
                'id' => $data['rel_id'],
            ];
        }

        return $relationships;
    }

    /**
     * Fetch contract relationships
     */
    private function fetch_contract_relationships($id, $data)
    {
        $relationships = [];

        // Client
        if (isset($data['client']) && $data['client'] > 0) {
            $this->CI->load->model('clients_model');
            $client = $this->CI->clients_model->get($data['client']);
            if ($client) {
                $relationships['client'] = [
                    'id' => $client->userid,
                    'company' => $client->company,
                ];
            }
        }

        return $relationships;
    }

    /**
     * Fetch expense relationships
     */
    private function fetch_expense_relationships($id, $data)
    {
        $relationships = [];

        // Client
        if (isset($data['clientid']) && $data['clientid'] > 0) {
            $this->CI->load->model('clients_model');
            $client = $this->CI->clients_model->get($data['clientid']);
            if ($client) {
                $relationships['client'] = [
                    'id' => $client->userid,
                    'company' => $client->company,
                ];
            }
        }

        // Project
        if (isset($data['project_id']) && $data['project_id'] > 0) {
            $this->CI->load->model('projects_model');
            $project = $this->CI->projects_model->get($data['project_id']);
            if ($project) {
                $relationships['project'] = [
                    'id' => $project->id,
                    'name' => $project->name,
                ];
            }
        }

        return $relationships;
    }

    /**
     * Track changes between old and new data
     *
     * @param array $new_data New data
     * @param array $old_data Old data
     * @return array Changed fields with old and new values
     */
    private function track_changes($new_data, $old_data)
    {
        $changes = [];

        // Convert objects to arrays
        if (is_object($new_data)) {
            $new_data = (array) $new_data;
        }
        if (is_object($old_data)) {
            $old_data = (array) $old_data;
        }

        // Compare each field
        foreach ($new_data as $key => $new_value) {
            $old_value = isset($old_data[$key]) ? $old_data[$key] : null;

            // Skip if values are the same
            if ($new_value === $old_value) {
                continue;
            }

            // Skip date modified fields (these always change)
            if (in_array($key, ['datemodified', 'last_updated', 'updated_at'])) {
                continue;
            }

            $changes[$key] = [
                'old' => $old_value,
                'new' => $new_value,
                'changed' => true,
            ];
        }

        return $changes;
    }
}
