<?php
/**
 * ZCA Inventory License Manager
 * 
 * Handles license activation, validation, and management
 * Integrates with API Key Manager at https://api.benjomabrasado.space
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZC_License {
    private static $instance = null;
    private static $api_url = 'https://api.benjomabrasado.space/api';
    private static $license_option = 'zc_license_key';
    private static $license_data_option = 'zc_license_data';
    private static $site_url;

    /**
     * Get singleton instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        self::$site_url = get_site_url();
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_license_menu'));
        add_action('admin_init', array($this, 'register_license_settings'));
        add_action('admin_notices', array($this, 'show_license_notices'));
        
        // Check license daily
        if (!wp_next_scheduled('zc_check_license')) {
            wp_schedule_event(time(), 'daily', 'zc_check_license');
        }
        add_action('zc_check_license', array($this, 'check_license_status'));
        
        // AJAX handlers
        add_action('wp_ajax_zc_activate_license', array($this, 'activate_license'));
        add_action('wp_ajax_zc_check_license', array($this, 'check_license_ajax'));
    }

    /**
     * Initialize - static method for main plugin to call
     */
    public static function init() {
        return self::instance();
    }

    /**
     * Add license menu to WordPress admin
     */
    public function add_license_menu() {
        add_submenu_page(
            'options-general.php',
            'ZCA Inventory License',
            'ZCA License',
            'manage_options',
            'zca-license',
            array($this, 'render_license_page')
        );
    }

    /**
     * Register license settings
     */
    public function register_license_settings() {
        register_setting('zca_license_settings', self::$license_option);
    }

    /**
     * Render license management page
     */
    public function render_license_page() {
        $license_key = get_option(self::$license_option, '');
        $license_data = get_transient(self::$license_data_option);
        ?>
        <div class="wrap">
            <h1>ZCA Inventory License</h1>
            
            <div class="card">
                <h2>License Activation</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('zca_license_settings'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="<?php echo self::$license_option; ?>">License Key</label>
                            </th>
                            <td>
                                <input type="text"
                                       id="<?php echo self::$license_option; ?>"
                                       name="<?php echo self::$license_option; ?>"
                                       value="<?php echo esc_attr($license_key); ?>"
                                       class="regular-text"
                                       placeholder="ZCA-XXXX-XXXX-XXXX-XXXX">
                                <p class="description">Enter your ZCA Inventory license key</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Save License Key'); ?>
                </form>
            </div>

            <?php if ($license_key): ?>
                <div class="card" style="margin-top: 20px;">
                    <h2>License Status</h2>
                    
                    <div style="margin-bottom: 15px;">
                        <button type="button" 
                                class="button button-primary" 
                                id="zca-activate-license">
                            Activate This Site
                        </button>
                        <button type="button" 
                                class="button" 
                                id="zca-check-license">
                            Check License Status
                        </button>
                    </div>
                    
                    <div id="zca-license-status">
                        <?php if ($license_data): ?>
                            <table class="widefat">
                                <tr>
                                    <th style="width: 200px;">Status</th>
                                    <td>
                                        <?php if ($license_data['valid']): ?>
                                            <span style="color: green; font-weight: bold;">✓ Active</span>
                                        <?php else: ?>
                                            <span style="color: red; font-weight: bold;">✗ Invalid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (isset($license_data['data']['isPaid'])): ?>
                                    <tr>
                                        <th>License Type</th>
                                        <td>
                                            <?php echo $license_data['data']['isPaid'] ? 'Paid License' : 'Trial License'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (isset($license_data['data']['daysRemaining']) && !$license_data['data']['isPaid']): ?>
                                    <tr>
                                        <th>Trial Days Remaining</th>
                                        <td>
                                            <strong><?php echo $license_data['data']['daysRemaining']; ?> days</strong>
                                            <?php if ($license_data['data']['daysRemaining'] <= 3): ?>
                                                <span style="color: orange;"> - Trial expiring soon!</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Trial Expires</th>
                                        <td><?php echo date('F j, Y', strtotime($license_data['data']['trialEndDate'])); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Site URL</th>
                                    <td><?php echo esc_html(self::$site_url); ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <p style="color: #666;">Click "Activate This Site" to activate your license.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#zca-activate-license').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('Activating...');
                
                $.post(ajaxurl, {
                    action: 'zc_activate_license',
                    nonce: '<?php echo wp_create_nonce('zca_license_nonce'); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('Activate This Site');
                    
                    if (response.success) {
                        alert('✓ License activated successfully! ' + response.data.message);
                        location.reload();
                    } else {
                        alert('✗ Activation failed: ' + response.data.message);
                    }
                }).fail(function() {
                    btn.prop('disabled', false).text('Activate This Site');
                    alert('✗ Connection error. Please try again.');
                });
            });

            $('#zca-check-license').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('Checking...');
                
                $.post(ajaxurl, {
                    action: 'zc_check_license',
                    nonce: '<?php echo wp_create_nonce('zca_license_nonce'); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('Check License Status');
                    location.reload();
                }).fail(function() {
                    btn.prop('disabled', false).text('Check License Status');
                    alert('✗ Connection error. Please try again.');
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX: Activate license for this site
     */
    public function activate_license() {
        check_ajax_referer('zca_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $license_key = get_option(self::$license_option);

        if (!$license_key) {
            wp_send_json_error(array('message' => 'No license key found. Please enter your license key first.'));
        }

        $response = wp_remote_post(self::$api_url . '/activate', array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'key' => $license_key,
                'siteUrl' => self::$site_url
            )),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($body['success']) {
            set_transient(self::$license_data_option, $body, DAY_IN_SECONDS);
            wp_send_json_success($body);
        } else {
            wp_send_json_error($body);
        }
    }

    /**
     * AJAX: Check license status
     */
    public function check_license_ajax() {
        check_ajax_referer('zca_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $result = $this->check_license_status();
        wp_send_json($result ? array('success' => true) : array('success' => false));
    }

    /**
     * Check license status with API
     */
    public function check_license_status() {
        $license_key = get_option(self::$license_option);
        
        if (!$license_key) {
            return false;
        }

        $response = wp_remote_post(self::$api_url . '/validate', array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'key' => $license_key,
                'siteUrl' => self::$site_url
            )),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($body['success'] && $body['valid']) {
            set_transient(self::$license_data_option, $body, DAY_IN_SECONDS);
            return true;
        } else {
            delete_transient(self::$license_data_option);
            return false;
        }
    }

    /**
     * Check if license is valid
     */
    public static function is_valid() {
        $license_data = get_transient(self::$license_data_option);
        return $license_data && isset($license_data['valid']) && $license_data['valid'];
    }

    /**
     * Get license data
     */
    public static function get_license_data() {
        return get_transient(self::$license_data_option);
    }

    /**
     * Show admin notices for license status
     */
    public function show_license_notices() {
        $license_key = get_option(self::$license_option);
        $license_data = get_transient(self::$license_data_option);

        // No license key entered
        if (!$license_key) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>ZCA Inventory:</strong> Please enter your license key in 
                    <a href="<?php echo admin_url('options-general.php?page=zca-license'); ?>">License Settings</a>
                </p>
            </div>
            <?php
            return;
        }

        // License invalid or not activated
        if (!$license_data || !$license_data['valid']) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong>ZCA Inventory:</strong> Your license is invalid or not activated. 
                    <a href="<?php echo admin_url('options-general.php?page=zca-license'); ?>">Activate your license</a>
                </p>
            </div>
            <?php
            return;
        }

        // Trial expiring soon
        if (isset($license_data['data']['isPaid']) && !$license_data['data']['isPaid']) {
            $days_remaining = $license_data['data']['daysRemaining'];
            
            if ($days_remaining <= 3 && $days_remaining > 0) {
                ?>
                <div class="notice notice-warning">
                    <p>
                        <strong>ZCA Inventory:</strong> Your trial expires in <?php echo $days_remaining; ?> days. 
                        Please contact support to upgrade to a paid license.
                    </p>
                </div>
                <?php
            } elseif ($days_remaining <= 0) {
                ?>
                <div class="notice notice-error">
                    <p>
                        <strong>ZCA Inventory:</strong> Your trial has expired. 
                        Please contact support to upgrade to a paid license.
                    </p>
                </div>
                <?php
            }
        }
    }
}
