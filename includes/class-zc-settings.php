<?php
/**
 * Settings Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZC_Settings {

    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_zc_update_settings', array(__CLASS__, 'update_settings'));
    }

    /**
     * Get setting value
     */
    public static function get($key, $default = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'zc_settings';

        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table WHERE setting_key = %s",
            $key
        ));

        return $value !== null ? $value : $default;
    }

    /**
     * Set setting value
     */
    public static function set($key, $value) {
        global $wpdb;
        $table = $wpdb->prefix . 'zc_settings';

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE setting_key = %s",
            $key
        ));

        if ($existing) {
            return $wpdb->update(
                $table,
                array('setting_value' => $value),
                array('setting_key' => $key),
                array('%s'),
                array('%s')
            );
        } else {
            return $wpdb->insert(
                $table,
                array(
                    'setting_key' => $key,
                    'setting_value' => $value
                ),
                array('%s', '%s')
            );
        }
    }

    /**
     * Get currency symbol
     */
    public static function get_currency_symbol() {
        return self::get('currency_symbol', '₱');
    }

    /**
     * Get currency code
     */
    public static function get_currency_code() {
        return self::get('currency_code', 'PHP');
    }

    /**
     * Get currency position
     */
    public static function get_currency_position() {
        return self::get('currency_position', 'before');
    }

    /**
     * Format currency
     */
    public static function format_currency($amount) {
        $symbol = self::get_currency_symbol();
        $position = self::get_currency_position();
        $formatted = number_format((float)$amount, 2);

        if ($position === 'before') {
            return $symbol . $formatted;
        } else {
            return $formatted . ' ' . $symbol;
        }
    }

    /**
     * AJAX: Update settings
     */
    public static function update_settings() {
        check_ajax_referer('zc_inventory_nonce', 'nonce');

        if (!ZC_Roles::is_owner()) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $currency_symbol = sanitize_text_field($_POST['currency_symbol']);
        $currency_code = sanitize_text_field($_POST['currency_code']);
        $currency_position = sanitize_text_field($_POST['currency_position']);

        if (empty($currency_symbol) || empty($currency_code)) {
            wp_send_json_error(array('message' => 'Please fill in all required fields'));
        }

        self::set('currency_symbol', $currency_symbol);
        self::set('currency_code', $currency_code);
        self::set('currency_position', $currency_position);

        wp_send_json_success(array('message' => 'Settings updated successfully'));
    }

    /**
     * Get available currencies
     */
    public static function get_available_currencies() {
        return array(
            'PHP' => array('name' => 'Philippine Peso', 'symbol' => '₱'),
            'USD' => array('name' => 'US Dollar', 'symbol' => '$'),
            'EUR' => array('name' => 'Euro', 'symbol' => '€'),
            'GBP' => array('name' => 'British Pound', 'symbol' => '£'),
            'JPY' => array('name' => 'Japanese Yen', 'symbol' => '¥'),
            'CNY' => array('name' => 'Chinese Yuan', 'symbol' => '¥'),
            'AUD' => array('name' => 'Australian Dollar', 'symbol' => 'A$'),
            'CAD' => array('name' => 'Canadian Dollar', 'symbol' => 'C$'),
            'SGD' => array('name' => 'Singapore Dollar', 'symbol' => 'S$'),
            'HKD' => array('name' => 'Hong Kong Dollar', 'symbol' => 'HK$'),
            'INR' => array('name' => 'Indian Rupee', 'symbol' => '₹'),
            'KRW' => array('name' => 'South Korean Won', 'symbol' => '₩'),
            'MYR' => array('name' => 'Malaysian Ringgit', 'symbol' => 'RM'),
            'THB' => array('name' => 'Thai Baht', 'symbol' => '฿'),
            'IDR' => array('name' => 'Indonesian Rupiah', 'symbol' => 'Rp'),
            'VND' => array('name' => 'Vietnamese Dong', 'symbol' => '₫'),
        );
    }
}
