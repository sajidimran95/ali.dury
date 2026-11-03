<?php

// Reference primary keys, AUTO_INCREMENT columns, indexes and foreign keys for the
// UltimatePOS schema, captured from a healthy install. Used by the
// repair_missing_keys_and_auto_increment migration. Do not edit by hand.

return [
    'account_transactions' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'account_transactions_account_id_index',
                'unique' => false,
                'columns' => [
                    'account_id',
                ],
            ],
            [
                'name' => 'account_transactions_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'account_transactions_operation_date_index',
                'unique' => false,
                'columns' => [
                    'operation_date',
                ],
            ],
            [
                'name' => 'account_transactions_sub_type_index',
                'unique' => false,
                'columns' => [
                    'sub_type',
                ],
            ],
            [
                'name' => 'account_transactions_transaction_id_index',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
            [
                'name' => 'account_transactions_transaction_payment_id_index',
                'unique' => false,
                'columns' => [
                    'transaction_payment_id',
                ],
            ],
            [
                'name' => 'account_transactions_transfer_transaction_id_index',
                'unique' => false,
                'columns' => [
                    'transfer_transaction_id',
                ],
            ],
            [
                'name' => 'account_transactions_type_index',
                'unique' => false,
                'columns' => [
                    'type',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'account_types' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'account_types_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'account_types_parent_account_type_id_index',
                'unique' => false,
                'columns' => [
                    'parent_account_type_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'accounts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'accounts_account_type_id_index',
                'unique' => false,
                'columns' => [
                    'account_type_id',
                ],
            ],
            [
                'name' => 'accounts_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'accounts_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'activity_log' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'activity_log_log_name_index',
                'unique' => false,
                'columns' => [
                    'log_name',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ai_admin_alerts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ai_admin_alerts_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'ai_admin_alerts_business_id_read_at_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'read_at',
                ],
            ],
            [
                'name' => 'ai_admin_alerts_type_index',
                'unique' => false,
                'columns' => [
                    'type',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ai_assistance_settings' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ai_assistance_settings_business_id_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'ai_assistance_settings_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'ai_chat_messages' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ai_chat_messages_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'ai_chat_messages_user_id_index',
                'unique' => false,
                'columns' => [
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ai_match_reviews' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ai_match_reviews_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'ai_match_reviews_po_id_index',
                'unique' => false,
                'columns' => [
                    'po_id',
                ],
            ],
            [
                'name' => 'ai_match_reviews_po_line_id_unique',
                'unique' => true,
                'columns' => [
                    'po_line_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'barcodes' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'barcodes_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'barcodes_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'bookings' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'bookings_booking_status_index',
                'unique' => false,
                'columns' => [
                    'booking_status',
                ],
            ],
            [
                'name' => 'bookings_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'bookings_contact_id_foreign',
                'unique' => false,
                'columns' => [
                    'contact_id',
                ],
            ],
            [
                'name' => 'bookings_correspondent_id_index',
                'unique' => false,
                'columns' => [
                    'correspondent_id',
                ],
            ],
            [
                'name' => 'bookings_created_by_foreign',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'bookings_location_id_index',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'bookings_table_id_index',
                'unique' => false,
                'columns' => [
                    'table_id',
                ],
            ],
            [
                'name' => 'bookings_waiter_id_index',
                'unique' => false,
                'columns' => [
                    'waiter_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'bookings_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'bookings_contact_id_foreign',
                'columns' => [
                    'contact_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'contacts',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'bookings_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'brands' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'brands_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'brands_created_by_foreign',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'brands_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'brands_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'bulk_price_updates' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'bulk_price_updates_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'bulk_price_updates_created_by_foreign',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'bulk_price_updates_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'bulk_price_updates_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'business' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'business_currency_id_foreign',
                'unique' => false,
                'columns' => [
                    'currency_id',
                ],
            ],
            [
                'name' => 'business_default_sales_tax_foreign',
                'unique' => false,
                'columns' => [
                    'default_sales_tax',
                ],
            ],
            [
                'name' => 'business_owner_id_foreign',
                'unique' => false,
                'columns' => [
                    'owner_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'business_currency_id_foreign',
                'columns' => [
                    'currency_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'currencies',
                'on_delete' => 'NO ACTION',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'business_default_sales_tax_foreign',
                'columns' => [
                    'default_sales_tax',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'tax_rates',
                'on_delete' => 'NO ACTION',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'business_owner_id_foreign',
                'columns' => [
                    'owner_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'business_locations' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'business_locations_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'business_locations_invoice_layout_id_foreign',
                'unique' => false,
                'columns' => [
                    'invoice_layout_id',
                ],
            ],
            [
                'name' => 'business_locations_invoice_scheme_id_foreign',
                'unique' => false,
                'columns' => [
                    'invoice_scheme_id',
                ],
            ],
            [
                'name' => 'business_locations_printer_id_index',
                'unique' => false,
                'columns' => [
                    'printer_id',
                ],
            ],
            [
                'name' => 'business_locations_receipt_printer_type_index',
                'unique' => false,
                'columns' => [
                    'receipt_printer_type',
                ],
            ],
            [
                'name' => 'business_locations_sale_invoice_layout_id_index',
                'unique' => false,
                'columns' => [
                    'sale_invoice_layout_id',
                ],
            ],
            [
                'name' => 'business_locations_selling_price_group_id_index',
                'unique' => false,
                'columns' => [
                    'selling_price_group_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'business_locations_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'business_locations_invoice_layout_id_foreign',
                'columns' => [
                    'invoice_layout_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'invoice_layouts',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'business_locations_invoice_scheme_id_foreign',
                'columns' => [
                    'invoice_scheme_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'invoice_schemes',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'cash_denominations' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [
            [
                'name' => 'cash_denominations_model_type_model_id_index',
                'unique' => false,
                'columns' => [
                    'model_type',
                    'model_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'cash_register_transactions' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'cash_register_transactions_cash_register_id_foreign',
                'unique' => false,
                'columns' => [
                    'cash_register_id',
                ],
            ],
            [
                'name' => 'cash_register_transactions_transaction_id_index',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
            [
                'name' => 'cash_register_transactions_transaction_payment_id_foreign',
                'unique' => false,
                'columns' => [
                    'transaction_payment_id',
                ],
            ],
            [
                'name' => 'cash_register_transactions_transaction_type_index',
                'unique' => false,
                'columns' => [
                    'transaction_type',
                ],
            ],
            [
                'name' => 'cash_register_transactions_type_index',
                'unique' => false,
                'columns' => [
                    'type',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'cash_register_transactions_cash_register_id_foreign',
                'columns' => [
                    'cash_register_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'cash_registers',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'cash_register_transactions_transaction_payment_id_foreign',
                'columns' => [
                    'transaction_payment_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'transaction_payments',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'cash_registers' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'cash_registers_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'cash_registers_location_id_index',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'cash_registers_user_id_foreign',
                'unique' => false,
                'columns' => [
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'cash_registers_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'cash_registers_user_id_foreign',
                'columns' => [
                    'user_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'categories' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'categories_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'categories_created_by_foreign',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'categories_parent_id_index',
                'unique' => false,
                'columns' => [
                    'parent_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'categories_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'categories_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'categorizables' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'categorizables_categorizable_type_categorizable_id_index',
                'unique' => false,
                'columns' => [
                    'categorizable_type',
                    'categorizable_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'contacts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'contacts_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'contacts_contact_status_index',
                'unique' => false,
                'columns' => [
                    'contact_status',
                ],
            ],
            [
                'name' => 'contacts_created_by_foreign',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'contacts_type_index',
                'unique' => false,
                'columns' => [
                    'type',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'contacts_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'contacts_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'crm_call_logs' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'crm_call_logs_business_id_contact_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'contact_id',
                ],
            ],
            [
                'name' => 'crm_call_logs_contact_id_foreign',
                'unique' => false,
                'columns' => [
                    'contact_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'crm_call_logs_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'crm_call_logs_contact_id_foreign',
                'columns' => [
                    'contact_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'contacts',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'crm_lead_users' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'crm_lead_users_contact_id_user_id_unique',
                'unique' => true,
                'columns' => [
                    'contact_id',
                    'user_id',
                ],
            ],
            [
                'name' => 'crm_lead_users_user_id_foreign',
                'unique' => false,
                'columns' => [
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'crm_lead_users_contact_id_foreign',
                'columns' => [
                    'contact_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'contacts',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'crm_lead_users_user_id_foreign',
                'columns' => [
                    'user_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'crm_schedules' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'crm_schedules_business_id_status_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'status',
                ],
            ],
            [
                'name' => 'crm_schedules_contact_id_foreign',
                'unique' => false,
                'columns' => [
                    'contact_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'crm_schedules_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'crm_schedules_contact_id_foreign',
                'columns' => [
                    'contact_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'contacts',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'currencies' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'customer_groups' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'customer_groups_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'customer_groups_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'customer_groups_price_calculation_type_index',
                'unique' => false,
                'columns' => [
                    'price_calculation_type',
                ],
            ],
            [
                'name' => 'customer_groups_selling_price_group_id_index',
                'unique' => false,
                'columns' => [
                    'selling_price_group_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'customer_groups_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'dashboard_configurations' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'dashboard_configurations_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'dashboard_configurations_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'deleted_sales' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'deleted_sales_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'deleted_sales_transaction_id_index',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'delivery_areas' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [
            [
                'name' => 'delivery_areas_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'delivery_route_orders' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [
            [
                'name' => 'delivery_route_orders_delivery_route_id_index',
                'unique' => false,
                'columns' => [
                    'delivery_route_id',
                ],
            ],
            [
                'name' => 'delivery_route_orders_delivery_route_id_transaction_id_unique',
                'unique' => true,
                'columns' => [
                    'delivery_route_id',
                    'transaction_id',
                ],
            ],
            [
                'name' => 'delivery_route_orders_transaction_id_index',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'delivery_route_orders_delivery_route_id_foreign',
                'columns' => [
                    'delivery_route_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'driver_delivery_routes',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'discount_variations' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'discount_variations_discount_id_index',
                'unique' => false,
                'columns' => [
                    'discount_id',
                ],
            ],
            [
                'name' => 'discount_variations_variation_id_index',
                'unique' => false,
                'columns' => [
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'discounts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'discounts_brand_id_index',
                'unique' => false,
                'columns' => [
                    'brand_id',
                ],
            ],
            [
                'name' => 'discounts_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'discounts_category_id_index',
                'unique' => false,
                'columns' => [
                    'category_id',
                ],
            ],
            [
                'name' => 'discounts_location_id_index',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'discounts_priority_index',
                'unique' => false,
                'columns' => [
                    'priority',
                ],
            ],
            [
                'name' => 'discounts_spg_index',
                'unique' => false,
                'columns' => [
                    'spg',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'document_and_notes' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'document_and_notes_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'document_and_notes_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'document_and_notes_notable_id_index',
                'unique' => false,
                'columns' => [
                    'notable_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'driver_delivery_routes' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [
            [
                'name' => 'driver_delivery_routes_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'driver_delivery_routes_business_id_route_date_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'route_date',
                ],
            ],
            [
                'name' => 'driver_delivery_routes_business_location_id_index',
                'unique' => false,
                'columns' => [
                    'business_location_id',
                ],
            ],
            [
                'name' => 'driver_delivery_routes_delivery_user_id_index',
                'unique' => false,
                'columns' => [
                    'delivery_user_id',
                ],
            ],
            [
                'name' => 'driver_delivery_routes_delivery_user_id_route_date_index',
                'unique' => false,
                'columns' => [
                    'delivery_user_id',
                    'route_date',
                ],
            ],
            [
                'name' => 'driver_delivery_routes_route_date_index',
                'unique' => false,
                'columns' => [
                    'route_date',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_api_settings' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_banners' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_cart_items' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_cart_items_cart_id_variation_id_unique',
                'unique' => true,
                'columns' => [
                    'cart_id',
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_carts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_carts_session_id_index',
                'unique' => false,
                'columns' => [
                    'session_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_collection_products' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_collection_products_collection_id_product_id_unique',
                'unique' => true,
                'columns' => [
                    'collection_id',
                    'product_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_collections' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_collections_business_id_slug_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                    'slug',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_coupon_usages' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_coupons' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_coupons_business_id_code_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                    'code',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_customer_addresses' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_customer_licenses' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_customer_licenses_contact_id_index',
                'unique' => false,
                'columns' => [
                    'contact_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_inquiries' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_order_meta' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_order_meta_tracking_token_unique',
                'unique' => true,
                'columns' => [
                    'tracking_token',
                ],
            ],
            [
                'name' => 'ecom_order_meta_transaction_id_unique',
                'unique' => true,
                'columns' => [
                    'transaction_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_password_resets' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_reviews' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_shipping_rules' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_stock_alerts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_stock_alerts_product_id_email_unique',
                'unique' => true,
                'columns' => [
                    'product_id',
                    'email',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_subscribers' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_subscribers_business_id_email_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                    'email',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecom_tax_rules' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'ecom_wishlists' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecom_wishlists_contact_id_product_id_variation_id_unique',
                'unique' => true,
                'columns' => [
                    'contact_id',
                    'product_id',
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'ecommerce_settings' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'ecommerce_settings_business_id_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'employee_attendance_breaks' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'employee_attendance_breaks_attendance_id_index',
                'unique' => false,
                'columns' => [
                    'attendance_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'employee_attendances' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'employee_attendances_business_id_clock_in_at_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'clock_in_at',
                ],
            ],
            [
                'name' => 'employee_attendances_business_id_user_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'user_id',
                ],
            ],
            [
                'name' => 'employee_attendances_user_id_status_index',
                'unique' => false,
                'columns' => [
                    'user_id',
                    'status',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'expense_categories' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'expense_categories_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'expense_categories_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'group_sub_taxes' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'group_sub_taxes_group_tax_id_foreign',
                'unique' => false,
                'columns' => [
                    'group_tax_id',
                ],
            ],
            [
                'name' => 'group_sub_taxes_tax_id_foreign',
                'unique' => false,
                'columns' => [
                    'tax_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'group_sub_taxes_group_tax_id_foreign',
                'columns' => [
                    'group_tax_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'tax_rates',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'group_sub_taxes_tax_id_foreign',
                'columns' => [
                    'tax_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'tax_rates',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'invoice_layouts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'invoice_layouts_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'invoice_layouts_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'invoice_schemes' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'invoice_schemes_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'invoice_schemes_number_type_index',
                'unique' => false,
                'columns' => [
                    'number_type',
                ],
            ],
            [
                'name' => 'invoice_schemes_scheme_type_index',
                'unique' => false,
                'columns' => [
                    'scheme_type',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'invoice_schemes_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'media' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'media_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'media_model_type_model_id_index',
                'unique' => false,
                'columns' => [
                    'model_type',
                    'model_id',
                ],
            ],
            [
                'name' => 'media_uploaded_by_index',
                'unique' => false,
                'columns' => [
                    'uploaded_by',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'migrations' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'mobile_app_tokens' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [
            [
                'name' => 'mobile_app_tokens_app_index',
                'unique' => false,
                'columns' => [
                    'app',
                ],
            ],
            [
                'name' => 'mobile_app_tokens_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'mobile_app_tokens_token_unique',
                'unique' => true,
                'columns' => [
                    'token',
                ],
            ],
            [
                'name' => 'mobile_app_tokens_tokenable_type_tokenable_id_index',
                'unique' => false,
                'columns' => [
                    'tokenable_type',
                    'tokenable_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'model_has_permissions' => [
        'primary' => [
            'permission_id',
            'model_id',
            'model_type',
        ],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'model_has_permissions_model_type_model_id_index',
                'unique' => false,
                'columns' => [
                    'model_type',
                    'model_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'model_has_permissions_permission_id_foreign',
                'columns' => [
                    'permission_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'permissions',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'model_has_roles' => [
        'primary' => [
            'role_id',
            'model_id',
            'model_type',
        ],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'model_has_roles_model_type_model_id_index',
                'unique' => false,
                'columns' => [
                    'model_type',
                    'model_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'model_has_roles_role_id_foreign',
                'columns' => [
                    'role_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'roles',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'notification_templates' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'notifications' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'notifications_notifiable_type_notifiable_id_index',
                'unique' => false,
                'columns' => [
                    'notifiable_type',
                    'notifiable_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'oauth_access_tokens' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'oauth_access_tokens_user_id_index',
                'unique' => false,
                'columns' => [
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'oauth_auth_codes' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => NULL,
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'oauth_clients' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'oauth_clients_user_id_index',
                'unique' => false,
                'columns' => [
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'oauth_personal_access_clients' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'oauth_personal_access_clients_client_id_index',
                'unique' => false,
                'columns' => [
                    'client_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'oauth_refresh_tokens' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'oauth_refresh_tokens_access_token_id_index',
                'unique' => false,
                'columns' => [
                    'access_token_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'password_resets' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'password_resets_email_index',
                'unique' => false,
                'columns' => [
                    'email',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'password_resets_contacts' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'password_resets_contacts_email_index',
                'unique' => false,
                'columns' => [
                    'email',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'permissions' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'pos_quick_layouts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'pos_quick_layouts_business_id_user_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'printers' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'printers_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'printers_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'product_barcodes' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [
            [
                'name' => 'product_barcodes_business_id_barcode_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                    'barcode',
                ],
            ],
            [
                'name' => 'product_barcodes_business_id_type_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'type',
                ],
            ],
            [
                'name' => 'product_barcodes_product_id_index',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
            [
                'name' => 'product_barcodes_variation_id_index',
                'unique' => false,
                'columns' => [
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'product_locations' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'product_locations_location_id_index',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'product_locations_product_id_index',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'product_racks' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'product_racks_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'product_racks_location_id_index',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'product_racks_product_id_index',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'product_variations' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'product_variations_name_index',
                'unique' => false,
                'columns' => [
                    'name',
                ],
            ],
            [
                'name' => 'product_variations_product_id_index',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'product_variations_product_id_foreign',
                'columns' => [
                    'product_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'products',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'products' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'products_barcode_type_index',
                'unique' => false,
                'columns' => [
                    'barcode_type',
                ],
            ],
            [
                'name' => 'products_brand_id_foreign',
                'unique' => false,
                'columns' => [
                    'brand_id',
                ],
            ],
            [
                'name' => 'products_business_id_deleted_at_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'deleted_at',
                ],
            ],
            [
                'name' => 'products_business_id_ecom_slug_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'ecom_slug',
                ],
            ],
            [
                'name' => 'products_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'products_business_id_sales_channel_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'sales_channel',
                ],
            ],
            [
                'name' => 'products_category_id_foreign',
                'unique' => false,
                'columns' => [
                    'category_id',
                ],
            ],
            [
                'name' => 'products_child_category_id_index',
                'unique' => false,
                'columns' => [
                    'child_category_id',
                ],
            ],
            [
                'name' => 'products_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'products_name_index',
                'unique' => false,
                'columns' => [
                    'name',
                ],
            ],
            [
                'name' => 'products_secondary_unit_id_index',
                'unique' => false,
                'columns' => [
                    'secondary_unit_id',
                ],
            ],
            [
                'name' => 'products_sub_category_id_foreign',
                'unique' => false,
                'columns' => [
                    'sub_category_id',
                ],
            ],
            [
                'name' => 'products_supplier_id_index',
                'unique' => false,
                'columns' => [
                    'supplier_id',
                ],
            ],
            [
                'name' => 'products_tax_foreign',
                'unique' => false,
                'columns' => [
                    'tax',
                ],
            ],
            [
                'name' => 'products_tax_type_index',
                'unique' => false,
                'columns' => [
                    'tax_type',
                ],
            ],
            [
                'name' => 'products_type_index',
                'unique' => false,
                'columns' => [
                    'type',
                ],
            ],
            [
                'name' => 'products_unit_id_index',
                'unique' => false,
                'columns' => [
                    'unit_id',
                ],
            ],
            [
                'name' => 'products_warranty_id_index',
                'unique' => false,
                'columns' => [
                    'warranty_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'products_brand_id_foreign',
                'columns' => [
                    'brand_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'brands',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'products_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'products_category_id_foreign',
                'columns' => [
                    'category_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'categories',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'products_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'products_sub_category_id_foreign',
                'columns' => [
                    'sub_category_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'categories',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'products_tax_foreign',
                'columns' => [
                    'tax',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'tax_rates',
                'on_delete' => 'NO ACTION',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'products_unit_id_foreign',
                'columns' => [
                    'unit_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'units',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'purchase_lines' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'purchase_lines_lot_number_index',
                'unique' => false,
                'columns' => [
                    'lot_number',
                ],
            ],
            [
                'name' => 'purchase_lines_product_id_foreign',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
            [
                'name' => 'purchase_lines_sub_unit_id_index',
                'unique' => false,
                'columns' => [
                    'sub_unit_id',
                ],
            ],
            [
                'name' => 'purchase_lines_tax_id_foreign',
                'unique' => false,
                'columns' => [
                    'tax_id',
                ],
            ],
            [
                'name' => 'purchase_lines_transaction_id_foreign',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
            [
                'name' => 'purchase_lines_variation_id_foreign',
                'unique' => false,
                'columns' => [
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'purchase_lines_product_id_foreign',
                'columns' => [
                    'product_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'products',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'purchase_lines_tax_id_foreign',
                'columns' => [
                    'tax_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'tax_rates',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'purchase_lines_transaction_id_foreign',
                'columns' => [
                    'transaction_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'transactions',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'purchase_lines_variation_id_foreign',
                'columns' => [
                    'variation_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'variations',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'reference_counts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'reference_counts_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'res_product_modifier_sets' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'res_product_modifier_sets_modifier_set_id_foreign',
                'unique' => false,
                'columns' => [
                    'modifier_set_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'res_product_modifier_sets_modifier_set_id_foreign',
                'columns' => [
                    'modifier_set_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'products',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'res_tables' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'res_tables_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'res_tables_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'role_has_permissions' => [
        'primary' => [
            'permission_id',
            'role_id',
        ],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'role_has_permissions_role_id_foreign',
                'unique' => false,
                'columns' => [
                    'role_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'role_has_permissions_permission_id_foreign',
                'columns' => [
                    'permission_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'permissions',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'role_has_permissions_role_id_foreign',
                'columns' => [
                    'role_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'roles',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'roles' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'roles_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'roles_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'sale_zero_sale_reasons' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'sale_zero_sale_reasons_slug_unique',
                'unique' => true,
                'columns' => [
                    'slug',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'sale_zero_sales' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'sale_zero_sales_business_id_contact_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'contact_id',
                ],
            ],
            [
                'name' => 'sale_zero_sales_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'sell_line_warranties' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'selling_price_groups' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'selling_price_groups_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'selling_price_groups_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'sessions' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [
            [
                'name' => 'sessions_id_unique',
                'unique' => true,
                'columns' => [
                    'id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'shipping_methods' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'shipping_methods_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'shipping_methods_business_id_name_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                    'name',
                ],
            ],
            [
                'name' => 'shipping_methods_is_active_index',
                'unique' => false,
                'columns' => [
                    'is_active',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'stock_adjustment_lines' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'stock_adjustment_lines_lot_no_line_id_index',
                'unique' => false,
                'columns' => [
                    'lot_no_line_id',
                ],
            ],
            [
                'name' => 'stock_adjustment_lines_product_id_foreign',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
            [
                'name' => 'stock_adjustment_lines_transaction_id_index',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
            [
                'name' => 'stock_adjustment_lines_variation_id_foreign',
                'unique' => false,
                'columns' => [
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'stock_adjustment_lines_product_id_foreign',
                'columns' => [
                    'product_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'products',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'stock_adjustment_lines_transaction_id_foreign',
                'columns' => [
                    'transaction_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'transactions',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'stock_adjustment_lines_variation_id_foreign',
                'columns' => [
                    'variation_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'variations',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'stock_adjustments_temp' => [
        'primary' => [],
        'auto_increment' => NULL,
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'sync_conflicts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'sync_log' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint unsigned',
        ],
        'indexes' => [
            [
                'name' => 'sync_log_created_at_index',
                'unique' => false,
                'columns' => [
                    'created_at',
                ],
            ],
            [
                'name' => 'sync_log_origin_id_index',
                'unique' => false,
                'columns' => [
                    'origin',
                    'id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'sync_nodes' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'sync_nodes_node_no_unique',
                'unique' => true,
                'columns' => [
                    'node_no',
                ],
            ],
            [
                'name' => 'sync_nodes_token_hash_unique',
                'unique' => true,
                'columns' => [
                    'token_hash',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'sync_receipts' => [
        'primary' => [
            'batch_id',
        ],
        'auto_increment' => NULL,
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'sync_state' => [
        'primary' => [
            'key',
        ],
        'auto_increment' => NULL,
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'system' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [],
        'foreign_keys' => [],
    ],
    'tax_rates' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'tax_rates_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'tax_rates_created_by_foreign',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'tax_rates_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'tax_rates_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'team_member_colors' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'team_member_colors_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'team_member_colors_business_id_user_id_unique',
                'unique' => true,
                'columns' => [
                    'business_id',
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'team_shifts' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'team_shifts_business_id_starts_at_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'starts_at',
                ],
            ],
            [
                'name' => 'team_shifts_business_id_user_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'time_off_requests' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'time_off_requests_business_id_starts_at_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'starts_at',
                ],
            ],
            [
                'name' => 'time_off_requests_business_id_status_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'status',
                ],
            ],
            [
                'name' => 'time_off_requests_business_id_user_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'transaction_payments' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'transaction_payments_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'transaction_payments_parent_id_index',
                'unique' => false,
                'columns' => [
                    'parent_id',
                ],
            ],
            [
                'name' => 'transaction_payments_payment_type_index',
                'unique' => false,
                'columns' => [
                    'payment_type',
                ],
            ],
            [
                'name' => 'transaction_payments_transaction_id_foreign',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'transaction_payments_transaction_id_foreign',
                'columns' => [
                    'transaction_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'transactions',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'transaction_sell_lines' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'transaction_sell_lines_children_type_index',
                'unique' => false,
                'columns' => [
                    'children_type',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_discount_id_index',
                'unique' => false,
                'columns' => [
                    'discount_id',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_line_discount_type_index',
                'unique' => false,
                'columns' => [
                    'line_discount_type',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_lot_no_line_id_index',
                'unique' => false,
                'columns' => [
                    'lot_no_line_id',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_parent_sell_line_id_index',
                'unique' => false,
                'columns' => [
                    'parent_sell_line_id',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_product_id_foreign',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_sub_unit_id_index',
                'unique' => false,
                'columns' => [
                    'sub_unit_id',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_tax_id_foreign',
                'unique' => false,
                'columns' => [
                    'tax_id',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_transaction_id_foreign',
                'unique' => false,
                'columns' => [
                    'transaction_id',
                ],
            ],
            [
                'name' => 'transaction_sell_lines_variation_id_foreign',
                'unique' => false,
                'columns' => [
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'transaction_sell_lines_product_id_foreign',
                'columns' => [
                    'product_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'products',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transaction_sell_lines_tax_id_foreign',
                'columns' => [
                    'tax_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'tax_rates',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transaction_sell_lines_transaction_id_foreign',
                'columns' => [
                    'transaction_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'transactions',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transaction_sell_lines_variation_id_foreign',
                'columns' => [
                    'variation_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'variations',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'transaction_sell_lines_purchase_lines' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'bigint',
        ],
        'indexes' => [
            [
                'name' => 'purchase_line_id',
                'unique' => false,
                'columns' => [
                    'purchase_line_id',
                ],
            ],
            [
                'name' => 'sell_line_id',
                'unique' => false,
                'columns' => [
                    'sell_line_id',
                ],
            ],
            [
                'name' => 'stock_adjustment_line_id',
                'unique' => false,
                'columns' => [
                    'stock_adjustment_line_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'transactions' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'transactions_biz_type_status_date_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                    'type',
                    'status',
                    'transaction_date',
                ],
            ],
            [
                'name' => 'transactions_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'transactions_commission_agent_index',
                'unique' => false,
                'columns' => [
                    'commission_agent',
                ],
            ],
            [
                'name' => 'transactions_contact_id_index',
                'unique' => false,
                'columns' => [
                    'contact_id',
                ],
            ],
            [
                'name' => 'transactions_created_by_index',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
            [
                'name' => 'transactions_delivery_date_index',
                'unique' => false,
                'columns' => [
                    'delivery_date',
                ],
            ],
            [
                'name' => 'transactions_delivery_person_index',
                'unique' => false,
                'columns' => [
                    'delivery_person',
                ],
            ],
            [
                'name' => 'transactions_delivery_status_index',
                'unique' => false,
                'columns' => [
                    'delivery_status',
                ],
            ],
            [
                'name' => 'transactions_discount_type_index',
                'unique' => false,
                'columns' => [
                    'discount_type',
                ],
            ],
            [
                'name' => 'transactions_expense_category_id_index',
                'unique' => false,
                'columns' => [
                    'expense_category_id',
                ],
            ],
            [
                'name' => 'transactions_expense_for_foreign',
                'unique' => false,
                'columns' => [
                    'expense_for',
                ],
            ],
            [
                'name' => 'transactions_fulfillment_stage_index',
                'unique' => false,
                'columns' => [
                    'fulfillment_stage',
                ],
            ],
            [
                'name' => 'transactions_location_id_index',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'transactions_order_source_index',
                'unique' => false,
                'columns' => [
                    'order_source',
                ],
            ],
            [
                'name' => 'transactions_packing_charge_type_index',
                'unique' => false,
                'columns' => [
                    'packing_charge_type',
                ],
            ],
            [
                'name' => 'transactions_payment_status_index',
                'unique' => false,
                'columns' => [
                    'payment_status',
                ],
            ],
            [
                'name' => 'transactions_recur_parent_id_index',
                'unique' => false,
                'columns' => [
                    'recur_parent_id',
                ],
            ],
            [
                'name' => 'transactions_res_order_status_index',
                'unique' => false,
                'columns' => [
                    'res_order_status',
                ],
            ],
            [
                'name' => 'transactions_res_table_id_index',
                'unique' => false,
                'columns' => [
                    'res_table_id',
                ],
            ],
            [
                'name' => 'transactions_res_waiter_id_index',
                'unique' => false,
                'columns' => [
                    'res_waiter_id',
                ],
            ],
            [
                'name' => 'transactions_return_parent_id_index',
                'unique' => false,
                'columns' => [
                    'return_parent_id',
                ],
            ],
            [
                'name' => 'transactions_selling_price_group_id_index',
                'unique' => false,
                'columns' => [
                    'selling_price_group_id',
                ],
            ],
            [
                'name' => 'transactions_shipping_date_index',
                'unique' => false,
                'columns' => [
                    'shipping_date',
                ],
            ],
            [
                'name' => 'transactions_status_index',
                'unique' => false,
                'columns' => [
                    'status',
                ],
            ],
            [
                'name' => 'transactions_sub_status_index',
                'unique' => false,
                'columns' => [
                    'sub_status',
                ],
            ],
            [
                'name' => 'transactions_sub_type_index',
                'unique' => false,
                'columns' => [
                    'sub_type',
                ],
            ],
            [
                'name' => 'transactions_tax_id_foreign',
                'unique' => false,
                'columns' => [
                    'tax_id',
                ],
            ],
            [
                'name' => 'transactions_transaction_date_index',
                'unique' => false,
                'columns' => [
                    'transaction_date',
                ],
            ],
            [
                'name' => 'transactions_transfer_parent_id_index',
                'unique' => false,
                'columns' => [
                    'transfer_parent_id',
                ],
            ],
            [
                'name' => 'transactions_type_index',
                'unique' => false,
                'columns' => [
                    'type',
                ],
            ],
            [
                'name' => 'transactions_types_of_service_id_index',
                'unique' => false,
                'columns' => [
                    'types_of_service_id',
                ],
            ],
            [
                'name' => 'type',
                'unique' => false,
                'columns' => [
                    'type',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'transactions_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transactions_contact_id_foreign',
                'columns' => [
                    'contact_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'contacts',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transactions_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transactions_expense_category_id_foreign',
                'columns' => [
                    'expense_category_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'expense_categories',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transactions_expense_for_foreign',
                'columns' => [
                    'expense_for',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transactions_location_id_foreign',
                'columns' => [
                    'location_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business_locations',
                'on_delete' => 'NO ACTION',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'transactions_tax_id_foreign',
                'columns' => [
                    'tax_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'tax_rates',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'types_of_services' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'types_of_services_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'units' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'units_base_unit_id_index',
                'unique' => false,
                'columns' => [
                    'base_unit_id',
                ],
            ],
            [
                'name' => 'units_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'units_created_by_foreign',
                'unique' => false,
                'columns' => [
                    'created_by',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'units_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'units_created_by_foreign',
                'columns' => [
                    'created_by',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'users',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'user_contact_access' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'user_contact_access_contact_id_index',
                'unique' => false,
                'columns' => [
                    'contact_id',
                ],
            ],
            [
                'name' => 'user_contact_access_user_id_index',
                'unique' => false,
                'columns' => [
                    'user_id',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'users' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'users_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'users_crm_contact_id_foreign',
                'unique' => false,
                'columns' => [
                    'crm_contact_id',
                ],
            ],
            [
                'name' => 'users_user_type_index',
                'unique' => false,
                'columns' => [
                    'user_type',
                ],
            ],
            [
                'name' => 'users_username_unique',
                'unique' => true,
                'columns' => [
                    'username',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'users_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'users_crm_contact_id_foreign',
                'columns' => [
                    'crm_contact_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'contacts',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'variation_group_prices' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'variation_group_prices_price_group_id_foreign',
                'unique' => false,
                'columns' => [
                    'price_group_id',
                ],
            ],
            [
                'name' => 'variation_group_prices_variation_id_foreign',
                'unique' => false,
                'columns' => [
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'variation_group_prices_price_group_id_foreign',
                'columns' => [
                    'price_group_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'selling_price_groups',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'variation_group_prices_variation_id_foreign',
                'columns' => [
                    'variation_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'variations',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'variation_location_details' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'variation_location_details_location_id_foreign',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'variation_location_details_product_id_index',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
            [
                'name' => 'variation_location_details_product_variation_id_index',
                'unique' => false,
                'columns' => [
                    'product_variation_id',
                ],
            ],
            [
                'name' => 'variation_location_details_variation_id_index',
                'unique' => false,
                'columns' => [
                    'variation_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'variation_location_details_location_id_foreign',
                'columns' => [
                    'location_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business_locations',
                'on_delete' => 'NO ACTION',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'variation_location_details_variation_id_foreign',
                'columns' => [
                    'variation_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'variations',
                'on_delete' => 'NO ACTION',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'variation_templates' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'variation_templates_business_id_foreign',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'variation_templates_business_id_foreign',
                'columns' => [
                    'business_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'business',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'variation_value_templates' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'variation_value_templates_name_index',
                'unique' => false,
                'columns' => [
                    'name',
                ],
            ],
            [
                'name' => 'variation_value_templates_variation_template_id_index',
                'unique' => false,
                'columns' => [
                    'variation_template_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'variation_value_templates_variation_template_id_foreign',
                'columns' => [
                    'variation_template_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'variation_templates',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'variations' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'variations_name_index',
                'unique' => false,
                'columns' => [
                    'name',
                ],
            ],
            [
                'name' => 'variations_product_id_foreign',
                'unique' => false,
                'columns' => [
                    'product_id',
                ],
            ],
            [
                'name' => 'variations_product_variation_id_foreign',
                'unique' => false,
                'columns' => [
                    'product_variation_id',
                ],
            ],
            [
                'name' => 'variations_sub_sku_index',
                'unique' => false,
                'columns' => [
                    'sub_sku',
                ],
            ],
            [
                'name' => 'variations_variation_value_id_index',
                'unique' => false,
                'columns' => [
                    'variation_value_id',
                ],
            ],
        ],
        'foreign_keys' => [
            [
                'name' => 'variations_product_id_foreign',
                'columns' => [
                    'product_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'products',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
            [
                'name' => 'variations_product_variation_id_foreign',
                'columns' => [
                    'product_variation_id',
                ],
                'references' => [
                    'id',
                ],
                'on' => 'product_variations',
                'on_delete' => 'CASCADE',
                'on_update' => 'NO ACTION',
            ],
        ],
    ],
    'voided_sales' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'voided_sales_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'voided_sales_invoice_no_index',
                'unique' => false,
                'columns' => [
                    'invoice_no',
                ],
            ],
            [
                'name' => 'voided_sales_location_id_index',
                'unique' => false,
                'columns' => [
                    'location_id',
                ],
            ],
            [
                'name' => 'voided_sales_original_transaction_id_index',
                'unique' => false,
                'columns' => [
                    'original_transaction_id',
                ],
            ],
            [
                'name' => 'voided_sales_voided_at_index',
                'unique' => false,
                'columns' => [
                    'voided_at',
                ],
            ],
            [
                'name' => 'voided_sales_voided_by_index',
                'unique' => false,
                'columns' => [
                    'voided_by',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
    'warranties' => [
        'primary' => [
            'id',
        ],
        'auto_increment' => [
            'column' => 'id',
            'type' => 'int unsigned',
        ],
        'indexes' => [
            [
                'name' => 'warranties_business_id_index',
                'unique' => false,
                'columns' => [
                    'business_id',
                ],
            ],
            [
                'name' => 'warranties_duration_type_index',
                'unique' => false,
                'columns' => [
                    'duration_type',
                ],
            ],
        ],
        'foreign_keys' => [],
    ],
];
