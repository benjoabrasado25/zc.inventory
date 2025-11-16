<?php
/**
 * Sales Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZC_Sales {

    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_zc_process_sale', array(__CLASS__, 'process_sale'));
        add_action('wp_ajax_zc_get_sales', array(__CLASS__, 'get_sales'));
        add_action('wp_ajax_zc_get_sales_by_cashier', array(__CLASS__, 'get_sales_by_cashier'));
        add_action('wp_ajax_zc_get_sale_details', array(__CLASS__, 'get_sale_details'));
    }

    /**
     * Process a sale
     */
    public static function process_sale() {
        check_ajax_referer('zc_inventory_nonce', 'nonce');

        if (!ZC_Roles::is_cashier() && !ZC_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $items = json_decode(stripslashes($_POST['items']), true);
        $cash_received = floatval($_POST['cash_received']);

        if (empty($items) || $cash_received <= 0) {
            wp_send_json_error(array('message' => 'Invalid sale data'));
        }

        global $wpdb;
        $sales_table = $wpdb->prefix . 'zc_sales';
        $items_table = $wpdb->prefix . 'zc_sale_items';

        // Calculate total
        $total_amount = 0;
        $sale_items = array();

        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $quantity = intval($item['quantity']);

            $product = ZC_Products::get_product_by_id($product_id);

            if (!$product) {
                wp_send_json_error(array('message' => 'Product not found: ' . $product_id));
            }

            if ($product->stock < $quantity) {
                wp_send_json_error(array('message' => 'Insufficient stock for: ' . $product->name));
            }

            $subtotal = $product->price * $quantity;
            $total_amount += $subtotal;

            $sale_items[] = array(
                'product_id' => $product_id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
                'subtotal' => $subtotal,
                'product' => $product
            );
        }

        // Check if cash received is enough
        if ($cash_received < $total_amount) {
            wp_send_json_error(array('message' => 'Insufficient cash received'));
        }

        $change_amount = $cash_received - $total_amount;

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Insert sale record
            $result = $wpdb->insert(
                $sales_table,
                array(
                    'cashier_id' => get_current_user_id(),
                    'total_amount' => $total_amount,
                    'cash_received' => $cash_received,
                    'change_amount' => $change_amount,
                ),
                array('%d', '%f', '%f', '%f')
            );

            if (!$result) {
                throw new Exception('Failed to create sale record');
            }

            $sale_id = $wpdb->insert_id;

            // Insert sale items and update stock
            foreach ($sale_items as $sale_item) {
                // Insert sale item
                $result = $wpdb->insert(
                    $items_table,
                    array(
                        'sale_id' => $sale_id,
                        'product_id' => $sale_item['product_id'],
                        'product_name' => $sale_item['product_name'],
                        'quantity' => $sale_item['quantity'],
                        'price' => $sale_item['price'],
                        'subtotal' => $sale_item['subtotal'],
                    ),
                    array('%d', '%d', '%s', '%d', '%f', '%f')
                );

                if (!$result) {
                    throw new Exception('Failed to insert sale item');
                }

                // Update product stock
                if (!ZC_Products::reduce_stock($sale_item['product_id'], $sale_item['quantity'])) {
                    throw new Exception('Failed to update stock');
                }
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            wp_send_json_success(array(
                'message' => 'Sale processed successfully',
                'sale_id' => $sale_id,
                'total' => $total_amount,
                'cash_received' => $cash_received,
                'change' => $change_amount
            ));

        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    /**
     * Get all sales
     */
    public static function get_all_sales($limit = null, $offset = 0) {
        global $wpdb;
        $sales_table = $wpdb->prefix . 'zc_sales';
        $users_table = $wpdb->prefix . 'users';

        $limit_clause = $limit ? "LIMIT $offset, $limit" : '';

        $sales = $wpdb->get_results(
            "SELECT s.*, u.display_name as cashier_name
            FROM $sales_table s
            LEFT JOIN $users_table u ON s.cashier_id = u.ID
            ORDER BY s.sale_date DESC
            $limit_clause"
        );

        return $sales;
    }

    /**
     * Get sales by cashier
     */
    public static function get_sales_by_cashier_id($cashier_id, $limit = null, $offset = 0) {
        global $wpdb;
        $sales_table = $wpdb->prefix . 'zc_sales';

        $limit_clause = $limit ? "LIMIT $offset, $limit" : '';

        $sales = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $sales_table
            WHERE cashier_id = %d
            ORDER BY sale_date DESC
            $limit_clause",
            $cashier_id
        ));

        return $sales;
    }

    /**
     * Get sale items
     */
    public static function get_sale_items($sale_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zc_sale_items';

        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE sale_id = %d",
            $sale_id
        ));

        return $items;
    }

    /**
     * AJAX: Get sales
     */
    public static function get_sales() {
        check_ajax_referer('zc_inventory_nonce', 'nonce');

        if (!ZC_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $sales = self::get_all_sales();

        wp_send_json_success(array('sales' => $sales));
    }

    /**
     * AJAX: Get sales by cashier
     */
    public static function get_sales_by_cashier() {
        check_ajax_referer('zc_inventory_nonce', 'nonce');

        if (!ZC_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $cashier_id = intval($_POST['cashier_id']);
        $sales = self::get_sales_by_cashier_id($cashier_id);

        wp_send_json_success(array('sales' => $sales));
    }

    /**
     * Get sales statistics
     */
    public static function get_sales_stats($cashier_id = null, $date_from = null, $date_to = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'zc_sales';

        $where = array();
        $values = array();

        if ($cashier_id) {
            $where[] = 'cashier_id = %d';
            $values[] = $cashier_id;
        }

        if ($date_from) {
            $where[] = 'sale_date >= %s';
            $values[] = $date_from;
        }

        if ($date_to) {
            $where[] = 'sale_date <= %s';
            $values[] = $date_to;
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT
                    COUNT(*) as total_sales,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as average_sale
                  FROM $table
                  $where_clause";

        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }

        $stats = $wpdb->get_row($query);

        return $stats;
    }

    /**
     * AJAX: Get sale details
     */
    public static function get_sale_details() {
        check_ajax_referer('zc_inventory_nonce', 'nonce');

        if (!ZC_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $sale_id = intval($_POST['sale_id']);

        global $wpdb;
        $sales_table = $wpdb->prefix . 'zc_sales';
        $users_table = $wpdb->prefix . 'users';

        $sale = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, u.display_name as cashier_name
            FROM $sales_table s
            LEFT JOIN $users_table u ON s.cashier_id = u.ID
            WHERE s.id = %d",
            $sale_id
        ));

        if (!$sale) {
            wp_send_json_error(array('message' => 'Sale not found'));
        }

        $items = self::get_sale_items($sale_id);

        wp_send_json_success(array(
            'sale' => $sale,
            'items' => $items
        ));
    }
}
