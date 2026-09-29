<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * n8n Connector - Event Configuration
 *
 * This file defines all available Perfex CRM events that can trigger n8n webhooks.
 * Each event is mapped to its hook name, display label, and relevant database table.
 *
 * All functions for accessing this data are in helpers/n8n_connector_helper.php
 */

return [
        // ==================== LEADS ====================
        'leads' => [
            'label' => 'Leads',
            'icon' => 'fa-user-plus',
            'events' => [
                'lead_created' => [
                    'label' => 'Lead Created',
                    'hook' => 'lead_created',
                    'description' => 'Triggered when a new lead is created',
                    'table' => 'leads',
                    'include_relationships' => true,
                ],
                'lead_updated' => [
                    'label' => 'Lead Updated',
                    'hook' => 'lead_updated',
                    'description' => 'Triggered when a lead is updated',
                    'table' => 'leads',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'lead_deleted' => [
                    'label' => 'Lead Deleted',
                    'hook' => 'lead_deleted',
                    'description' => 'Triggered when a lead is deleted',
                    'table' => 'leads',
                    'include_relationships' => false,
                ],
                'lead_status_changed' => [
                    'label' => 'Lead Status Changed',
                    'hook' => 'lead_status_changed',
                    'description' => 'Triggered when lead status changes',
                    'table' => 'leads',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'lead_converted_to_customer' => [
                    'label' => 'Lead Converted to Customer',
                    'hook' => 'lead_converted_to_customer',
                    'description' => 'Triggered when a lead is converted to customer',
                    'table' => 'leads',
                    'include_relationships' => true,
                ],
            ],
        ],

        // ==================== CUSTOMERS/CLIENTS ====================
        'customers' => [
            'label' => 'Customers',
            'icon' => 'fa-users',
            'events' => [
                'customer_created' => [
                    'label' => 'Customer Created',
                    'hook' => 'client_created',
                    'description' => 'Triggered when a new customer is created',
                    'table' => 'clients',
                    'include_relationships' => true,
                ],
                'customer_updated' => [
                    'label' => 'Customer Updated',
                    'hook' => 'client_updated',
                    'description' => 'Triggered when a customer is updated',
                    'table' => 'clients',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'customer_deleted' => [
                    'label' => 'Customer Deleted',
                    'hook' => 'client_deleted',
                    'description' => 'Triggered when a customer is deleted',
                    'table' => 'clients',
                    'include_relationships' => false,
                ],
            ],
        ],

        // ==================== CONTACTS ====================
        'contacts' => [
            'label' => 'Contacts',
            'icon' => 'fa-address-card',
            'events' => [
                'contact_created' => [
                    'label' => 'Contact Created',
                    'hook' => 'contact_created',
                    'description' => 'Triggered when a new contact is created',
                    'table' => 'contacts',
                    'include_relationships' => true,
                ],
                'contact_updated' => [
                    'label' => 'Contact Updated',
                    'hook' => 'contact_updated',
                    'description' => 'Triggered when a contact is updated',
                    'table' => 'contacts',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'contact_deleted' => [
                    'label' => 'Contact Deleted',
                    'hook' => 'contact_deleted',
                    'description' => 'Triggered when a contact is deleted',
                    'table' => 'contacts',
                    'include_relationships' => false,
                ],
            ],
        ],

        // ==================== INVOICES ====================
        'invoices' => [
            'label' => 'Invoices',
            'icon' => 'fa-file-invoice-dollar',
            'events' => [
                'invoice_created' => [
                    'label' => 'Invoice Created',
                    'hook' => 'invoice_created',
                    'description' => 'Triggered when a new invoice is created',
                    'table' => 'invoices',
                    'include_relationships' => true,
                ],
                'invoice_updated' => [
                    'label' => 'Invoice Updated',
                    'hook' => 'invoice_updated',
                    'description' => 'Triggered when an invoice is updated',
                    'table' => 'invoices',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'invoice_deleted' => [
                    'label' => 'Invoice Deleted',
                    'hook' => 'invoice_deleted',
                    'description' => 'Triggered when an invoice is deleted',
                    'table' => 'invoices',
                    'include_relationships' => false,
                ],
                'invoice_status_changed' => [
                    'label' => 'Invoice Status Changed',
                    'hook' => 'invoice_status_changed',
                    'description' => 'Triggered when invoice status changes',
                    'table' => 'invoices',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'invoice_sent' => [
                    'label' => 'Invoice Sent to Customer',
                    'hook' => 'invoice_sent_to_customer',
                    'description' => 'Triggered when an invoice is sent',
                    'table' => 'invoices',
                    'include_relationships' => true,
                ],
            ],
        ],

        // ==================== PAYMENTS ====================
        'payments' => [
            'label' => 'Payments',
            'icon' => 'fa-money-bill-wave',
            'events' => [
                'payment_created' => [
                    'label' => 'Payment Created',
                    'hook' => 'after_payment_added',
                    'description' => 'Triggered when a payment is recorded',
                    'table' => 'invoicepaymentrecords',
                    'include_relationships' => true,
                ],
                'payment_deleted' => [
                    'label' => 'Payment Deleted',
                    'hook' => 'after_payment_deleted',
                    'description' => 'Triggered when a payment is deleted',
                    'table' => 'invoicepaymentrecords',
                    'include_relationships' => false,
                ],
            ],
        ],

        // ==================== ESTIMATES ====================
        'estimates' => [
            'label' => 'Estimates',
            'icon' => 'fa-file-contract',
            'events' => [
                'estimate_created' => [
                    'label' => 'Estimate Created',
                    'hook' => 'estimate_created',
                    'description' => 'Triggered when a new estimate is created',
                    'table' => 'estimates',
                    'include_relationships' => true,
                ],
                'estimate_updated' => [
                    'label' => 'Estimate Updated',
                    'hook' => 'estimate_updated',
                    'description' => 'Triggered when an estimate is updated',
                    'table' => 'estimates',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'estimate_deleted' => [
                    'label' => 'Estimate Deleted',
                    'hook' => 'estimate_deleted',
                    'description' => 'Triggered when an estimate is deleted',
                    'table' => 'estimates',
                    'include_relationships' => false,
                ],
                'estimate_status_changed' => [
                    'label' => 'Estimate Status Changed',
                    'hook' => 'estimate_status_changed',
                    'description' => 'Triggered when estimate status changes',
                    'table' => 'estimates',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'estimate_sent' => [
                    'label' => 'Estimate Sent to Customer',
                    'hook' => 'estimate_sent_to_customer',
                    'description' => 'Triggered when an estimate is sent',
                    'table' => 'estimates',
                    'include_relationships' => true,
                ],
                'estimate_accepted' => [
                    'label' => 'Estimate Accepted',
                    'hook' => 'estimate_accepted',
                    'description' => 'Triggered when an estimate is accepted by customer',
                    'table' => 'estimates',
                    'include_relationships' => true,
                ],
                'estimate_declined' => [
                    'label' => 'Estimate Declined',
                    'hook' => 'estimate_declined',
                    'description' => 'Triggered when an estimate is declined by customer',
                    'table' => 'estimates',
                    'include_relationships' => true,
                ],
            ],
        ],

        // ==================== PROPOSALS ====================
        'proposals' => [
            'label' => 'Proposals',
            'icon' => 'fa-file-signature',
            'events' => [
                'proposal_created' => [
                    'label' => 'Proposal Created',
                    'hook' => 'proposal_created',
                    'description' => 'Triggered when a new proposal is created',
                    'table' => 'proposals',
                    'include_relationships' => true,
                ],
                'proposal_updated' => [
                    'label' => 'Proposal Updated',
                    'hook' => 'proposal_updated',
                    'description' => 'Triggered when a proposal is updated',
                    'table' => 'proposals',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'proposal_deleted' => [
                    'label' => 'Proposal Deleted',
                    'hook' => 'proposal_deleted',
                    'description' => 'Triggered when a proposal is deleted',
                    'table' => 'proposals',
                    'include_relationships' => false,
                ],
                'proposal_accepted' => [
                    'label' => 'Proposal Accepted',
                    'hook' => 'proposal_accepted',
                    'description' => 'Triggered when a proposal is accepted by customer',
                    'table' => 'proposals',
                    'include_relationships' => true,
                ],
                'proposal_declined' => [
                    'label' => 'Proposal Declined',
                    'hook' => 'proposal_declined',
                    'description' => 'Triggered when a proposal is declined by customer',
                    'table' => 'proposals',
                    'include_relationships' => true,
                ],
            ],
        ],

        // ==================== PROJECTS ====================
        'projects' => [
            'label' => 'Projects',
            'icon' => 'fa-project-diagram',
            'events' => [
                'project_created' => [
                    'label' => 'Project Created',
                    'hook' => 'project_created',
                    'description' => 'Triggered when a new project is created',
                    'table' => 'projects',
                    'include_relationships' => true,
                ],
                'project_updated' => [
                    'label' => 'Project Updated',
                    'hook' => 'project_updated',
                    'description' => 'Triggered when a project is updated',
                    'table' => 'projects',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'project_deleted' => [
                    'label' => 'Project Deleted',
                    'hook' => 'project_deleted',
                    'description' => 'Triggered when a project is deleted',
                    'table' => 'projects',
                    'include_relationships' => false,
                ],
                'project_status_changed' => [
                    'label' => 'Project Status Changed',
                    'hook' => 'project_status_changed',
                    'description' => 'Triggered when project status changes',
                    'table' => 'projects',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
            ],
        ],

        // ==================== TASKS ====================
        'tasks' => [
            'label' => 'Tasks',
            'icon' => 'fa-tasks',
            'events' => [
                'task_created' => [
                    'label' => 'Task Created',
                    'hook' => 'task_created',
                    'description' => 'Triggered when a new task is created',
                    'table' => 'tasks',
                    'include_relationships' => true,
                ],
                'task_updated' => [
                    'label' => 'Task Updated',
                    'hook' => 'task_updated',
                    'description' => 'Triggered when a task is updated',
                    'table' => 'tasks',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'task_deleted' => [
                    'label' => 'Task Deleted',
                    'hook' => 'task_deleted',
                    'description' => 'Triggered when a task is deleted',
                    'table' => 'tasks',
                    'include_relationships' => false,
                ],
                'task_status_changed' => [
                    'label' => 'Task Status Changed',
                    'hook' => 'task_status_changed',
                    'description' => 'Triggered when task status changes',
                    'table' => 'tasks',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
            ],
        ],

        // ==================== TICKETS ====================
        'tickets' => [
            'label' => 'Support Tickets',
            'icon' => 'fa-ticket-alt',
            'events' => [
                'ticket_created' => [
                    'label' => 'Ticket Created',
                    'hook' => 'ticket_created',
                    'description' => 'Triggered when a new ticket is created',
                    'table' => 'tickets',
                    'include_relationships' => true,
                ],
                'ticket_updated' => [
                    'label' => 'Ticket Updated',
                    'hook' => 'ticket_updated',
                    'description' => 'Triggered when a ticket is updated',
                    'table' => 'tickets',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'ticket_deleted' => [
                    'label' => 'Ticket Deleted',
                    'hook' => 'ticket_deleted',
                    'description' => 'Triggered when a ticket is deleted',
                    'table' => 'tickets',
                    'include_relationships' => false,
                ],
                'ticket_status_changed' => [
                    'label' => 'Ticket Status Changed',
                    'hook' => 'ticket_status_changed',
                    'description' => 'Triggered when ticket status changes',
                    'table' => 'tickets',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'ticket_reply_added' => [
                    'label' => 'Ticket Reply Added',
                    'hook' => 'after_ticket_reply_added',
                    'description' => 'Triggered when a reply is added to ticket',
                    'table' => 'tickets',
                    'include_relationships' => true,
                ],
            ],
        ],

        // ==================== CONTRACTS ====================
        'contracts' => [
            'label' => 'Contracts',
            'icon' => 'fa-file-contract',
            'events' => [
                'contract_created' => [
                    'label' => 'Contract Created',
                    'hook' => 'contract_created',
                    'description' => 'Triggered when a new contract is created',
                    'table' => 'contracts',
                    'include_relationships' => true,
                ],
                'contract_updated' => [
                    'label' => 'Contract Updated',
                    'hook' => 'contract_updated',
                    'description' => 'Triggered when a contract is updated',
                    'table' => 'contracts',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'contract_deleted' => [
                    'label' => 'Contract Deleted',
                    'hook' => 'contract_deleted',
                    'description' => 'Triggered when a contract is deleted',
                    'table' => 'contracts',
                    'include_relationships' => false,
                ],
            ],
        ],

        // ==================== EXPENSES ====================
        'expenses' => [
            'label' => 'Expenses',
            'icon' => 'fa-receipt',
            'events' => [
                'expense_created' => [
                    'label' => 'Expense Created',
                    'hook' => 'expense_created',
                    'description' => 'Triggered when a new expense is created',
                    'table' => 'expenses',
                    'include_relationships' => true,
                ],
                'expense_updated' => [
                    'label' => 'Expense Updated',
                    'hook' => 'expense_updated',
                    'description' => 'Triggered when an expense is updated',
                    'table' => 'expenses',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'expense_deleted' => [
                    'label' => 'Expense Deleted',
                    'hook' => 'expense_deleted',
                    'description' => 'Triggered when an expense is deleted',
                    'table' => 'expenses',
                    'include_relationships' => false,
                ],
            ],
        ],

        // ==================== STAFF ====================
        'staff' => [
            'label' => 'Staff Members',
            'icon' => 'fa-user-tie',
            'events' => [
                'staff_created' => [
                    'label' => 'Staff Member Created',
                    'hook' => 'staff_member_created',
                    'description' => 'Triggered when a new staff member is created',
                    'table' => 'staff',
                    'include_relationships' => true,
                ],
                'staff_updated' => [
                    'label' => 'Staff Member Updated',
                    'hook' => 'staff_member_updated',
                    'description' => 'Triggered when a staff member is updated',
                    'table' => 'staff',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'staff_deleted' => [
                    'label' => 'Staff Member Deleted',
                    'hook' => 'staff_member_deleted',
                    'description' => 'Triggered when a staff member is deleted',
                    'table' => 'staff',
                    'include_relationships' => false,
                ],
            ],
        ],

        // ==================== CREDIT NOTES ====================
        'credit_notes' => [
            'label' => 'Credit Notes',
            'icon' => 'fa-file-invoice',
            'events' => [
                'credit_note_created' => [
                    'label' => 'Credit Note Created',
                    'hook' => 'credit_note_created',
                    'description' => 'Triggered when a new credit note is created',
                    'table' => 'creditnotes',
                    'include_relationships' => true,
                ],
                'credit_note_updated' => [
                    'label' => 'Credit Note Updated',
                    'hook' => 'credit_note_updated',
                    'description' => 'Triggered when a credit note is updated',
                    'table' => 'creditnotes',
                    'include_relationships' => true,
                    'track_changes' => true,
                ],
                'credit_note_deleted' => [
                    'label' => 'Credit Note Deleted',
                    'hook' => 'credit_note_deleted',
                    'description' => 'Triggered when a credit note is deleted',
                    'table' => 'creditnotes',
                    'include_relationships' => false,
                ],
            ],
        ],
    ];

