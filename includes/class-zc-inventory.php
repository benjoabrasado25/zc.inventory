<?php
/**
 * Inventory Manager Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZC_Inventory_Manager {

    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_zc_update_inventory', array(__CLASS__, 'update_inventory'));
        add_action('wp_ajax_zc_get_inventory_logs', array(__CLASS__, 'get_inventory_logs'));
    }

    /**
     * Update inventory
     */
    public static function update_product_inventory($product_id, $new_quantity, $reason = '') {
        $product = ZC_Products::get_product_by_id($product_id);

        if (!$product) {
            return false;
        }

        $quantity_before = $product->stock;
        $quantity_change = $new_quantity - $quantity_before;

        // Update stock
        if (!ZC_Products::update_stock($product_id, $new_quantity)) {
            return false;
        }

        // Log the change
        self::log_inventory_change(
            $product_id,
            $quantity_before,
            $new_quantity,
            $quantity_change,
            $reason
        );

        return true;
    }

    /**
     * Log inventory change
     */
    public static function log_inventory_change($product_id, $quantity_before, $quantity_after, $quantity_change, $reason = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'zc_inventory_logs';

        $result = $wpdb->insert(
            $table,
            array(
                'product_id' => $product_id,
                'user_id' => get_current_user_id(),
                'quantity_before' => $quantity_before,
                'quantity_after' => $quantity_after,
                'quantity_change' => $quantity_change,
                'reason' => $reason,
            ),
            array('%d', '%d', '%d', '%d', '%d', '%s')
        );

        return $result !== false;
    }

    /**
     * Get inventory logs
     */
    public static function get_logs($product_id = null, $limit = 50, $offset = 0) {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'zc_inventory_logs';
        $users_table = $wpdb->prefix . 'users';
        $products_table = $wpdb->prefix . 'zc_products';

        $where = '';
        if ($product_id) {
            $where = $wpdb->prepare('WHERE l.product_id = %d', $product_id);
        }

        $logs = $wpdb->get_results(
            "SELECT l.*, u.display_name as user_name, p.name as product_name
            FROM $logs_table l
            LEFT JOIN $users_table u ON l.user_id = u.ID
            LEFT JOIN $products_table p ON l.product_id = p.id
            $where
            ORDER BY l.log_date DESC
            LIMIT $offset, $limit"
        );

        return $logs;
    }

    /**
     * AJAX: Update inventory
     */
    public static function update_inventory() {
        check_ajax_referer('zc_inventory_nonce', 'nonce');

        if (!ZC_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $product_id = intval($_POST['product_id']);
        $new_quantity = intval($_POST['quantity']);
        $reason = sanitize_text_field($_POST['reason']);

        if ($new_quantity < 0) {
            wp_send_json_error(array('message' => 'Quantity cannot be negative'));
        }

        if (self::update_product_inventory($product_id, $new_quantity, $reason)) {
            wp_send_json_success(array('message' => 'Inventory updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update inventory'));
        }
    }

    /**
     * AJAX: Get inventory logs
     */
    public static function get_inventory_logs() {
        check_ajax_referer('zc_inventory_nonce', 'nonce');

        if (!ZC_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
        $logs = self::get_logs($product_id);

        wp_send_json_success(array('logs' => $logs));
    }
}
