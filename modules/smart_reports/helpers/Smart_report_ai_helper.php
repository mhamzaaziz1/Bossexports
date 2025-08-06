<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Process natural language query and convert it to SQL
 * @param  string $query Natural language query
 * @return array        Result with SQL and metadata
 */
function process_nlp_query($query)
{
    $CI = &get_instance();

    // Load OpenAI API key from settings
    $api_key = get_option('openai_api_key');

    // If no API key is set, return an error
    if (empty($api_key)) {
        return [
            'success' => false,
            'error' => 'OpenAI API key is not configured. Please set it in the settings.'
        ];
    }

    try {
        // Prepare the database schema information to help the AI understand the database structure
        $schema_info = get_database_schema_info();

        // Prepare the prompt for OpenAI
        $prompt = prepare_openai_prompt($query, $schema_info);

        // Call OpenAI API
        $response = call_openai_api($prompt, $api_key);

        // Parse the response to extract SQL
        $sql = parse_openai_response($response);

        // Validate the SQL to ensure it's safe
        $validated_sql = validate_sql($sql);

        return [
            'success' => true,
            'sql' => $validated_sql,
            'original_query' => $query
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get database schema information to help the AI understand the database structure
 * @return string Database schema information
 */
function get_database_schema_info()
{
    $CI = &get_instance();

    // Get list of tables
    $tables = $CI->db->list_tables();

    $schema_info = "Database Schema:\n";

    foreach ($tables as $table) {
        // Skip tables that are not relevant for reporting
        if (strpos($table, 'sessions') !== false || 
            strpos($table, 'migrations') !== false || 
            strpos($table, 'options') !== false) {
            continue;
        }

        $schema_info .= "Table: " . $table . "\n";

        // Get fields for this table
        $fields = $CI->db->field_data($table);

        foreach ($fields as $field) {
            $schema_info .= "  - " . $field->name . " (" . $field->type;

            if (isset($field->max_length) && $field->max_length) {
                $schema_info .= ", max length: " . $field->max_length;
            }

            if (isset($field->primary_key) && $field->primary_key) {
                $schema_info .= ", primary key";
            }

            $schema_info .= ")\n";
        }

        $schema_info .= "\n";
    }

    return $schema_info;
}

/**
 * Prepare the prompt for OpenAI
 * @param  string $query       Natural language query
 * @param  string $schema_info Database schema information
 * @return string             Formatted prompt
 */
function prepare_openai_prompt($query, $schema_info)
{
    $prompt = <<<EOT
You are a SQL query generator for a CRM system. Convert the following natural language query into a SQL query based on the database schema provided.

Database Schema:
$schema_info

User Query: $query

Rules:
1. Generate only a valid SQL query that will work with MySQL.
2. Do not include any explanations, just the SQL query.
3. Make sure to use proper table prefixes as shown in the schema.
4. For date ranges, use proper MySQL date functions.
5. If the query mentions "top N" or "limit to N", use LIMIT in the SQL.
6. If grouping is needed, use appropriate GROUP BY clauses.
7. If sorting is needed, use appropriate ORDER BY clauses.
8. Join tables as needed to fulfill the query requirements.
9. Use COUNT, SUM, AVG, etc. for aggregations as appropriate.
10. If the query is ambiguous, make reasonable assumptions based on common CRM reporting needs.

SQL Query:
EOT;

    return $prompt;
}

/**
 * Call OpenAI API
 * @param  string $prompt  Prompt for OpenAI
 * @param  string $api_key OpenAI API key
 * @return string         OpenAI response
 */
function call_openai_api($prompt, $api_key)
{
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    // Set a reasonable timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    // Enable SSL verification but provide fallback
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    // Prepare the data - try to use GPT-3.5-turbo if GPT-4 is not available
    $data = [
        'model' => 'gpt-3.5-turbo',  // Use GPT-3.5-turbo as it's more widely available
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a SQL query generator for a CRM system. Your task is to convert natural language queries into valid SQL queries.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,  // Lower temperature for more deterministic outputs
        'max_tokens' => 500    // Limit the response length
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the cURL session
    $response = curl_exec($ch);

    // Get HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Connection error: ' . $error);
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the response
    $response_data = json_decode($response, true);

    // Check for API errors
    if ($http_code != 200) {
        $error_message = 'OpenAI API error';

        if (isset($response_data['error']['message'])) {
            $error_message .= ': ' . $response_data['error']['message'];
        } else {
            $error_message .= ' (HTTP status code: ' . $http_code . ')';
        }

        // Log the error for debugging
        log_activity('OpenAI API error: ' . $error_message . ' - Response: ' . json_encode($response_data));

        throw new Exception($error_message);
    }

    // Check if the response is valid
    if (!isset($response_data['choices'][0]['message']['content'])) {
        throw new Exception('Invalid response format from OpenAI API');
    }

    return $response_data['choices'][0]['message']['content'];
}

/**
 * Parse OpenAI response to extract SQL
 * @param  string $response OpenAI response
 * @return string          Extracted SQL
 */
function parse_openai_response($response)
{
    // The response should be just the SQL query, but let's clean it up just in case
    $response = trim($response);

    // Remove any markdown code block indicators
    $response = preg_replace('/^```sql\s*|^```\s*|```\s*$/', '', $response);

    return trim($response);
}

/**
 * Validate SQL to ensure it's safe
 * @param  string $sql SQL query
 * @return string     Validated SQL
 */
function validate_sql($sql)
{
    // Check for dangerous SQL commands
    $dangerous_commands = [
        'DROP',
        'TRUNCATE',
        'DELETE',
        'UPDATE',
        'INSERT',
        'ALTER',
        'CREATE',
        'GRANT',
        'REVOKE'
    ];

    foreach ($dangerous_commands as $command) {
        if (preg_match('/\b' . $command . '\b/i', $sql)) {
            throw new Exception('SQL contains dangerous command: ' . $command);
        }
    }

    // Ensure the query starts with SELECT
    if (!preg_match('/^\s*SELECT\b/i', $sql)) {
        throw new Exception('SQL must start with SELECT');
    }

    return $sql;
}

/**
 * Fallback function for when OpenAI is not available or configured
 * @param  string $query Natural language query
 * @return string       Generated SQL
 */
function generate_fallback_sql($query)
{
    $CI = &get_instance();

    // Simple keyword matching for common report types
    $query = strtolower($query);

    // Default SQL
    $sql = "SELECT * FROM " . db_prefix() . "clients LIMIT 10";

    // Check for common patterns
    if (strpos($query, 'top') !== false && strpos($query, 'customer') !== false) {
        // Top customers query
        if (strpos($query, 'revenue') !== false || strpos($query, 'sales') !== false) {
            $sql = "SELECT " . db_prefix() . "clients.company as customer, 
                   SUM(" . db_prefix() . "invoices.total) as total_amount 
                   FROM " . db_prefix() . "invoices 
                   LEFT JOIN " . db_prefix() . "clients ON " . db_prefix() . "clients.userid = " . db_prefix() . "invoices.clientid 
                   GROUP BY " . db_prefix() . "clients.company 
                   ORDER BY total_amount DESC 
                   LIMIT 10";
        }
    } elseif (strpos($query, 'sales') !== false && strpos($query, 'month') !== false) {
        // Monthly sales query
        $sql = "SELECT DATE_FORMAT(" . db_prefix() . "invoices.date, '%Y-%m') as month, 
               SUM(" . db_prefix() . "invoices.total) as total_amount 
               FROM " . db_prefix() . "invoices 
               WHERE " . db_prefix() . "invoices.date >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
               GROUP BY month 
               ORDER BY month";
    } elseif (strpos($query, 'lead') !== false && strpos($query, 'source') !== false) {
        // Leads by source query
        $sql = "SELECT " . db_prefix() . "leads_sources.name as source, 
               COUNT(" . db_prefix() . "leads.id) as lead_count 
               FROM " . db_prefix() . "leads 
               LEFT JOIN " . db_prefix() . "leads_sources ON " . db_prefix() . "leads_sources.id = " . db_prefix() . "leads.source 
               GROUP BY " . db_prefix() . "leads_sources.id 
               ORDER BY lead_count DESC";
    }

    return $sql;
}
