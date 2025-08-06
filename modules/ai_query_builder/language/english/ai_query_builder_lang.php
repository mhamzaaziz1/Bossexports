<?php

# Module Name
$lang['ai_query_builder'] = 'AI Query Builder';
$lang['ai_query_builder_settings'] = 'AI Query Builder Settings';

# Settings
$lang['openai_api_key'] = 'OpenAI API Key';
$lang['openai_api_key_help'] = 'Enter your OpenAI API key. You can get one from https://platform.openai.com/api-keys';
$lang['openai_model'] = 'OpenAI Model';
$lang['openai_model_help'] = 'Select the OpenAI model to use for generating SQL queries. GPT-4 is more accurate but more expensive.';
$lang['max_rows'] = 'Maximum Rows';
$lang['max_rows_help'] = 'Maximum number of rows to return in query results (1-1000).';
$lang['save'] = 'Save Settings';
$lang['settings_updated'] = 'Settings updated successfully.';
$lang['settings_update_failed'] = 'Failed to update settings.';

# Main UI
$lang['enter_your_query'] = 'Enter your natural language query';
$lang['query_placeholder'] = 'e.g., "Show me all customers who have unpaid invoices"';
$lang['run_query'] = 'Run Query';
$lang['export_csv'] = 'Export to CSV';
$lang['save_query'] = 'Save Query';
$lang['generated_sql'] = 'Generated SQL';
$lang['query_results'] = 'Query Results';
$lang['execution_time'] = 'Execution Time';
$lang['rows_returned'] = 'Rows Returned';
$lang['no_results'] = 'No results found';
$lang['loading'] = 'Loading...';
$lang['processing_query'] = 'Processing your query, please wait...';
$lang['query_name'] = 'Query Name';
$lang['enter_query_name'] = 'Enter a name for this query';
$lang['close'] = 'Close';
$lang['saved_queries'] = 'Saved Queries';
$lang['name'] = 'Name';
$lang['query'] = 'Query';
$lang['date'] = 'Date';
$lang['options'] = 'Options';
$lang['load'] = 'Load';

# Errors and Warnings
$lang['openai_api_key_missing'] = 'OpenAI API key is not set. Please configure it in the settings.';
$lang['openai_api_key_missing_warning'] = 'OpenAI API key is not configured. The AI Query Builder will not work without an API key.';
$lang['go_to_settings'] = 'Go to Settings';
$lang['query_empty'] = 'Please enter a query.';
$lang['sql_generation_failed'] = 'Failed to generate SQL from your query. Please try again or rephrase your query.';
$lang['only_select_queries_allowed'] = 'Only SELECT queries are allowed for security reasons.';
$lang['sql_empty'] = 'No SQL query to execute.';
$lang['no_data_to_export'] = 'No data to export.';
$lang['ajax_error'] = 'An error occurred while processing your request';
$lang['query_name_required'] = 'Please enter a name for the query.';
$lang['query_saved_successfully'] = 'Query saved successfully.';
$lang['query_save_failed'] = 'Failed to save the query.';
$lang['missing_parameters'] = 'Missing required parameters.';
$lang['query_not_found'] = 'Query not found.';

# Usage Instructions
$lang['usage_instructions'] = 'Usage Instructions';
$lang['how_to_use'] = 'How to Use';
$lang['usage_step_1'] = 'Enter a natural language query describing the data you want to retrieve.';
$lang['usage_step_2'] = 'Click "Run Query" to convert your query to SQL and execute it.';
$lang['usage_step_3'] = 'Review the generated SQL and the results in the table below.';
$lang['usage_step_4'] = 'Use the "Export to CSV" button to download the results if needed.';
$lang['usage_step_5'] = 'Click "Save Query" to save the query with a name for future use.';
$lang['usage_step_6'] = 'Use the saved queries list to quickly load and run previously saved queries.';

$lang['example_queries'] = 'Example Queries';
$lang['example_query_1'] = 'Show me all clients who have unpaid invoices';
$lang['example_query_2'] = 'List the top 10 customers by total invoice amount';
$lang['example_query_3'] = 'Show me all projects that are in progress with their assigned staff members';

$lang['limitations'] = 'Limitations';
$lang['limitation_1'] = 'Only SELECT queries are allowed for security reasons.';
$lang['limitation_2'] = 'Results are limited to a maximum number of rows (configurable in settings).';
$lang['limitation_3'] = 'Complex queries may require rephrasing to get accurate results.';
