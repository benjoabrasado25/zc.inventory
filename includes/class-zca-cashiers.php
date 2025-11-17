<?php
/**
 * Cashiers Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZCA_Cashiers {

    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_zc_add_cashier', array(__CLASS__, 'add_cashier'));
        add_action('wp_ajax_zc_update_cashier', array(__CLASS__, 'update_cashier'));
        add_action('wp_ajax_zc_toggle_cashier', array(__CLASS__, 'toggle_cashier'));
        add_action('wp_ajax_zc_get_cashiers', array(__CLASS__, 'get_cashiers'));
    }

    /**
     * Get all cashiers
     */
    public static function get_all_cashiers() {
        $args = array(
            'role' => 'zca_cashier',
            'orderby' => 'display_name',
            'order' => 'ASC'
        );

        $cashiers = get_users($args);

        // Get active status for each cashier
        foreach ($cashiers as &$cashier) {
            $cashier->is_active = self::is_active($cashier->ID);
        }

        return $cashiers;
    }

    /**
     * Check if cashier is active
     */
    public static function is_active($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zca_cashier_settings';

        $is_active = $wpdb->get_var($wpdb->prepare(
            "SELECT is_active FROM $table WHERE user_id = %d",
            $user_id
        ));

        // If no record exists, cashier is active by default
        if ($is_active === null) {
            return true;
        }

        return (bool) $is_active;
    }

    /**
     * Set cashier active status
     */
    public static function set_active_status($user_id, $is_active) {
        global $wpdb;
        $table = $wpdb->prefix . 'zca_cashier_settings';

        // Check if record exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d",
            $user_id
        ));

        if ($exists) {
            // Update existing record
            $result = $wpdb->update(
                $table,
                array('is_active' => $is_active ? 1 : 0),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'is_active' => $is_active ? 1 : 0
                ),
                array('%d', '%d')
            );
        }

        return $result !== false;
    }

    /**
     * AJAX: Add cashier
     */
    public static function add_cashier() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $display_name = sanitize_text_field($_POST['display_name']);

        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'Please fill in all required fields'));
        }

        // Check if username exists
        if (username_exists($username)) {
            wp_send_json_error(array('message' => 'Username already exists'));
        }

        // Check if email exists
        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'Email already exists'));
        }

        // Create user
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }

        // Update display name
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $display_name ? $display_name : $username,
            'role' => 'zca_cashier'
        ));

        // Set as active
        self::set_active_status($user_id, true);

        wp_send_json_success(array(
            'message' => 'Cashier added successfully',
            'user_id' => $user_id
        ));
    }

    /**
     * AJAX: Update cashier
     */
    public static function update_cashier() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $user_id = intval($_POST['user_id']);
        $email = sanitize_email($_POST['email']);
        $display_name = sanitize_text_field($_POST['display_name']);
        $password = $_POST['password'];

        if (empty($email)) {
            wp_send_json_error(array('message' => 'Email is required'));
        }

        // Check if email exists for another user
        $email_user = get_user_by('email', $email);
        if ($email_user && $email_user->ID != $user_id) {
            wp_send_json_error(array('message' => 'Email already exists'));
        }

        $update_data = array(
            'ID' => $user_id,
            'user_email' => $email,
            'display_name' => $display_name
        );

        // Update password if provided
        if (!empty($password)) {
            $update_data['user_pass'] = $password;
        }

        $result = wp_update_user($update_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => 'Cashier updated successfully'));
    }

    /**
     * AJAX: Toggle cashier active status
     */
    public static function toggle_cashier() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $user_id = intval($_POST['user_id']);
        $is_active = isset($_POST['is_active']) && $_POST['is_active'] === 'true' ? true : false;

        $result = self::set_active_status($user_id, $is_active);

        if ($result) {
            $message = $is_active ? 'Cashier activated successfully' : 'Cashier deactivated successfully';
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(array('message' => 'Failed to update cashier status'));
        }
    }

    /**
     * AJAX: Get cashiers
     */
    public static function get_cashiers() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $cashiers = self::get_all_cashiers();

        wp_send_json_success(array('cashiers' => $cashiers));
    }
}
