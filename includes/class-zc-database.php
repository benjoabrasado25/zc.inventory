<?php
/**
 * Database Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZC_Database {

    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Products table
        $table_products = $wpdb->prefix . 'zc_products';
        $sql_products = "CREATE TABLE $table_products (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            price decimal(10,2) NOT NULL,
            stock int(11) NOT NULL DEFAULT 0,
            sku varchar(100),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY sku (sku)
        ) $charset_collate;";
        dbDelta($sql_products);

        // Sales table
        $table_sales = $wpdb->prefix . 'zc_sales';
        $sql_sales = "CREATE TABLE $table_sales (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            cashier_id bigint(20) NOT NULL,
            total_amount decimal(10,2) NOT NULL,
            cash_received decimal(10,2) NOT NULL,
            change_amount decimal(10,2) NOT NULL,
            sale_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY cashier_id (cashier_id),
            KEY sale_date (sale_date)
        ) $charset_collate;";
        dbDelta($sql_sales);

        // Sale items table
        $table_sale_items = $wpdb->prefix . 'zc_sale_items';
        $sql_sale_items = "CREATE TABLE $table_sale_items (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            sale_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            product_name varchar(255) NOT NULL,
            quantity int(11) NOT NULL,
            price decimal(10,2) NOT NULL,
            subtotal decimal(10,2) NOT NULL,
            PRIMARY KEY (id),
            KEY sale_id (sale_id),
            KEY product_id (product_id)
        ) $charset_collate;";
        dbDelta($sql_sale_items);

        // Inventory logs table
        $table_inventory_logs = $wpdb->prefix . 'zc_inventory_logs';
        $sql_inventory_logs = "CREATE TABLE $table_inventory_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            quantity_before int(11) NOT NULL,
            quantity_after int(11) NOT NULL,
            quantity_change int(11) NOT NULL,
            reason varchar(255),
            log_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY log_date (log_date)
        ) $charset_collate;";
        dbDelta($sql_inventory_logs);

        // Cashier settings table (for storing active/inactive status)
        $table_cashier_settings = $wpdb->prefix . 'zc_cashier_settings';
        $sql_cashier_settings = "CREATE TABLE $table_cashier_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_cashier_settings);

        // Settings table
        $table_settings = $wpdb->prefix . 'zc_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        dbDelta($sql_settings);

        // Insert default currency settings
        $settings_table = $wpdb->prefix . 'zc_settings';
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $settings_table WHERE setting_key = 'currency_symbol'");

        if ($existing == 0) {
            $wpdb->insert($settings_table, array(
                'setting_key' => 'currency_symbol',
                'setting_value' => '₱'
            ));
            $wpdb->insert($settings_table, array(
                'setting_key' => 'currency_code',
                'setting_value' => 'PHP'
            ));
            $wpdb->insert($settings_table, array(
                'setting_key' => 'currency_position',
                'setting_value' => 'before'
            ));
        }

        // Cash register sessions table
        $table_register = $wpdb->prefix . 'zc_cash_register';
        $sql_register = "CREATE TABLE $table_register (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            cashier_id bigint(20) NOT NULL,
            opening_cash decimal(10,2) NOT NULL,
            closing_cash decimal(10,2) DEFAULT NULL,
            expected_cash decimal(10,2) DEFAULT NULL,
            variance decimal(10,2) DEFAULT NULL,
            notes text,
            opened_at datetime DEFAULT CURRENT_TIMESTAMP,
            closed_at datetime DEFAULT NULL,
            status varchar(20) DEFAULT 'open',
            PRIMARY KEY (id),
            KEY cashier_id (cashier_id),
            KEY status (status),
            KEY opened_at (opened_at)
        ) $charset_collate;";
        dbDelta($sql_register);
    }

    /**
     * Drop all plugin tables
     */
    public static function drop_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'zc_products',
            $wpdb->prefix . 'zc_sales',
            $wpdb->prefix . 'zc_sale_items',
            $wpdb->prefix . 'zc_inventory_logs',
            $wpdb->prefix . 'zc_cashier_settings',
            $wpdb->prefix . 'zc_settings',
            $wpdb->prefix . 'zc_cash_register'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
