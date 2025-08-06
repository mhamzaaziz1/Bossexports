<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ai_query_builder extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Check if user has permission to access this module
        if (!has_permission('ai_query_builder', '', 'view')) {
            access_denied('ai_query_builder');
        }

        // Load required models
        $this->load->model('ai_query_builder_model');
    }

    /**
     * Main page for AI Query Builder
     */
    public function index()
    {
        $data['title'] = _l('ai_query_builder');

        // Get settings
        $settings = $this->ai_query_builder_model->get_settings();
        $data['settings'] = $settings;

        // Get saved queries
        $data['saved_queries'] = $this->ai_query_builder_model->get_saved_queries();

        // Load the view
        $this->load->view('ai_query_builder/query_builder', $data);
    }

    /**
     * Settings page for AI Query Builder
     */
    public function settings()
    {
        // Check if user has admin permission
        if (!is_admin()) {
            access_denied('ai_query_builder_settings');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            $success = $this->ai_query_builder_model->update_settings($data);

            if ($success) {
                set_alert('success', _l('settings_updated'));
            } else {
                set_alert('danger', _l('settings_update_failed'));
            }

            redirect(admin_url('ai_query_builder/settings'));
        }

        $data['title'] = _l('ai_query_builder_settings');
        $data['settings'] = $this->ai_query_builder_model->get_settings();

        $this->load->view('ai_query_builder/settings', $data);
    }

    /**
     * Process the natural language query and convert it to SQL
     */
    public function process_query()
    {
        // Check if this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $query = $this->input->post('query');

        if (empty($query)) {
            echo json_encode([
                'success' => false,
                'message' => _l('query_empty')
            ]);
            return;
        }

        // Get database schema
        $schema = $this->get_database_schema();

        // Get settings
        $settings = $this->ai_query_builder_model->get_settings();

        // Check if API key is set
        if (empty($settings->openai_api_key)) {
            echo json_encode([
                'success' => false,
                'message' => _l('openai_api_key_missing')
            ]);
            return;
        }

        // Start timing the execution
        $start_time = microtime(true);

        // Send query to OpenAI API
        $sql = $this->generate_sql_from_query($query, $schema, $settings);

        if (!$sql) {
            echo json_encode([
                'success' => false,
                'message' => _l('sql_generation_failed')
            ]);
            return;
        }

        // Validate that it's a SELECT query
        if (!$this->is_select_query($sql)) {
            echo json_encode([
                'success' => false,
                'message' => _l('only_select_queries_allowed'),
                'sql' => $sql
            ]);
            return;
        }

        // Execute the query
        try {
            $result = $this->db->query($sql);
            $rows = $result->result_array();

            // Limit the number of rows
            $rows = array_slice($rows, 0, $settings->max_rows);

            // Calculate execution time
            $execution_time = microtime(true) - $start_time;

            // Log the query
            $log_id = $this->ai_query_builder_model->log_query([
                'query' => $query,
                'sql' => $sql,
                'execution_time' => $execution_time,
                'rows_returned' => count($rows),
                'staff_id' => get_staff_user_id(),
                'date_created' => date('Y-m-d H:i:s')
            ]);

            echo json_encode([
                'success' => true,
                'sql' => $sql,
                'data' => $rows,
                'execution_time' => round($execution_time, 4),
                'row_count' => count($rows),
                'log_id' => $log_id
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'sql' => $sql
            ]);
        }
    }

    /**
     * Get database schema information
     */
    private function get_database_schema()
    {
        $db_name = $this->db->database;

        $sql = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE REFERENCED_TABLE_SCHEMA = '{$db_name}'";

        $result = $this->db->query($sql);
        $foreign_keys = $result->result_array();

        // Get all tables and columns
        $sql = "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '{$db_name}'";

        $result = $this->db->query($sql);
        $columns = $result->result_array();

        // Format the schema information
        $schema = [
            'tables' => [],
            'foreign_keys' => $foreign_keys
        ];

        foreach ($columns as $column) {
            $table_name = $column['TABLE_NAME'];

            if (!isset($schema['tables'][$table_name])) {
                $schema['tables'][$table_name] = [];
            }

            $schema['tables'][$table_name][] = [
                'name' => $column['COLUMN_NAME'],
                'type' => $column['DATA_TYPE']
            ];
        }

        return $schema;
    }

    /**
     * Generate SQL from natural language query using OpenAI API
     */
    private function generate_sql_from_query($query, $schema, $settings)
    {
        $api_key = $settings->openai_api_key;
        $model = $settings->model;

        // Format the schema as a string
        $schema_str = json_encode($schema, JSON_PRETTY_PRINT);

        // Create the prompt
        $prompt = "Convert this user query to a safe MySQL SELECT query based on this schema:\n";
        $prompt .= $schema_str . "\n\n";
        $prompt .= "Query: " . $query . "\n";
        $prompt .= "Return only the SQL query without any explanations.";

        // Set up the API request
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ];

        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a SQL expert. Your task is to convert natural language queries into SQL SELECT statements. Do not include any explanations, just return the SQL query.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 500
        ];

        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the request
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse the response
        $response_data = json_decode($response, true);

        if (isset($response_data['choices'][0]['message']['content'])) {
            $sql = trim($response_data['choices'][0]['message']['content']);

            // Remove any backticks or markdown formatting
            $sql = str_replace('```sql', '', $sql);
            $sql = str_replace('```', '', $sql);

            return trim($sql);
        }

        return false;
    }

    /**
     * Check if the query is a SELECT query
     */
    private function is_select_query($sql)
    {
        $sql = trim($sql);
        return stripos($sql, 'SELECT') === 0 && 
               stripos($sql, 'INSERT') === false && 
               stripos($sql, 'UPDATE') === false && 
               stripos($sql, 'DELETE') === false && 
               stripos($sql, 'DROP') === false && 
               stripos($sql, 'TRUNCATE') === false && 
               stripos($sql, 'ALTER') === false && 
               stripos($sql, 'CREATE') === false;
    }

    /**
     * Save a query with a name
     */
    public function save_query()
    {
        // Check if this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');
        $name = $this->input->post('name');

        if (empty($id) || empty($name)) {
            echo json_encode([
                'success' => false,
                'message' => _l('missing_parameters')
            ]);
            return;
        }

        $success = $this->ai_query_builder_model->save_query($id, $name);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => _l('query_saved_successfully')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('query_save_failed')
            ]);
        }
    }

    /**
     * Load a saved query
     */
    public function load_query()
    {
        // Check if this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        if (empty($id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('missing_parameters')
            ]);
            return;
        }

        $this->db->where('id', $id);
        $query = $this->db->get(db_prefix() . 'ai_query_builder_logs');

        if ($query->num_rows() > 0) {
            $log = $query->row();

            echo json_encode([
                'success' => true,
                'query' => $log->query,
                'sql' => $log->sql
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('query_not_found')
            ]);
        }
    }

    /**
     * Export query results to CSV
     */
    public function export_csv()
    {
        // Check if this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $sql = $this->input->post('sql');

        if (empty($sql)) {
            echo json_encode([
                'success' => false,
                'message' => _l('sql_empty')
            ]);
            return;
        }

        // Validate that it's a SELECT query
        if (!$this->is_select_query($sql)) {
            echo json_encode([
                'success' => false,
                'message' => _l('only_select_queries_allowed')
            ]);
            return;
        }

        // Execute the query
        try {
            $result = $this->db->query($sql);
            $rows = $result->result_array();

            // Get settings
            $settings = $this->ai_query_builder_model->get_settings();

            // Limit the number of rows
            $rows = array_slice($rows, 0, $settings->max_rows);

            if (empty($rows)) {
                echo json_encode([
                    'success' => false,
                    'message' => _l('no_data_to_export')
                ]);
                return;
            }

            // Create a temporary file
            $temp_file = tempnam(sys_get_temp_dir(), 'csv');
            $file = fopen($temp_file, 'w');

            // Add headers
            fputcsv($file, array_keys($rows[0]));

            // Add rows
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

            // Read the file contents
            $csv_content = file_get_contents($temp_file);

            // Delete the temporary file
            unlink($temp_file);

            echo json_encode([
                'success' => true,
                'csv_content' => base64_encode($csv_content),
                'filename' => 'query_results_' . date('Y-m-d') . '.csv'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
