<?php
/**
 * Cash Register Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZCA_Register {

    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_zc_open_register', array(__CLASS__, 'open_register'));
        add_action('wp_ajax_zc_close_register', array(__CLASS__, 'close_register'));
        add_action('wp_ajax_zc_get_active_session', array(__CLASS__, 'get_active_session'));
        add_action('wp_ajax_zc_get_today_stats', array(__CLASS__, 'get_today_stats'));
    }

    /**
     * Open cash register
     */
    public static function open_register() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_cashier() && !ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $opening_cash = floatval($_POST['opening_cash']);
        $cashier_id = get_current_user_id();

        if ($opening_cash < 0) {
            wp_send_json_error(array('message' => 'Opening cash cannot be negative'));
        }

        // Check if there's already an open session
        $existing = self::get_active_session_data($cashier_id);
        if ($existing) {
            wp_send_json_error(array('message' => 'You already have an open cash register session'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'zca_cash_register';

        $result = $wpdb->insert(
            $table,
            array(
                'cashier_id' => $cashier_id,
                'opening_cash' => $opening_cash,
                'status' => 'open'
            ),
            array('%d', '%f', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => 'Cash register opened successfully',
                'session_id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to open cash register'));
        }
    }

    /**
     * Close cash register
     */
    public static function close_register() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        if (!ZCA_Roles::is_cashier() && !ZCA_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $session_id = intval($_POST['session_id']);
        $closing_cash = floatval($_POST['closing_cash']);
        $notes = sanitize_textarea_field($_POST['notes']);
        $cashier_id = get_current_user_id();

        // Get session
        global $wpdb;
        $table = $wpdb->prefix . 'zca_cash_register';

        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND cashier_id = %d AND status = 'open'",
            $session_id,
            $cashier_id
        ));

        if (!$session) {
            wp_send_json_error(array('message' => 'Session not found or already closed'));
        }

        // Calculate expected cash
        $opening_cash = floatval($session->opening_cash);
        $sales_total = self::get_session_sales_total($cashier_id, $session->opened_at);
        $expected_cash = $opening_cash + $sales_total;
        $variance = $closing_cash - $expected_cash;

        // Update session
        $result = $wpdb->update(
            $table,
            array(
                'closing_cash' => $closing_cash,
                'expected_cash' => $expected_cash,
                'variance' => $variance,
                'notes' => $notes,
                'closed_at' => current_time('mysql'),
                'status' => 'closed'
            ),
            array('id' => $session_id),
            array('%f', '%f', '%f', '%s', '%s', '%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Cash register closed successfully',
                'expected' => $expected_cash,
                'actual' => $closing_cash,
                'variance' => $variance
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to close cash register'));
        }
    }

    /**
     * Get active session for cashier
     */
    public static function get_active_session_data($cashier_id = null) {
        if (!$cashier_id) {
            $cashier_id = get_current_user_id();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'zca_cash_register';

        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE cashier_id = %d AND status = 'open' ORDER BY opened_at DESC LIMIT 1",
            $cashier_id
        ));

        return $session;
    }

    /**
     * AJAX: Get active session
     */
    public static function get_active_session() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        $cashier_id = get_current_user_id();
        $session = self::get_active_session_data($cashier_id);

        if ($session) {
            wp_send_json_success(array('session' => $session));
        } else {
            wp_send_json_error(array('message' => 'No active session'));
        }
    }

    /**
     * Get sales total for current session
     */
    public static function get_session_sales_total($cashier_id, $since_datetime) {
        global $wpdb;
        $table = $wpdb->prefix . 'zca_sales';

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) FROM $table
            WHERE cashier_id = %d AND sale_date >= %s",
            $cashier_id,
            $since_datetime
        ));

        return floatval($total);
    }

    /**
     * Get today's stats for cashier
     */
    public static function get_cashier_today_stats($cashier_id = null) {
        if (!$cashier_id) {
            $cashier_id = get_current_user_id();
        }

        global $wpdb;
        $sales_table = $wpdb->prefix . 'zca_sales';

        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(*) as total_sales,
                COALESCE(SUM(total_amount), 0) as total_revenue
            FROM $sales_table
            WHERE cashier_id = %d
            AND sale_date >= %s
            AND sale_date <= %s",
            $cashier_id,
            $today_start,
            $today_end
        ));

        return $stats;
    }

    /**
     * Get today's stats for all cashiers
     */
    public static function get_all_today_stats() {
        global $wpdb;
        $sales_table = $wpdb->prefix . 'zca_sales';

        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(*) as total_sales,
                COALESCE(SUM(total_amount), 0) as total_revenue
            FROM $sales_table
            WHERE sale_date >= %s
            AND sale_date <= %s",
            $today_start,
            $today_end
        ));

        return $stats;
    }

    /**
     * Get today's stats by cashier
     */
    public static function get_today_stats_by_cashier() {
        global $wpdb;
        $sales_table = $wpdb->prefix . 'zca_sales';
        $users_table = $wpdb->prefix . 'users';

        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');

        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT
                s.cashier_id,
                u.display_name as cashier_name,
                COUNT(*) as total_sales,
                COALESCE(SUM(s.total_amount), 0) as total_revenue
            FROM $sales_table s
            LEFT JOIN $users_table u ON s.cashier_id = u.ID
            WHERE s.sale_date >= %s
            AND s.sale_date <= %s
            GROUP BY s.cashier_id
            ORDER BY total_revenue DESC",
            $today_start,
            $today_end
        ));

        return $stats;
    }

    /**
     * AJAX: Get today stats
     */
    public static function get_today_stats() {
        check_ajax_referer('zca_inventory_nonce', 'nonce');

        $cashier_id = get_current_user_id();
        $stats = self::get_cashier_today_stats($cashier_id);

        wp_send_json_success(array('stats' => $stats));
    }
}
