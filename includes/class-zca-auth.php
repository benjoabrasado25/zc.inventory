<?php
/**
 * Authentication Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZCA_Auth {

    public static function init() {
        // Handle login form submission
        add_action('wp_ajax_nopriv_zc_login', array(__CLASS__, 'handle_login'));
        add_action('wp_ajax_zc_login', array(__CLASS__, 'handle_login'));
    }

    /**
     * Handle login
     */
    public static function handle_login() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'zca_login_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }

        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;

        // Authenticate user
        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'Invalid username or password'));
        }

        // Check if user has access
        if (!ZCA_Roles::has_access($user->ID)) {
            wp_send_json_error(array('message' => 'You do not have access to the inventory system'));
        }

        // Check if cashier is active
        if (ZCA_Roles::is_cashier($user->ID)) {
            if (!self::is_cashier_active($user->ID)) {
                wp_send_json_error(array('message' => 'Your account has been deactivated. Please contact the owner.'));
            }
        }

        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);

        // Determine redirect URL
        $redirect_url = home_url('/zca-inventory/dashboard');

        wp_send_json_success(array(
            'message' => 'Login successful',
            'redirect' => $redirect_url
        ));
    }

    /**
     * Logout user
     */
    public static function logout() {
        wp_logout();
        wp_redirect(home_url('/zca-inventory/login'));
        exit;
    }

    /**
     * Check if user is logged in and has access
     */
    public static function check_access($required_role = '') {
        if (!is_user_logged_in()) {
            wp_redirect(home_url('/zca-inventory/login'));
            exit;
        }

        $user_id = get_current_user_id();

        // Check if user has access to inventory system
        if (!ZCA_Roles::has_access($user_id)) {
            wp_die('You do not have access to this page.');
        }

        // Check specific role requirements
        if ($required_role === 'owner' && !ZCA_Roles::is_owner($user_id)) {
            wp_die('You do not have permission to access this page.');
        }

        if ($required_role === 'cashier' && !ZCA_Roles::is_cashier($user_id)) {
            wp_die('You do not have permission to access this page.');
        }

        // Check if cashier is active
        if (ZCA_Roles::is_cashier($user_id) && !self::is_cashier_active($user_id)) {
            wp_logout();
            wp_redirect(home_url('/zca-inventory/login?deactivated=1'));
            exit;
        }

        return true;
    }

    /**
     * Check if cashier is active
     */
    public static function is_cashier_active($user_id) {
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
     * Get current user role
     */
    public static function get_user_role() {
        if (!is_user_logged_in()) {
            return false;
        }

        $user_id = get_current_user_id();

        if (ZCA_Roles::is_owner($user_id)) {
            return 'owner';
        }

        if (ZCA_Roles::is_cashier($user_id)) {
            return 'cashier';
        }

        return false;
    }
}
