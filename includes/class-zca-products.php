<?php
/**
 * Products Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZCA_Products {

    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_zca_add_product', array(__CLASS__, 'add_product'));
        add_action('wp_ajax_zca_update_product', array(__CLASS__, 'update_product'));
        add_action('wp_ajax_zca_delete_product', array(__CLASS__, 'delete_product'));
        add_action('wp_ajax_zca_get_product', array(__CLASS__, 'get_product'));
        add_action('wp_ajax_zca_get_products', array(__CLASS__, 'get_products'));
    }

    /**
     * Get all products
     */
    public static function get_all_products($include_deleted = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'zca_products';

        $where = $include_deleted ? '' : 'WHERE deleted_at IS NULL';

        $products = $wpdb->get_results(
            "SELECT * FROM $table $where ORDER BY name ASC"
        );

        return $products;
    }

    /**
     * Get product by ID
     */
    public static function get_product_by_id($product_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zca_products';

        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND deleted_at IS NULL",
            $product_id
        ));

        return $product;
    }

    /**
     * AJAX: Add product
     */
    public static function add_product() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $sku = sanitize_text_field($_POST['sku']);

        if (empty($name) || $price < 0 || $stock < 0) {
            wp_send_json_error(array('message' => 'Please fill in all required fields correctly'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'zca_products';

        $result = $wpdb->insert(
            $table,
            array(
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'sku' => $sku,
            ),
            array('%s', '%s', '%f', '%d', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => 'Product added successfully',
                'product_id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to add product'));
        }
    }

    /**
     * AJAX: Update product
     */
    public static function update_product() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $product_id = intval($_POST['product_id']);
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $sku = sanitize_text_field($_POST['sku']);

        if (empty($name) || $price < 0 || $stock < 0) {
            wp_send_json_error(array('message' => 'Please fill in all required fields correctly'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'zca_products';

        $result = $wpdb->update(
            $table,
            array(
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'sku' => $sku,
            ),
            array('id' => $product_id),
            array('%s', '%s', '%f', '%d', '%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Product updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update product'));
        }
    }

    /**
     * AJAX: Delete product (soft delete)
     */
    public static function delete_product() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $product_id = intval($_POST['product_id']);

        global $wpdb;
        $table = $wpdb->prefix . 'zca_products';

        $result = $wpdb->update(
            $table,
            array('deleted_at' => current_time('mysql')),
            array('id' => $product_id),
            array('%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Product deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete product'));
        }
    }

    /**
     * AJAX: Get product
     */
    public static function get_product() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        $product_id = intval($_POST['product_id']);
        $product = self::get_product_by_id($product_id);

        if ($product) {
            wp_send_json_success(array('product' => $product));
        } else {
            wp_send_json_error(array('message' => 'Product not found'));
        }
    }

    /**
     * AJAX: Get products
     */
    public static function get_products() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        $products = self::get_all_products();

        wp_send_json_success(array('products' => $products));
    }

    /**
     * Update product stock
     */
    public static function update_stock($product_id, $new_stock) {
        global $wpdb;
        $table = $wpdb->prefix . 'zca_products';

        $result = $wpdb->update(
            $table,
            array('stock' => $new_stock),
            array('id' => $product_id),
            array('%d'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Reduce stock
     */
    public static function reduce_stock($product_id, $quantity) {
        $product = self::get_product_by_id($product_id);

        if (!$product) {
            return false;
        }

        $new_stock = $product->stock - $quantity;

        if ($new_stock < 0) {
            return false;
        }

        return self::update_stock($product_id, $new_stock);
    }
}
