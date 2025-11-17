<?php
/**
 * Plugin Name: Z Inventory
 * Plugin URI: https://example.com/z-inventory
 * Description: A complete inventory management system with POS for WordPress with Owner and Cashier roles
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: z-inventory
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ZC_INVENTORY_VERSION', '1.0.0');
define('ZC_INVENTORY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZC_INVENTORY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZC_INVENTORY_PLUGIN_FILE', __FILE__);

// Include required files
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-license.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-database.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-roles.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-settings.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-auth.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-products.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-sales.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-cashiers.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-inventory.php';
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zc-register.php';

/**
 * Main Z Inventory Class
 */
class ZC_Inventory_Main {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));

        // Load assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        // Register rewrite rules
        add_action('init', array($this, 'register_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }

    public function activate() {
        // Create database tables
        ZC_Database::create_tables();

        // Create custom roles
        ZC_Roles::create_roles();

        // Flush rewrite rules
        $this->register_rewrite_rules();
        flush_rewrite_rules();
    }

    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function init() {
        // Initialize license first
        ZC_License::init();

        // Initialize components
        ZC_Settings::init();
        ZC_Auth::init();
        ZC_Products::init();
        ZC_Sales::init();
        ZC_Cashiers::init();
        ZC_Inventory_Manager::init();
        ZC_Register::init();
    }

    public function enqueue_assets() {
        // Only load on plugin pages
        if ($this->is_plugin_page()) {
            // Bootstrap CSS
            wp_enqueue_style(
                'bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                array(),
                '5.3.0'
            );

            // Bootstrap Icons
            wp_enqueue_style(
                'bootstrap-icons',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
                array(),
                '1.10.0'
            );

            // Custom CSS
            wp_enqueue_style(
                'zc-inventory-style',
                ZC_INVENTORY_PLUGIN_URL . 'assets/css/style.css',
                array('bootstrap'),
                ZC_INVENTORY_VERSION
            );

            // Bootstrap JS
            wp_enqueue_script(
                'bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                array('jquery'),
                '5.3.0',
                true
            );

            // Custom JS
            wp_enqueue_script(
                'zc-inventory-script',
                ZC_INVENTORY_PLUGIN_URL . 'assets/js/script.js',
                array('jquery', 'bootstrap'),
                ZC_INVENTORY_VERSION,
                true
            );

            // Localize script
            wp_localize_script('zc-inventory-script', 'zcInventory', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zc_inventory_nonce'),
                'currency' => array(
                    'symbol' => ZC_Settings::get_currency_symbol(),
                    'code' => ZC_Settings::get_currency_code(),
                    'position' => ZC_Settings::get_currency_position()
                )
            ));
        }
    }

    private function is_plugin_page() {
        global $wp_query;

        // Check if we're on a plugin page
        if (isset($wp_query->query_vars['zc_inventory'])) {
            return true;
        }

        // Check for admin pages
        if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'zc-inventory') === 0) {
            return true;
        }

        return false;
    }

    public function register_rewrite_rules() {
        // Login page
        add_rewrite_rule(
            '^zc-inventory/login/?$',
            'index.php?zc_inventory=login',
            'top'
        );

        // Dashboard
        add_rewrite_rule(
            '^zc-inventory/dashboard/?$',
            'index.php?zc_inventory=dashboard',
            'top'
        );

        // Owner pages
        add_rewrite_rule(
            '^zc-inventory/cashiers/?$',
            'index.php?zc_inventory=cashiers',
            'top'
        );

        add_rewrite_rule(
            '^zc-inventory/products/?$',
            'index.php?zc_inventory=products',
            'top'
        );

        add_rewrite_rule(
            '^zc-inventory/sales-report/?$',
            'index.php?zc_inventory=sales-report',
            'top'
        );

        add_rewrite_rule(
            '^zc-inventory/inventory/?$',
            'index.php?zc_inventory=inventory',
            'top'
        );

        add_rewrite_rule(
            '^zc-inventory/settings/?$',
            'index.php?zc_inventory=settings',
            'top'
        );

        // Cashier pages
        add_rewrite_rule(
            '^zc-inventory/pos/?$',
            'index.php?zc_inventory=pos',
            'top'
        );

        // Logout
        add_rewrite_rule(
            '^zc-inventory/logout/?$',
            'index.php?zc_inventory=logout',
            'top'
        );
    }

    public function add_query_vars($vars) {
        $vars[] = 'zc_inventory';
        return $vars;
    }

    public function template_redirect() {
        global $wp_query;

        if (isset($wp_query->query_vars['zc_inventory'])) {
            $page = $wp_query->query_vars['zc_inventory'];

            // Handle logout
            if ($page === 'logout') {
                ZC_Auth::logout();
                return;
            }

            // Pages that don't require license validation
            $public_pages = array('login');

            // Check license for authenticated pages
            if (!in_array($page, $public_pages)) {
                if (!ZC_License::is_valid()) {
                    // Redirect to WordPress admin to show license notice
                    wp_redirect(admin_url('options-general.php?page=zca-license'));
                    exit;
                }
            }

            // Load appropriate template
            $template_file = ZC_INVENTORY_PLUGIN_DIR . 'templates/' . $page . '.php';

            if (file_exists($template_file)) {
                include $template_file;
                exit;
            } else {
                // 404
                $wp_query->set_404();
                status_header(404);
            }
        }
    }
}

// Initialize the plugin
function zc_inventory() {
    return ZC_Inventory_Main::get_instance();
}

// Start the plugin
zc_inventory();
